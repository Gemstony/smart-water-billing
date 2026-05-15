<?php
require_once __DIR__ . '/../includes/config.php';

class User {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    // Find user by email (for login)
    public function findByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
    
    // Find user by ID
    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    // Create new user (registration)
    public function create($data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO users (full_name, email, phone, password_hash, meter_id, role) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $data['full_name'],
            $data['email'],
            $data['phone'],
            password_hash($data['password'], PASSWORD_DEFAULT),
            $data['meter_id'] ?? null,
            $data['role'] ?? 'customer'
        ]);
    }
    
    // Update user's water balance
    public function updateBalance($userId, $newBalance) {
        $stmt = $this->pdo->prepare("UPDATE users SET account_balance = ? WHERE user_id = ?");
        return $stmt->execute([$newBalance, $userId]);
    }
    
    // Get user by meter_id (for ESP32)
    public function findByMeterId($meterId) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE meter_id = ?");
        $stmt->execute([$meterId]);
        return $stmt->fetch();
    }
}
?>