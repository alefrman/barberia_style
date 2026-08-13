<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Helpers\Auth;
use App\Helpers\Session;

/**
 * AuthController
 *
 * Autenticación del panel de administración (login / logout).
 */
class AuthController extends Controller
{
    /**
     * Muestra el formulario de login.
     */
    public function showLogin(Request $request, array $params): Response
    {
        if (Auth::check()) {
            return $this->redirect('/admin.php/dashboard');
        }

        return $this->viewRaw('admin/auth/login', [
            'title'   => 'Iniciar sesión',
            'csrf'    => Session::csrfToken(),
            'flashes' => Session::getFlashes(),
        ]);
    }

    /**
     * Procesa el login.
     */
    public function login(Request $request, array $params): Response
    {
        // Verificación CSRF
        $token = $request->input('_csrf');
        if (!Session::verifyCsrf(is_string($token) ? $token : null)) {
            Session::flash('error', 'Token de seguridad inválido. Intenta nuevamente.');
            return $this->redirect('/admin.php/login');
        }

        $email = trim((string) $request->input('email', ''));
        $password = (string) $request->input('password', '');

        if ($email === '' || $password === '') {
            Session::flash('error', 'Completa el email y la contraseña.');
            return $this->redirect('/admin.php/login');
        }

        if (Auth::attempt($email, $password)) {
            Session::flash('success', 'Bienvenido de nuevo.');
            return $this->redirect('/admin.php/dashboard');
        }

        Session::flash('error', 'Credenciales incorrectas o usuario inactivo.');
        return $this->redirect('/admin.php/login');
    }

    /**
     * Cierra la sesión.
     */
    public function logout(Request $request, array $params): Response
    {
        Auth::logout();
        Session::flash('success', 'Sesión cerrada correctamente.');
        return $this->redirect('/admin.php/login');
    }
}
