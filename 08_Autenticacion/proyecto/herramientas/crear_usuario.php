<?php
declare(strict_types=1);
// Uso EXCLUSIVO en CLI: php herramientas/crear_usuario.php
if(PHP_SAPI!=='cli'){http_response_code(403);exit('Solo CLI');}
spl_autoload_register(function(string $clase): void {
    if(str_starts_with($clase,'App\\')) {
        $ruta=dirname(__DIR__).'/app/'.str_replace('\\','/',substr($clase,4)).'.php';
        if(is_file($ruta))require $ruta;
    }
});
use App\Core\Database;
use App\Models\Usuario;
function preguntar(string $pregunta): string {echo $pregunta;return trim((string)fgets(STDIN));}
$nombre=preguntar('Nombre (máx 120): ');
$email=preguntar('Correo: ');
$rol=preguntar('Rol [admin/lector]: ');
$clave=preguntar('Contraseña (mínimo 12 caracteres, entrada visible): ');
if($nombre===''||strlen($nombre)>120||!filter_var($email,FILTER_VALIDATE_EMAIL)
 ||!in_array($rol,['admin','lector'],true)||strlen($clave)<12){fwrite(STDERR,"Datos inválidos.\n");
     exit(1);}
$u=new Usuario(Database::conectar());
$u->crear($nombre,$email,password_hash($clave,PASSWORD_DEFAULT),$rol);
echo "Usuario creado. Elimine la contraseña de cualquier registro de terminal.\n";
