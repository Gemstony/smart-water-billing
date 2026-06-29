<?php
class Token
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Generate a unique 10-digit numeric token
     * Example: 5839201746
     */
    private function generateUniqueCode()
    {
        $code = str_pad((string) random_int(0, 9999999999), 10, '0', STR_PAD_LEFT);

        // Check uniqueness
        $stmt = $this->pdo->prepare("SELECT token_id FROM tokens WHERE token_code = ?");
        $stmt->execute([$code]);

        if ($stmt->fetch()) {
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
    public function createToken($userId, $units)
    {
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
    public function findValidToken($tokenCode, $userId)
    {
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
    public function markAsUsed($tokenId)
    {
        $stmt = $this->pdo->prepare("
            UPDATE tokens SET is_used = 1, used_at = NOW() 
            WHERE token_id = ?
        ");
        return $stmt->execute([$tokenId]);
    }

    /**
     * Get all tokens for a specific user (customer token history)
     * @param int $userId
     * @return array
     */
    public function getByUser($userId)
    {
        $stmt = $this->pdo->prepare("
        SELECT * FROM tokens 
        WHERE user_id = ? 
        ORDER BY generated_at DESC
    ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /**
     * Get all tokens with user details (admin use)
     * @return array
     */
    public function getAllWithUsers()
    {
        $stmt = $this->pdo->prepare("
        SELECT t.*, u.full_name, u.email, u.meter_id 
        FROM tokens t
        JOIN users u ON t.user_id = u.user_id
        ORDER BY t.generated_at DESC
    ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get paginated tokens with user details (admin use)
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function getAllWithUsersPaginated($page = 1, $limit = 10)
    {
        $offset = ($page - 1) * $limit;
        $stmt = $this->pdo->prepare("
        SELECT t.*, u.full_name, u.email, u.meter_id 
        FROM tokens t
        JOIN users u ON t.user_id = u.user_id
        ORDER BY t.generated_at DESC
        LIMIT :limit OFFSET :offset
    ");
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Count total tokens with user details (for pagination)
     * @return int
     */
    public function countAllWithUsers()
    {
        $stmt = $this->pdo->prepare("
        SELECT COUNT(*) as total 
        FROM tokens t
        JOIN users u ON t.user_id = u.user_id
    ");
        $stmt->execute();
        return (int) $stmt->fetch()['total'];
    }
}