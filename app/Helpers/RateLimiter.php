<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * RateLimiter
 *
 * Limitador de intentos basado en archivos. Cuenta los intentos por clave
 * (por ejemplo, IP o email) dentro de una ventana de tiempo y permite
 * bloquear temporalmente cuando se supera el máximo.
 *
 * No requiere base de datos: cada clave se guarda como un archivo JSON
 * en el directorio configurado (fuera del docroot).
 */
final class RateLimiter
{
    /**
     * @param string $dir Directorio donde se guardan los contadores.
     */
    public function __construct(private readonly string $dir)
    {
        if (!is_dir($this->dir)) {
            @mkdir($this->dir, 0775, true);
        }
    }

    /**
     * Indica si la clave ya superó el número máximo de intentos.
     */
    public function tooManyAttempts(string $key, int $maxAttempts): bool
    {
        return $this->attempts($key) >= $maxAttempts;
    }

    /**
     * Registra un intento para la clave y devuelve el total acumulado.
     */
    public function hit(string $key, int $decaySeconds): int
    {
        $data = $this->read($key);

        if ($data === null || (int) ($data['reset_at'] ?? 0) <= time()) {
            $data = ['count' => 0, 'reset_at' => time() + $decaySeconds];
        }

        $data['count'] = (int) ($data['count'] ?? 0) + 1;
        $this->write($key, $data);

        return $data['count'];
    }

    /**
     * Número de intentos vigentes para la clave (0 si la ventana expiró).
     */
    public function attempts(string $key): int
    {
        $data = $this->read($key);

        if ($data === null || (int) ($data['reset_at'] ?? 0) <= time()) {
            return 0;
        }

        return (int) ($data['count'] ?? 0);
    }

    /**
     * Segundos restantes hasta que la clave se libere.
     */
    public function availableIn(string $key): int
    {
        $data = $this->read($key);

        if ($data === null) {
            return 0;
        }

        return max(0, (int) ($data['reset_at'] ?? 0) - time());
    }

    /**
     * Elimina el contador de la clave (por ejemplo, tras un login exitoso).
     */
    public function clear(string $key): void
    {
        $file = $this->file($key);
        if (is_file($file)) {
            @unlink($file);
        }
    }

    private function read(string $key): ?array
    {
        $file = $this->file($key);
        if (!is_file($file)) {
            return null;
        }

        $raw = @file_get_contents($file);
        if ($raw === false || $raw === '') {
            return null;
        }

        $data = json_decode($raw, true);

        return is_array($data) ? $data : null;
    }

    private function write(string $key, array $data): void
    {
        @file_put_contents($this->file($key), json_encode($data), LOCK_EX);
    }

    private function file(string $key): string
    {
        return rtrim($this->dir, '/\\') . '/' . hash('sha256', $key) . '.json';
    }
}
