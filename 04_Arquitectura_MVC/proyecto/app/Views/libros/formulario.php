<p>
<a href="<?=e(ruta('administrar'))?>">← Volver a administración</a>
</p>
<h2><?=$id===null?'Registrar libro':'Editar libro'?></h2>
<?php foreach($errores as $error): ?><div class="error" role="alert"><?=e($error)?></div><?php endforeach; ?>
<form class="panel formulario" method="post" action="<?=e(ruta($id===null?'guardar':'actualizar',
    $id))?>">
<input type="hidden" name="_csrf" value="<?=e(csrf_token())?>">
<div class="campo">
<label for="titulo">Título</label>
<input id="titulo" name="titulo" maxlength="180" required value="<?=e($datos['titulo']??'')?>">
</div>
<div class="campo">
<label for="autor">Autor</label>
<input id="autor" name="autor" maxlength="160" required value="<?=e($datos['autor']??'')?>">
</div>
<div class="campo">
<label for="resumen">Resumen del documento</label>
<textarea id="resumen" name="resumen" maxlength="4000" required><?=e($datos['resumen']??'')?></textarea>
</div>
<div class="campo">
<label for="categoria_id">Categoría</label>
<select name="categoria_id" id="categoria_id" required>
<option value="">Seleccione</option>
<?php foreach ($categorias as $c): ?>
<option value="<?=(int)$c['id']?>"
    <?=((int)($datos['categoria_id'] ?? 0) === (int)$c['id'] ? 'selected' : '')?>>
    <?=e($c['nombre'])?>
</option>
<?php endforeach; ?>
</select>
</div>

<div class="controles">
<button class="btn btn-primary" type="submit">Guardar libro</button>
<a class="btn btn-outline" href="<?=e(ruta('administrar'))?>">Cancelar</a>
</div>
</form>