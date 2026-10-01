<?php

require_once __DIR__ . '/../../config.php';

use App\Repositories\EvaluationRepositories\EvaluationRepository;
use App\Services\Evaluation\EvaluationService;
use App\Controllers\EvaluationControllers\EvaluationController;

try {

    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['submission_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing submission_id']);
        exit;
    }

    require_once __DIR__ . '/../../App/database/database.php';

    $evaluationRepository = new EvaluationRepository($pdo);
    $evaluationService = new EvaluationService($evaluationRepository, $pdo);
    $evaluationController = new EvaluationController($evaluationService);

    $response = $evaluationController->evaluateSubmission($input);

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
      'error' => 'Internal Server Error',
      'message' => $e->getMessage()
    ]);
    exit;
}
