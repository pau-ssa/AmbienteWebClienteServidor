<?php
declare(strict_types=1);
namespace App\Models;
use App\Core\Procedimientos;
use PDO;
final class Categoria {
    public function __construct(private PDO $db) {}
    public function listar(): array {return Procedimientos::filas($this->db,'sp_categoria_listar');}
    public function obtener(int $id): ?array {return Procedimientos::uno($this->db,
        'sp_categoria_obtener',[$id]);}
    public function crear(string $nombre,string $descripcion): int {
        return (int)(Procedimientos::uno($this->db,'sp_categoria_crear',[$nombre,
            $descripcion])['id']??0);
    }
    public function actualizar(int $id,string $nombre,string $descripcion): void {
        Procedimientos::uno($this->db,'sp_categoria_actualizar',[$id,$nombre,$descripcion]);
    }
    public function eliminar(int $id): void {Procedimientos::uno($this->db,'sp_categoria_eliminar',
        [$id]);}
}
