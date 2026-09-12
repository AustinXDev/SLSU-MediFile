<?php 

namespace App\Services\Auth\TwoFactor;

use App\Repositories\AdminRepository;
use App\Repositories\TwoFactorRepository;
use App\Provider\Mailer;
use RuntimeException;

class TwoFactorService
{

  private const CODE_EXPIRATION_MINUTES = 5;
  private const MAX_ATTEMPTS = 5;
  private const ATTEMPT_WINDOW_MINUTES = 5;

  public function __construct(
    private AdminRepository $adminRepo,
    private TwoFactorRepository $twoFactorRepo,
    private Mailer $mailer
  )
  {
  }


  /**
   * Generate and send 2fa code
   */
  public function sendCode(
    int $adminId,
    string $purpose 
  ): array {

    $admin = $this->adminRepo->findById($adminId);

    if(!$admin){
      throw new RuntimeException(
        'Unable to process the verification request.'
      );
    }

    if ($this->twoFactorRepo->hasRecentCode(
        $adminId,
        $purpose,
        60
    )) {
        throw new RuntimeException(
            'Please wait before requesting another verification code.'
        );
    }

    //Invalidate previous code
    $this->twoFactorRepo->invalidatePreviousCode(
      $adminId,
      $purpose
    );

    $code = (string) random_int(
      100000,
      999999
    );

    $codeHash = password_hash(
      $code,
      PASSWORD_DEFAULT
    );

    $timezone = new \DateTimeZone(
      'Asia/Manila'
    );

    $expiresAt = (new \DateTimeImmutable(
      'now',
      $timezone
    ))
    ->modify(
      '+' . self::CODE_EXPIRATION_MINUTES . 'minutes'
    )
    ->format(
      'Y-m-d H:i:s'
    );

    /**
     * store created code
     */
    $created = $this->twoFactorRepo->createCode(
      $adminId,
      $codeHash,
      $purpose,
      $expiresAt
    );

    if(!$created){
      throw new RuntimeException(
        "Unable to generate a verification code."
      );
    }

    $this->mailer->send(
      $admin->email,
      'SLSU-Health Record Login Verification',
      TwoFactorEmail::build(
        $admin->firstName . ' ' . $admin->lastName,
        $code,
        $purpose,
        self::CODE_EXPIRATION_MINUTES
      )
    );

    return [
      'status'  =>  'success',
      'message' =>  'A verification code has been sent to your registered email.'
    ];

  }


  /**
   * Verify submitted code
   */
  public function verify(
    int $adminId,
    string $purpose,
    string $code,
    string $ip
  ): array|bool {

    if($adminId <= 0 || $code === ''){
      return [
        'status' => false,
        'message' => 'Verification code is required'
      ];
    }

    if(!preg_match('/^\d{6}$/', $code)) {

      $this->twoFactorRepo->recordAttempt(
        null,
        $adminId,
        'Failed',
        $ip,
        $_SERVER['HTTP_USER_AGENT'] ?? null
      );

      return [
        'status' => false,
        'message' => 'OTP must be a 6-digit code.'
      ];

    }

    /*
     * Find active OTP
     */
    $otp = $this->twoFactorRepo->findValidCode(
        $adminId,
    );

    if (!$otp) {

        $this->twoFactorRepo->recordAttempt(
            null,
            $adminId,
            'Failed',
            $ip,
            $_SERVER['HTTP_USER_AGENT'] ?? null
        );

        return [
          'status' => false,
          'message' => 'OTP is invalid or expired.'
        ];
    }


    /*
     * Check failures for this specific OTP
     */
    $otpFailures = $this->twoFactorRepo->countRecentFailures(
        (int) $otp['otp_id'],
        $adminId,
        self::ATTEMPT_WINDOW_MINUTES
    );

    if ($otpFailures >= self::MAX_ATTEMPTS) {

        $this->twoFactorRepo->recordAttempt(
            (int) $otp['otp_id'],
            $adminId,
            'Blocked',
            $ip,
            $_SERVER['HTTP_USER_AGENT'] ?? null
        );

        return [
          'status' => false,
          'message' => 'Too many OTP attempts. Please request a new code.'
        ];
    }


    /*
     * Verify OTP hash
     */
    if (!password_verify($code, $otp['otp_hash'])) {

        $this->twoFactorRepo->recordAttempt(
            (int) $otp['otp_id'],
            $adminId,
            'Failed',
            $ip,
            $_SERVER['HTTP_USER_AGENT'] ?? null
        );

        return [
          'status' => false,
          'message' => 'Invalid verification code.'
        ];
    }


    /**
     * Mark OTP as verified
     */
    $this->twoFactorRepo->markVerified(
      (int) $otp['otp_id']
    );


    /**
     * Record success attempt
     */
    $this->twoFactorRepo->recordAttempt(
        (int) $otp['otp_id'],
        $adminId,
        'Success',
        $ip,
        $_SERVER['HTTP_USER_AGENT'] ?? null
    );

    return [
      'status' => 'success',
      'message' => 'OTP verified successfully.'
    ];
  }

}

?>