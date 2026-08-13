<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Helpers\Auth;
use App\Helpers\Session;

/**
 * AuthMiddleware
 *
 * Verifica que exista una sesión autenticada.
 * Si no la hay, redirige al login.
 */
class AuthMiddleware
{
    public function handle(Request $request): void
    {
        if (Auth::check()) {
            return;
        }

        Session::flash('error', 'Debes iniciar sesión para acceder al panel.');
        header('Location: ' . APP_URL . '/admin.php/login');
        exit;
    }
}
