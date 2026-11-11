<?php
declare(strict_types=1);
function listarLibros(PDO $db): array {
    $q = $db->query('CALL sp_libro_listar()');
    $rows = $q->fetchAll(); $q->closeCursor();
    return $rows;
}
function obtenerLibro(PDO $db, int $id): ?array {
    $q = $db->prepare('CALL sp_libro_obtener(?)');
    $q->execute([$id]); $row = $q->fetch(); $q->closeCursor();
    return $row ?: null;
}