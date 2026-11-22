<?php

require_once __DIR__ . '/../../config/init.php';

use App\Controllers\SettingsController;
use App\Services\Settings\SettingsFactory;
use App\Session\SessionManager;
use App\Support\ApiResponse;

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, private');

if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed.']);
    exit;
}

try {
    require_once __DIR__ . '/../../App/database/database.php';
    $input = json_decode(file_get_contents('php://input') ?: '[]', true);
    if (!is_array($input)) {
        throw new InvalidArgumentException('Invalid password change request.');
    }

    $controller = new SettingsController(
        SettingsFactory::settings($pdo, new SessionManager())
    );
    $controller->changePassword($input);
    echo json_encode(['status' => 'success', 'message' => 'Password changed successfully.']);
} catch (Throwable $e) {
    error_log('Settings password change failed: ' . $e->getMessage());
    ApiResponse::sendError($e, 'Unable to change your password.');
}
