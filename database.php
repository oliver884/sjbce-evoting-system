<?php
/**
 * Database connection (PDO / MySQL 8)
 * Update these four constants for your environment.
 */
define('DB_HOST', 'localhost');
define('DB_NAME', 'sjbce_election');
define('DB_USER', 'root');
define('DB_PASS', '');

function get_db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false, // real prepared statements -> SQL injection protection
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            // Match PHP's Africa/Accra timezone (UTC+0) so NOW() in MySQL agrees
            // with PHP's clock — prevents election windows from silently drifting.
            $pdo->exec("SET time_zone = '+00:00'");
        } catch (PDOException $e) {
            // Never leak DB details to the browser
            error_log('DB connection failed: ' . $e->getMessage());
            http_response_code(500);
            die('A system error occurred. Please try again later.');
        }
    }

    return $pdo;
}
