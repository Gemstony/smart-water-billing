<?php
require_once __DIR__ . '/../models/Token.php';
require_once __DIR__ . '/../models/Rate.php';

class TokenController {
    private $tokenModel;
    private $rateModel;
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->tokenModel = new Token($pdo);
        $this->rateModel = new Rate($pdo);
    }
    
    /**
     * Generate a token after payment
     * @param int $userId
     * @param int $units
     * @param string $paymentMethod (e.g., 'Simulated', 'M-Pesa', etc.)
     * @return array ['success' => bool, 'token' => string, 'units' => int, 'amount' => float, 'message' => string]
     */
    public function generateToken($userId, $units, $paymentMethod = 'Simulated') {
        if ($units <= 0) {
            return ['success' => false, 'message' => 'Invalid number of units'];
        }
        
        // Get current rate
        $rate = $this->rateModel->getCurrent();
        $pricePerUnit = $rate ? $rate['price_per_unit'] : 1000;
        $totalAmount = $units * $pricePerUnit;
        
        // Generate token (check if it already exists for this transaction)
        $tokenCode = $this->tokenModel->createToken($userId, $units);
        if (!$tokenCode) {
            return ['success' => false, 'message' => 'Failed to generate token'];
        }
        
        // Check if a transaction already exists for this token (to prevent double processing)
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as count FROM transactions 
            WHERE user_id = ? AND water_units = ? AND status = 'completed'
        ");
        $stmt->execute([$userId, $units]);
        $existing = $stmt->fetch();
        
        if ($existing['count'] > 0) {
            // Update user's balance if not already done
            $userModel = new User($this->pdo);
            $user = $userModel->findById($userId);
            $userModel->updateBalance($userId, $user['account_balance'] + $units);
            return [
                'success' => true,
                'token' => $tokenCode,
                'units' => $units,
                'amount' => $totalAmount,
                'price_per_unit' => $pricePerUnit,
                'message' => 'Token generated successfully'
            ];
        }
        
        // Record transaction if not exists
        $controlNumber = ($paymentMethod === 'Simulated') ? 'SIM-' . strtoupper(uniqid()) : 'AZM-' . strtoupper(uniqid());
        $stmt = $this->pdo->prepare("
            INSERT INTO transactions (user_id, amount, water_units, control_number, payment_method, status)
            VALUES (?, ?, ?, ?, ?, 'completed')
        ");
        $stmt->execute([$userId, $totalAmount, $units, $controlNumber, $paymentMethod]);
        
        // Update user's water balance
        $userModel = new User($this->pdo);
        $user = $userModel->findById($userId);
        $userModel->updateBalance($userId, $user['account_balance'] + $units);
        
        return [
            'success' => true,
            'token' => $tokenCode,
            'units' => $units,
            'amount' => $totalAmount,
            'price_per_unit' => $pricePerUnit,
            'message' => 'Token generated successfully'
        ];
    }
    
    /**
     * Record a transaction (simulated)
     */
    private function recordTransaction($userId, $units, $tokenCode) {
        // For now, we need a rate per unit. We'll use a simple method: get current rate from rates table or default.
        $rate = $this->getCurrentRate();
        $amount = $units * $rate;
        $controlNumber = 'SIM-' . strtoupper(uniqid());
        
        $stmt = $this->pdo->prepare("
            INSERT INTO transactions (user_id, amount, water_units, control_number, payment_method, status)
            VALUES (?, ?, ?, ?, 'Simulated', 'completed')
        ");
        $stmt->execute([$userId, $amount, $units, $controlNumber]);
    }
    
    /**
     * Get current price per unit from rates table, or default 1000 TZS/unit
     */
    private function getCurrentRate() {
        $stmt = $this->pdo->prepare("SELECT price_per_unit FROM rates WHERE effective_date <= CURDATE() ORDER BY effective_date DESC LIMIT 1");
        $stmt->execute();
        $rate = $stmt->fetch();
        if ($rate) {
            return $rate['price_per_unit'];
        }
        return 1000; // Default rate (TZS per unit)
    }
}