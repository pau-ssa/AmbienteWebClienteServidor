<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Procedimientos;
use PDO;

/** El modelo representa operaciones de datos; nunca genera HTML. */
final class Libro
{
    public function __construct(private PDO $db)
    {
    }

    public function listar(): array
    {
        return Procedimientos::filas($this->db, 'sp_libro_listar');
    }

    public function obtener(int $id): ?array
    {
        return Procedimientos::uno($this->db, 'sp_libro_obtener', [$id]);
    }

    public function crear(array $datos): int
    {
        $resultado = Procedimientos::uno($this->db, 'sp_libro_crear', [
            $datos['titulo'], $datos['autor'], $datos['resumen'], (int) $datos['categoria_id'],
        ]);
        return (int) ($resultado['id'] ?? 0);
    }

    public function actualizar(int $id, array $datos): void
    {
        Procedimientos::uno($this->db, 'sp_libro_actualizar', [
            $id, $datos['titulo'], $datos['autor'], $datos['resumen'], (int) $datos['categoria_id'],
        ]);
    }

    public function eliminar(int $id): void
    {
        Procedimientos::uno($this->db, 'sp_libro_eliminar', [$id]);
    }

    public function buscar(string $texto, int $categoria = 0): array
    {
        return Procedimientos::filas($this->db, 'sp_libro_buscar', [$texto, $categoria]);
    }
}
