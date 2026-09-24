<?php

namespace App\Repositories\PatientRepositories;

use PDO;

class DentaTreatmentPlanRepository
{
    protected PDO $pdo;

    public function __construct(
        PDO $pdo
    ) {

        $this->pdo = $pdo;

    }

    public function upsert(
        int $dentalId,
        int $clinicId,
        string $treatment
    ): void {



        if ($treatment === '') {

            $sql = "
                DELETE FROM patient_dental_treatment_plans
                WHERE dental_id = ?
                  AND clinic_id = ?
            ";

            $stmt = $this->pdo->prepare($sql);

            $stmt->execute([
                $dentalId,
                $clinicId
            ]);

            return;
        }

        $sql = "
            INSERT INTO patient_dental_treatment_plans (
                dental_id,
                clinic_id,
                treatment
            )
            VALUES (?, ?, ?)

            ON DUPLICATE KEY UPDATE
                treatment = VALUES(treatment),
                updated_at = CURRENT_TIMESTAMP
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            $dentalId,
            $clinicId,
            $treatment
        ]);

    }

}
