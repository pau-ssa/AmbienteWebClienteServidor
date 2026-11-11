<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\HttpException;
use App\Core\View;
use App\Models\Categoria;
use App\Models\Documento;
use App\Models\Libro;
use App\Services\ArchivoStorage;
use DomainException;
use PDO;

/**
 * Controlador web: coordina solicitudes, validaciones y transacciones.
 * MySQL almacena los metadatos y la cola de limpieza; PHP almacena los archivos.
 */
final class LibroController
{
    private Libro $libros;
    private Categoria $categorias;
    private Documento $documentos;

    public function __construct(private PDO $db)
    {
        $this->libros = new Libro($db);
        $this->categorias = new Categoria($db);
        $this->documentos = new Documento($db);
    }

    public function catalogo(): void
    {
        $texto = is_string($_GET['q'] ?? null) ? trim($_GET['q']) : '';
        $texto = cortar_utf8($texto, 0, 200);
        $categoria = filter_var($_GET['categoria'] ?? 0, FILTER_VALIDATE_INT);
        $categoria = ($categoria === false || $categoria < 0) ? 0 : $categoria;
        View::render('libros/catalogo', [
            'libros' => $this->libros->buscar($texto, $categoria),
            'q' => $texto,
            'categoria' => $categoria,
            'categorias' => $this->categorias->listar(),
        ], 'Catálogo');
    }

    public function detalle(): void
    {
        $libro = $this->libros->obtener(identificador());
        if (!$libro) {
            throw new HttpException(404, 'El libro solicitado no existe.');
        }
        View::render('libros/detalle', ['libro' => $libro], $libro['titulo']);
    }

    public function administrar(): void
    {
        \App\Core\Auth::requerirAdmin();
        View::render('libros/administrar', ['libros' => $this->libros->listar()], 'Administración');
    }

    private function formulario(array $datos, array $categorias, ?int $id, array $errores = []): void
    {
        if (!array_key_exists('pdf_original', $datos)) {
            $actual = $id !== null ? $this->libros->obtener($id) : null;
            $datos['pdf_original'] = $actual['pdf_original'] ?? null;
        }
        View::render('libros/formulario', compact('datos', 'categorias', 'id', 'errores'),
            $id === null ? 'Nuevo libro' : 'Editar libro');
    }

    public function crear(): void
    {
        \App\Core\Auth::requerirAdmin();
        $this->formulario(['titulo' => '', 'autor' => '', 'resumen' => '', 'categoria_id' => ''],
            $this->categorias->listar(), null);
    }

    public function guardar(): void
    {
        \App\Core\Auth::requerirAdmin();
        csrf_comprobar();
        $datos = datos_libro_post();
        $categorias = $this->categorias->listar();
        $errores = validar_libro($datos, $categorias);
        if ($errores) {
            http_response_code(422);
            $this->formulario($datos, $categorias, null, $errores);
            return;
        }

        try {
            // Se mueve el archivo antes de la transacción: si la BD falla, se compensa.
            $archivo = ArchivoStorage::recibir($_FILES['pdf'] ?? null, true);
        } catch (DomainException $error) {
            http_response_code(422);
            $this->formulario($datos, $categorias, null, [$error->getMessage()]);
            return;
        }

        try {
            $this->db->beginTransaction();
            $id = $this->libros->crear($datos);
            if ($id < 1) {
                throw new \RuntimeException('MySQL no devolvió el nuevo ID.');
            }
            $this->documentos->asignar($id, $archivo);
            $this->db->commit();
        } catch (\Throwable $error) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            if (!ArchivoStorage::eliminar($archivo['nombre_interno'])) {
                error_log('Archivo sin registro tras revertir la inserción: ' . $archivo['nombre_interno']);
            }
            throw $error;
        }

        aviso('Libro registrado correctamente.');
        redirigir('administrar');
    }

    public function editar(): void
    {
        \App\Core\Auth::requerirAdmin();
        $id = identificador();
        $libro = $this->libros->obtener($id);
        if (!$libro) {
            throw new HttpException(404, 'No existe el libro que desea editar.');
        }
        $this->formulario($libro, $this->categorias->listar(), $id);
    }

    public function actualizar(): void
    {
        \App\Core\Auth::requerirAdmin();
        csrf_comprobar();
        $id = identificador();
        $actual = $this->libros->obtener($id);
        if (!$actual) {
            throw new HttpException(404, 'No existe el libro que desea actualizar.');
        }
        $datos = datos_libro_post();
        $categorias = $this->categorias->listar();
        $errores = validar_libro($datos, $categorias);
        if ($errores) {
            http_response_code(422);
            $this->formulario($datos, $categorias, $id, $errores);
            return;
        }

        // Antes de mover el nuevo PDF, obtener el anterior desde MySQL.
        $anterior = $this->documentos->obtener($id);
        try {
            $nuevo = ArchivoStorage::recibir($_FILES['pdf'] ?? null, false);
        } catch (DomainException $error) {
            http_response_code(422);
            $this->formulario($datos, $categorias, $id, [$error->getMessage()]);
            return;
        }

        try {
            $this->db->beginTransaction();
            $this->libros->actualizar($id, $datos);
            if ($nuevo !== null) {
                $this->documentos->asignar($id, $nuevo);
                if ($anterior !== null) {
                    // Si la transacción revierte, también desaparece el trabajo pendiente.
                    $this->documentos->programarEliminacion($anterior['nombre_interno']);
                }
            }
            $this->db->commit();
        } catch (\Throwable $error) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            if ($nuevo !== null && !ArchivoStorage::eliminar($nuevo['nombre_interno'])) {
                error_log('Archivo sin referencia al revertir actualización: ' . $nuevo['nombre_interno']);
            }
            throw $error;
        }

        $limpio = $nuevo === null || $anterior === null ||
            $this->limpiarPendiente($anterior['nombre_interno']);
        aviso($limpio ? 'Libro actualizado correctamente.' :
            'Libro actualizado. Quedó un PDF anterior pendiente de limpieza.');
        redirigir('administrar');
    }

    public function eliminar(): void
    {
        \App\Core\Auth::requerirAdmin();
        csrf_comprobar();
        $id = identificador();
        if (!$this->libros->obtener($id)) {
            throw new HttpException(404, 'No existe el libro que desea eliminar.');
        }
        $documento = $this->documentos->obtener($id);
        try {
            $this->db->beginTransaction();
            if ($documento !== null) {
                $this->documentos->programarEliminacion($documento['nombre_interno']);
            }
            $this->libros->eliminar($id);
            $this->db->commit();
        } catch (\Throwable $error) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $error;
        }

        $limpio = $documento === null || $this->limpiarPendiente($documento['nombre_interno']);
        aviso($limpio ? 'Libro eliminado correctamente.' :
            'Libro eliminado. El PDF quedó pendiente de limpieza.');
        redirigir('administrar');
    }

    /** Limpieza posterior al COMMIT: el pendiente está guardado antes en MySQL. */
    private function limpiarPendiente(string $interno): bool
    {
        try {
            if (!ArchivoStorage::eliminar($interno)) {
                return false;
            }
            $this->documentos->completarEliminacion($interno);
            return true;
        } catch (\Throwable $error) {
            error_log('No se completó la limpieza pendiente: ' . $interno . ': ' . $error->getMessage());
            return false;
        }
    }
}
