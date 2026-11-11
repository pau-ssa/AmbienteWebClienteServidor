<?php
declare(strict_types=1);
function consultarTodos(PDO $db, string $sp, array $args=[]): array {
    $marks = implode(',',array_fill(0,count($args),'?'));
    $q = $db->prepare('CALL '.$sp.'('.$marks.')');
    $q->execute($args);$rows = $q->fetchAll();$q->closeCursor();return $rows;
}
function consultarUno(PDO $db, string $sp, array $args=[]): ?array {
    $rows = consultarTodos($db,$sp,$args);return $rows[0]??null;
}
function listarLibros(PDO $db): array {return consultarTodos($db,'sp_libro_listar');}
function obtenerLibro(PDO $db,int $id): ?array {return consultarUno($db,'sp_libro_obtener',[$id]);}
function listarCategorias(PDO $db): array {return consultarTodos($db,'sp_categoria_listar');}
function crearLibro(PDO $db,array $datos): int {
    $r=consultarUno($db,'sp_libro_crear',[$datos['titulo'],$datos['autor'],$datos['resumen'],
        $datos['categoria_id']]);return (int)($r['id']??0);
}
function actualizarLibro(PDO $db,int $id,array $datos): bool {
    $r=consultarUno($db,'sp_libro_actualizar',[$id,$datos['titulo'],$datos['autor'],
        $datos['resumen'],$datos['categoria_id']]);return (int)($r['afectados']??0)>0;
}
function eliminarLibro(PDO $db,int $id): bool {
    $r=consultarUno($db,'sp_libro_eliminar',[$id]);return (int)($r['afectados']??0)>0;
}
