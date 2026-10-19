<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Helpers\Auth;
use App\Helpers\RateLimiter;
use App\Helpers\Session;

/**
 * AuthController
 *
 * Autenticación del panel de administración (login / logout).
 */
class AuthController extends Controller
{
    /**
     * Máximo de intentos fallidos por combinación email+IP.
     */
    private const MAX_ATTEMPTS = 5;

    /**
     * Máximo de intentos fallidos por IP (para frenar probar muchas cuentas).
     */
    private const MAX_IP_ATTEMPTS = 20;

    /**
     * Ventana de bloqueo en segundos (15 minutos).
     */
    private const DECAY_SECONDS = 900;

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

        // Limitador de intentos (anti fuerza bruta)
        // - Clave combinada email+IP: frena el ataque sobre una cuenta concreta.
        // - Clave solo IP: frena el probar muchas cuentas distintas desde un mismo origen.
        $limiter = new RateLimiter(BASE_PATH . '/storage/ratelimit');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $ipKey = 'login:ip:' . $ip;
        $pairKey = 'login:pair:' . mb_strtolower($email) . ':' . $ip;

        $pairBlocked = $limiter->tooManyAttempts($pairKey, self::MAX_ATTEMPTS);
        $ipBlocked = $limiter->tooManyAttempts($ipKey, self::MAX_IP_ATTEMPTS);

        if ($pairBlocked || $ipBlocked) {
            $wait = max($limiter->availableIn($pairKey), $limiter->availableIn($ipKey));
            $minutes = max(1, (int) ceil($wait / 60));
            Session::flash('error', "Demasiados intentos fallidos. Vuelve a intentarlo en {$minutes} minuto(s).");
            return $this->redirect('/admin.php/login');
        }

        if (Auth::attempt($email, $password)) {
            $limiter->clear($pairKey);
            $limiter->clear($ipKey);
            Session::flash('success', 'Bienvenido de nuevo.');
            return $this->redirect('/admin.php/dashboard');
        }

        $limiter->hit($pairKey, self::DECAY_SECONDS);
        $limiter->hit($ipKey, self::DECAY_SECONDS);

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
