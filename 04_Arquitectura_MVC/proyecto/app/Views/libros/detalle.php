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

</article>
