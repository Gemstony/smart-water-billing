<?php
class Token {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Generate a unique token code
     * Format: WTR-XXXX-YYYY (e.g., WTR-8F3A-9B2E)
     */
    private function generateUniqueCode() {
        $prefix = 'WTR';
        $part1 = strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));
        $part2 = strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));
        $code = $prefix . '-' . $part1 . '-' . $part2;
        
        // Check uniqueness
        $stmt = $this->pdo->prepare("SELECT token_id FROM tokens WHERE token_code = ?");
        $stmt->execute([$code]);
        if ($stmt->fetch()) {
            // Recursively generate a new one if collision (unlikely)
            return $this->generateUniqueCode();
        }
        return $code;
    }
    
    /**
     * Create a new token for a user
     * @param int $userId
     * @param int $units
     * @return string|false The token code or false on failure
     */
    public function createToken($userId, $units) {
        $tokenCode = $this->generateUniqueCode();
        $expiresAt = date('Y-m-d H:i:s', strtotime('+7 days')); // Token valid for 7 days
        
        $stmt = $this->pdo->prepare("
            INSERT INTO tokens (token_code, user_id, units_purchased, expires_at)
            VALUES (?, ?, ?, ?)
        ");
        if ($stmt->execute([$tokenCode, $userId, $units, $expiresAt])) {
            return $tokenCode;
        }
        return false;
    }
    
    /**
     * Find a valid token (not used, not expired)
     * Will be used in Phase 5
     */
    public function findValidToken($tokenCode, $userId) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM tokens 
            WHERE token_code = ? 
            AND user_id = ? 
            AND is_used = 0 
            AND (expires_at IS NULL OR expires_at > NOW())
        ");
        $stmt->execute([$tokenCode, $userId]);
        return $stmt->fetch();
    }
    
    /**
     * Mark token as used
     * Will be used in Phase 5
     */
    public function markAsUsed($tokenId) {
        $stmt = $this->pdo->prepare("
            UPDATE tokens SET is_used = 1, used_at = NOW() 
            WHERE token_id = ?
        ");
        return $stmt->execute([$tokenId]);
    }
}