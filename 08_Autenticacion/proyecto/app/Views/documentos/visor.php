<p>
<a href="<?=e(ruta('detalle',(int)$libro['id']))?>">← Volver al libro</a>
</p>
<div class="barra">
<div>
<h2><?=e($libro['titulo'])?></h2>
<p>
<strong>Autor:</strong> <?=e($libro['autor'])?> · <span class="etiqueta"><?=e($libro['categoria'])?></span>
</p>
</div>
<a class="btn btn-outline" href="<?=e(ruta('descargar',(int)$libro['id']))?>">Descargar PDF</a>
</div>
<iframe class="visor-pdf" title="Lectura del PDF: <?=e($libro['titulo'])?>"
    src="<?=e(ruta('archivo', (int)$libro['id']))?>">
</iframe>
<p class="ayuda">Si el navegador no tiene visor PDF, utilice «Descargar PDF».</p>
