<?php

namespace App\Services\Auth\Login;

use App\Repositories\AdminRepository;
use App\Services\Auth\TwoFactor\TwoFactorService;
use App\Session\SessionManager;
use RuntimeException;

class LoginService
{
    private const MAX_ATTEMPTS = 3;
    private const OTP_EXPIRATION_MINUTES = 5;

    public function __construct(
        private AdminRepository $admins,
        private LoginRateLimiter $rateLimiter,
        private TwoFactorService $twoFactorService,
        private SessionManager $session
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
        $this->session->set('admin_2fa_pending', true);

        $this->session->set('admin_2fa_id', $admin->adminId);

        $expiration = time() + (self::OTP_EXPIRATION_MINUTES * 60);

        $this->session->set('admin_2fa_expires_at', $expiration);


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
            empty($this->session->get('admin_2fa_pending')) ||
            empty($this->session->get('admin_2fa_id'))
        ) {

            throw new RuntimeException(
                'No verification request is pending.'
            );
        }


        /**
         * Check expiration
         */
        if (
            empty($this->session->get('admin_2fa_expires_at')) ||
            time() > $this->session->get('admin_2fa_expires_at')
        ) {

            $this->session->remove('admin_2fa_pending');
            $this->session->remove('admin_2fa_id');
            $this->session->remove('admin_2fa_expires_at');

            throw new RuntimeException(
                'Your verification session has expired. Please log in again.'
            );
        }


        $adminId = (int) $this->session->get('admin_2fa_id');


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
        $this->session->set('admin_authenticated', true);
        $this->session->set('admin_id', $adminId);
        $this->session->set('role', $admin->role);
        $this->session->set('admin_username', $admin->username);


        /**
         * Remove temporary 2FA session
         */
        $this->session->remove('admin_2fa_pending');
        $this->session->remove('admin_2fa_id');
        $this->session->remove('admin_2fa_expires_at');


        return [
            'status' => 'success',
            'message' => 'Verification successful. Welcome back.',
            'redirect' => 'dashboard'
        ];
    }
}
