<?php
// Allow requests from any origin (for ESP32 / mobile app)
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed. Use POST.']);
    exit;
}

// Include database config and models
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Token.php';

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    // Also try to read as form-data (just in case)
    $input = $_POST;
}

$tokenCode = $input['token_code'] ?? '';
$meterId = $input['meter_id'] ?? '';

if (empty($tokenCode) || empty($meterId)) {
    echo json_encode(['success' => false, 'message' => 'Missing token_code or meter_id']);
    exit;
}

// Find user by meter_id
$userModel = new User($pdo);
$user = $userModel->findByMeterId($meterId);

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'Invalid meter ID']);
    exit;
}

// Validate token
$tokenModel = new Token($pdo);
$token = $tokenModel->findValidToken($tokenCode, $user['user_id']);

if (!$token) {
    echo json_encode(['success' => false, 'message' => 'Invalid or expired token']);
    exit;
}

// Mark token as used
$tokenModel->markAsUsed($token['token_id']);

// Update user's water balance
$newBalance = $user['account_balance'] + $token['units_purchased'];
$userModel->updateBalance($user['user_id'], $newBalance);

// Success response
echo json_encode([
    'success' => true,
    'message' => 'Token accepted',
    'units_added' => $token['units_purchased'],
    'new_balance' => $newBalance,
    'meter_id' => $meterId
]);