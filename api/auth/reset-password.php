<?php

require_once __DIR__ . '/../../config/init.php';

use App\Controllers\PasswordResetController;
use App\Repositories\PasswordResetRepository;
use App\Repositories\LogRepositories\LogRepository;
use App\Services\Auth\PasswordReset\PasswordResetService;
use App\Services\Logs\LogsService;

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, private');

if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed.']);
    exit;
}

$payload = json_decode(file_get_contents('php://input') ?: '[]', true);
$token = is_array($payload) && is_string($payload['token'] ?? null)
    ? $payload['token']
    : '';
$password = is_array($payload) && is_string($payload['password'] ?? null)
    ? $payload['password']
    : '';

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
    $controller->reset($token, $password);

    echo json_encode([
        'status' => 'success',
        'message' => 'Your password has been updated successfully. You can now sign in using your new password.',
    ]);
} catch (InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
    ]);
} catch (Throwable $e) {
    error_log('Password reset failed: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Unable to update your password. Please try again.',
    ]);
}
