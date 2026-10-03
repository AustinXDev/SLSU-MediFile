<?php

namespace App\Repositories;

use PDO;

class PasswordResetRepository
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    public function findActiveAccountByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT admin_id, email, first_name, username
            FROM admin
            WHERE email = ?
              AND status = 'Active'
            LIMIT 1
        ");
        $stmt->execute([$email]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function countRecentRequests(int $userId, int $minutes): int
    {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*)
            FROM password_reset_tokens
            WHERE user_id = ?
              AND created_at > DATE_SUB(NOW(), INTERVAL ? MINUTE)
        ");
        $stmt->execute([$userId, $minutes]);

        return (int) $stmt->fetchColumn();
    }

    public function invalidateActiveTokens(int $userId): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE password_reset_tokens
            SET used_at = NOW()
            WHERE user_id = ?
              AND used_at IS NULL
        ");
        $stmt->execute([$userId]);
    }

    public function createToken(
        int $userId,
        string $tokenHash,
        int $lifetimeMinutes
    ): int {
        $stmt = $this->pdo->prepare("
            INSERT INTO password_reset_tokens (
                user_id,
                token_hash,
                expires_at
            )
            VALUES (?, ?, DATE_ADD(NOW(), INTERVAL ? MINUTE))
        ");
        $stmt->execute([$userId, $tokenHash, $lifetimeMinutes]);

        return (int) $this->pdo->lastInsertId();
    }

    public function invalidateToken(int $tokenId): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE password_reset_tokens
            SET used_at = NOW()
            WHERE id = ?
              AND used_at IS NULL
        ");
        $stmt->execute([$tokenId]);
    }

    public function findValidToken(string $tokenHash): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT t.id, t.user_id
            FROM password_reset_tokens t
            INNER JOIN admin a ON a.admin_id = t.user_id
            WHERE t.token_hash = ?
              AND t.used_at IS NULL
              AND t.expires_at > NOW()
              AND a.status = 'Active'
            LIMIT 1
            FOR UPDATE
        ");
        $stmt->execute([$tokenHash]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function updatePasswordAndConsumeToken(
        int $tokenId,
        int $userId,
        string $passwordHash
    ): bool {
        $updatePassword = $this->pdo->prepare("
            UPDATE admin
            SET password_hash = ?, updated_at = NOW()
            WHERE admin_id = ?
              AND status = 'Active'
        ");
        $updatePassword->execute([$passwordHash, $userId]);

        if ($updatePassword->rowCount() !== 1) {
            return false;
        }

        $consume = $this->pdo->prepare("
            UPDATE password_reset_tokens
            SET used_at = NOW()
            WHERE id = ?
              AND user_id = ?
              AND used_at IS NULL
              AND expires_at > NOW()
        ");
        $consume->execute([$tokenId, $userId]);

        if ($consume->rowCount() !== 1) {
            return false;
        }

        $this->invalidateActiveTokens($userId);
        return true;
    }

    public function isTokenValid(string $tokenHash): bool
    {
        $stmt = $this->pdo->prepare("
            SELECT 1
            FROM password_reset_tokens t
            INNER JOIN admin a ON a.admin_id = t.user_id
            WHERE t.token_hash = ?
              AND t.used_at IS NULL
              AND t.expires_at > NOW()
              AND a.status = 'Active'
            LIMIT 1
        ");
        $stmt->execute([$tokenHash]);

        return $stmt->fetchColumn() !== false;
    }
}
