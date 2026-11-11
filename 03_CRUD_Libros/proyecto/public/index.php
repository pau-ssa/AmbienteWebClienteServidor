<?php
declare(strict_types=1);
require __DIR__ . '/../lib/Conexion.php';
require __DIR__ . '/../lib/RepositorioLibros.php';
require __DIR__ . '/../lib/Seguridad.php';
const HAY_ADMIN=true;


/** Longitudes y recortes en caracteres; iconv sirve de respaldo si falta mbstring. */
function largo_utf8(string $texto): int
{
    if (function_exists('mb_strlen')) {
        return mb_strlen($texto, 'UTF-8');
    }
    $n = iconv_strlen($texto, 'UTF-8');
    return $n === false ? strlen($texto) : $n;
}

function cortar_utf8(string $texto, int $inicio, int $cantidad): string
{
    if (function_exists('mb_substr')) {
        return mb_substr($texto, $inicio, $cantidad, 'UTF-8');
    }
    $trozo = iconv_substr($texto, $inicio, $cantidad, 'UTF-8');
    return $trozo === false ? substr($texto, $inicio, $cantidad) : $trozo;
}

function e(?string $s): string
{
    return htmlspecialchars($s ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function iniciarPagina(string $titulo): void
{
    $titulo = e($titulo);
    echo '<!doctype html>'
        . '<html lang="es"><head><meta charset="utf-8">'
        . '<meta name="viewport" content="width=device-width,initial-scale=1">'
        . '<title>' . $titulo . ' | Biblioteca Virtual</title>'
        . '<link rel="stylesheet" href="assets/css/ort-fidelitas.css">'
        . '<link rel="stylesheet" href="assets/css/biblioteca.css">'
        . '</head><body>';

    echo '<a class="skip-link" href="#contenido">Saltar al contenido</a>'
        . '<header class="site-header"><div class="container navbar">'
        . '<a class="marca" href="index.php">Biblioteca Virtual Fidélitas</a>'
        . '<nav aria-label="Principal">'
        . '<a class="nav-link" href="index.php">Catálogo</a>';

    if (defined('HAY_ADMIN') && HAY_ADMIN) {
        echo '<a class="nav-link" href="index.php?accion=administrar">'
            . 'Administrar libros</a>';
    }

    echo '</nav></div></header>'
        . '<div class="panel-hero"><div class="container">'
        . '<p class="course-kicker">SC-502 · Laboratorio de evolución</p>'
        . '<h1>Biblioteca Virtual</h1>'
        . '<p>Libros académicos organizados para consultar y aprender.</p>'
        . '</div></div><main id="contenido" class="container">';
}

function terminarPagina(): void
{
    echo '</main><footer class="pie-local"><div class="container">'
        . '<p>SC-502 · Prototipo didáctico · Universidad Fidélitas</p>'
        . '</div></footer></body></html>';
}

function pintarLibros(array $libros): void
{
    if (!$libros) {
        echo '<p class="alert alert-info">No hay libros disponibles.</p>';
        return;
    }

    echo '<section class="catalogo-grid" aria-label="Libros disponibles">';
    foreach ($libros as $libro) {
        $id = (int) $libro['id'];
        $enlace = 'index.php?accion=detalle&amp;id=' . $id;
        echo '<article class="libro-card">'
            . '<span class="libro-icono" aria-hidden="true">PDF</span>'
            . '<h3>' . e($libro['titulo']) . '</h3>'
            . '<p><strong>Autor:</strong> ' . e($libro['autor']) . '</p>'
            . '<p><span class="etiqueta">' . e($libro['categoria']) . '</span></p>'
            . '<p>' . e(mbResumen($libro['resumen'])) . '</p>'
            . '<div class="acciones"><a class="btn btn-primary" href="' . $enlace . '">'
            . 'Ver detalle</a></div></article>';
    }
    echo '</section>';
}

function mbResumen(string $texto): string
{
    return largo_utf8($texto) > 175
        ? cortar_utf8($texto, 0, 172) . '...'
        : $texto;
}

function pintarDetalle(array $libro): void
{
    echo '<p><a href="index.php">← Volver al catálogo</a></p>'
        . '<article><span class="etiqueta">' . e($libro['categoria']) . '</span>'
        . '<h2>' . e($libro['titulo']) . '</h2>'
        . '<p><strong>Autor:</strong> ' . e($libro['autor']) . '</p>'
        . '<h3>Resumen</h3>'
        . '<p class="resumen">' . nl2br(e($libro['resumen'])) . '</p>'
        . '</article>';
}

function mostrarFormulario(
    array $datos,
    array $categorias,
    ?int $id = null,
    array $errores = []
): void {
    $titulo = $id === null ? 'Registrar libro' : 'Editar libro';
    $accion = $id === null
        ? 'index.php?accion=guardar'
        : 'index.php?accion=actualizar&amp;id=' . $id;

    echo '<p><a href="index.php?accion=administrar">← Volver a administración</a></p>'
        . '<h2>' . $titulo . '</h2>';

    foreach ($errores as $error) {
        echo '<p class="error">' . e($error) . '</p>';
    }

    echo '<form class="formulario panel" method="post" action="' . $accion . '">'
        . '<input type="hidden" name="_csrf" value="' . e(csrf()) . '">';

    foreach (['titulo' => 'Título', 'autor' => 'Autor'] as $campo => $etiqueta) {
        $maximo = $campo === 'titulo' ? 180 : 160;
        $valor = e($datos[$campo] ?? '');
        echo '<div class="campo"><label for="' . $campo . '">' . $etiqueta . '</label>'
            . '<input id="' . $campo . '" name="' . $campo . '" maxlength="' . $maximo
            . '" required value="' . $valor . '"></div>';
    }

    echo '<div class="campo"><label for="resumen">Resumen</label>'
        . '<textarea id="resumen" name="resumen" maxlength="4000" required>'
        . e($datos['resumen'] ?? '') . '</textarea></div>';

    echo '<div class="campo"><label for="categoria_id">Categoría</label>'
        . '<select id="categoria_id" name="categoria_id" required>'
        . '<option value="">Seleccione...</option>';

    foreach ($categorias as $categoria) {
        $idCategoria = (int) $categoria['id'];
        $seleccionada = (int) ($datos['categoria_id'] ?? 0) === $idCategoria;
        echo '<option value="' . $idCategoria . '" '
            . ($seleccionada ? 'selected' : '') . '>'
            . e($categoria['nombre']) . '</option>';
    }

    echo '</select></div><div class="controles">'
        . '<button class="btn btn-primary" type="submit">Guardar</button>'
        . '<a class="btn btn-outline" href="index.php?accion=administrar">Cancelar</a>'
        . '</div></form>';
}

function mostrarAdministracion(array $libros): void
{
    echo '<div class="barra"><h2>Administración de libros</h2>'
        . '<a class="btn btn-primary" href="index.php?accion=crear">Nuevo libro</a></div>'
        . '<p class="alert alert-warning">Prototipo didáctico local sin autenticación: '
        . 'no publicar en Internet.</p>'
        . '<div class="tabla-externa"><table><thead><tr>'
        . '<th>Título</th><th>Autor</th><th>Categoría</th><th>Operaciones</th>'
        . '</tr></thead><tbody>';

    foreach ($libros as $libro) {
        $id = (int) $libro['id'];
        echo '<tr><td>' . e($libro['titulo']) . '</td>'
            . '<td>' . e($libro['autor']) . '</td>'
            . '<td>' . e($libro['categoria']) . '</td><td>'
            . '<div class="acciones-tabla">'
            . '<a class="btn btn-sm btn-outline" '
            . 'href="index.php?accion=editar&amp;id=' . $id . '">Editar</a>'
            . '<form method="post" action="index.php?accion=eliminar&amp;id=' . $id . '" '
            . 'onsubmit="return confirm(&quot;¿Eliminar el libro?&quot;)">'
            . '<input type="hidden" name="_csrf" value="' . e(csrf()) . '">'
            . '<button class="boton-peligro" type="submit">Eliminar</button>'
            . '</form></div></td></tr>';
    }
    echo '</tbody></table></div>';
}

iniciarSesion();
$accion = (string) ($_GET['accion'] ?? 'catalogo');
$metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (isset($_GET['id']) && (!$id || $id < 1)) {
    http_response_code(400);
    iniciarPagina('Error');
    echo '<p class="error">Identificador inválido.</p>';
    terminarPagina();
    exit;
}

try {
    $db = conexion();
    $permitidas = [
        'catalogo', 'detalle', 'administrar', 'crear', 'guardar',
        'editar', 'actualizar', 'eliminar',
    ];
    if (!in_array($accion, $permitidas, true)) {
        http_response_code(404);
        iniciarPagina('No encontrado');
        echo '<p class="error">Ruta inexistente.</p>';
        terminarPagina();
        exit;
    }

    $mutaciones = ['guardar', 'actualizar', 'eliminar'];
    if (in_array($accion, $mutaciones, true) && $metodo !== 'POST') {
        http_response_code(405);
        header('Allow: POST');
        exit('Método no permitido.');
    }
    if (!in_array($accion, $mutaciones, true) && $metodo !== 'GET') {
        http_response_code(405);
        header('Allow: GET');
        exit('Método no permitido.');
    }
    if (in_array($accion, $mutaciones, true)) {
        validarCsrf();
    }

    if (in_array($accion, ['editar', 'actualizar', 'eliminar', 'detalle'], true) && !$id) {
        http_response_code(400);
        exit('Falta un ID válido.');
    }

    if (in_array($accion, ['guardar', 'actualizar'], true)) {
        $datos = [
            'titulo' => trim((string) ($_POST['titulo'] ?? '')),
            'autor' => trim((string) ($_POST['autor'] ?? '')),
            'resumen' => trim((string) ($_POST['resumen'] ?? '')),
            'categoria_id' => $_POST['categoria_id'] ?? '',
        ];
        $errores = validarLibro($datos);
        $cats = listarCategorias($db);
        $categoriaExiste = in_array(
            (int) $datos['categoria_id'],
            array_map(fn($c) => (int) $c['id'], $cats),
            true
        );
        if (!$categoriaExiste) {
            $errores[] = 'La categoría no existe.';
        }
        if ($errores) {
            http_response_code(422);
            iniciarPagina('Revisar formulario');
            mostrarFormulario($datos, $cats, $accion === 'actualizar' ? $id : null, $errores);
            terminarPagina();
            exit;
        }

        if ($accion === 'guardar') {
            crearLibro($db, $datos);
            avisar('Libro registrado correctamente.');
        } else {
            if (!obtenerLibro($db, $id)) {
                http_response_code(404);
                exit('Libro inexistente.');
            }
            actualizarLibro($db, $id, $datos);
            avisar('Libro actualizado correctamente.');
        }
        header('Location: index.php?accion=administrar', true, 303);
        exit;
    }

    if ($accion === 'eliminar') {
        eliminarLibro($db, $id);
        avisar('Operación de eliminación procesada.');
        header('Location: index.php?accion=administrar', true, 303);
        exit;
    }

    $libro = in_array($accion, ['editar', 'detalle'], true)
        ? obtenerLibro($db, $id)
        : null;
    if (in_array($accion, ['editar', 'detalle'], true) && !$libro) {
        http_response_code(404);
        iniciarPagina('No encontrado');
        echo '<p class="error">Libro inexistente.</p>';
        terminarPagina();
        exit;
    }

    $lista = in_array($accion, ['catalogo', 'administrar'], true)
        ? listarLibros($db)
        : [];
    $cats = in_array($accion, ['crear', 'editar'], true)
        ? listarCategorias($db)
        : [];

    iniciarPagina('Biblioteca');
    $aviso = consumirAviso();
    if ($aviso) {
        echo '<p class="ok">' . e($aviso) . '</p>';
    }

    if ($accion === 'detalle') {
        pintarDetalle($libro);
    } elseif ($accion === 'crear') {
        mostrarFormulario(
            ['titulo' => '', 'autor' => '', 'resumen' => '', 'categoria_id' => ''],
            $cats
        );
    } elseif ($accion === 'editar') {
        mostrarFormulario($libro, $cats, $id);
    } elseif ($accion === 'administrar') {
        mostrarAdministracion($lista);
    } else {
        echo '<div class="barra"><h2>Catálogo desde MySQL</h2>'
            . '<p>CRUD disponible en Administración.</p></div>';
        pintarLibros($lista);
    }
    terminarPagina();
} catch (PDOException $ex) {
    error_log($ex->getMessage());
    http_response_code(503);
    iniciarPagina('Error de base de datos');
    echo '<p class="error">La operación no pudo completarse. '
        . 'Revise MySQL y los datos enviados.</p>';
    terminarPagina();
}
