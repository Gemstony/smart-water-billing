<?php
class AdminDashboardController {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Get all admin dashboard data
     */
    public function getDashboardData() {
        // Total customers
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM users WHERE role = 'customer'");
        $stmt->execute();
        $totalCustomers = $stmt->fetch()['count'];
        
        // Total revenue (completed)
        $stmt = $this->pdo->prepare("SELECT SUM(amount) as total FROM transactions WHERE status = 'completed'");
        $stmt->execute();
        $totalRevenue = $stmt->fetch()['total'] ?? 0;
        
        // Total units sold
        $stmt = $this->pdo->prepare("SELECT SUM(water_units) as total FROM transactions WHERE status = 'completed'");
        $stmt->execute();
        $totalUnitsSold = $stmt->fetch()['total'] ?? 0;
        
        // Today's revenue
        $stmt = $this->pdo->prepare("SELECT SUM(amount) as today FROM transactions WHERE status = 'completed' AND DATE(created_at) = CURDATE()");
        $stmt->execute();
        $todayRevenue = $stmt->fetch()['today'] ?? 0;
        
        // Pending transactions
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as pending FROM transactions WHERE status = 'pending'");
        $stmt->execute();
        $pendingTransactions = $stmt->fetch()['pending'];
        
        // Active meters (users with meter_id not null and is_active=1)
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as active FROM users WHERE role = 'customer' AND meter_id IS NOT NULL AND is_active = 1");
        $stmt->execute();
        $activeMeters = $stmt->fetch()['active'];
        
        // Revenue chart data: last 30 days
        $stmt = $this->pdo->prepare("
            SELECT DATE(created_at) as date, SUM(amount) as daily_revenue
            FROM transactions
            WHERE status = 'completed' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            GROUP BY DATE(created_at)
            ORDER BY date ASC
        ");
        $stmt->execute();
        $revenueData = $stmt->fetchAll();
        $revenueDates = [];
        $revenueAmounts = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $revenueDates[] = $date;
            $found = false;
            foreach ($revenueData as $row) {
                if ($row['date'] == $date) {
                    $revenueAmounts[] = floatval($row['daily_revenue']);
                    $found = true;
                    break;
                }
            }
            if (!$found) $revenueAmounts[] = 0;
        }
        
        // Top 5 customers by total water units purchased
        $stmt = $this->pdo->prepare("
            SELECT u.user_id, u.full_name, u.email, SUM(t.water_units) as total_units
            FROM users u
            JOIN transactions t ON u.user_id = t.user_id
            WHERE t.status = 'completed'
            GROUP BY u.user_id
            ORDER BY total_units DESC
            LIMIT 5
        ");
        $stmt->execute();
        $topCustomers = $stmt->fetchAll();
        
        // Recent transactions (last 10)
        $stmt = $this->pdo->prepare("
            SELECT t.*, u.full_name
            FROM transactions t
            JOIN users u ON t.user_id = u.user_id
            ORDER BY t.created_at DESC
            LIMIT 10
        ");
        $stmt->execute();
        $recentTransactions = $stmt->fetchAll();
        
        // Water usage trend: last 7 days total usage across all customers
        $stmt = $this->pdo->prepare("
            SELECT DATE(log_time) as date, SUM(water_used) as total_used
            FROM water_usage
            WHERE log_time >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            GROUP BY DATE(log_time)
            ORDER BY date ASC
        ");
        $stmt->execute();
        $usageData = $stmt->fetchAll();
        $usageDates = [];
        $usageAmounts = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $usageDates[] = $date;
            $found = false;
            foreach ($usageData as $row) {
                if ($row['date'] == $date) {
                    $usageAmounts[] = floatval($row['total_used']);
                    $found = true;
                    break;
                }
            }
            if (!$found) $usageAmounts[] = 0;
        }
        
        // Active vs inactive customers count
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as active FROM users WHERE role = 'customer' AND is_active = 1");
        $stmt->execute();
        $activeCustomers = $stmt->fetch()['active'];
        $inactiveCustomers = $totalCustomers - $activeCustomers;
        
        // Recent water usage (last 5 logs with customer names)
        $stmt = $this->pdo->prepare("
            SELECT w.*, u.full_name, u.meter_id
            FROM water_usage w
            JOIN users u ON w.user_id = u.user_id
            ORDER BY w.log_time DESC
            LIMIT 5
        ");
        $stmt->execute();
        $recentUsage = $stmt->fetchAll();
        
        return [
            'totalCustomers' => $totalCustomers,
            'totalRevenue' => $totalRevenue,
            'totalUnitsSold' => $totalUnitsSold,
            'todayRevenue' => $todayRevenue,
            'pendingTransactions' => $pendingTransactions,
            'activeMeters' => $activeMeters,
            'revenueDates' => $revenueDates,
            'revenueAmounts' => $revenueAmounts,
            'topCustomers' => $topCustomers,
            'recentTransactions' => $recentTransactions,
            'usageDates' => $usageDates,
            'usageAmounts' => $usageAmounts,
            'activeCustomers' => $activeCustomers,
            'inactiveCustomers' => $inactiveCustomers,
            'recentUsage' => $recentUsage
        ];
    }
}