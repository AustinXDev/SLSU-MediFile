<?php

namespace App\Services\Auth\Login;

use App\Repositories\AdminRepository;
use App\Services\Auth\TwoFactor\TwoFactorService;
use RuntimeException;

class LoginService
{
    private const MAX_ATTEMPTS = 3;
    private const OTP_EXPIRATION_MINUTES = 5;

    public function __construct(
        private AdminRepository $admins,
        private LoginRateLimiter $rateLimiter,
        private TwoFactorService $twoFactorService
    ) {
    }

    public function login(
        string $username,
        string $password,
        string $ip
    ): array {

        $username = trim($username);

        if ($username === '' || $password === '') {
            throw new RuntimeException(
                'Username and password are required.'
            );
        }

        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

        /**
         * Find Admin
         */
        $admin = $this->admins->findByUsername($username);


        /**
         * Admin does not exist
         *
         */
        if (!$admin) {

            throw new RuntimeException(
                'Incorrect username or password.'
            );
        }


        /**
         * Check recent failed attempts
         */
        if ($this->rateLimiter->isLocked(
            $admin->adminId,
            $username,
            $ip
        )) {

            throw new RuntimeException(
                'Too many failed login attempts. Please try again later.'
            );
        }


        /**
         * Verify password
         */
        if (!password_verify(
            $password,
            $admin->passwordHash
        )) {

            $this->rateLimiter->recordFailure(
                $admin->adminId,
                $username,
                $ip,
                'Incorrect username or password.',
                $userAgent
            );

            throw new RuntimeException(
                'Incorrect username or password.'
            );
        }


        /**
         * Credentials are correct.
         *
         * Send 2FA code.
         */
        $send = $this->twoFactorService->sendCode(
            $admin->adminId,
            'Login'
        );


        /**
         * IMPORTANT:
         * sendCode() already returns an array.
         */
        if (($send['status'] ?? '') !== 'success') {

            throw new RuntimeException(
                'Unable to generate verification code.'
            );
        }


        /**
         * Store pending authentication
         */
        $_SESSION['admin_2fa_pending'] = true;

        $_SESSION['admin_2fa_id'] =
            $admin->adminId;

        $_SESSION['admin_2fa_expires_at'] =
            time() + (
                self::OTP_EXPIRATION_MINUTES * 60
            );


        return [
            'status' => '2fa_required',
            'message' =>
                'A verification code has been sent to your registered email.'
        ];

    }


    public function verify(
        string $code,
        string $ip
    ): array {

        /**
         * Check 2FA is pending
         */
        if (
            empty($_SESSION['admin_2fa_pending']) ||
            empty($_SESSION['admin_2fa_id'])
        ) {

            throw new RuntimeException(
                'No verification request is pending.'
            );
        }


        /**
         * Check expiration
         */
        if (
            empty($_SESSION['admin_2fa_expires_at']) ||
            time() > $_SESSION['admin_2fa_expires_at']
        ) {

            unset(
                $_SESSION['admin_2fa_pending'],
                $_SESSION['admin_2fa_id'],
                $_SESSION['admin_2fa_expires_at']
            );

            throw new RuntimeException(
                'Your verification session has expired. Please log in again.'
            );
        }


        $adminId = (int) $_SESSION['admin_2fa_id'];


        /**
         * Verify OTP
         */
        $verify = $this->twoFactorService->verify(
            $adminId,
            'Login',
            $code,
            $ip
        );


        if (($verify['status'] ?? '') !== 'success') {

            throw new RuntimeException(
                $verify['message'] ?? 'OTP verification failed.'
            );
        }


        /**
         * Find Admin
         */
        $admin = $this->admins->findById($adminId);

        if (!$admin) {

            throw new RuntimeException(
                'Unable to load administrator account.'
            );
        }


        /**
         * Regenerate session ID
         */
        session_regenerate_id(true);


        /**
         * Create authenticated session
         */
        $_SESSION['admin_authenticated'] = true;
        $_SESSION['admin_id'] = $adminId;
        $_SESSION['role'] = $admin->role;
        $_SESSION['admin_username'] = $admin->username;


        /**
         * Remove temporary 2FA session
         */
        unset(
            $_SESSION['admin_2fa_pending'],
            $_SESSION['admin_2fa_id'],
            $_SESSION['admin_2fa_expires_at']
        );


        return [
            'status' => 'success',
            'message' => 'Verification successful. Welcome back.',
            'redirect' => 'dashboard'
        ];
    }
}
