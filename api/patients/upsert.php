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


    $data = json_decode(
        file_get_contents("php://input"),
        true
    );

    require_once __DIR__ . '/../../App/database/database.php';

    $patientRepo = new PatientReposity($pdo);

    $physicalExamRepo = new PhysicalExaminationRepository($pdo);

    $service = new PatientService($pdo, $patientRepo, $physicalExamRepo);

    $controller = new PatientController($service);

    $result = $controller->upsert($data);

    echo json_encode($result);

} catch (\Throwable $e) {

    http_response_code(500);

    echo json_encode([
      'status' => 'error',
      'message' => $e->getMessage()
    ]);
}
