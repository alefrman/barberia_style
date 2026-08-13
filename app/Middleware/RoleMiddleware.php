<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Helpers\Auth;
use App\Helpers\Session;

/**
 * RoleMiddleware
 *
 * Verifica que el usuario autenticado tenga el rol requerido.
 * Uso: new RoleMiddleware('Superadmin')
 * Se registra en el Router como: [RoleMiddleware::class, ['Superadmin']]
 */
class RoleMiddleware
{
    public function __construct(private readonly string $role) {}

    public function handle(Request $request): void
    {
        if (!Auth::check()) {
            Session::flash('error', 'Debes iniciar sesión para acceder al panel.');
            header('Location: ' . APP_URL . '/admin.php/login');
            exit;
        }

        if (Auth::isRole($this->role)) {
            return;
        }

        http_response_code(403);
        Session::flash('error', 'No tienes permisos para acceder a esta sección.');
        header('Location: ' . APP_URL . '/admin.php/dashboard');
        exit;
    }
}
