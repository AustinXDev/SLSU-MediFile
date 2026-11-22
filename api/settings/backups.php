<?php

require_once __DIR__ . '/../../config/init.php';

use App\Controllers\BackupController;
use App\Services\Settings\SettingsFactory;
use App\Session\SessionManager;
use App\Support\ApiResponse;

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, private');

try {
    require_once __DIR__ . '/../../App/database/database.php';
    $controller = new BackupController(
        SettingsFactory::backups($pdo, new SessionManager())
    );
    $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

    if ($method === 'GET') {
        echo json_encode(['status' => 'success', 'backups' => $controller->list()]);
        exit;
    }
    if ($method === 'POST') {
        set_time_limit(0);
        echo json_encode([
            'status' => 'success',
            'message' => 'Backup created successfully.',
            'backup' => $controller->create(),
        ]);
        exit;
    }

    http_response_code(405);
    header('Allow: GET, POST');
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed.']);
} catch (Throwable $e) {
    error_log('Backup archive request failed: ' . $e->getMessage());
    ApiResponse::sendError($e, 'Unable to process the backup request.');
}
