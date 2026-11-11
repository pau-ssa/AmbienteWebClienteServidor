<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Procedimientos;
use PDO;

/** Metadatos, cola transaccional de limpieza y consultas de documentos. */
final class Documento
{
    public function __construct(private PDO $db)
    {
    }

    public function obtener(int $libroId): ?array
    {
        return Procedimientos::uno($this->db, 'sp_documento_obtener', [$libroId]);
    }

    public function asignar(int $libroId, array $archivo): void
    {
        Procedimientos::uno($this->db, 'sp_documento_asignar', [
            $libroId,
            $archivo['nombre_original'],
            $archivo['nombre_interno'],
            'application/pdf',
            $archivo['bytes_archivo'],
        ]);
    }

    public function nombres(): array
    {
        return Procedimientos::filas($this->db, 'sp_documento_nombres');
    }

    /** Debe llamarse dentro de la misma transacción que altera el documento. */
    public function programarEliminacion(string $interno): void
    {
        Procedimientos::uno($this->db, 'sp_archivo_pendiente_agregar', [$interno]);
    }

    public function pendientes(): array
    {
        return Procedimientos::filas($this->db, 'sp_archivo_pendiente_listar');
    }

    public function completarEliminacion(string $interno): void
    {
        Procedimientos::uno($this->db, 'sp_archivo_pendiente_quitar', [$interno]);
    }
}
