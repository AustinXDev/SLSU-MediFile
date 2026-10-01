<?php

namespace App\Services\Evaluation;

use App\Repositories\EvaluationRepositories\EvaluationRepository;
use PDO;

class EvaluationService
{
    private EvaluationRepository $evaluationRepository;
    private PDO $pdo;

    public function __construct(EvaluationRepository $evaluationRepository, PDO $pdo)
    {
        $this->evaluationRepository = $evaluationRepository;
        $this->pdo = $pdo;
    }

    public function evaluateSubmission(array $data): array
    {
        $submissionId = $data['submission_id'] ?? null;

        if (!$submissionId) {
            throw new \InvalidArgumentException('Missing submission_id');
        }

        $existingSubmission = $this->evaluationRepository->findSubmissionById($submissionId);

        if ($existingSubmission) {
            throw new \Exception('Submission has already been evaluated');
        }

        try {

            $this->pdo->beginTransaction();

            $this->evaluationRepository->store($data);

            $this->pdo->commit();

            return [
                'status' => 'success',
                'message' => 'Evaluation stored successfully'
            ];

        } catch (\Throwable $e) {

            $this->pdo->rollBack();
            throw $e;

        }
    }
}
