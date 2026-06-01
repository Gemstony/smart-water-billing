<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../controllers/TokenController.php';

// Enable error logging for debugging
ini_set('log_errors', 1);
error_log("=== AzamPay Webhook Called ===");

// Get raw input
$rawInput = file_get_contents('php://input');
error_log("Raw input: " . $rawInput);

$data = json_decode($rawInput, true);
if (!$data) {
    error_log("Invalid JSON payload");
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
    exit;
}

// Map AzamPay fields to our expected variables
// Emulated response uses: utilityref, transid, transactionstatus, amount, operator
$externalId = $data['utilityref'] ?? $data['externalId'] ?? null;
$transactionId = $data['transid'] ?? $data['transactionId'] ?? null;
$status = $data['transactionstatus'] ?? $data['status'] ?? '';
$amount = $data['amount'] ?? null;
$provider = $data['operator'] ?? $data['provider'] ?? null;

error_log("Mapped: externalId=$externalId, transactionId=$transactionId, status=$status, amount=$amount, provider=$provider");

if (!$externalId) {
    error_log("Missing externalId (utilityref)");
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing externalId']);
    exit;
}

// Find the pending transaction by external_id (utilityref)
$stmt = $pdo->prepare("
    SELECT * FROM transactions 
    WHERE external_id = ? AND status = 'pending'
    LIMIT 1
");
$stmt->execute([$externalId]);
$transaction = $stmt->fetch();

if (!$transaction) {
    error_log("Transaction not found for external_id: $externalId");
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Transaction not found']);
    exit;
}

// Check if payment was successful
$isCompleted = (stripos($status, 'success') !== false || $status === 'success');

if ($isCompleted) {
    // Mark transaction as completed
    $stmt = $pdo->prepare("
        UPDATE transactions 
        SET status = 'completed', completed_at = NOW(), mpesa_receipt = ? 
        WHERE transaction_id = ?
    ");
    $stmt->execute([$transactionId, $transaction['transaction_id']]);
    
    // Generate token for the user
    $tokenController = new TokenController($pdo);
    $result = $tokenController->generateToken(
        $transaction['user_id'],
        $transaction['water_units'],
        $transaction['payment_method']
    );
    
    if ($result['success']) {
        error_log("Token generated successfully: " . $result['token']);
        echo json_encode(['success' => true, 'message' => 'Token generated', 'token' => $result['token']]);
    } else {
        error_log("Token generation failed: " . $result['message']);
        echo json_encode(['success' => false, 'message' => 'Token generation failed']);
    }
} else {
    // Mark as failed
    $stmt = $pdo->prepare("UPDATE transactions SET status = 'failed' WHERE transaction_id = ?");
    $stmt->execute([$transaction['transaction_id']]);
    error_log("Payment failed for transaction: {$transaction['transaction_id']}");
    echo json_encode(['success' => false, 'message' => 'Payment failed']);
}

http_response_code(200);
?>