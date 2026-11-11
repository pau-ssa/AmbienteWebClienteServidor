<p>
<a href="<?=e(ruta())?>">← Volver al catálogo</a>
</p>
<article>
<span class="etiqueta"><?=e($libro['categoria'])?></span>
<h2><?=e($libro['titulo'])?></h2>
<p>
<strong>Autor:</strong> <?=e($libro['autor'])?></p>
<h3>Resumen del documento</h3>
<p class="resumen"><?=nl2br(e($libro['resumen']))?></p>

<?php if (!empty($libro['pdf_interno'])): ?>
<a class="btn btn-primary" href="<?=e(ruta('visor',(int)$libro['id']))?>">Abrir visor PDF</a>
<a class="btn btn-outline" href="<?=e(ruta('descargar',(int)$libro['id']))?>">Descargar PDF</a>
<?php else: ?><p class="ayuda">Este registro aún no tiene PDF asociado.</p><?php endif; ?>
</article>
