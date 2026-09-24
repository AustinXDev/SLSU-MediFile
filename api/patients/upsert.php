<?php

require_once __DIR__ . '/../../config/init.php';

use App\Controllers\PatientControllers\PatientController;
use App\Middleware\AdminMiddleware;
use App\Repositories\PatientRepositories\DentalClinicRepository;
use App\Repositories\PatientRepositories\DentalRecordRepository;
use App\Repositories\PatientRepositories\DentaTreatmentPlanRepository;
use App\Repositories\PatientRepositories\PatientRepository;
use App\Repositories\PatientRepositories\PhysicalExaminationRepository;
use App\Services\PatientServices\PatientService;
use App\Session\SessionManager;

header(
    "Content-Type: application/json; charset=utf-8"
);

try {

    /**
     * Validate first if authenticated
     * before proceed to delete process
     */
    $session = new SessionManager();
    $middleware = new AdminMiddleware($session);

    $middleware->requireAuth();


    $data = json_decode(
        file_get_contents("php://input"),
        true
    );

    $data = json_decode(
        file_get_contents("php://input"),
        true
    );

    require_once __DIR__ . '/../../App/database/database.php';

    $patientRepo = new PatientRepository($pdo);

    $physicalExamRepo = new PhysicalExaminationRepository($pdo);

    $dentalRepo = new DentalRecordRepository($pdo);

    $dentalClinicRepo = new DentalClinicRepository($pdo);

    $dentalTreatmentRepo = new DentaTreatmentPlanRepository($pdo);

    $service = new PatientService($pdo, $patientRepo, $physicalExamRepo, $dentalRepo, $dentalClinicRepo, $dentalTreatmentRepo);

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
