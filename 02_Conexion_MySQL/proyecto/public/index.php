<?php
declare(strict_types=1);
require __DIR__ . '/../lib/Conexion.php';
require __DIR__ . '/../lib/RepositorioLibros.php';


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

function e(?string $s): string
{
    return htmlspecialchars($s ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function iniciarPagina(string $titulo): void
{
    $titulo = e($titulo);
    echo '<!doctype html>'
        . '<html lang="es"><head><meta charset="utf-8">'
        . '<meta name="viewport" content="width=device-width,initial-scale=1">'
        . '<title>' . $titulo . ' | Biblioteca Virtual</title>'
        . '<link rel="stylesheet" href="assets/css/ort-fidelitas.css">'
        . '<link rel="stylesheet" href="assets/css/biblioteca.css">'
        . '</head><body>';

    echo '<a class="skip-link" href="#contenido">Saltar al contenido</a>'
        . '<header class="site-header"><div class="container navbar">'
        . '<a class="marca" href="index.php">Biblioteca Virtual Fidélitas</a>'
        . '<nav aria-label="Principal">'
        . '<a class="nav-link" href="index.php">Catálogo</a>';

    if (defined('HAY_ADMIN') && HAY_ADMIN) {
        echo '<a class="nav-link" href="index.php?accion=administrar">'
            . 'Administrar libros</a>';
    }

    echo '</nav></div></header>'
        . '<div class="panel-hero"><div class="container">'
        . '<p class="course-kicker">SC-502 · Laboratorio de evolución</p>'
        . '<h1>Biblioteca Virtual</h1>'
        . '<p>Libros académicos organizados para consultar y aprender.</p>'
        . '</div></div><main id="contenido" class="container">';
}

function terminarPagina(): void
{
    echo '</main><footer class="pie-local"><div class="container">'
        . '<p>SC-502 · Prototipo didáctico · Universidad Fidélitas</p>'
        . '</div></footer></body></html>';
}

function pintarLibros(array $libros): void
{
    if (!$libros) {
        echo '<p class="alert alert-info">No hay libros disponibles.</p>';
        return;
    }

    echo '<section class="catalogo-grid" aria-label="Libros disponibles">';
    foreach ($libros as $libro) {
        $id = (int) $libro['id'];
        $enlace = 'index.php?id=' . $id;
        echo '<article class="libro-card">'
            . '<span class="libro-icono" aria-hidden="true">PDF</span>'
            . '<h3>' . e($libro['titulo']) . '</h3>'
            . '<p><strong>Autor:</strong> ' . e($libro['autor']) . '</p>'
            . '<p><span class="etiqueta">' . e($libro['categoria']) . '</span></p>'
            . '<p>' . e(mbResumen($libro['resumen'])) . '</p>'
            . '<div class="acciones"><a class="btn btn-primary" href="' . $enlace . '">'
            . 'Ver detalle</a></div></article>';
    }
    echo '</section>';
}

function mbResumen(string $texto): string
{
    return largo_utf8($texto) > 175
        ? cortar_utf8($texto, 0, 172) . '...'
        : $texto;
}

function pintarDetalle(array $libro): void
{
    echo '<p><a href="index.php">← Volver al catálogo</a></p>'
        . '<article><span class="etiqueta">' . e($libro['categoria']) . '</span>'
        . '<h2>' . e($libro['titulo']) . '</h2>'
        . '<p><strong>Autor:</strong> ' . e($libro['autor']) . '</p>'
        . '<h3>Resumen</h3>'
        . '<p class="resumen">' . nl2br(e($libro['resumen'])) . '</p>'
        . '</article>';
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (isset($_GET['id']) && (!$id || $id <= 0)) {
    http_response_code(400);
    iniciarPagina('Solicitud inválida');
    echo '<div class="error">El ID debe ser positivo.</div>';
    terminarPagina();
    exit;
}

$error = null;
$lista = [];
$libro = null;
try {
    $db = conexion();
    if ($id) {
        $libro = obtenerLibro($db, $id);
    } else {
        $lista = listarLibros($db);
    }
} catch (PDOException $ex) {
    error_log($ex->getMessage());
    http_response_code(503);
    $error = 'No se pudo consultar MySQL. Revise la conexión y los scripts SQL.';
}

if ($id && !$libro && !$error) {
    http_response_code(404);
}
iniciarPagina($libro ? $libro['titulo'] : 'Catálogo');
if ($error) {
    echo '<div class="error">' . e($error) . '</div>';
} elseif ($id) {
    if ($libro) {
        pintarDetalle($libro);
    } else {
        echo '<div class="error">Libro inexistente.</div>';
    }
} else {
    echo '<div class="barra"><h2>Catálogo desde MySQL</h2>'
        . '<p>Consulta mediante Stored Procedures.</p></div>';
    pintarLibros($lista);
}
terminarPagina();
