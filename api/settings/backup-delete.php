<?php

require_once __DIR__ . '/../../config/init.php';

use App\Controllers\BackupController;
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
    $backupId = is_array($input) ? (int) ($input['backup_id'] ?? 0) : 0;
    $controller = new BackupController(
        SettingsFactory::backups($pdo, new SessionManager())
    );
    $controller->delete($backupId);
    echo json_encode(['status' => 'success', 'message' => 'Backup deleted successfully.']);
} catch (Throwable $e) {
    error_log('Backup deletion failed: ' . $e->getMessage());
    ApiResponse::sendError($e, 'Unable to delete the backup archive.');
}
