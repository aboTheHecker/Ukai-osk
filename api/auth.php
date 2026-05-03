<?php
require_once '../includes/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

// TEMP DEBUG — remove after fixing
if (isset($_GET['debug'])) {
    die(json_encode([
        'host' => getenv('MYSQLHOST'),
        'db'   => getenv('MYSQLDATABASE'),
        'user' => getenv('MYSQLUSER'),
        'port' => getenv('MYSQLPORT'),
        'conn' => ($conn instanceof mysqli && !$conn->connect_error) ? 'OK' : $conn->connect_error,
    ]));
}