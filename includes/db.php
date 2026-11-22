<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';

$dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';

$dbusername = DB_USER;
$dbpassword = DB_PASS;

try {
    $pdo = new PDO($dsn, $dbusername, $dbpassword, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false
]);

} catch (PDOException $e) {
    error_log('Database connection failed: ' . $e->getMessage());

    http_response_code(500);

    exit('Database connection failed. Check your MySQL settings.');
}
