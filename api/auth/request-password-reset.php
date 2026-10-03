<?php

require_once __DIR__ . '/../../config/init.php';

use App\Controllers\PasswordResetController;
use App\Provider\Mailer;
use App\Repositories\PasswordResetRepository;
use App\Services\Auth\PasswordReset\PasswordResetService;
use App\Services\Logs\LogsService;
use App\Repositories\LogRepositories\LogRepository;

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, private');

if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed.']);
    exit;
}

$payload = json_decode(file_get_contents('php://input') ?: '[]', true);
$email = is_array($payload) && is_string($payload['email'] ?? null)
    ? trim($payload['email'])
    : '';

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Please enter a valid email address.',
    ]);
    exit;
}

try {
    require_once __DIR__ . '/../../App/database/database.php';

    $controller = new PasswordResetController(
        new PasswordResetService(
            $pdo,
            new PasswordResetRepository($pdo),
            new LogsService(new LogRepository($pdo)),
            new Mailer(),
            $_ENV['APP_URL']
        )
    );

    $controller->request($email);

    echo json_encode([
        'status' => 'success',
        'message' => 'If an account exists for that email address, a password reset link has been sent.',
    ]);
} catch (Throwable $e) {
    error_log('Password reset request failed: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Unable to process your request. Please try again.',
    ]);
}
