<?php

require_once __DIR__ . '/../../config/init.php';

use App\Controllers\Auth\LoginController\LoginController;
use App\Provider\Mailer;
use App\Repositories\AdminRepository;
use App\Repositories\LogRepositories\LogRepository;
use App\Repositories\LoginAttemptRepository;
use App\Repositories\TwoFactorRepository;
use App\Services\Auth\Login\LoginRateLimiter;
use App\Services\Auth\Login\LoginService;
use App\Services\Auth\TwoFactor\TwoFactorService;
use App\Services\Logs\LogsService;
use App\Session\SessionManager;

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, private');

if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    echo json_encode([
        'status' => 'error',
        'message' => 'Method Not Allowed.',
    ]);
    exit;
}

try {
    require_once __DIR__ . '/../../App/database/database.php';

    $adminRepository = new AdminRepository($pdo);
    $logs = new LogsService(new LogRepository($pdo));
    $twoFactor = new TwoFactorService(
        $adminRepository,
        new TwoFactorRepository($pdo),
        new Mailer()
    );
    $loginService = new LoginService(
        $adminRepository,
        new LoginRateLimiter(new LoginAttemptRepository($pdo)),
        $twoFactor,
        new SessionManager(),
        $logs
    );
    $controller = new LoginController($loginService);

    $result = $controller->resendOtp();
    echo json_encode($result);
} catch (Throwable $e) {
    error_log('Login OTP resend failed: ' . $e->getMessage());
    $statusCode = $e->getCode() === 429 ? 429 : 400;
    http_response_code($statusCode);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
    ]);
}
