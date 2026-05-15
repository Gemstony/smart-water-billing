<?php
class User {
    public $user_id;
    public $full_name;
    public $email;
    public $phone;
    public $password_hash;
    public $meter_id;
    public $account_balance;
    public $role;
    public $is_active;
    public $created_at;
    public $updated_at;

    public function __construct($data = []) {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    public function save($pdo) {
        if ($this->user_id) {
            $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, phone = ?, meter_id = ?, account_balance = ?, role = ?, is_active = ? WHERE user_id = ?");
            return $stmt->execute([$this->full_name, $this->email, $this->phone, $this->meter_id, $this->account_balance, $this->role, $this->is_active, $this->user_id]);
        }
        $stmt = $pdo->prepare("INSERT INTO users (full_name, email, phone, password_hash, meter_id, account_balance, role, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$this->full_name, $this->email, $this->phone, $this->password_hash, $this->meter_id, $this->account_balance, $this->role, $this->is_active]);
    }

    public static function find($pdo, $id) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? new self($row) : null;
    }

    public static function findByEmail($pdo, $email) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? new self($row) : null;
    }

    public static function all($pdo) {
        $stmt = $pdo->query("SELECT * FROM users");
        return array_map(fn($row) => new self($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public static function getCustomers($pdo) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE role = 'customer'");
        $stmt->execute();
        return array_map(fn($row) => new self($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }
}