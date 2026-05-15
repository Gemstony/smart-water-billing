<?php
class Token {
    public $token_id;
    public $token_code;
    public $user_id;
    public $units_purchased;
    public $is_used;
    public $generated_at;
    public $expires_at;
    public $used_at;

    public function __construct($data = []) {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    public function generateCode() {
        $this->token_code = strtoupper(bin2hex(random_bytes(5)));
    }

    public function activate($pdo, $userId) {
        $this->user_id = $userId;
        $this->is_used = 1;
        $this->used_at = date('Y-m-d H:i:s');
        $stmt = $pdo->prepare("UPDATE tokens SET user_id = ?, is_used = 1, used_at = ? WHERE token_id = ?");
        return $stmt->execute([$userId, $this->used_at, $this->token_id]);
    }

    public function save($pdo) {
        $stmt = $pdo->prepare("INSERT INTO tokens (token_code, user_id, units_purchased, is_used, expires_at) VALUES (?, ?, ?, 0, ?)");
        return $stmt->execute([$this->token_code, $this->user_id, $this->units_purchased, $this->expires_at]);
    }

    public static function findByCode($pdo, $code) {
        $stmt = $pdo->prepare("SELECT * FROM tokens WHERE token_code = ?");
        $stmt->execute([$code]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? new self($row) : null;
    }

    public static function getUnusedByUser($pdo, $userId) {
        $stmt = $pdo->prepare("SELECT * FROM tokens WHERE user_id = ? AND is_used = 0");
        $stmt->execute([$userId]);
        return array_map(fn($row) => new self($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public static function getByUser($pdo, $userId) {
        $stmt = $pdo->prepare("SELECT * FROM tokens WHERE user_id = ?");
        $stmt->execute([$userId]);
        return array_map(fn($row) => new self($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }
}