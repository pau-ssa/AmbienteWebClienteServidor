<?php
declare(strict_types=1);

/**
 * Desde proyecto/:
 *   php herramientas/procesar_pendientes.php             # revisión, no borra
 *   php herramientas/procesar_pendientes.php --aplicar   # elimina solo pendientes no referenciados
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Solo consola.');
}

spl_autoload_register(function (string $nombre): void {
    if (!str_starts_with($nombre, 'App\\')) {
        return;
    }
    $ruta = dirname(__DIR__) . '/app/' . str_replace('\\', '/', substr($nombre, 4)) . '.php';
    if (is_file($ruta)) {
        require $ruta;
    }
});

use App\Core\Database;
use App\Models\Documento;
use App\Services\ArchivoStorage;

$aplicar = ($argv[1] ?? '') === '--aplicar';
$documentos = new Documento(Database::conectar());
$referenciados = array_column($documentos->nombres(), 'nombre_interno');
$pendientes = $documentos->pendientes();
echo 'Pendientes: ' . count($pendientes) . PHP_EOL;
$faltas = 0;

foreach ($pendientes as $pendiente) {
    $nombre = $pendiente['nombre_interno'];
    if (in_array($nombre, $referenciados, true)) {
        echo 'ALERTA: referenciado, no se elimina: ' . $nombre . PHP_EOL;
        $faltas++;
        continue;
    }
    if (!$aplicar) {
        echo 'REVISAR: ' . $nombre . PHP_EOL;
        continue;
    }
    if (!ArchivoStorage::eliminar($nombre)) {
        echo 'FALLO al borrar físicamente: ' . $nombre . PHP_EOL;
        $faltas++;
        continue;
    }
    $documentos->completarEliminacion($nombre);
    echo 'LIMPIADO: ' . $nombre . PHP_EOL;
}

if (!$aplicar) {
    echo 'Sin cambios. Use --aplicar cuando haya comprobado la lista.' . PHP_EOL;
}
exit($faltas > 0 ? 1 : 0);
