<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Core\HttpException;
use App\Core\View;
use App\Models\Categoria;
use PDO;
use PDOException;
final class CategoriaController {
    private Categoria $categorias;
    public function __construct(PDO $db){$this->categorias=new Categoria($db);}
    private function autorizar(): void {\App\Core\Auth::requerirAdmin();}
    public function indice(): void {$this->autorizar();View::render('categorias/index',
        ['categorias'=>$this->categorias->listar()],'Categorías');}
    private function formulario(array $datos,?int $id,array $errores=[]): void {
        View::render('categorias/formulario',compact('datos','id','errores'),'Categoría');
    }
    public function crear(): void {$this->autorizar();$this->formulario(['nombre'=>'',
        'descripcion'=>''],null);}
    public function editar(): void {
        $this->autorizar();$id=identificador();$datos=$this->categorias->obtener($id);
        if(!$datos) throw new HttpException(404,'No existe la categoría.');
        $this->formulario($datos,$id);
    }
    private function validarPost(): array {
        $nombre=trim(is_string($_POST['nombre']??null)?$_POST['nombre']:'');
        $descripcion=trim(is_string($_POST['descripcion']??null)?$_POST['descripcion']:'');
        $errores=[];
        if($nombre==='' || largo_utf8($nombre)>100) $errores[]='El nombre debe tener entre 1 y 100 caracteres.';
        if(largo_utf8($descripcion)>300) $errores[]='La descripción no puede superar 300 caracteres.';
        return [['nombre'=>$nombre,'descripcion'=>$descripcion],$errores];
    }
    public function guardar(): void {
        $this->autorizar();csrf_comprobar();[$d,$errores]=$this->validarPost();
        if($errores){http_response_code(422);$this->formulario($d,null,$errores);return;}
        try{$this->categorias->crear($d['nombre'],$d['descripcion']);}
        catch(PDOException $ex){if($ex->getCode()!=='23000')throw $ex;http_response_code(422);
            $this->formulario($d,null,['Ya existe una categoría con ese nombre.']);return;}
        aviso('Categoría registrada.');redirigir('categorias');
    }
    public function actualizar(): void {
        $this->autorizar();csrf_comprobar();$id=identificador();
        if(!$this->categorias->obtener($id)) throw new HttpException(404,'No existe la categoría.');
        [$d,$errores]=$this->validarPost();
        if($errores){http_response_code(422);$this->formulario($d,$id,$errores);return;}
        try{$this->categorias->actualizar($id,$d['nombre'],$d['descripcion']);}
        catch(PDOException $ex){if($ex->getCode()!=='23000')throw $ex;http_response_code(422);
            $this->formulario($d,$id,['El nombre está ocupado por otra categoría.']);return;}
        aviso('Categoría actualizada.');redirigir('categorias');
    }
    public function eliminar(): void {
        $this->autorizar();csrf_comprobar();$id=identificador();
        if(!$this->categorias->obtener($id)) throw new HttpException(404,'No existe la categoría.');
        try{$this->categorias->eliminar($id);aviso('Categoría eliminada.');}
        catch(PDOException $ex){if($ex->getCode()!=='23000')throw $ex;
            aviso('No se puede eliminar: existen libros asociados a esta categoría.');}
        redirigir('categorias');
    }
}
