<?php
class Rate {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function getAll() {
        $stmt = $this->pdo->prepare("SELECT * FROM rates ORDER BY effective_date DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getCurrent() {
        $stmt = $this->pdo->prepare("SELECT * FROM rates WHERE effective_date <= CURDATE() ORDER BY effective_date DESC LIMIT 1");
        $stmt->execute();
        return $stmt->fetch();
    }
    
    public function add($price, $effectiveDate, $createdBy) {
        $stmt = $this->pdo->prepare("INSERT INTO rates (price_per_unit, effective_date, created_by) VALUES (?, ?, ?)");
        return $stmt->execute([$price, $effectiveDate, $createdBy]);
    }
    
    public function update($rateId, $price, $effectiveDate) {
        $stmt = $this->pdo->prepare("UPDATE rates SET price_per_unit = ?, effective_date = ? WHERE rate_id = ?");
        return $stmt->execute([$price, $effectiveDate, $rateId]);
    }
    
    public function delete($rateId) {
        $stmt = $this->pdo->prepare("DELETE FROM rates WHERE rate_id = ?");
        return $stmt->execute([$rateId]);
    }
}
?>