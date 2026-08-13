<?php

namespace App\Core;

/**
 * Config
 *
 * Gestión centralizada de configuración.
 * Carga variables del archivo .env y provee acceso estático a las mismas.
 */
class Config
{
    private static array $items = [];
    private static bool $loaded = false;

    /**
     * Carga el archivo .env en memoria (una sola vez).
     */
    public static function load(string $basePath): void
    {
        if (self::$loaded) {
            return;
        }

        $envFile = rtrim($basePath, '/') . '/.env';

        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '' || str_starts_with($line, '#') || str_starts_with($line, ';')) {
                    continue;
                }
                if (!str_contains($line, '=')) {
                    continue;
                }
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                if ($key !== '') {
                    $_ENV[$key] = $value;
                    putenv("$key=$value");
                }
            }
        }

        self::$items = $_ENV;
        self::$loaded = true;
    }

    /**
     * Obtiene un valor de configuración.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return self::$items[$key] ?? $default;
    }

    /**
     * Define un valor de configuración en tiempo de ejecución.
     */
    public static function set(string $key, mixed $value): void
    {
        self::$items[$key] = $value;
    }

    /**
     * Verifica si un valor existe.
     */
    public static function has(string $key): bool
    {
        return array_key_exists($key, self::$items);
    }
}
