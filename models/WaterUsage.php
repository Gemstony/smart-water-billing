<?php
class WaterUsage {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Log water usage for a user
     * @param string $meterId
     * @param int $userId
     * @param float $waterUsed (in cubic meters or liters – we'll use cubic meters)
     * @return bool
     */
    public function logUsage($meterId, $userId, $waterUsed) {
        $stmt = $this->pdo->prepare("
            INSERT INTO water_usage (meter_id, user_id, water_used, log_time)
            VALUES (?, ?, ?, NOW())
        ");
        return $stmt->execute([$meterId, $userId, $waterUsed]);
    }
    
    /**
     * Get total usage for a user in a given period (optional, for reports)
     */
    public function getTotalUsage($userId, $startDate = null, $endDate = null) {
        $sql = "SELECT SUM(water_used) as total FROM water_usage WHERE user_id = ?";
        $params = [$userId];
        if ($startDate) {
            $sql .= " AND log_time >= ?";
            $params[] = $startDate;
        }
        if ($endDate) {
            $sql .= " AND log_time <= ?";
            $params[] = $endDate;
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row['total'] ?? 0;
    }
}