<?php

namespace App\Repositories\PatientRepositories;

use PDO;

class DentalRecordRepository
{
    protected PDO $pdo;

    public function __construct(
        PDO $pdo
    ) {

        $this->pdo = $pdo;

    }

    /**
     * Add new dental records
     */
    public function create(
        int $patientId,
        array $dentalRecords
    ): int {

        $teethData = !empty($dentalRecords['teethData'])
                    ? json_encode($dentalRecords['teethData']) : null;


        $sql = "INSERT INTO patient_dental_records (
            patient_id,
            teeth_data
          )
          VALUES
          (?, ?)
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
          $patientId,
          $teethData
        ]);

        return (int) $this->pdo->lastInsertId();

    }


    /**
     * Update the existing dental records by primary key ID
     */
    public function update(
        int $dentalId,
        array $dentalRecords,
    ): bool {

        $teethData = !empty($dentalRecords['teethData'] ? json_encode($dentalRecords['teethData']) : null);

        $sql = "UPDATE patient_dental_records
                SET 
                  teeth_data = ?
                WHERE dental_id = ?";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
          $teethData,
          $dentalId
        ]);

    }



}
