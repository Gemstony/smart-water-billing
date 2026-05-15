<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

class EspDeviceController {
    private $pdo;

    public function __construct($pdo = null) {
        global $pdo;
        $globalPdo = $pdo;
        $this->pdo = $pdo ?? $globalPdo;
    }

    // Register a new ESP device
    public function register() {
        $input = json_decode(file_get_contents('php://input'), true);
        $meterId = $input['meter_id'] ?? '';
        $firmwareVersion = $input['firmware_version'] ?? '1.0.0';

        if (empty($meterId)) {
            http_response_code(400);
            echo json_encode(['error' => 'meter_id is required']);
            return;
        }

        $device = EspDevice::findByMeterId($this->pdo, $meterId);
        if ($device) {
            http_response_code(409);
            echo json_encode(['error' => 'Device already registered']);
            return;
        }

        $device = new EspDevice([
            'meter_id' => $meterId,
            'firmware_version' => $firmwareVersion,
            'valve_status' => 1,
            'status' => 'active'
        ]);
        $device->save($this->pdo);

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Device registered successfully']);
    }

    // ESP device heartbeat / check-in
    public function heartbeat() {
        requireAuth();
        $input = json_decode(file_get_contents('php://input'), true);
        $meterId = $input['meter_id'] ?? '';
        $firmwareVersion = $input['firmware_version'] ?? null;
        $valveStatus = $input['valve_status'] ?? null;

        $device = EspDevice::findByMeterId($this->pdo, $meterId);
        if (!$device) {
            http_response_code(404);
            echo json_encode(['error' => 'Device not found']);
            return;
        }

        if ($firmwareVersion !== null) $device->firmware_version = $firmwareVersion;
        if ($valveStatus !== null) $device->valve_status = $valveStatus;
        $device->last_seen = date('Y-m-d H:i:s');
        $device->save($this->pdo);

        header('Content-Type: application/json');
        $stmt = $this->pdo->prepare("SELECT account_balance FROM users WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'balance' => $user['account_balance'] ?? 0,
            'valve_command' => $device->valve_status
        ]);
    }

    // Get all devices (admin)
    public function listDevices() {
        requireAdmin();
        $devices = EspDevice::getAll($this->pdo);
        header('Content-Type: application/json');
        echo json_encode(['devices' => $devices]);
    }

    // Get devices for a specific user
    public function userDevices() {
        requireAuth();
        $devices = EspDevice::getByUser($this->pdo, $_SESSION['user_id']);
        header('Content-Type: application/json');
        echo json_encode(['devices' => $devices]);
    }

    // Assign device to user
    public function assignUser($deviceId) {
        requireAdmin();
        $input = json_decode(file_get_contents('php://input'), true);
        $userId = $input['user_id'] ?? null;

        $device = EspDevice::find($this->pdo, $deviceId);
        if (!$device) {
            http_response_code(404);
            echo json_encode(['error' => 'Device not found']);
            return;
        }

        $device->user_id = $userId;
        $device->save($this->pdo);

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Device assigned to user']);
    }

    // Update valve status
    public function setValve($deviceId) {
        requireAdmin();
        $input = json_decode(file_get_contents('php://input'), true);
        $valveStatus = $input['valve_status'] ?? null;

        if ($valveStatus === null) {
            http_response_code(400);
            echo json_encode(['error' => 'valve_status required']);
            return;
        }

        $device = EspDevice::find($this->pdo, $deviceId);
        if (!$device) {
            http_response_code(404);
            echo json_encode(['error' => 'Device not found']);
            return;
        }

        $device->valve_status = $valveStatus;
        $device->save($this->pdo);

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'valve_status' => $device->valve_status]);
    }
}