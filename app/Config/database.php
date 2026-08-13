<?php

namespace App\Config;

use App\Core\Config;

/**
 * Configuración de la base de datos.
 * Los valores provienen del archivo .env mediante App\Core\Config.
 */
return [
    'host'     => Config::get('DB_HOST', '127.0.0.1'),
    'port'     => Config::get('DB_PORT', '3306'),
    'database' => Config::get('DB_NAME', ''),
    'username' => Config::get('DB_USER', 'root'),
    'password' => Config::get('DB_PASS', ''),
    'charset'  => Config::get('DB_CHARSET', 'utf8mb4'),
    'options'  => [
        \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        \PDO::ATTR_EMULATE_PREPARES   => false,
        \PDO::ATTR_PERSISTENT         => false,
    ],
];
