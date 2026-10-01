<?php

namespace App\Services\PatientServices;

use App\Repositories\PatientRepositories\PatientRepository;
use App\Repositories\PatientRepositories\PhysicalExaminationRepository;
use App\Repositories\PatientRepositories\DentalRecordRepository;
use App\Repositories\PatientRepositories\DentalClinicRepository;
use App\Repositories\PatientRepositories\DentaTreatmentPlanRepository;
use App\Repositories\PatientRepositories\DentalServiceRepository;
use App\Services\Logs\LogsService;
use App\Session\SessionManager;
use PDO;
use RuntimeException;
use Throwable;

class PatientService
{
    protected PDO $pdo;
    protected PatientRepository $patientRepo;
    protected PhysicalExaminationRepository $physicalExamRepo;
    protected DentalRecordRepository $dentalRepo;
    protected DentalClinicRepository $dentalClinicRepo;
    protected DentaTreatmentPlanRepository $dentalTreatmentRepo;
    protected DentalServiceRepository $dentalServiceRepo;
    protected LogsService $logService;
    protected SessionManager $session;


    public function __construct(
        PDO $pdo,
        PatientRepository $patientRepo,
        PhysicalExaminationRepository $physicalExamRepo,
        DentalRecordRepository $dentalRepo,
        DentalClinicRepository $dentalClinicRepo,
        DentaTreatmentPlanRepository $dentalTreatmentRepo,
        DentalServiceRepository $dentalServiceRepo,
        LogsService $logService,
        SessionManager $session
    ) {

        $this->pdo                  = $pdo;
        $this->patientRepo          = $patientRepo;
        $this->physicalExamRepo     = $physicalExamRepo;
        $this->dentalRepo           = $dentalRepo;
        $this->dentalClinicRepo     = $dentalClinicRepo;
        $this->dentalTreatmentRepo  = $dentalTreatmentRepo;
        $this->dentalServiceRepo    = $dentalServiceRepo;
        $this->logService           = $logService;
        $this->session              = $session;

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

        $dentalId = !empty($dental_record['id'])
        ? (int) $dental_record['id']
        : null;

        $treatmentPlan = $patient_data['treatmentPlan'] ?? [];

        $dentalServices = $patient_data['dentalServices'] ?? [];

        $adminId = $this->session->get('admin_id');
        $isNewPatient = !$patient_id;

        if (!$adminId) {
            throw new RuntimeException(
                'Authenticated administrator not found.'
            );
        }

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

            $this->saveTreatmentPlan($dentalId, $treatmentPlan);

            $this->saveDentalServices($patient_id, $dentalId, $dentalServices);

            $this->pdo->commit();


            /**
             * Audit Log
             */
            $this->logService->record(
                $adminId,
                $isNewPatient ? 'CREATE' : 'UPDATE',
                $isNewPatient
                    ? 'Created a new patient record.'
                    : 'Updated a patient record.',
                'patients',
                $patient_id
            );

            /**
             * Return response
             */
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

        $adminId = $this->session->get('admin_id');

        if (!$adminId) {
            throw new RuntimeException(
                'Authenticated administrator not found.'
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

            $this->logService->record(
                $adminId,
                'DELETE',
                'Deleted a patient record.',
                'patients',
                $patientId
            );

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


    private function saveTreatmentPlan(
        int $dentalId,
        array $treatmentPlan
    ): void {

        foreach ($treatmentPlan as $clinicKey => $treatment) {

            $treatment = trim((string) $treatment);

            // Find clinic ID from the stable clinic key
            $clinicId = $this->dentalClinicRepo->findIdByKey(
                $clinicKey
            );

            if ($clinicId === null) {
                throw new RuntimeException(
                    "Unknown dental clinic: {$clinicKey}"
                );
            }

            // Insert / update / delete
            $this->dentalTreatmentRepo->upsert(
                $dentalId,
                $clinicId,
                $treatment
            );
        }

    }


    private function saveDentalServices(
        int $patientId,
        int $dentalId,
        array $services
    ): void {
        foreach ($services as $service) {

            $serviceDate = trim(
                (string)($service['serviceDate'] ?? '')
            );

            $serviceRendered = trim(
                (string)($service['serviceRendered'] ?? '')
            );

            $patientSignature = $service['patientSignature'] ?? null;

            if ($serviceDate === '') {
                throw new RuntimeException(
                    'Dental service date is required.'
                );
            }

            if ($serviceRendered === '') {
                throw new RuntimeException(
                    'Dental service description is required.'
                );
            }

            $signature = $this->savePatientSignature(
                $patientSignature
            );

            $dentistId = 0;

            $this->dentalServiceRepo->create(
                $patientId,
                $dentalId,
                $serviceDate,
                $serviceRendered,
                $dentistId,
                $signature['path'],
                $signature['hash']
            );
        }
    }


    /**
     * Save patient signature
     */
    private function savePatientSignature(?string $dataUrl): array
    {
        if (!$dataUrl) {
            throw new RuntimeException(
                'Patient signature is required.'
            );
        }

        if (
            !preg_match(
                '/^data:image\/png;base64,(.+)$/',
                $dataUrl,
                $matches
            )
        ) {
            throw new RuntimeException(
                'Invalid patient signature format.'
            );
        }

        $binary = base64_decode(
            $matches[1],
            true
        );

        if ($binary === false) {
            throw new RuntimeException(
                'Unable to decode patient signature.'
            );
        }

        // Verify that the decoded data is actually a PNG image.
        $imageInfo = @getimagesizefromstring($binary);

        if (
            $imageInfo === false ||
            ($imageInfo['mime'] ?? '') !== 'image/png'
        ) {
            throw new RuntimeException(
                'Invalid patient signature image.'
            );
        }

        // Prevent excessively large signatures.
        if (strlen($binary) > 2 * 1024 * 1024) {
            throw new RuntimeException(
                'Patient signature is too large.'
            );
        }

        $directory = dirname(__DIR__, 2)
            . '/storage/private/signatures';

        if (!is_dir($directory)) {
            if (!mkdir($directory, 0750, true)) {
                throw new RuntimeException(
                    'Unable to create signature directory.'
                );
            }
        }

        $filename = bin2hex(random_bytes(32)) . '.png';

        $fullPath = $directory . '/' . $filename;

        if (file_put_contents($fullPath, $binary) === false) {
            throw new RuntimeException(
                'Unable to save patient signature.'
            );
        }

        return [
            'path' => 'storage/private/signatures/' . $filename,
            'hash' => hash('sha256', $binary),
        ];
    }


    /**
     * Delete dental service
     */
    public function deleteDentalService(
        int $serviceId
    ): void {

        $this->pdo->beginTransaction();

        try {

            $service = $this->dentalServiceRepo->findById($serviceId);

            if (!$service) {
                throw new RuntimeException(
                    'Dental service not found.'
                );
            }

            $signaturePath =  $service['patient_signature_path'] ?? null;

            $this->dentalServiceRepo->delete($serviceId);

            /*
            |--------------------------------------------------------------------------
            | Delete private signature file
            |--------------------------------------------------------------------------
            */

            if ($signaturePath) {

                $filePath = ROOT_PATH
                    . '/App/'
                    . ltrim($signaturePath, '/');

                if (is_file($filePath)) {
                    unlink($filePath);
                }
            }

            $this->pdo->commit();

        } catch (Throwable $e) {

            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }

    }

}
