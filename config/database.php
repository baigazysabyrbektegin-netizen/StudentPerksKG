<?php
declare(strict_types=1);

/**
 * Copy this file's settings into your local Laragon configuration or set
 * DB_HOST, DB_NAME, DB_USER, and DB_PASS as environment variables.
 */
$databaseHost = getenv('DB_HOST') ?: '127.0.0.1';
$databaseName = getenv('DB_NAME') ?: 'studentperks_kg';
$databaseUser = getenv('DB_USER') ?: 'root';
$databasePassword = getenv('DB_PASS') ?: '';

$dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $databaseHost, $databaseName);

try {
    $pdo = new PDO($dsn, $databaseUser, $databasePassword, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $exception) {
    error_log('StudentPerks database connection failed: ' . $exception->getMessage());
    http_response_code(503);
    exit('The StudentPerks database is currently unavailable. Please check the local setup.');
}
