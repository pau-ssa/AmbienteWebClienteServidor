<div class="barra">
<h2>Catálogo de documentos</h2>
<span class="etiqueta"><?=count($libros)?> libro(s)</span>
</div>

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