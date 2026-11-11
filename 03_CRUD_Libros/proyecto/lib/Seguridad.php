<?php
declare(strict_types=1);
function iniciarSesion(): void {
    if(session_status()!==PHP_SESSION_ACTIVE){ini_set('session.use_strict_mode','1');session_start();}
}
function csrf(): string {iniciarSesion();return $_SESSION['csrf'] ??= bin2hex(random_bytes(24));}
function validarCsrf(): void {
    if (!is_string($_POST['_csrf']??null) || !hash_equals(csrf(),$_POST['_csrf'])) {
        http_response_code(403);
            exit('Solicitud rechazada (CSRF). Regrese al formulario e inténtelo nuevamente.');
    }
}
function avisar(string $texto): void {iniciarSesion();$_SESSION['aviso']=$texto;}
function consumirAviso(): ?string {iniciarSesion();$x=$_SESSION['aviso']??null;
    unset($_SESSION['aviso']);return $x;}
function validarLibro(array $d): array {
    $errores=[];
    if (trim($d['titulo']) === '' || largo_utf8($d['titulo']) > 180) {
        $errores[] = 'El título es obligatorio (máximo 180 caracteres).';
    }
    if (trim($d['autor']) === '' || largo_utf8($d['autor']) > 160) {
        $errores[] = 'El autor es obligatorio (máximo 160 caracteres).';
    }
    if (trim($d['resumen']) === '' || largo_utf8($d['resumen']) > 4000) {
        $errores[] = 'El resumen es obligatorio (máximo 4000 caracteres).';
    }
    if (!filter_var($d['categoria_id'],
        FILTER_VALIDATE_INT) || (int)$d['categoria_id']<1) $errores[]='Seleccione una categoría válida.';
    return $errores;
}
