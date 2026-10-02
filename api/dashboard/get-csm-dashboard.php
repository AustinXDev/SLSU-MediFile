<?php

require_once __DIR__ . '/../../config/init.php';

use App\Controllers\Dashboard\CsmDashboardController;
use App\Middleware\AdminMiddleware;
use App\Repositories\DashboardRepositories\CsmDashboardRepository;
use App\Services\Dashboard\CsmDashboardService;

header('Content-Type: application/json; charset=utf-8');

try {
    AdminMiddleware::handle();
    require_once __DIR__ . '/../../App/database/database.php';

    $repository = new CsmDashboardRepository($pdo);
    $service = new CsmDashboardService($repository);
    $controller = new CsmDashboardController($service);
    $data = $controller->getDashboard($_GET);

    echo json_encode(['status' => 'success', 'data' => $data], JSON_THROW_ON_ERROR);
} catch (InvalidArgumentException $exception) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $exception->getMessage()]);
} catch (Throwable $exception) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Unable to load CSM dashboard data.']);
}
