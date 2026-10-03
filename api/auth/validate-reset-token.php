<?php

require_once __DIR__ . '/../../config/init.php';

use App\Controllers\PasswordResetController;
use App\Repositories\LogRepositories\LogRepository;
use App\Repositories\PasswordResetRepository;
use App\Services\Auth\PasswordReset\PasswordResetService;
use App\Services\Logs\LogsService;

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, private');

if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    http_response_code(405);
    header('Allow: GET');
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed.']);
    exit;
}

$token = is_string($_GET['token'] ?? null) ? $_GET['token'] : '';

try {
    require_once __DIR__ . '/../../App/database/database.php';

    $controller = new PasswordResetController(
        new PasswordResetService(
            $pdo,
            new PasswordResetRepository($pdo),
            new LogsService(new LogRepository($pdo)),
            null,
            $_ENV['APP_URL']
        )
    );

    echo json_encode([
        'status' => 'success',
        'valid' => $controller->validate($token),
    ]);
} catch (Throwable $e) {
    error_log('Password reset token validation failed: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Unable to validate this reset link. Please try again.',
    ]);
}
