<?php
class Transaction {
    public $transaction_id;
    public $user_id;
    public $amount;
    public $water_units;
    public $control_number;
    public $payment_method;
    public $status;
    public $mpesa_receipt;
    public $created_at;
    public $completed_at;

    public function __construct($data = []) {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    public function save($pdo) {
        $stmt = $pdo->prepare("INSERT INTO transactions (user_id, amount, water_units, control_number, payment_method, status, mpesa_receipt) VALUES (?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$this->user_id, $this->amount, $this->water_units, $this->control_number, $this->payment_method, $this->status, $this->mpesa_receipt]);
    }

    public static function getByUser($pdo, $userId) {
        $stmt = $pdo->prepare("SELECT * FROM transactions WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$userId]);
        return array_map(fn($row) => new self($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public static function getByStatus($pdo, $status) {
        $stmt = $pdo->prepare("SELECT * FROM transactions WHERE status = ? ORDER BY created_at DESC");
        $stmt->execute([$status]);
        return array_map(fn($row) => new self($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }
}