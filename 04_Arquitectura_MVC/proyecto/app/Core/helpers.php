<?php
declare(strict_types=1);
use App\Core\HttpException;

/** Longitudes y recortes en caracteres; iconv sirve de respaldo si falta mbstring. */
function largo_utf8(string $texto): int
{
    if (function_exists('mb_strlen')) {
        return mb_strlen($texto, 'UTF-8');
    }
    $n = iconv_strlen($texto, 'UTF-8');
    return $n === false ? strlen($texto) : $n;
}

function cortar_utf8(string $texto, int $inicio, int $cantidad): string
{
    if (function_exists('mb_substr')) {
        return mb_substr($texto, $inicio, $cantidad, 'UTF-8');
    }
    $trozo = iconv_substr($texto, $inicio, $cantidad, 'UTF-8');
    return $trozo === false ? substr($texto, $inicio, $cantidad) : $trozo;
}

function e(mixed $valor): string {return htmlspecialchars((string)($valor??''),
    ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');}
function ruta(string $accion='catalogo',?int $id=null): string {
    $url='index.php?accion='.rawurlencode($accion);
    return $id===null?$url:$url.'&id='.$id;
}
function csrf_token(): string {return $_SESSION['csrf']??=bin2hex(random_bytes(24));}
function csrf_comprobar(): void {
    if(!is_string($_POST['_csrf']??null) || !hash_equals(csrf_token(),$_POST['_csrf']))
        throw new HttpException(403,'Solicitud rechazada: token de formulario inválido.');
}
function identificador(): int {
    $id=filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT);
    if ($id===false || $id===null || $id<1) throw new HttpException(400,
        'Se requiere un identificador numérico positivo.');
    return $id;
}
function redirigir(string $accion='catalogo'): never {header('Location: '.ruta($accion),true,303);exit;}
function aviso(string $texto): void {$_SESSION['aviso']=$texto;}
function tomar_aviso(): ?string {$texto=$_SESSION['aviso']??null;unset($_SESSION['aviso']);
    return $texto;}
function validar_libro(array $d,array $categorias): array {
    $errores=[];
    if (trim($d['titulo']) === '' || largo_utf8($d['titulo']) > 180) {
        $errores[] = 'El título es obligatorio y no debe superar 180 caracteres.';
    }
    if (trim($d['autor']) === '' || largo_utf8($d['autor']) > 160) {
        $errores[] = 'El autor es obligatorio y no debe superar 160 caracteres.';
    }
    if (trim($d['resumen']) === '' || largo_utf8($d['resumen']) > 4000) {
        $errores[] = 'El resumen es obligatorio y no debe superar 4000 caracteres.';
    }
    $id=filter_var($d['categoria_id'],FILTER_VALIDATE_INT);
    if($id===false || $id<1 || !in_array((int)$id,array_map(fn($x)=>(int)$x['id'],$categorias),true))
        $errores[]='Debe seleccionar una categoría existente.';
    return $errores;
}
function datos_libro_post(): array {
    return [
      'titulo'=>trim(is_string($_POST['titulo']??null)?$_POST['titulo']:''),
      'autor'=>trim(is_string($_POST['autor']??null)?$_POST['autor']:''),
      'resumen'=>trim(is_string($_POST['resumen']??null)?$_POST['resumen']:''),
      'categoria_id'=>is_string($_POST['categoria_id']??null)?$_POST['categoria_id']:'',
    ];
}
