<?php

namespace App\Services\Auth\PasswordReset;

use App\Provider\Mailer;
use App\Repositories\PasswordResetRepository;
use InvalidArgumentException;
use PDO;
use RuntimeException;
use App\Repositories\LogRepositories\LogRepository;
use App\Services\Logs\LogsService;

class PasswordResetService
{
    private const TOKEN_LIFETIME_MINUTES = 30;
    private const MAX_REQUESTS_PER_ACCOUNT = 3;
    private const REQUEST_WINDOW_MINUTES = 24 * 60;

    public function __construct(
        private PDO $pdo,
        private PasswordResetRepository $repository,
        private LogsService $log,
        private ?Mailer $mailer,
        private string $applicationUrl
    ) {
    }

    public function requestReset(string $email): void
    {
        $email = trim($email);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Please enter a valid email address.');
        }

        $account = $this->repository->findActiveAccountByEmail($email);
        if (!$account) {
            return;
        }

        if (
            $this->repository->countRecentRequests(
                (int) $account['admin_id'],
                self::REQUEST_WINDOW_MINUTES
            ) >= self::MAX_REQUESTS_PER_ACCOUNT
        ) {
            return;
        }

        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);

        $this->pdo->beginTransaction();

        try {
            $this->repository->invalidateActiveTokens((int) $account['admin_id']);
            $tokenId = $this->repository->createToken(
                (int) $account['admin_id'],
                $tokenHash,
                self::TOKEN_LIFETIME_MINUTES
            );
            $this->pdo->commit();

            $this->log->record(
                (int) $account['admin_id'],
                "REQUEST RESET",
                "Requested a password reset link for their account.",
                "authentication",
                (int) $account['admin_id']
            );
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            $this->log->record(
                (int) $account['admin_id'],
                "REQUEST RESET",
                "Failed to requested a password reset link for their account.",
                "authentication",
                (int) $account['admin_id']
            );

            throw $e;
        }

        $resetUrl = rtrim($this->applicationUrl, '/')
            . '/resetpassword?token='
            . rawurlencode($token);

        if (!$this->mailer?->send(
            (string) $account['email'],
            'Password Reset Request',
            $this->buildResetEmail(
                (string) ($account['first_name'] ?: $account['username']),
                $resetUrl
            )
        )) {
            $this->repository->invalidateToken($tokenId);
            error_log('Password reset email could not be delivered.');
        }
    }

    public function validateToken(string $token): bool
    {
        if (!preg_match('/\A[a-f0-9]{64}\z/i', $token)) {
            return false;
        }

        return $this->repository->isTokenValid(hash('sha256', $token));
    }

    public function resetPassword(string $token, string $password): void
    {
        if (!preg_match('/\A[a-f0-9]{64}\z/i', $token)) {
            throw new InvalidArgumentException(
                'This password reset link is invalid or has expired.'
            );
        }

        $this->validatePassword($password);
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        if ($passwordHash === false) {
            throw new RuntimeException('Unable to update password.');
        }

        $this->pdo->beginTransaction();

        $record = $this->repository->findValidToken(hash('sha256', $token));

        try {
            if (!$record) {
                throw new InvalidArgumentException(
                    'This password reset link is invalid or has expired.'
                );
            }

            $updated = $this->repository->updatePasswordAndConsumeToken(
                (int) $record['id'],
                (int) $record['user_id'],
                $passwordHash
            );

            if (!$updated) {
                throw new InvalidArgumentException(
                    'This password reset link is invalid or has expired.'
                );
            }

            $this->pdo->commit();

            $this->log->record(
                (int) $record['user_id'],
                "RESET PASSWORD",
                "Successfully reset the account password using a password reset link.",
                "authentication",
                (int) $record['user_id']
            );
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            $this->log->record(
                (int) $record['user_id'],
                "RESET PASSWORD",
                "Successfully reset the account password using a password reset link.",
                "authentication",
                (int) $record['user_id']
            );
            throw $e;
        }
    }

    private function validatePassword(string $password): void
    {
        if (mb_strlen($password, 'UTF-8') < 8) {
            throw new InvalidArgumentException(
                'Password must be at least 8 characters long.'
            );
        }

        $requirements = [
            ['/[A-Z]/', 'Password must contain at least one uppercase letter.'],
            ['/[a-z]/', 'Password must contain at least one lowercase letter.'],
            ['/[0-9]/', 'Password must contain at least one number.'],
            ['/[!@#$%^&*]/', 'Password must contain at least one special character.'],
        ];

        foreach ($requirements as [$pattern, $message]) {
            if (!preg_match($pattern, $password)) {
                throw new InvalidArgumentException($message);
            }
        }
    }

    private function buildResetEmail(string $name, string $resetUrl): string
    {
        $safeName = htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeUrl = htmlspecialchars($resetUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return <<<HTML
<!doctype html>
<html lang="en">
<body style="margin:0;padding:32px;background:#f5faf8;font-family:Inter,Arial,sans-serif;color:#0f2624">
  <div style="max-width:560px;margin:0 auto;padding:36px;background:#ffffff;border:1px solid #dceae6;border-radius:16px">
    <p style="margin:0 0 8px;color:#12786f;font-size:12px;font-weight:700;letter-spacing:1px;text-transform:uppercase">SLSU-Health Record</p>
    <h1 style="margin:0 0 18px;font-size:24px;color:#0f2624">Password Reset Request</h1>
    <p style="font-size:15px;line-height:1.6">Hello {$safeName}, we received a request to reset your password.</p>
    <p style="font-size:15px;line-height:1.6">Select the button below to create a new password.</p>
    <p style="margin:28px 0">
      <a href="{$safeUrl}" style="display:inline-block;padding:14px 22px;border-radius:10px;background:#12786f;color:#ffffff;text-decoration:none;font-weight:700">Reset Password</a>
    </p>
    <p style="font-size:13px;line-height:1.6;color:#4a625f">This link expires in 30 minutes and can only be used once.</p>
    <p style="font-size:13px;line-height:1.6;color:#4a625f">If you did not request a password reset, you can safely ignore this email.</p>
  </div>
</body>
</html>
HTML;
    }
}
