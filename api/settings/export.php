<?php

require_once __DIR__ . '/../../config/init.php';

use App\Controllers\ExportController;
use App\Services\Settings\SettingsFactory;
use App\Session\SessionManager;
use App\Support\ApiResponse;

try {
    require_once __DIR__ . '/../../App/database/database.php';
    $controller = new ExportController(
        SettingsFactory::exports($pdo, new SessionManager())
    );
    if (($_GET['action'] ?? '') === 'datasets') {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, private');
        echo json_encode([
            'status' => 'success',
            'datasets' => $controller->availableDatasets(),
        ]);
        exit;
    }

    if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
        http_response_code(405);
        header('Allow: GET');
        exit;
    }

    $dataset = (string) ($_GET['dataset'] ?? '');
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="slsu-' . rawurlencode($dataset) . '-' . date('Ymd-His') . '.csv"');
    header('Cache-Control: no-store, private');
    $controller->streamCsv($dataset, fopen('php://output', 'wb'));
} catch (Throwable $e) {
    error_log('Data export failed: ' . $e->getMessage());
    ApiResponse::sendError($e, 'Unable to export the selected data.');
}
