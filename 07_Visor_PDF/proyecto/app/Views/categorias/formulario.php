<p>
<a href="<?=e(ruta('categorias'))?>">← Volver a categorías</a>
</p>
<h2><?=$id===null?'Crear categoría':'Editar categoría'?></h2>
<?php foreach($errores as $error): ?><div class="error" role="alert"><?=e($error)?></div><?php endforeach; ?>
<form class="panel formulario" method="post"
    action="<?=e(ruta($id === null ? 'categoria_guardar' : 'categoria_actualizar', $id))?>">
<input type="hidden" name="_csrf" value="<?=e(csrf_token())?>">
<div class="campo">
<label for="nombre">Nombre de la categoría</label>
<input id="nombre" name="nombre" maxlength="100" required value="<?=e($datos['nombre']??'')?>">
</div>
<div class="campo">
<label for="descripcion">Descripción</label>
<textarea id="descripcion" name="descripcion" maxlength="300"><?=e($datos['descripcion']??'')?></textarea>
</div>
<div class="controles">
<button class="btn btn-primary" type="submit">Guardar categoría</button>
<a class="btn btn-outline" href="<?=e(ruta('categorias'))?>">Cancelar</a>
</div>
</form>
