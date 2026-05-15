<?php
class WaterUsage {
    public $usage_id;
    public $meter_id;
    public $user_id;
    public $water_used;
    public $log_time;

    public function __construct($data = []) {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    public function save($pdo) {
        $stmt = $pdo->prepare("INSERT INTO water_usage (meter_id, user_id, water_used, log_time) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$this->meter_id, $this->user_id, $this->water_used, $this->log_time]);
    }

    public static function getByUser($pdo, $userId, $startDate = null, $endDate = null) {
        $sql = "SELECT * FROM water_usage WHERE user_id = ?";
        $params = [$userId];
        if ($startDate && $endDate) {
            $sql .= " AND log_time BETWEEN ? AND ?";
            $params[] = $startDate;
            $params[] = $endDate;
        }
        $sql .= " ORDER BY log_time DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return array_map(fn($row) => new self($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public static function getTotalUsage($pdo, $userId, $startDate = null, $endDate = null) {
        $sql = "SELECT SUM(water_used) as total FROM water_usage WHERE user_id = ?";
        $params = [$userId];
        if ($startDate && $endDate) {
            $sql .= " AND log_time BETWEEN ? AND ?";
            $params[] = $startDate;
            $params[] = $endDate;
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (float) ($result['total'] ?? 0);
    }

    public static function getByMeter($pdo, $meterId) {
        $stmt = $pdo->prepare("SELECT * FROM water_usage WHERE meter_id = ? ORDER BY log_time DESC");
        $stmt->execute([$meterId]);
        return array_map(fn($row) => new self($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }
}