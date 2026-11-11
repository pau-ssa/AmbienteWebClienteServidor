<div class="barra">
<h2>Catálogo de documentos</h2>
<span class="etiqueta"><?=count($libros)?> libro(s)</span>
</div>

<form method="get" action="index.php" class="panel mb-5" role="search">
<input type="hidden" name="accion" value="catalogo">
<div class="grid-3">
<div class="campo">
<label for="q">Buscar por título o autor</label>
<input id="q" name="q" maxlength="200" value="<?=e($q)?>">
</div>
<div class="campo">
<label for="categoria">Categoría</label>
<select id="categoria" name="categoria">
<option value="0">Todas</option>
<?php foreach ($categorias as $c): ?>
<option value="<?=(int)$c['id']?>"
    <?=($categoria === (int)$c['id'] ? 'selected' : '')?>>
    <?=e($c['nombre'])?>
</option>
<?php endforeach; ?>
</select>
</div>
<div class="campo" style="align-self:end">
<button type="submit" class="btn btn-primary">Buscar</button>
</div>
</div>
</form>

<?php if (!$libros): ?>
<p class="alert alert-info">No hay documentos que coincidan con la consulta.</p>
<?php else: ?>
<div class="catalogo-grid">
<?php foreach($libros as $l): ?>
<article class="libro-card">
<span class="libro-icono" aria-hidden="true">PDF</span>
<h3><?=e($l['titulo'])?></h3>
<p>
<strong>Autor:</strong> <?=e($l['autor'])?></p>
<p>
<span class="etiqueta"><?=e($l['categoria'])?></span>
</p>
<p><?=e(largo_utf8($l['resumen'])>175?cortar_utf8($l['resumen'],0,172).'...':$l['resumen'])?></p>
<div class="acciones">
<a class="btn btn-primary" href="<?=e(ruta('detalle',
    (int)$l['id']))?>">Ver detalle</a>

</div>
</article>
<?php endforeach; ?></div><?php endif; ?>
