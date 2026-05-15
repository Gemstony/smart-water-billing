<?php
session_start(); // Start session for login management

$host = 'localhost';
$dbname = 'smart_water_billing';  // Change to your actual database name
$username = 'root';          // Your MySQL username
$password = '';              // Your MySQL password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

define('BASE_URL', '/smart-water-billing/');

// Optional: Function to quickly get PDO instance
function getDB() {
    global $pdo;
    return $pdo;
}
?>