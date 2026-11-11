<?php
declare(strict_types=1);
namespace App\Core;

/** Renderiza una plantilla sin permitir rutas arbitrarias desde parámetros HTTP. */
final class View
{
    public static function render(string $plantilla, array $datos = [],
        string $titulo = 'Biblioteca Virtual'): void
    {
        if (!preg_match('~\A[a-z0-9_/-]+\z~', $plantilla)) {
            throw new \InvalidArgumentException('Vista inválida.');
        }

        $base = dirname(__DIR__) . '/Views/';
        $ruta = $base . $plantilla . '.php';
        if (!is_file($ruta)) {
            throw new \LogicException('Vista no implementada: ' . $plantilla);
        }

        extract($datos, EXTR_SKIP);
        require $base . 'layouts/header.php';
        require $ruta;
        require $base . 'layouts/footer.php';
    }
}
