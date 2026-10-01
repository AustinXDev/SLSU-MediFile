<?php

namespace App\Controllers\EvaluationControllers;

use App\Services\Evaluation\EvaluationService;

class EvaluationController
{
    private EvaluationService $evaluationService;

    public function __construct(EvaluationService $evaluationService)
    {
        $this->evaluationService = $evaluationService;
    }

    public function evaluateSubmission(array $data): array
    {
        return $this->evaluationService->evaluateSubmission($data);
    }
}
