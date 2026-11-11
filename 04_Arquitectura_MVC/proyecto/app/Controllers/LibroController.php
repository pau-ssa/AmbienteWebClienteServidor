<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Core\HttpException;
use App\Core\View;
use App\Models\Libro;
use App\Models\Categoria;
use PDO;

final class LibroController {
    private Libro $libros;
    private Categoria $categorias;

    public function __construct(private PDO $db) {
        $this->libros=new Libro($db);
        $this->categorias=new Categoria($db);
    }
    public function catalogo(): void {
        View::render('libros/catalogo',['libros'=>$this->libros->listar()],'Catálogo');
    }
    public function detalle(): void {
        $libro=$this->libros->obtener(identificador());
        if(!$libro) throw new HttpException(404,'El libro solicitado no existe.');
        View::render('libros/detalle',['libro'=>$libro],$libro['titulo']);
    }
    public function administrar(): void {

        View::render('libros/administrar',['libros'=>$this->libros->listar()],'Administración');
    }
    private function formulario(array $datos,array $categorias,?int $id,array $errores=[]): void {

        View::render('libros/formulario',compact('datos','categorias','id','errores'),
            $id===null?'Nuevo libro':'Editar libro');
    }
    public function crear(): void {

        $this->formulario(['titulo'=>'','autor'=>'','resumen'=>'','categoria_id'=>''],
            $this->categorias->listar(),null);
    }
    public function guardar(): void {

        csrf_comprobar();
        $datos=datos_libro_post();$categorias=$this->categorias->listar();
        $errores=validar_libro($datos,$categorias);
        if ($errores){http_response_code(422);$this->formulario($datos,$categorias,null,$errores);
            return;}
        $this->libros->crear($datos);
        aviso('Libro registrado correctamente.');redirigir('administrar');
    }
    public function editar(): void {

        $id=identificador();$libro=$this->libros->obtener($id);
        if(!$libro) throw new HttpException(404,'No existe el libro que desea editar.');
        $this->formulario($libro,$this->categorias->listar(),$id);
    }
    public function actualizar(): void {

        csrf_comprobar();$id=identificador();
        $existente=$this->libros->obtener($id);
        if(!$existente) throw new HttpException(404,'No existe el libro que desea actualizar.');
        $datos=datos_libro_post();$categorias=$this->categorias->listar();
        $errores=validar_libro($datos,$categorias);
        if($errores){http_response_code(422);$this->formulario($datos,$categorias,$id,$errores);return;}
        $this->libros->actualizar($id,$datos);
        aviso('Libro actualizado correctamente.');redirigir('administrar');
    }
    public function eliminar(): void {

        csrf_comprobar();$id=identificador();
        if(!$this->libros->obtener($id)) throw new HttpException(404,
            'No existe el libro que desea eliminar.');
        $this->libros->eliminar($id);
        aviso('Libro eliminado correctamente.');redirigir('administrar');
    }
}
