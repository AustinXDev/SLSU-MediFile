<?php


require_once __DIR__ . '/../../config/init.php';

use App\Helpers\Dashboard\DashboardHelper;
use App\Middleware\AdminMiddleware;
use App\Repositories\DashboardRepositories\DashboardRepository;
use App\Repositories\LogRepositories\LogRepository;
use App\Services\Dashboard\DashboardService;
use App\Services\Logs\LogsService;
use App\Controllers\Dashboard\DashboardController;

try {

    AdminMiddleware::handle();

    require_once __DIR__ . '/../../App/database/database.php';

    // Dependencies
    $helper = new DashboardHelper();
    $dashboardRepo = new DashboardRepository($pdo, $helper);
    $logRepo = new LogRepository($pdo);
    $logService = new LogsService($logRepo);
    $dashboardService = new DashboardService($dashboardRepo, $logService);
    $dashboardController = new DashboardController($dashboardService);

    $response = $dashboardController->Dasboard();

    header('Content-Type: application/json');

    echo json_encode([
        'status' => 'success',
        'data' => $response
    ]);

} catch (\Throwable $e) {

    http_response_code(500);

    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'line' => $e->getLine(),
        'file' => $e->getFile()
    ]);

}
