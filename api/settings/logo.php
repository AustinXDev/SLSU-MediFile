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
    $controller = new SettingsController(
        SettingsFactory::settings($pdo, new SessionManager())
    );
    echo json_encode([
        'status' => 'success',
        'message' => 'School logo updated successfully.',
        'settings' => $controller->updateSchoolLogo($_FILES['school_logo'] ?? []),
    ]);
} catch (Throwable $e) {
    error_log('School logo upload failed: ' . $e->getMessage());
    ApiResponse::sendError($e, 'Unable to update the school logo.');
}
