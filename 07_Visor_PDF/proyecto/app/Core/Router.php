<?php
declare(strict_types=1);
namespace App\Core;
use App\Controllers\LibroController;
use App\Controllers\CategoriaController;
use App\Controllers\DocumentoController;

use PDO;
final class Router {
    public function __construct(private PDO $db) {}
    public function ejecutar(): void {
        $accion=$_GET['accion']??'catalogo';
        if(!is_string($accion) || !preg_match('/\A[a-z_]+\z/D',$accion))
            throw new HttpException(400,'Ruta inválida.');
        $metodo=$_SERVER['REQUEST_METHOD']??'GET';
        $libros=new LibroController($this->db);
        $categorias=new CategoriaController($this->db);
        $documentos=new DocumentoController($this->db);

        $rutas=[
            'GET catalogo'=>fn()=> $libros->catalogo(),
            'GET detalle'=>fn()=> $libros->detalle(),
            'GET administrar'=>fn()=> $libros->administrar(),
            'GET crear'=>fn()=> $libros->crear(),
            'POST guardar'=>fn()=> $libros->guardar(),
            'GET editar'=>fn()=> $libros->editar(),
            'POST actualizar'=>fn()=> $libros->actualizar(),
            'POST eliminar'=>fn()=> $libros->eliminar(),

            'GET categorias'=>fn()=> $categorias->indice(),
            'GET categoria_crear'=>fn()=> $categorias->crear(),
            'POST categoria_guardar'=>fn()=> $categorias->guardar(),
            'GET categoria_editar'=>fn()=> $categorias->editar(),
            'POST categoria_actualizar'=>fn()=> $categorias->actualizar(),
            'POST categoria_eliminar'=>fn()=> $categorias->eliminar(),

            'GET descargar'=>fn()=> $documentos->descargar(),

            'GET visor'=>fn()=> $documentos->visor(),
            'GET archivo'=>fn()=> $documentos->archivo(),

        ];
        $clave=$metodo.' '.$accion;
        if(isset($rutas[$clave])){$rutas[$clave]();return;}
        foreach(array_keys($rutas) as $ruta){
            if(str_ends_with($ruta,' '.$accion)) {
                http_response_code(405);
                header('Allow: '.(str_starts_with($ruta,'GET')?'GET':'POST'));
                throw new HttpException(405,'El método HTTP no está permitido para esta acción.');
            }
        }
        throw new HttpException(404,'No existe la dirección solicitada.');
    }
}
