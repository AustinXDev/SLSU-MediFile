<?php

namespace App\Repositories\PatientRepositories;

use PDO;

class DentalClinicRepository
{
    protected PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findIdByKey(
        string $clinicKey
    ): ?int {

        $sql = "
            SELECT clinic_id
            FROM dental_clinics
            WHERE clinic_key = ?
              AND is_active = 1
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            $clinicKey
        ]);

        $clinicId = $stmt->fetchColumn();

        return $clinicId !== false
            ? (int) $clinicId
            : null;

    }

}
