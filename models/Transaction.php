<?php
class Transaction {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Get all transactions (admin use)
     * @return array
     */
    public function getAll() {
        $stmt = $this->pdo->prepare("
            SELECT t.*, u.full_name, u.email, u.meter_id 
            FROM transactions t
            JOIN users u ON t.user_id = u.user_id
            ORDER BY t.created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Get transactions for a specific user (customer history)
     * @param int $userId
     * @return array
     */
    public function getByUser($userId) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM transactions 
            WHERE user_id = ? 
            ORDER BY created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get total revenue (optional for dashboard)
     */
    public function getTotalRevenue() {
        $stmt = $this->pdo->prepare("SELECT SUM(amount) as total FROM transactions WHERE status = 'completed'");
        $stmt->execute();
        $row = $stmt->fetch();
        return $row['total'] ?? 0;
    }
}