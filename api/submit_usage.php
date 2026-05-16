<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/WaterUsage.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    // Try form-data as fallback
    $meterId = $_POST['meter_id'] ?? null;
    $waterUsed = $_POST['water_used'] ?? null;
} else {
    $meterId = $input['meter_id'] ?? null;
    $waterUsed = $input['water_used'] ?? null;
}

// Validate presence
if (empty($meterId) || empty($waterUsed)) {
    echo json_encode(['success' => false, 'message' => 'Missing meter_id or water_used']);
    exit;
}

// Convert water_used to float (cubic meters)
$waterUsed = floatval($waterUsed);
if ($waterUsed <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid water_used value']);
    exit;
}

$userModel = new User($pdo);
$user = $userModel->findByMeterId($meterId);

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'Invalid meter_id']);
    exit;
}

// Start transaction to ensure consistency
try {
    $pdo->beginTransaction();
    
    // Get current balance
    $currentBalance = $user['account_balance'];
    $newBalance = $currentBalance - $waterUsed;
    
    // If balance goes negative, we can either reject or allow and then shut off valve later
    // For now, we allow negative, but we'll flag for valve closure
    $valveStatus = ($newBalance >= 0) ? 'open' : 'closed';
    
    // Update user's balance
    $userModel->updateBalance($user['user_id'], $newBalance);
    
    // Log usage
    $waterUsageModel = new WaterUsage($pdo);
    $waterUsageModel->logUsage($meterId, $user['user_id'], $waterUsed);
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Usage logged successfully',
        'new_balance' => $newBalance,
        'valve_status' => $valveStatus
    ]);
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}