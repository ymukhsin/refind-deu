<?php
/**
 * ReFind — PDO database connection wrapper (singleton).
 *
 * Provides a single shared PDO connection for the whole request.
 */

require_once __DIR__ . '/../config/config.php';

class Database
{
    /** @var PDO|null */
    private static $connection = null;

    /**
     * Private constructor to prevent direct instantiation.
     */
    private function __construct()
    {
    }

    /**
     * Prevent cloning of the singleton.
     */
    private function __clone()
    {
    }

    /**
     * Prevent unserializing the singleton.
     */
    public function __wakeup()
    {
        throw new \RuntimeException('Cannot unserialize a singleton.');
    }

    /**
     * Return the shared PDO connection, creating it on first use.
     *
     * @return PDO
     */
    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=utf8mb4',
                DB_HOST,
                DB_NAME
            );

            self::$connection = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        }

        return self::$connection;
    }
}
