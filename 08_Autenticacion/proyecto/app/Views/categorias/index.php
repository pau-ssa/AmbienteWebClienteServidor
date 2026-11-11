<div class="barra">
<h2>Administración de categorías</h2>
<a class="btn btn-primary" href="<?=e(ruta('categoria_crear'))?>">Nueva categoría</a>
</div>

<div class="tabla-externa">
<table>
<thead>
<tr>
<th>Categoría</th>
<th>Descripción</th>
<th>Acciones</th>
</tr>
</thead>
<tbody>
<?php foreach($categorias as $c): ?><tr>
<td><?=e($c['nombre'])?></td>
<td><?=e($c['descripcion'])?></td>
<td>
<div class="acciones-tabla">
<a class="btn btn-sm btn-outline" href="<?=e(ruta('categoria_editar',(int)$c['id']))?>">Editar</a>
<form method="post"
    action="<?=e(ruta('categoria_eliminar', (int)$c['id']))?>"
    onsubmit="return confirm('¿Eliminar esta categoría?')">
<input type="hidden" name="_csrf" value="<?=e(csrf_token())?>">
<button class="boton-peligro" type="submit">Eliminar</button>
</form>
</div>
</td>
</tr><?php endforeach; ?></tbody>
</table>
</div>
