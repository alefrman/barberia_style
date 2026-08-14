<?php

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/vendor/autoload.php';

use App\Core\Config;
use App\Core\Request;
use App\Core\Router;
use App\Helpers\Session;
use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;
use App\Controllers\Admin\AuthController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\UserController;
use App\Controllers\Admin\AppointmentController;
use App\Controllers\Admin\ServiceController;
use App\Controllers\Admin\TeamController;
use App\Controllers\Admin\ProductController;
use App\Controllers\Admin\ExpenseController;
use App\Controllers\Admin\GalleryController;

Config::load(BASE_PATH);
require BASE_PATH . '/app/Config/config.php';

// Sesión (necesaria para auth, CSRF y flashes)
Session::start();

/**
 * FRONT-CONTROLLER — Panel de Administración
 * La app vive en /public/admin.php, por eso el base path incluye admin.php.
 */
define('ADMIN_URL', APP_URL . '/admin.php');

$router = new Router();
$router->setBasePath(parse_url(APP_URL, PHP_URL_PATH) . '/admin.php');

// ============ AUTENTICACIÓN (públicas) ============
$router->get('/login', AuthController::class . '@showLogin');
$router->post('/login', AuthController::class . '@login');
$router->get('/logout', AuthController::class . '@logout');

// ============ ÁREA PROTEGIDA ============
$router->get('/', DashboardController::class . '@index', [AuthMiddleware::class]);
$router->get('/dashboard', DashboardController::class . '@index', [AuthMiddleware::class]);

// ============ MÓDULO USUARIOS (solo Superadmin) ============
$router->get('/users', UserController::class . '@index', [AuthMiddleware::class, [RoleMiddleware::class, ['Superadmin']]]);
$router->get('/users/create', UserController::class . '@create', [AuthMiddleware::class, [RoleMiddleware::class, ['Superadmin']]]);
$router->post('/users/store', UserController::class . '@store', [AuthMiddleware::class, [RoleMiddleware::class, ['Superadmin']]]);
$router->get('/users/edit/{id}', UserController::class . '@edit', [AuthMiddleware::class, [RoleMiddleware::class, ['Superadmin']]]);
$router->post('/users/update/{id}', UserController::class . '@update', [AuthMiddleware::class, [RoleMiddleware::class, ['Superadmin']]]);
$router->post('/users/delete/{id}', UserController::class . '@destroy', [AuthMiddleware::class, [RoleMiddleware::class, ['Superadmin']]]);

// ============ MÓDULO CITAS ============
$router->get('/appointments', AppointmentController::class . '@index', [AuthMiddleware::class]);
$router->get('/appointments/create', AppointmentController::class . '@create', [AuthMiddleware::class]);
$router->post('/appointments/store', AppointmentController::class . '@store', [AuthMiddleware::class]);
$router->get('/appointments/show/{id}', AppointmentController::class . '@show', [AuthMiddleware::class]);
$router->get('/appointments/edit/{id}', AppointmentController::class . '@edit', [AuthMiddleware::class]);
$router->post('/appointments/update/{id}', AppointmentController::class . '@update', [AuthMiddleware::class]);
$router->post('/appointments/delete/{id}', AppointmentController::class . '@destroy', [AuthMiddleware::class]);

// ============ MÓDULO SERVICIOS ============
$router->get('/services', ServiceController::class . '@index', [AuthMiddleware::class]);
$router->get('/services/create', ServiceController::class . '@create', [AuthMiddleware::class]);
$router->post('/services/store', ServiceController::class . '@store', [AuthMiddleware::class]);
$router->get('/services/edit/{id}', ServiceController::class . '@edit', [AuthMiddleware::class]);
$router->post('/services/update/{id}', ServiceController::class . '@update', [AuthMiddleware::class]);
$router->post('/services/delete/{id}', ServiceController::class . '@destroy', [AuthMiddleware::class]);

// ============ MÓDULO BARBEROS ============
$router->get('/team', TeamController::class . '@index', [AuthMiddleware::class]);
$router->get('/team/create', TeamController::class . '@create', [AuthMiddleware::class]);
$router->post('/team/store', TeamController::class . '@store', [AuthMiddleware::class]);
$router->get('/team/edit/{id}', TeamController::class . '@edit', [AuthMiddleware::class]);
$router->post('/team/update/{id}', TeamController::class . '@update', [AuthMiddleware::class]);
$router->post('/team/toggle/{id}', TeamController::class . '@toggle', [AuthMiddleware::class]);
$router->post('/team/delete/{id}', TeamController::class . '@destroy', [AuthMiddleware::class]);

// ============ MÓDULO INVENTARIO ============
$router->get('/inventory', ProductController::class . '@index', [AuthMiddleware::class]);
$router->get('/inventory/create', ProductController::class . '@create', [AuthMiddleware::class]);
$router->post('/inventory/store', ProductController::class . '@store', [AuthMiddleware::class]);
$router->get('/inventory/edit/{id}', ProductController::class . '@edit', [AuthMiddleware::class]);
$router->post('/inventory/update/{id}', ProductController::class . '@update', [AuthMiddleware::class]);
$router->post('/inventory/toggle/{id}', ProductController::class . '@toggle', [AuthMiddleware::class]);
$router->post('/inventory/delete/{id}', ProductController::class . '@destroy', [AuthMiddleware::class]);

// ============ MÓDULO GASTOS ============
$router->get('/expenses', ExpenseController::class . '@index', [AuthMiddleware::class]);
$router->get('/expenses/create', ExpenseController::class . '@create', [AuthMiddleware::class]);
$router->post('/expenses/store', ExpenseController::class . '@store', [AuthMiddleware::class]);
$router->get('/expenses/edit/{id}', ExpenseController::class . '@edit', [AuthMiddleware::class]);
$router->post('/expenses/update/{id}', ExpenseController::class . '@update', [AuthMiddleware::class]);
$router->post('/expenses/delete/{id}', ExpenseController::class . '@destroy', [AuthMiddleware::class]);

// ============ MÓDULO GALERÍA ============
$router->get('/gallery', GalleryController::class . '@index', [AuthMiddleware::class]);
$router->get('/gallery/create', GalleryController::class . '@create', [AuthMiddleware::class]);
$router->post('/gallery/store', GalleryController::class . '@store', [AuthMiddleware::class]);
$router->get('/gallery/edit/{id}', GalleryController::class . '@edit', [AuthMiddleware::class]);
$router->post('/gallery/update/{id}', GalleryController::class . '@update', [AuthMiddleware::class]);
$router->post('/gallery/delete/{id}', GalleryController::class . '@destroy', [AuthMiddleware::class]);

$request = Request::createFromGlobals();
$response = $router->dispatch($request);

$response->send();
