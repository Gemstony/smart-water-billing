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


// Add to the bottom of /smart-water-billing/includes/config.php
define('AZAMPAY_APP_NAME', getenv('AZAMPAY_APP_NAME') ?: 'WaterBilling');
define('AZAMPAY_CLIENT_ID', getenv('AZAMPAY_CLIENT_ID') ?: 'b8a0aa66-4ac4-41f4-aaf1-23073dff9789');
define('AZAMPAY_CLIENT_SECRET', getenv('AZAMPAY_CLIENT_SECRET') ?: 'OlAanBTm1o23Z4Gfu+h/PrHTn4dgzxXAsjiUVnRUZEvVWPCM81APCEkJuMFcB7kQ+GS6BVCiiUkj8lhOCb8Su9ObO3aDxKlgLvF2ocKMXn6p48L5+ttMPjYEF+YeCCRyiZfNNy4lTQqpRCefYCB/RCa4VB98Q0ZMYs6j9TU9J4Gk0DnlwpFMv+iBcz/ADlXE6FUxqt1S8HX7qxqOQSJaxHWQgW6haJ4x3Czz6F8LgfrXSqXXVDt7sCOyDqV5kLSv9NoMwvau4q78nkV5H4aKwgcTShH4L0Lem+daXhimyckGreunaNoTrM/TpyK2U39eU2eBKZVuAxoWWhqekBfotdLeLJchuQNFhQ5eO3N6gZCjmJ76S0XAHhfvve6zhgrHLK7P88SbGBK8UM2v4r32+ywNykx8lWYetGKOTkrQx5iJNp792Hzl1CyKLujcuagkjeCWvGjr6mWOTW4bHq/x7u8OxWiv4fHGN6WTSsiDteNDleDTO1NGhJTD3bCj2c1WcvjmYmZmy7nerp5EMqqsUW/DeBPlyDRt7dCDfLd6b858NifK8jtY2QHIkhpjxkClU9ChVRnuXhUCTmpLVG4Wqya9ZOWSzrcU1AADuvcDGn0Zz8O9Wu2EKwpWTGNgSeCH6faghJ7uCN1xBmKiy/W6y42H32ptybaxrMKRN6R76js=');
define('AZAMPAY_API_KEY', getenv('AZAMPAY_API_KEY') ?: 'OlAanBTm1o23Z4Gfu+h/PrHTn4dgzxXAsjiUVnRUZEvVWPCM81APCEkJuMFcB7kQ+GS6BVCiiUkj8lhOCb8Su9ObO3aDxKlgLvF2ocKMXn6p48L5+ttMPjYEF+YeCCRyiZfNNy4lTQqpRCefYCB/RCa4VB98Q0ZMYs6j9TU9J4Gk0DnlwpFMv+iBcz/ADlXE6FUxqt1S8HX7qxqOQSJaxHWQgW6haJ4x3Czz6F8LgfrXSqXXVDt7sCOyDqV5kLSv9NoMwvau4q78nkV5H4aKwgcTShH4L0Lem+daXhimyckGreunaNoTrM/TpyK2U39eU2eBKZVuAxoWWhqekBfotdLeLJchuQNFhQ5eO3N6gZCjmJ76S0XAHhfvve6zhgrHLK7P88SbGBK8UM2v4r32+ywNykx8lWYetGKOTkrQx5iJNp792Hzl1CyKLujcuagkjeCWvGjr6mWOTW4bHq/x7u8OxWiv4fHGN6WTSsiDteNDleDTO1NGhJTD3bCj2c1WcvjmYmZmy7nerp5EMqqsUW/DeBPlyDRt7dCDfLd6b858NifK8jtY2QHIkhpjxkClU9ChVRnuXhUCTmpLVG4Wqya9ZOWSzrcU1AADuvcDGn0Zz8O9Wu2EKwpWTGNgSeCH6faghJ7uCN1xBmKiy/W6y42H32ptybaxrMKRN6R76js=');
define('AZAMPAY_ENVIRONMENT', getenv('AZAMPAY_ENVIRONMENT') ?: 'sandbox');
?>