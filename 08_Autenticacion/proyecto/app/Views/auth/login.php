<div class="login">
<h2>Ingreso al sistema</h2>
<?php foreach($errores as $error): ?><div class="error" role="alert"><?=e($error)?></div><?php endforeach; ?>
<form method="post" action="<?=e(ruta('autenticar'))?>" class="panel">
<input type="hidden" name="_csrf" value="<?=e(csrf_token())?>">
<div class="campo">
<label for="email">Correo electrónico</label>
<input id="email" type="email" name="email" autocomplete="username"
    required maxlength="190" value="<?=e($email)?>">
</div>
<div class="campo">
<label for="clave">Contraseña</label>
<input id="clave" type="password" name="clave" autocomplete="current-password" required>
</div>
<button class="btn btn-primary" type="submit">Iniciar sesión</button>
</form>
<p class="ayuda">
    Las cuentas de demostración se documentan solo para uso local.
    No reutilice sus credenciales en producción.
</p>
</div>
