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
     * Generate a token after simulated payment
     * @param int $userId
     * @param int $units Number of water units to purchase
     * @return array ['success' => bool, 'token' => string, 'message' => string]
     */
 /**
     * Generate a token after payment simulation
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
        $pricePerUnit = $rate ? $rate['price_per_unit'] : 1000; // fallback
        $totalAmount = $units * $pricePerUnit;
        
        // Simulate payment success (later you can integrate real API)
        // For now, assume payment is successful.
        
        // Generate token
        $tokenCode = $this->tokenModel->createToken($userId, $units);
        if ($tokenCode) {
            // Record transaction
            $controlNumber = 'SIM-' . strtoupper(uniqid());
            $stmt = $this->pdo->prepare("
                INSERT INTO transactions (user_id, amount, water_units, control_number, payment_method, status)
                VALUES (?, ?, ?, ?, ?, 'completed')
            ");
            $stmt->execute([$userId, $totalAmount, $units, $controlNumber, $paymentMethod]);
            
            return [
                'success' => true,
                'token' => $tokenCode,
                'units' => $units,
                'amount' => $totalAmount,
                'price_per_unit' => $pricePerUnit,
                'message' => 'Token generated successfully'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Failed to generate token'
            ];
        }
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