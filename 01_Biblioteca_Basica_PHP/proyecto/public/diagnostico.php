<?php
declare(strict_types=1);

// Laboratorio XAMPP: diagnóstico de solo lectura, accesible únicamente desde localhost.
$remoto = $_SERVER['REMOTE_ADDR'] ?? '';
if (!in_array($remoto, ['127.0.0.1', '::1'], true)) {
    http_response_code(403);
    exit('Diagnóstico disponible exclusivamente en localhost.');
}

$filas = [];
$filas[] = ['PHP >= 8.1', version_compare(PHP_VERSION, '8.1.0', '>='), PHP_VERSION];
$filas[] = ['SAPI Apache', PHP_SAPI !== 'cli', PHP_SAPI];
$filas[] = ['Soporte UTF-8', extension_loaded('mbstring') || extension_loaded('iconv'), 'mbstring o iconv'];
$filas[] = ['fileinfo', extension_loaded('fileinfo'), 'Validación de PDF'];
$correcto = !in_array(false, array_column($filas, 1), true);
http_response_code($correcto ? 200 : 503);
header('Content-Type: text/html; charset=UTF-8');
header('X-Content-Type-Options: nosniff');
function htmlSeguro(string $texto): string {
    return htmlspecialchars($texto, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<!doctype html>
<html lang="es"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Diagnóstico XAMPP | Biblioteca Fidélitas</title>
<style>
body{font-family:Segoe UI,Arial,sans-serif;max-width:800px;margin:35px auto;padding:0 20px;}
h1{color:#123f7a;}table{border-collapse:collapse;width:100%;}
 td,th{border-bottom:1px solid #cbd5e1;padding:12px;text-align:left;}
.ok{color:#167044}.fallo{color:#a32938}code{background:#e8edf5;padding:3px;}
</style></head><body>
<h1>Diagnóstico local de XAMPP</h1>
<p>Prototipo P01 · PHP servido por Apache.</p>
<table><tr><th>Comprobación</th><th>Estado</th><th>Detalle</th></tr>
<?php foreach ($filas as [$nombre,$ok,$detalle]): ?>
<tr><td><?=htmlSeguro($nombre)?></td>
<td class="<?=$ok?'ok':'fallo'?>"><?=$ok?'Correcto':'Revisar'?></td>
<td><?=htmlSeguro((string)$detalle)?></td></tr>
<?php endforeach; ?></table>
<p><a href="index.php">Volver a la biblioteca</a></p>
</body></html>
