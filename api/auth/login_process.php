<?php

require_once __DIR__ . '/../../config/init.php';

use App\Repositories\AdminRepository;
use App\Repositories\LoginAttemptRepository;
use App\Repositories\TwoFactorRepository;

use App\Services\Auth\Login\LoginService;
use App\Services\Auth\Login\LoginRateLimiter;
use App\Services\Auth\TwoFactor\TwoFactorService;

use App\Controllers\Auth\LoginController\LoginController;
use App\Provider\Mailer;

header('Content-Type: application/json');

try {

    /**
     * Get JSON request
     */
    $input = json_decode(
        file_get_contents('php://input'),
        true
    ) ?? [];


    /**
     * Get credentials
     */
    $username = trim(
        $input['username'] ?? ''
    );

    $password = $input['password'] ?? '';

    $ip = $_SERVER['REMOTE_ADDR'] ?? '';

    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;


    /**
     * Validate input
     */
    if ($username === '' || $password === '') {

        http_response_code(400);

        echo json_encode([
            'status' => 'error',
            'message' => 'Username and password are required.'
        ]);

        exit;
    }


    /**
     * Database
     */
    require_once __DIR__ . '/../../App/database/database.php';


    /**
     * Repositories
     */
    $adminRepo = new AdminRepository($pdo);

    $attemptRepo = new LoginAttemptRepository($pdo);

    $twoFactorRepo = new TwoFactorRepository($pdo);


    /**
     * Login rate limiter
     */
    $rateLimiter = new LoginRateLimiter(
        $attemptRepo
    );


    /**
     * Email provider
     */
    $mailer = new Mailer();


    /**
     * Two-factor authentication
     */
    $twoFactorService = new TwoFactorService(
        $adminRepo,
        $twoFactorRepo,
        $mailer
    );


    /**
     * Login service
     */
    $auth = new LoginService(
        $adminRepo,
        $rateLimiter,
        $twoFactorService
    );


    /**
     * Controller
     */
    $controller = new LoginController(
        $auth
    );


    /**
     * Execute login
     */
    $response = $controller->login(
        $username,
        $password,
        $ip
    );


    /**
     * Success
     */
    http_response_code(200);

    echo json_encode($response);

} catch (Throwable $e) {

    error_log(
        $e->getMessage()
    );

    http_response_code(400);

    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);

    exit;
}