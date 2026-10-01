<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/config/init.php';
require_once __DIR__ . '/../../App/database/database.php';

use App\Controllers\PatientControllers\PatientController;
use App\Middleware\AdminMiddleware;
use App\Repositories\PatientRepositories\DentalClinicRepository;
use App\Repositories\PatientRepositories\DentalRecordRepository;
use App\Repositories\PatientRepositories\DentalServiceRepository;
use App\Repositories\PatientRepositories\DentaTreatmentPlanRepository;
use App\Repositories\PatientRepositories\PatientRepository;
use App\Repositories\PatientRepositories\PhysicalExaminationRepository;
use App\Repositories\LogRepositories\LogRepository;
use App\Services\Logs\LogsService;
use App\Services\PatientServices\PatientService;
use App\Session\SessionManager;

header('Content-Type: application/json');

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


    /*
    |--------------------------------------------------------------------------
    | Get request body
    |--------------------------------------------------------------------------
    */

    $input = json_decode(
        file_get_contents('php://input'),
        true
    );

    if (!is_array($input)) {
        throw new InvalidArgumentException(
            'Invalid request body.'
        );
    }

    $data = $input['data'] ?? [];

    if (!is_array($data)) {
        throw new InvalidArgumentException(
            'Invalid request data.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Validate service ID
    |--------------------------------------------------------------------------
    */

    $serviceId = filter_var(
        $data['serviceId'] ?? null,
        FILTER_VALIDATE_INT
    );


    if (!$serviceId || $serviceId < 1) {
        throw new InvalidArgumentException(
            'Invalid dental service ID.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Dependencies
    |--------------------------------------------------------------------------
    */
    $patientRepo = new PatientRepository($pdo);

    $physicalExamRepo = new PhysicalExaminationRepository($pdo);

    $dentalRepo = new DentalRecordRepository($pdo);

    $dentalClinicRepo = new DentalClinicRepository($pdo);

    $dentalTreatmentRepo = new DentaTreatmentPlanRepository($pdo);
    $dentalServiceRepo = new DentalServiceRepository($pdo);

    $logRepo = new LogRepository($pdo);

    $logService = new LogsService($logRepo);
    $service = new PatientService($pdo, $patientRepo, $physicalExamRepo, $dentalRepo, $dentalClinicRepo, $dentalTreatmentRepo, $dentalServiceRepo, $logService, $session);

    $controller = new PatientController($service);

    /*
    |--------------------------------------------------------------------------
    | Delete service
    |--------------------------------------------------------------------------
    */

    $controller->deleteDentalService($serviceId);

    echo json_encode([
        'status' => 'success',
        'message' => 'Dental service deleted successfully.',
    ]);

} catch (InvalidArgumentException $e) {

    http_response_code(400);

    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
    ]);

} catch (Throwable $e) {

    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode([
        'status' => 'error',
        'message' => 'Unable to delete dental service.',
    ]);
}
