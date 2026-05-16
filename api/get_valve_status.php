<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../models/User.php';

$method = $_SERVER['REQUEST_METHOD'];
if ($method !== 'GET' && $method !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$meterId = null;
if ($method === 'GET') {
    $meterId = $_GET['meter_id'] ?? null;
} else {
    $input = json_decode(file_get_contents('php://input'), true);
    if ($input) {
        $meterId = $input['meter_id'] ?? null;
    } else {
        $meterId = $_POST['meter_id'] ?? null;
    }
}

if (empty($meterId)) {
    echo json_encode(['success' => false, 'message' => 'Missing meter_id']);
    exit;
}

$userModel = new User($pdo);
$user = $userModel->findByMeterId($meterId);

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'Invalid meter_id']);
    exit;
}

$balance = floatval($user['account_balance']);
$valveStatus = ($balance > 0) ? 'open' : 'closed';
$command = ($valveStatus === 'open') ? 1 : 0;

// Update esp_devices table (log last seen and valve status)
$stmt = $pdo->prepare("
    INSERT INTO esp_devices (meter_id, user_id, last_seen, valve_status, status)
    VALUES (?, ?, NOW(), ?, 'active')
    ON DUPLICATE KEY UPDATE last_seen = NOW(), valve_status = ?, status = 'active'
");
$stmt->execute([$meterId, $user['user_id'], $valveStatus, $valveStatus]);

echo json_encode([
    'success' => true,
    'meter_id' => $meterId,
    'balance' => $balance,
    'valve_status' => $valveStatus,
    'command' => $command
]);