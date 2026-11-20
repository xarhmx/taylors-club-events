<?php
/*
 * includes/db.php
 * ------------------------------------------------------------
 * Connects to the MySQL database using PDO and gives every page
 * a $pdo variable.
 *
 * Security:
 *  - All queries in this project use PREPARED STATEMENTS
 *    ($pdo->prepare(...) + execute([...])), so user input is never
 *    glued into SQL. This stops SQL injection.
 *  - Real emulation of prepared statements is turned off so MySQL
 *    itself handles the values.
 *  - If the connection fails, visitors see a friendly message, not
 *    the database password or error details.
 * ------------------------------------------------------------
 */
require_once __DIR__ . '/config.php';

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,  // throw errors instead of failing silently
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,        // rows come back as ['column' => value]
            PDO::ATTR_EMULATE_PREPARES   => false,                   // real prepared statements
        ]
    );
} catch (PDOException $exception) {
    error_log('Database connection failed: ' . $exception->getMessage());
    http_response_code(500);
    exit(APP_DEBUG
        ? 'Database connection failed: ' . htmlspecialchars($exception->getMessage())
        : 'Sorry, the site cannot reach its database right now. Please try again later.');
}
