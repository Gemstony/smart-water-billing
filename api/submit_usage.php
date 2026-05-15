<?php
// API: Submit water usage (front controller)
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

requireAuth();

$input = json_decode(file_get_contents('php://input'), true);
$meterReading = $input['meter_reading'] ?? 0;

// TODO: Calculate units consumed and process billing

header('Content-Type: application/json');
echo json_encode(['success' => true, 'message' => 'Usage submitted']);