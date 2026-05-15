<?php
class EspDevice {
    public $device_id;
    public $meter_id;
    public $user_id;
    public $firmware_version;
    public $last_seen;
    public $valve_status;
    public $status;
    public $created_at;

    public function __construct($data = []) {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    public function save($pdo) {
        if ($this->device_id) {
            $stmt = $pdo->prepare("UPDATE esp_devices SET meter_id = ?, user_id = ?, firmware_version = ?, valve_status = ?, status = ? WHERE device_id = ?");
            return $stmt->execute([$this->meter_id, $this->user_id, $this->firmware_version, $this->valve_status, $this->status, $this->device_id]);
        }
        $stmt = $pdo->prepare("INSERT INTO esp_devices (meter_id, user_id, firmware_version, valve_status, status) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$this->meter_id, $this->user_id, $this->firmware_version, $this->valve_status, $this->status]);
    }

    public static function find($pdo, $id) {
        $stmt = $pdo->prepare("SELECT * FROM esp_devices WHERE device_id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? new self($row) : null;
    }

    public static function findByMeterId($pdo, $meterId) {
        $stmt = $pdo->prepare("SELECT * FROM esp_devices WHERE meter_id = ?");
        $stmt->execute([$meterId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? new self($row) : null;
    }

    public static function getAll($pdo) {
        $stmt = $pdo->query("SELECT * FROM esp_devices ORDER BY device_id DESC");
        return array_map(fn($row) => new self($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public static function getByUser($pdo, $userId) {
        $stmt = $pdo->prepare("SELECT * FROM esp_devices WHERE user_id = ?");
        $stmt->execute([$userId]);
        return array_map(fn($row) => new self($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }
}