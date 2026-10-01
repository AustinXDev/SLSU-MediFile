<?php

namespace App\Repositories\PatientRepositories;

use PDO;

class DentalServiceRepository
{
    protected PDO $pdo;

    public function __construct(
        PDO $pdo
    ) {
        $this->pdo = $pdo;
    }


    public function findById(int $serviceId): ?array
    {
        $sql = "
        SELECT
            service_id,
            patient_id,
            dental_id,
            patient_signature_path
        FROM patient_dental_services
        WHERE service_id = ?
        LIMIT 1
    ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$serviceId]);

        $service = $stmt->fetch(PDO::FETCH_ASSOC);

        return $service ?: null;
    }


    public function create(
        int $patientId,
        int $dentalId,
        string $serviceDate,
        string $serviceRendered,
        int $dentistId,
        ?string $signaturePath = null,
        ?string $signatureHash = null
    ): int {

        $sql = '
          INSERT INTO patient_dental_services (
            patient_id,
            dental_id,
            service_date,
            service_rendered,
            patient_signature_path,
            patient_signature_hash,
            dentist_id
          )
          VALUES(?, ?, ?, ?, ?, ?, ?)
        ';

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
           $patientId,
           $dentalId,
           $serviceDate,
           $serviceRendered,
           $signaturePath,
           $signatureHash,
           $dentistId
        ]);

        return (int) $this->pdo->lastInsertId();

    }


    public function delete(int $serviceId): bool
    {
        $sql = "
        DELETE FROM patient_dental_services
        WHERE service_id = ?
    ";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([$serviceId]);
    }

}
