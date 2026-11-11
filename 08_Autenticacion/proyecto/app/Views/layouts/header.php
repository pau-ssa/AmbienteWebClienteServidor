<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?=e($titulo)?> | Biblioteca Virtual Fidélitas</title>
<link rel="stylesheet" href="assets/css/ort-fidelitas.css">
<link rel="stylesheet" href="assets/css/biblioteca.css">
</head>
<body>
<a class="skip-link" href="#contenido">Saltar al contenido</a>
<header class="site-header">
<div class="container navbar">
<a class="marca" href="<?=e(ruta())?>">Biblioteca Virtual Fidélitas</a>
<nav class="nav" aria-label="Navegación principal">
<a class="nav-link" href="<?=e(ruta())?>">Catálogo</a>
<?php if (\App\Core\Auth::esAdmin()): ?>
<a class="nav-link" href="<?=e(ruta('administrar'))?>">Libros</a>
<a class="nav-link" href="<?=e(ruta('categorias'))?>">Categorías</a>
<?php endif; ?>
<?php if (isset($_SESSION['usuario'])): ?>
<form method="post" action="<?=e(ruta('logout'))?>" style="width:auto;display:inline">
<input type="hidden" name="_csrf" value="<?=e(csrf_token())?>">
<button type="submit" class="btn btn-sm btn-outline">Cerrar sesión</button>
</form>
<?php else: ?>
<a class="nav-link" href="<?=e(ruta('login'))?>">Ingresar</a>
<?php endif; ?>
</nav>
</div>
</header>
<div class="panel-hero">
<div class="container">
<p class="course-kicker">SC-502 · Ejemplo progresivo</p>
<h1>Biblioteca Virtual</h1>
<p>Gestión documental con PHP, MySQL y MVC.</p>
</div>
</div>
<main id="contenido" class="container">
<?php if ($aviso=tomar_aviso()): ?><div class="ok" role="status"><?=e($aviso)?></div><?php endif; ?>
