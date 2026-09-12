<?php 

namespace App\Repositories;

use PDO;

class TwoFactorRepository
{

  public function __construct(
    private PDO $pdo
  )
  {
  }

  /**
   * Create new OTP
   * use: login  verification
   */
  public function createCode(
    int $adminId,
    string $codeHash,
    string $purpose,
    string $expiresAt
  ): bool {

    $stmt = $this->pdo->prepare("
      INSERT INTO otp_codes
      (
        admin_id,
        otp_hash,
        purpose,
        expires_at
      ) 
      VALUES (?, ?, ?, ?)
    ");

    return $stmt->execute([
      $adminId,
      $codeHash,
      $purpose,
      $expiresAt
    ]);

  }


  /**
   * Find valid codes from database
   * 
   */
  public function findValidCode(
    int $adminId,
  ): ?array
  {

    $stmt = $this->pdo->prepare("
      SELECT *
      FROM otp_codes
      WHERE admin_id = ?
        AND verified_at IS NULL
        AND invalidate_at IS NULL
        AND expires_at > NOW()
      ORDER BY created_at DESC
      LIMIT 1
    ");

    $stmt->execute([$adminId]);

    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

  }

  /**
   * Mark otp verified 
   * after credentials are correct
   */
  public function markVerified(
    int $id
  ): bool
  {

    $stmt = $this->pdo->prepare("
      UPDATE otp_codes
      SET verified_at = NOW()
      WHERE otp_id = ?
        AND verified_at IS NULL
    ");

    $stmt->execute([$id]);

    return $stmt->rowCount() > 0;

  }

  /**
   * Delete previous code
   */
  public function invalidatePreviousCode(
    int $adminId,
    string $purpose
  ): void {

    $stmt = $this->pdo->prepare("
      UPDATE otp_codes
      SET invalidate_at = NOW()
      WHERE admin_id = ?
        AND purpose = ?
        AND verified_at IS NULL
        AND invalidate_at IS NULL
    ");

    $stmt->execute([
      $adminId,
      $purpose
    ]);

  }


  public function recordAttempt(
    ?int $otpId,
    int $adminId,
    string $attemptStatus,
    ?string $ip,
    ?string $userAgent = null
  ): void {

    $stmt = $this->pdo->prepare("
      INSERT INTO otp_attempts (
        otp_id,
        admin_id,
        attempt_status,
        ip_address,
        user_agent
      ) 
      VALUES(?, ?, ?, ?, ?)
    ");

    $stmt->execute([
      $otpId,
      $adminId,
      $attemptStatus,
      $ip,
      $userAgent
    ]);

  }

  
  public function countRecentFailures(
    ?int $otp_id,
    int $adminId,
    int $withinMinutes
  ): int {

    $stmt = $this->pdo->prepare("
      SELECT COUNT(*) AS attempt_count
      FROM otp_attempts
      WHERE otp_id = ?
        AND admin_id = ?
        AND attempt_status = 'Failed'
        AND attempted_at > DATE_SUB(
          NOW(),
          INTERVAL ? MINUTE
        )
    ");

    $stmt->execute([
      $otp_id,
      $adminId,
      $withinMinutes
    ]);

    return (int) (
      $stmt->fetch(PDO::FETCH_ASSOC)['attempt_count'] ?? 0
    );

  }


  public function hasRecentCode(
    int $adminId,
    string $purpose,
    int $seconds = 60
  ): bool {

    $stmt = $this->pdo->prepare("
      SELECT otp_id
      FROM otp_codes
      WHERE admin_id = ?
        AND purpose = ?
        AND created_at > DATE_SUB(
          NOW(),
          INTERVAL ? SECOND
        )
      LIMIT 1
    ");

    $stmt->execute([
      $adminId,
      $purpose,
      $seconds
    ]);

    return $stmt->fetchColumn() !== false;

  }

}

?>