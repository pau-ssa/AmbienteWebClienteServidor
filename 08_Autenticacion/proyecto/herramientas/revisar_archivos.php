<?php
declare(strict_types=1);
// php herramientas/revisar_archivos.php ; solo lectura, no borra nada.
if(PHP_SAPI!=='cli'){http_response_code(403);exit('Solo CLI');}
spl_autoload_register(function(string $nombre): void {
    if(str_starts_with($nombre,'App\\')) {
        $ruta=dirname(__DIR__).'/app/'.str_replace('\\','/',substr($nombre,4)).'.php';
        if(is_file($ruta))require $ruta;
    }
});
use App\Core\Database;
use App\Models\Documento;
use App\Services\ArchivoStorage;
$documentos=new Documento(Database::conectar());
$registrados=array_column($documentos->nombres(),'nombre_interno');
$archivos=array_map('basename',glob(ArchivoStorage::directorio().'/*.pdf')?:[]);
$huerfanos=array_values(array_diff($archivos,$registrados));
$faltantes=array_values(array_diff($registrados,$archivos));
echo 'Archivos no registrados: '.json_encode($huerfanos,JSON_UNESCAPED_UNICODE).PHP_EOL;
echo 'Registros sin archivo: '.json_encode($faltantes,JSON_UNESCAPED_UNICODE).PHP_EOL;
echo 'Pendientes de eliminación: '.json_encode($documentos->pendientes(),
    JSON_UNESCAPED_UNICODE).PHP_EOL;
echo "Este programa NO elimina documentos.\n";
