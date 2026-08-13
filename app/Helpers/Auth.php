<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Models\User;
use App\Models\Role;

/**
 * Auth
 *
 * Autenticación de usuarios del panel de administración.
 * Usa password_hash / password_verify (bcrypt) y sesión.
 */
class Auth
{
    private const SESSION_KEY = 'auth_user_id';

    /**
     * Intenta autenticar un usuario por email + contraseña.
     */
    public static function attempt(string $email, string $password): bool
    {
        $user = User::whereFirst(['email' => $email]);

        if ($user === null) {
            return false;
        }

        if ((int) $user->getAttribute('is_active') !== 1) {
            return false;
        }

        if (!password_verify($password, (string) $user->getAttribute('password'))) {
            return false;
        }

        // Regenera el ID de sesión para prevenir session fixation
        Session::regenerate();
        Session::set(self::SESSION_KEY, (int) $user->getAttribute('id'));

        User::updateWhere(['id' => (int) $user->getAttribute('id')], ['last_login' => date('Y-m-d H:i:s')]);

        return true;
    }

    /**
     * Verifica si hay una sesión autenticada.
     */
    public static function check(): bool
    {
        return Session::get(self::SESSION_KEY) !== null;
    }

    /**
     * Retorna el usuario autenticado o null.
     */
    public static function user(): ?User
    {
        $id = Session::get(self::SESSION_KEY);
        if ($id === null) {
            return null;
        }

        $user = User::find((int) $id);
        if ($user === null || (int) $user->getAttribute('is_active') !== 1) {
            self::logout();
            return null;
        }

        return $user;
    }

    /**
     * ID del usuario autenticado.
     */
    public static function id(): ?int
    {
        $id = Session::get(self::SESSION_KEY);
        return $id === null ? null : (int) $id;
    }

    /**
     * Verifica que el usuario tenga un rol específico.
     */
    public static function isRole(string $roleName): bool
    {
        $user = self::user();
        if ($user === null) {
            return false;
        }

        $role = Role::find((int) $user->getAttribute('role_id'));
        if ($role === null) {
            return false;
        }

        return strcasecmp((string) $role->getAttribute('name'), $roleName) === 0;
    }

    /**
     * Cierra la sesión del usuario.
     */
    public static function logout(): void
    {
        Session::remove(self::SESSION_KEY);
    }
}
