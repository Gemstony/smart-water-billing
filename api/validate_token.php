<?php
// API: Validate a token code
require_once __DIR__ . '/../includes/config.php';

$input = json_decode(file_get_contents('php://input'), true);
$tokenCode = $input['code'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM tokens WHERE code = ? AND status = 'unused'");
$stmt->execute([$tokenCode]);
$token = $stmt->fetch(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
if ($token) {
    echo json_encode(['valid' => true, 'denomination' => $token['denomination']]);
} else {
    echo json_encode(['valid' => false, 'message' => 'Invalid or already used token']);
}