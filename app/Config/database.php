<?php

namespace App\Config;

use App\Core\Config;

/**
 * Configuración de la base de datos.
 * Los valores provienen del archivo .env mediante App\Core\Config.
 */
$dbUser = (string) Config::get('DB_USER');
$dbPass = (string) Config::get('DB_PASS');

if ($dbUser === '' || $dbPass === '') {
    throw new \RuntimeException(
        'DB_USER o DB_PASS no están definidos en .env. Crea un usuario dedicado con privilegios mínimos.'
    );
}

return [
    'host'     => Config::get('DB_HOST', '127.0.0.1'),
    'port'     => Config::get('DB_PORT', '3306'),
    'database' => Config::get('DB_NAME', ''),
    'username' => $dbUser,
    'password' => $dbPass,
    'charset'  => Config::get('DB_CHARSET', 'utf8mb4'),
    'options'  => [
        \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        \PDO::ATTR_EMULATE_PREPARES   => false,
        \PDO::ATTR_PERSISTENT         => false,
    ],
];
