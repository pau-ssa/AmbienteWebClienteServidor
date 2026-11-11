<?php
declare(strict_types=1);
namespace App\Models;
use App\Core\Procedimientos;
use PDO;
final class Usuario {
    public function __construct(private PDO $db) {}
    public function buscarPorCorreo(string $correo): ?array {
        return Procedimientos::uno($this->db,'sp_usuario_buscar_por_correo',[$correo]);
    }
    public function crear(string $nombre,string $correo,string $hash,string $rol): int {
        return (int)(Procedimientos::uno($this->db,'sp_usuario_crear',[$nombre,$correo,$hash,
            $rol])['id']??0);
    }
}
