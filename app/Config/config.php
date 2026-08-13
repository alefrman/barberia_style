<?php

namespace App\Config;

use App\Core\Config;

Config::load(BASE_PATH);

define('APP_NAME', 'Barbería Style');
define('APP_VERSION', '1.0.0');
define('APP_DEBUG', Config::get('APP_ENV', 'development') === 'development');

define('APP_URL', rtrim((string) Config::get('APP_URL', 'http://localhost'), '/'));
define('UPLOAD_DIR', APP_URL . '/assets/uploads/');
define('UPLOAD_PATH', BASE_PATH . '/public/assets/uploads/');

define('SESSION_SECRET', Config::get('SESSION_SECRET', 'barberia_style_secret'));
define('TIMEZONE', 'America/El_Salvador');
define('CURRENCY', 'USD');

date_default_timezone_set(TIMEZONE);
mb_internal_encoding('UTF-8');

if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

require_once __DIR__ . '/database.php';
