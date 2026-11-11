<?php
declare(strict_types=1);
namespace App\Models;
use App\Core\Procedimientos;
use PDO;
final class Categoria {
    public function __construct(private PDO $db) {}
    public function listar(): array {return Procedimientos::filas($this->db,'sp_categoria_listar');}
}