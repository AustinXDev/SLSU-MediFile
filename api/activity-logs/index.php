<?php

require_once __DIR__ . '/../../config/init.php';

use App\Controllers\ActivityLogController;
use App\Middleware\AdminMiddleware;
use App\Repositories\LogRepositories\ActivityLogRepository;
use App\Services\Logs\ActivityLogService;
use App\Session\SessionManager;

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, private');

try {
    if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
        http_response_code(405);
        header('Allow: GET');
        echo json_encode([
            'status' => 'error',
            'message' => 'Method Not Allowed. Use GET for this endpoint.',
        ]);
        exit;
    }

    $session = new SessionManager();
    (new AdminMiddleware($session))->requireAuth();

    $role = trim((string) ($session->get('role') ?? ''));
    if (!in_array($role, ['Super Admin', 'Administrator'], true)) {
        http_response_code(403);
        echo json_encode([
            'status' => 'error',
            'message' => 'Access denied.',
        ]);
        exit;
    }

    require_once __DIR__ . '/../../App/database/database.php';

    $controller = new ActivityLogController(
        new ActivityLogService(new ActivityLogRepository($pdo))
    );

    if (isset($_GET['id'])) {
        $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
        if ($id === false || $id < 1) {
            throw new InvalidArgumentException('Invalid activity record ID.');
        }

        $record = $controller->getDetails($id);
        if ($record === null) {
            http_response_code(404);
            echo json_encode([
                'status' => 'error',
                'message' => 'Activity record not found.',
            ]);
            exit;
        }

        echo json_encode([
            'status' => 'success',
            'data' => $record,
        ], JSON_INVALID_UTF8_SUBSTITUTE);
        exit;
    }

    echo json_encode([
        'status' => 'success',
        'data' => $controller->getDashboardData($_GET),
    ], JSON_INVALID_UTF8_SUBSTITUTE);
} catch (\InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
    ], JSON_INVALID_UTF8_SUBSTITUTE);
} catch (\Throwable $e) {
    error_log('Activity logs API failed: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Unable to load activity logs.',
    ]);
}
