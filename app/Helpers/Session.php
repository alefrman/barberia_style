<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Session
 *
 * Gestión de la sesión PHP: inicio, datos, CSRF y mensajes flash.
 */
class Session
{
    private static bool $started = false;

    /**
     * Inicia la sesión una sola vez por request.
     */
    public static function start(): void
    {
        if (self::$started) {
            return;
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_name('barberia_style_session');
            session_start([
                'cookie_httponly' => true,
                'cookie_samesite' => 'Lax',
                'use_strict_mode' => true,
            ]);
        }

        self::$started = true;
    }

    public static function set(string $key, mixed $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        self::start();
        return array_key_exists($key, $_SESSION);
    }

    public static function remove(string $key): void
    {
        self::start();
        unset($_SESSION[$key]);
    }

    /**
     * Destruye la sesión completa.
     */
    public static function destroy(): void
    {
        self::start();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        session_destroy();
    }

    /**
     * Regenera el ID de sesión (importante tras el login).
     */
    public static function regenerate(): void
    {
        self::start();
        session_regenerate_id(true);
    }

    /* ============================================================
     * CSRF
     * ========================================================== */

    /**
     * Obtiene (y crea si no existe) el token CSRF de la sesión.
     */
    public static function csrfToken(): string
    {
        self::start();
        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf_token'];
    }

    /**
     * Verifica el token CSRF enviado.
     */
    public static function verifyCsrf(?string $token): bool
    {
        self::start();
        return is_string($token)
            && isset($_SESSION['_csrf_token'])
            && hash_equals($_SESSION['_csrf_token'], $token);
    }

    /* ============================================================
     * Mensajes flash
     * ========================================================== */

    public static function flash(string $type, string $message): void
    {
        self::start();
        $_SESSION['_flash'][] = ['type' => $type, 'message' => $message];
    }

    /**
     * Obtiene y limpia los mensajes flash pendientes.
     */
    public static function getFlashes(): array
    {
        self::start();
        $flashes = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);
        return $flashes;
    }
}
