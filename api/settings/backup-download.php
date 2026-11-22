<?php

require_once __DIR__ . '/../../config/init.php';

use App\Controllers\BackupController;
use App\Services\Settings\SettingsFactory;
use App\Session\SessionManager;
use App\Support\ApiResponse;

try {
    if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
        http_response_code(405);
        header('Allow: GET');
        exit;
    }

    require_once __DIR__ . '/../../App/database/database.php';
    $controller = new BackupController(
        SettingsFactory::backups($pdo, new SessionManager())
    );
    $backup = $controller->download((int) ($_GET['id'] ?? 0));
    header('Content-Type: application/gzip');
    header('Content-Disposition: attachment; filename="' . basename($backup['filename']) . '"');
    header('Content-Length: ' . filesize($backup['path']));
    header('Cache-Control: no-store, private');
    readfile($backup['path']);
} catch (Throwable $e) {
    error_log('Backup download failed: ' . $e->getMessage());
    ApiResponse::sendError($e, 'Unable to download the backup archive.');
}
