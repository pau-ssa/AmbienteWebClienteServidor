<div class="barra">
<h2>Administración de libros</h2>
<a class="btn btn-primary" href="<?=e(ruta('crear'))?>">Registrar libro</a>
</div>

<div class="tabla-externa">
<table>
<thead>
<tr>
<th>Título</th>
<th>Autor</th>
<th>Categoría</th>
<th>Acciones</th>
</tr>
</thead>
<tbody>
<?php foreach($libros as $l): ?><tr>
<td><?=e($l['titulo'])?></td>
<td><?=e($l['autor'])?></td>
<td><?=e($l['categoria'])?></td>
<td>
<div class="acciones-tabla">
<a class="btn btn-sm btn-outline" href="<?=e(ruta('editar',
    (int)$l['id']))?>">Editar</a>
<form method="post" action="<?=e(ruta('eliminar',
    (int)$l['id']))?>" onsubmit="return confirm('¿Eliminar el libro y su archivo asociado?')">
<input type="hidden" name="_csrf" value="<?=e(csrf_token())?>">
<button type="submit" class="boton-peligro">Eliminar</button>
</form>
</div>
</td>
</tr><?php endforeach; ?></tbody>
</table>
</div>
