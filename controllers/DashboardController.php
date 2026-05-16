<?php
class DashboardController {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Get dashboard data for a specific customer
     * @param int $userId
     * @return array
     */
    public function getCustomerData($userId) {
        // Get user details
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        // Last water usage
        $stmt = $this->pdo->prepare("SELECT water_used, log_time FROM water_usage WHERE user_id = ? ORDER BY log_time DESC LIMIT 1");
        $stmt->execute([$userId]);
        $lastUsage = $stmt->fetch();
        
        // Recent transactions (last 5)
        $stmt = $this->pdo->prepare("SELECT * FROM transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
        $stmt->execute([$userId]);
        $recentTransactions = $stmt->fetchAll();
        
        // Recent usage logs (last 5)
        $stmt = $this->pdo->prepare("SELECT water_used, log_time FROM water_usage WHERE user_id = ? ORDER BY log_time DESC LIMIT 5");
        $stmt->execute([$userId]);
        $recentUsage = $stmt->fetchAll();
        
        // Chart data: last 7 days consumption (grouped by date)
        $stmt = $this->pdo->prepare("
            SELECT DATE(log_time) as date, SUM(water_used) as total_used
            FROM water_usage
            WHERE user_id = ? AND log_time >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            GROUP BY DATE(log_time)
            ORDER BY date ASC
        ");
        $stmt->execute([$userId]);
        $chartData = $stmt->fetchAll();
        
        // Fill missing dates with zero
        $dates = [];
        $usage = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $dates[] = $date;
            $found = false;
            foreach ($chartData as $row) {
                if ($row['date'] == $date) {
                    $usage[] = floatval($row['total_used']);
                    $found = true;
                    break;
                }
            }
            if (!$found) $usage[] = 0;
        }
        
        return [
            'user' => $user,
            'lastUsage' => $lastUsage,
            'recentTransactions' => $recentTransactions,
            'recentUsage' => $recentUsage,
            'chartDates' => $dates,
            'chartUsage' => $usage
        ];
    }
}