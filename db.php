<?php
// includes/db.php
declare(strict_types=1);

/**
 * Configure these for XAMPP:
 * - DB_HOST: usually '127.0.0.1'
 * - DB_USER: usually 'root'
 * - DB_PASS: usually ''
 * - DB_NAME: 'restorativecare'
 */
$DB_HOST = 'localhost';
$DB_PORT = '3307';
$DB_NAME = 'restorativecare';
$DB_USER = 'root';
$DB_PASS = '';

$dsn = "mysql:host={$DB_HOST};port={$DB_PORT};dbname={$DB_NAME};charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    die('Database connection failed.');
}
