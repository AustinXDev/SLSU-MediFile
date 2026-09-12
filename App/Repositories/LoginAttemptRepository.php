<?php

namespace App\Repositories;

use PDO;

class LoginAttemptRepository
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    /**
     * Count recent failed login attempts.
     */
    public function countRecentFailures(
        int $adminId,
        string $username,
        string $ip,
        int $withinMinutes
    ): int {

        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) AS attempt_count
            FROM login_attempts
            WHERE admin_id = ?
              AND username = ?
              AND ip_address = ?
              AND attempted_at > DATE_SUB(
                  NOW(),
                  INTERVAL ? MINUTE
              )
              AND attempt_status = 'Failed'
        ");

        $stmt->execute([
            $adminId,
            $username,
            $ip,
            $withinMinutes
        ]);

        return (int) (
            $stmt->fetch(PDO::FETCH_ASSOC)['attempt_count'] ?? 0
        );
    }


    /**
     * Record failed login attempt.
     */
    public function recordLoginFailure(
        ?int $adminId,
        string $username,
        ?string $ip,
        string $reason,
        ?string $userAgent = null
    ): void {

        $stmt = $this->pdo->prepare("
            INSERT INTO login_attempts (
                admin_id,
                username,
                ip_address,
                user_agent,
                attempt_status,
                failure_reason
            )
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $adminId,
            $username,
            $ip,
            $userAgent,
            'Failed',
            $reason
        ]);
    }


    /**
     * Record successful login attempt.
     */
    public function recordSuccess(
        int $adminId,
        string $username,
        ?string $ip,
        ?string $userAgent = null
    ): void {

        $stmt = $this->pdo->prepare("
            INSERT INTO login_attempts (
                admin_id,
                username,
                ip_address,
                user_agent,
                attempt_status,
                failure_reason
            )
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $adminId,
            $username,
            $ip,
            $userAgent,
            'Success',
            null
        ]);
    }
}