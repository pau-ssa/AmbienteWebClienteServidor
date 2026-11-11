<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Core\View;
use App\Models\Usuario;
use PDO;
final class AuthController {
    private Usuario $usuarios;
    public function __construct(PDO $db){$this->usuarios=new Usuario($db);}
    public function formulario(): void {
        if(\App\Core\Auth::esAdmin()){redirigir('administrar');}
        View::render('auth/login',['errores'=>[],'email'=>''],'Ingreso');
    }
    public function autenticar(): void {
        csrf_comprobar();
        $email=trim(is_string($_POST['email']??null)?$_POST['email']:'');
        $clave=is_string($_POST['clave']??null)?$_POST['clave']:'';
        $ahora=time();
        $intentos=$_SESSION['login_fallos']??[];
        $intentos=array_values(array_filter($intentos,fn($v)=>is_int($v)&&$v>$ahora-600));
        if(count($intentos)>=5){http_response_code(429);View::render('auth/login',
            ['errores'=>['Demasiados intentos. Espere diez minutos.'],'email'=>$email],'Ingreso');return;}
        $usuario=filter_var($email,FILTER_VALIDATE_EMAIL)?$this->usuarios->buscarPorCorreo($email):null;
        if(!$usuario || !password_verify($clave,$usuario['clave_hash'])) {
            $intentos[]=$ahora;$_SESSION['login_fallos']=$intentos;
            http_response_code(422);View::render('auth/login',
                ['errores'=>['Correo o contraseña incorrectos.'],'email'=>$email],'Ingreso');return;
        }
        session_regenerate_id(true);
        unset($_SESSION['login_fallos']);
        $_SESSION['usuario']=['id'=>(int)$usuario['id'],'nombre'=>$usuario['nombre'],
            'rol'=>$usuario['rol']];
        aviso('Sesión iniciada correctamente.');
        redirigir($usuario['rol']==='admin'?'administrar':'catalogo');
    }
    public function salir(): void {
        csrf_comprobar();unset($_SESSION['usuario']);
        session_regenerate_id(true);aviso('Sesión cerrada.');redirigir();
    }
}
