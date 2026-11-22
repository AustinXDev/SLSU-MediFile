<?php

require_once __DIR__ . '/../../config/init.php';

use App\Controllers\SettingsController;
use App\Services\Settings\SettingsFactory;
use App\Session\SessionManager;
use App\Support\ApiResponse;

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, private');

try {
    require_once __DIR__ . '/../../App/database/database.php';
    $controller = new SettingsController(
        SettingsFactory::settings($pdo, new SessionManager())
    );
    $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

    if ($method === 'GET') {
        echo json_encode(['status' => 'success', 'settings' => $controller->getSystemSettings()]);
        exit;
    }
    if ($method === 'PUT') {
        $input = json_decode(file_get_contents('php://input') ?: '[]', true);
        if (!is_array($input)) {
            throw new InvalidArgumentException('Invalid application settings.');
        }
        echo json_encode([
            'status' => 'success',
            'message' => 'Application settings saved.',
            'settings' => $controller->updateSystemSettings($input),
        ]);
        exit;
    }

    http_response_code(405);
    header('Allow: GET, PUT');
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed.']);
} catch (Throwable $e) {
    error_log('System settings request failed: ' . $e->getMessage());
    ApiResponse::sendError($e, 'Unable to process system settings.');
}
