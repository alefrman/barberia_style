<?php

namespace App\Core;

use PDO;
use PDOException;

/**
 * Database
 *
 * Conexión PDO única (patrón Singleton).
 * - Modo excepciones (ERRMODE_EXCEPTION)
 * - Sentencias preparadas obligatorias
 * - Transacciones
 * - Seguridad contra SQL Injection
 */
final class Database
{
    private static ?PDO $instance = null;

    private function __construct() {}

    private function __clone() {}

    public static function connect(): PDO
    {
        if (self::$instance === null) {
            $host     = Config::get('DB_HOST', '127.0.0.1');
            $port     = Config::get('DB_PORT', '3306');
            $dbname   = Config::get('DB_NAME', '');
            $user     = Config::get('DB_USER', 'root');
            $password = Config::get('DB_PASS', '');
            $charset  = Config::get('DB_CHARSET', 'utf8mb4');

            $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_PERSISTENT         => false,
            ];

            try {
                self::$instance = new PDO($dsn, $user, $password, $options);
            } catch (PDOException $e) {
                error_log('[Database] Error de conexión: ' . $e->getMessage());
                throw new \RuntimeException(
                    'No se pudo establecer la conexión con la base de datos.'
                );
            }
        }

        return self::$instance;
    }

    public static function beginTransaction(): bool
    {
        return self::connect()->beginTransaction();
    }

    public static function commit(): bool
    {
        return self::connect()->commit();
    }

    public static function rollBack(): bool
    {
        return self::connect()->rollBack();
    }

    public static function lastInsertId(): int
    {
        return (int) self::connect()->lastInsertId();
    }

    /**
     * Ejecuta una consulta con sentencia preparada y retorna la cantidad de filas afectadas.
     */
    public static function execute(string $sql, array $params = []): int
    {
        $stmt = self::connect()->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    /**
     * Ejecuta una consulta y retorna todas las filas.
     */
    public static function fetchAll(string $sql, array $params = []): array
    {
        $stmt = self::connect()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Ejecuta una consulta y retorna una sola fila.
     */
    public static function fetch(string $sql, array $params = []): ?array
    {
        $stmt = self::connect()->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    /**
     * Ejecuta una consulta y retorna el primer valor de la primera fila.
     */
    public static function fetchValue(string $sql, array $params = []): mixed
    {
        $stmt = self::connect()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }
}
