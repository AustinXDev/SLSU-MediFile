<?php

namespace App\Services\PatientServices;

use App\Repositories\PatientRepositories\PatientReposity;
use App\Repositories\PatientRepositories\PhysicalExaminationRepository;
use PDO;
use RuntimeException;
use Throwable;

class PatientService
{
    protected PDO $pdo;
    protected PatientReposity $patientRepo;
    protected PhysicalExaminationRepository $physicalExamRepo;


    public function __construct(
        PDO $pdo,
        PatientReposity $patientRepo,
        PhysicalExaminationRepository $physicalExamRepo
    ) {

        $this->pdo = $pdo;
        $this->patientRepo = $patientRepo;
        $this->physicalExamRepo = $physicalExamRepo;

    }

    public function getAll(): array
    {

        try {
            return $this->patientRepo->getAll();
        } catch (\PDOException $e) {
            error_log("PatientService::getAll Error: " . $e->getMessage());

            return [];
        }

    }


    // Create or Update a Single Patient Record
    public function upsert(array $data): array
    {
        $patient_id   = !empty($patient_data['id']) ? (int)$patient_data['id'] : null;
        $patient_data = $data['record'] ?? [];
        $basicInfo = $patient_data['basicInformation'] ?? [];
        $physicalExam = $patient_data['physicalExamination'] ?? [];
        $histories = $patient_data['histories'] ?? [];

        $examinationId = !empty($physicalExam['id']) ? (int)$physicalExam['id'] : null;


        if (!is_array($basicInfo) || empty($basicInfo)) {
            return [
                'status'  => 'error',
                'message' => 'Invalid or missing basicInformation payload structure.'
            ];
        }

        $requiredFields = [
            'firstname',
            'surname',
            'middlename',
            'dob',
            'gender',
            'civilStatus',
            'religion',
            'nationality',
            'department',
            'position',
            'contact',
            'address',
            'emergencyName',
            'emergencyNumber',
            'emergencyAddress'
        ];

        // 1. Validate Single Record
        $missingFields = [];
        foreach ($requiredFields as $field) {
            if (!array_key_exists($field, $basicInfo) || trim((string)($basicInfo[$field] ?? '')) === '') {
                $missingFields[] = $field;
            }
        }

        if (!empty($missingFields)) {
            return [
                'status'         => 'error',
                'missing_fields' => $missingFields,
                'message'        => 'Missing required field(s): ' . implode(', ', $missingFields)
            ];
        }

        // 2. Database Execution
        try {
            if (!$this->pdo->inTransaction()) {
                $this->pdo->beginTransaction();
            }

            //Save or update patient record
            if ($patient_id) {
                $this->patientRepo->update($patient_id, $basicInfo);
            } else {
                // Insert new single patient
                $patient_id = $this->patientRepo->create($basicInfo);
            }

            //Save or update physical examination
            if ($examinationId) {
                $this->physicalExamRepo->update($examinationId, $physicalExam, $histories);
            } else {
                $examinationId = $this->physicalExamRepo->create($patient_id, $physicalExam, $histories);
            }

            $this->pdo->commit();

            return [
                'status'     => 'success',
                'message'        => 'Record saved successfully.',
                'patient_id' => $patient_id,
                'examination_id' => $examinationId
            ];

        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            return [
                'status'  => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

}
