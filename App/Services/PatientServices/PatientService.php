<?php

namespace App\Services\PatientServices;

use App\Repositories\PatientRepositories\PatientReposity;
use App\Repositories\PatientRepositories\PhysicalExaminationRepository;
use App\Repositories\PatientRepositories\DentalRecordRepository;
use PDO;
use RuntimeException;
use Throwable;

class PatientService
{
    protected PDO $pdo;
    protected PatientReposity $patientRepo;
    protected PhysicalExaminationRepository $physicalExamRepo;
    protected DentalRecordRepository $dentalRepo;


    public function __construct(
        PDO $pdo,
        PatientReposity $patientRepo,
        PhysicalExaminationRepository $physicalExamRepo,
        DentalRecordRepository $dentalRepo
    ) {

        $this->pdo = $pdo;
        $this->patientRepo = $patientRepo;
        $this->physicalExamRepo = $physicalExamRepo;
        $this->dentalRepo = $dentalRepo;

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
        $patient_id   = !empty($data['payload']['id']) ? (int)$data['payload']['id'] : null;

        $patient_data = $data['payload'] ?? [];

        $basicInfo = $patient_data['basicInformation'] ?? [];

        $physicalExam = $patient_data['physicalExamination'] ?? [];

        $histories = $patient_data['histories'] ?? [];

        $examinationId = !empty($physicalExam['id']) ? (int)$physicalExam['id'] : null;

        $dental_record = $patient_data['dental_record'] ?? [];

        $dentalId = !empty($dental_record['id'] ? (int)$dental_record['id'] : null);


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

        $method = [];

        // 2. Database Execution
        try {
            if (!$this->pdo->inTransaction()) {
                $this->pdo->beginTransaction();
            }

            //Save or update patient record
            if ($patient_id) {
                $this->patientRepo->update($patient_id, $basicInfo);
                $method[] = 'update patient information';
            } else {
                // Insert new single patient
                $patient_id = $this->patientRepo->create($basicInfo);
                $method[] = 'create patient information';
            }

            //Save or update physical examination
            if ($examinationId) {
                $this->physicalExamRepo->update($examinationId, $physicalExam, $histories);
                $method[] = 'update patient examination';
            } else {
                $examinationId = $this->physicalExamRepo->create($patient_id, $physicalExam, $histories);
                $method[] = 'create patient examination.';
            }

            //Save or update dental records
            if ($dentalId) {
                $this->dentalRepo->update(
                    $dentalId,
                    $dental_record
                );
            } else {
                $dentalId = $this->dentalRepo->create(
                    $patient_id,
                    $dental_record
                );
            }

            $this->pdo->commit();

            return [
                'status'     => 'success',
                'message'        => 'Record saved successfully.',
                'patient_id' => $patient_id,
                'examination_id' => $examinationId,
                'dental_id' => $dentalId,
                'method' => $method
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


    /**
     * Delete patient record
     */
    public function delete(
        int $patientId
    ): array {
        if ($patientId <= 0) {
            throw new RuntimeException(
                "Failed to delete record."
            );
        }

        try {
            $this->pdo->beginTransaction();

            $isDeleted = $this->patientRepo->delete($patientId);

            if (!$isDeleted) {
                throw new RuntimeException(
                    "Failed to delete record."
                );
            }

            $this->pdo->commit();

            return [
                'status'  => 'success',
                'message' => 'Successfully deleted record.'
            ];

        } catch (\Throwable $e) {

            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw new RuntimeException(
                "Failed to delete record.",
                0,
                $e
            );
        }
    }

}
