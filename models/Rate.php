<?php
class Rate {
    public $rate_id;
    public $price_per_unit;
    public $effective_date;
    public $created_by;

    public function __construct($data = []) {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    public function save($pdo) {
        $stmt = $pdo->prepare("INSERT INTO rates (price_per_unit, effective_date, created_by) VALUES (?, ?, ?)");
        return $stmt->execute([$this->price_per_unit, $this->effective_date, $this->created_by]);
    }

    public static function getActiveRate($pdo) {
        $stmt = $pdo->query("SELECT * FROM rates ORDER BY effective_date DESC LIMIT 1");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? new self($row) : null;
    }

    public static function getAll($pdo) {
        $stmt = $pdo->query("SELECT * FROM rates ORDER BY effective_date DESC");
        return array_map(fn($row) => new self($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public static function getByDate($pdo, $date) {
        $stmt = $pdo->prepare("SELECT * FROM rates WHERE effective_date <= ? ORDER BY effective_date DESC LIMIT 1");
        $stmt->execute([$date]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? new self($row) : null;
    }
}