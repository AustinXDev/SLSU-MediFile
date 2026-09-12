<?php

namespace App\Services\Auth\Login;

use App\Repositories\LoginAttemptRepository;

class LoginRateLimiter
{
    public function __construct(
        private LoginAttemptRepository $attempts,
        private int $maxAttempts = 3,
        private int $lockMinutes = 5
    ) {
    }


    /**
     * Check whether the admin is currently locked.
     */
    public function isLocked(
        int $adminId,
        string $username,
        ?string $ip
    ): bool {

        $failures = $this->attempts->countRecentFailures(
            $adminId,
            $username,
            $ip,
            $this->lockMinutes
        );

        return $failures >= $this->maxAttempts;
    }


    /**
     * Get number of recent failures.
     */
    public function getFailureCount(
        int $adminId,
        string $username,
        ?string $ip
    ): int {

        return $this->attempts->countRecentFailures(
            $adminId,
            $username,
            $ip,
            $this->lockMinutes
        );
    }


    /**
     * Get maximum allowed attempts.
     */
    public function maxAttempts(): int
    {
        return $this->maxAttempts;
    }


    /**
     * Get lock duration.
     */
    public function lockMinutes(): int
    {
        return $this->lockMinutes;
    }


    /**
     * Record failed login.
     */
    public function recordFailure(
        ?int $adminId,
        string $username,
        ?string $ip,
        string $reason,
        ?string $userAgent = null
    ): void {

        $this->attempts->recordLoginFailure(
            $adminId,
            $username,
            $ip,
            $reason,
            $userAgent
        );
    }


    /**
     * Record successful login.
     */
    public function recordSuccess(
        int $adminId,
        string $username,
        ?string $ip,
        ?string $userAgent = null
    ): void {

        $this->attempts->recordSuccess(
            $adminId,
            $username,
            $ip,
            $userAgent
        );
    }
}