<?php
declare(strict_types=1);

namespace App\Services;

use DomainException;

/**
 * Guarda PDF fuera de public/ y bloquea por Apache las carpetas privadas.
 * La base de datos mantiene los metadatos; nunca se usa el nombre original como ruta.
 */
final class ArchivoStorage
{
    private const MAX_BYTES = 10 * 1024 * 1024;

    public static function directorio(): string
    {
        return dirname(__DIR__, 2) . '/storage/pdfs';
    }

    public static function ruta(string $nombre): string
    {
        if (!preg_match('/\A(?:[a-f0-9]{32}|demo_[a-z0-9_]+)\.pdf\z/D', $nombre)) {
            throw new DomainException('Identificador de archivo no permitido.');
        }

        return self::directorio() . '/' . $nombre;
    }

    public static function recibir(?array $archivo, bool $obligatorio): ?array
    {
        if ($archivo === null || ($archivo['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            if ($obligatorio) {
                throw new DomainException('Debe seleccionar un archivo PDF.');
            }
            return null;
        }

        if (!is_int($archivo['error'] ?? null) || $archivo['error'] !== UPLOAD_ERR_OK) {
            throw new DomainException('No se pudo cargar el archivo. Revise el tamaño y PHP.');
        }

        $temporal = $archivo['tmp_name'] ?? '';
        if (!is_string($temporal) || !is_uploaded_file($temporal)) {
            throw new DomainException('Archivo de carga inválido.');
        }

        $bytes = filesize($temporal);
        if ($bytes === false || $bytes < 10 || $bytes > self::MAX_BYTES) {
            throw new DomainException('El PDF debe tener contenido y no superar 10 MB.');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        if ($finfo->file($temporal) !== 'application/pdf') {
            throw new DomainException('El archivo no corresponde al formato PDF.');
        }

        $descriptor = fopen($temporal, 'rb');
        if ($descriptor === false) {
            throw new DomainException('No se pudo inspeccionar el PDF.');
        }
        $marca = fread($descriptor, 5);
        fclose($descriptor);
        if ($marca !== '%PDF-') {
            throw new DomainException('Cabecera PDF inválida.');
        }

        $original = basename(str_replace('\\', '/', (string) ($archivo['name'] ?? 'documento.pdf')));
        $original = preg_replace('/[^\pL\pN. _-]/u', '_', $original) ?? 'documento.pdf';
        $original = (function_exists('mb_strcut')
            ? mb_strcut($original, 0, 240, 'UTF-8')
            : iconv_substr($original, 0, 120, 'UTF-8'));
        if ($original === '') {
            $original = 'documento.pdf';
        }

        if (!is_dir(self::directorio()) && !mkdir(self::directorio(), 0770,
            true) && !is_dir(self::directorio())) {
            throw new DomainException('No se pudo crear el almacenamiento de PDF.');
        }

        $interno = bin2hex(random_bytes(16)) . '.pdf';
        $destino = self::ruta($interno);
        if (!move_uploaded_file($temporal, $destino)) {
            throw new DomainException('No se pudo guardar el PDF en el servidor.');
        }
        @chmod($destino, 0660);

        return [
            'nombre_original' => $original,
            'nombre_interno' => $interno,
            'bytes_archivo' => $bytes,
        ];
    }

    /**
     * Eliminación idempotente: true si se eliminó o si ya no existía.
     * false si existe pero no fue posible retirarlo. Nunca oculta silenciosamente el fallo.
     */
    public static function eliminar(string $interno): bool
    {
        try {
            $ruta = self::ruta($interno);
        } catch (DomainException $error) {
            error_log('Nombre interno de PDF inválido durante la limpieza.');
            return false;
        }

        if (!file_exists($ruta)) {
            return true;
        }
        if (!is_file($ruta) || !@unlink($ruta)) {
            error_log('PDF pendiente de eliminación: ' . $interno);
            return false;
        }
        return true;
    }

    public static function verificar(string $interno): string
    {
        $ruta = self::ruta($interno);
        $base = realpath(self::directorio());
        $real = realpath($ruta);
        if ($base === false || $real === false ||
            !str_starts_with($real, $base . DIRECTORY_SEPARATOR) || !is_file($real)) {
            throw new DomainException('El PDF registrado no se encuentra disponible.');
        }
        return $real;
    }
}
