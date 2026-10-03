<?php

require_once __DIR__ . '/../../config/init.php';

use App\Controllers\Admin\AdminController;
use App\Middleware\AdminMiddleware;
use App\Repositories\AdminRepository;
use App\Repositories\LogRepositories\LogRepository;
use App\Services\Admin\AdminService;
use App\Services\Logs\LogsService;
use App\Session\SessionManager;

header('Content-Type: application/json; charset=utf-8');

try {
    $session = new SessionManager();
    $middleware = new AdminMiddleware($session);
    $middleware->requireAuth();

    $role = trim((string) ($session->get('role') ?? ''));
    if ($role !== 'Super Admin' && $role !== 'Administrator') {
        http_response_code(403);
        echo json_encode([
            'status' => 'error',
            'message' => 'Access denied.'
        ]);
        exit;
    }

    require_once __DIR__ . '/../../App/database/database.php';

    $repo = new AdminRepository($pdo);
    $service = new AdminService($repo, new LogsService(new LogRepository($pdo)), $session);
    $controller = new AdminController($service);

    $payload = json_decode(file_get_contents('php://input') ?: '[]', true);
    if (!is_array($payload)) {
        $payload = [];
    }

    $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    $id = (int) ($_GET['id'] ?? $payload['id'] ?? 0);
    $action = $_GET['action'] ?? $payload['action'] ?? null;

    if ($method === 'GET') {
        if ($id > 0) {
            $result = $controller->getAccount($id);
            echo json_encode([
                'status' => 'success',
                'data' => $result['account'] ?? null,
            ]);
            exit;
        }

        $result = $controller->getAccounts($_GET);
        echo json_encode([
            'status' => 'success',
            'data' => $result,
        ]);
        exit;
    }

    if ($method === 'POST') {
        if ($action === 'reset-password') {
            $result = $controller->resetPassword($payload);
            echo json_encode([
                'status' => 'success',
                'message' => $result['message'] ?? 'Password reset successfully.'
            ]);
            exit;
        }

        if ($action === 'toggle-status') {
            $result = $controller->toggleStatus($payload);
            echo json_encode([
                'status' => 'success',
                'message' => $result['message'] ?? 'User status updated successfully.'
            ]);
            exit;
        }

        $result = $controller->create($payload);
        echo json_encode([
            'status' => 'success',
            'message' => $result['message'] ?? 'User account created successfully.',
            'data' => $result['account'] ?? null,
        ]);
        exit;
    }

    if ($method === 'PUT' || $method === 'PATCH') {
        $result = $controller->update($payload);
        echo json_encode([
            'status' => 'success',
            'message' => $result['message'] ?? 'User account updated successfully.',
            'data' => $result['account'] ?? null,
        ]);
        exit;
    }

    if ($method === 'DELETE') {
        $currentAdminId = (int) ($session->get('admin_id') ?? 0);
        if ($currentAdminId === $id) {
            http_response_code(403);
            echo json_encode([
                'status' => 'error',
                'message' => 'You cannot delete your own account while logged in.'
            ]);
            exit;
        }

        $result = $controller->delete($id);
        echo json_encode([
            'status' => 'success',
            'message' => $result['message'] ?? 'User account deleted successfully.'
        ]);
        exit;
    }

    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Unsupported request method.'
    ]);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
    ]);
}
