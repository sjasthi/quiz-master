<?php
/**
 * Shared PDO database connection for Quiz Master.
 *
 * Include this near the top of any page that needs the database:
 *
 *   require_once __DIR__ . '/../includes/db.php';
 *
 * After including, use the $pdo object for queries, e.g.:
 *
 *   $stmt = $pdo->query("SELECT * FROM quizzes");
 *   $quizzes = $stmt->fetchAll();
 */

require_once __DIR__ . '/config.php';

$dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,   // throw on errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,         // fetch as assoc arrays
    PDO::ATTR_EMULATE_PREPARES   => false,                   // use real prepared statements
];

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // For development we show the error. Before deploying to learnandhelp.com,
    // switch this to a generic message and log the real error server-side.
    die('Database connection failed: ' . $e->getMessage());
}
