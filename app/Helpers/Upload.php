<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Upload
 *
 * Helper de subida y borrado de imágenes del sitio.
 */
class Upload
{
    public const MAX_SIZE = 2 * 1024 * 1024; // 2 MB
    public const ALLOWED = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    private static ?string $error = null;

    public static function error(): ?string
    {
        return self::$error;
    }

    /**
     * Procesa la subida de una imagen desde $_FILES[$field].
     *
     * - Si no se envió archivo: devuelve $currentImage (no cambia nada).
     * - Si sube bien: borra el archivo anterior (si existe) y devuelve la
     *   ruta relativa bajo UPLOAD_PATH, p. ej. "logo/logo_20260814_....png".
     * - Si falla: setea el error (ver self::error()) y devuelve null.
     */
    public static function image(string $field, string $subdir, string $prefix, ?string $currentImage): ?string
    {
        self::$error = null;

        if (!isset($_FILES[$field]) || ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return $currentImage;
        }

        $file = $_FILES[$field];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            self::$error = 'Error al subir el archivo (código ' . $file['error'] . ').';
            return null;
        }

        if ($file['size'] > self::MAX_SIZE) {
            self::$error = 'La imagen no puede superar los 2 MB.';
            return null;
        }

        $info = @getimagesize($file['tmp_name']);
        if ($info === false) {
            self::$error = 'El archivo debe ser una imagen válida.';
            return null;
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, self::ALLOWED, true)) {
            self::$error = 'Formato no permitido (usa JPG, PNG, WEBP o GIF).';
            return null;
        }

        $dir = UPLOAD_PATH . trim($subdir, '/');
        if (!is_dir($dir) && !@mkdir($dir, 0775, true)) {
            self::$error = 'No se pudo crear el directorio de imágenes.';
            return null;
        }

        $filename = $prefix . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $destination = $dir . '/' . $filename;

        $moved = move_uploaded_file($file['tmp_name'], $destination);
        if (!$moved) {
            // Fallback para entornos donde move_uploaded_file no aplica
            $moved = @rename($file['tmp_name'], $destination);
        }
        if (!$moved) {
            self::$error = 'No se pudo mover el archivo al directorio de imágenes.';
            return null;
        }

        if ($currentImage !== null && $currentImage !== '' && basename($currentImage) !== $filename) {
            self::delete($currentImage);
        }

        return trim($subdir, '/') . '/' . $filename;
    }

    /**
     * Borra un archivo subido de forma segura (sin permitir ".." ni barras).
     */
    public static function delete(?string $path): void
    {
        if ($path === null || $path === '') {
            return;
        }
        $clean = str_replace(['\\', '..'], '', $path);
        $full = UPLOAD_PATH . ltrim($clean, '/');
        if (is_file($full)) {
            @unlink($full);
        }
    }
}
