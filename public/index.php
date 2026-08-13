<?php

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/vendor/autoload.php';

use App\Core\Config;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Controllers\Public\HomeController;

Config::load(BASE_PATH);
require BASE_PATH . '/app/Config/config.php';

/**
 * FRONT-CONTROLLER — Vista Pública
 * Registra las rutas y despacha la petición.
 */
$router = new Router();
$router->setBasePath(parse_url(APP_URL, PHP_URL_PATH) ?: '');

// Rutas de la vista pública
$router->get('/', HomeController::class . '@index');
$router->get('/services', HomeController::class . '@services');
$router->get('/products', HomeController::class . '@products');
$router->get('/team', HomeController::class . '@team');
$router->get('/gallery', HomeController::class . '@gallery');

$request = Request::createFromGlobals();
$response = $router->dispatch($request);

$response->send();
