<?php
declare(strict_types=1);
namespace App\Core;
final class Auth {
    public static function esAdmin(): bool {return ($_SESSION['usuario']['rol']??null)==='admin';}
    public static function requerirAdmin(): void {
        if(!isset($_SESSION['usuario'])){header('Location: '.\ruta('login'),true,303);exit;}
        if(!self::esAdmin())throw new HttpException(403,
            'No tiene permisos para administrar el catálogo.');
    }
}
