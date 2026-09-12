<?php


require_once __DIR__ . '/../../config/init.php';

use App\Repositories\PatientRepositories\PatientReposity;
use App\Repositories\PatientRepositories\PhysicalExaminationRepository;
use App\Services\PatientServices\PatientService;
use App\Controllers\PatientControllers\PatientController;

header(
    "Content-Type: application/json; charset=utf-8"
);

try {

    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode([
            'status' => 'error',
            'message' => 'Method Not Allowed. Use GET for this endpoint.'
        ]);
        exit;
    }

    require_once __DIR__ . '/../../App/database/database.php';

    $patientRepo = new PatientReposity($pdo);

    $physicalExamRepo = new PhysicalExaminationRepository($pdo);

    $service = new PatientService($pdo, $patientRepo, $physicalExamRepo);

    $controller = new PatientController($service);

    $result = $controller->getAll();

    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'data' => $result
    ]);

} catch (\Throwable $e) {

    http_response_code(500);

    echo json_encode([
      'status' => 'error',
      'message' => $e->getMessage()
    ]);

}
