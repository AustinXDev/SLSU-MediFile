<?php

namespace App\Repositories\PatientRepositories;

use PDO;

class PatientReposity
{
    protected PDO $pdo;

    public function __construct(
        PDO $pdo
    ) {
        $this->pdo = $pdo;
    }

    public function getAll(): array
    {
        $sql = "SELECT 
                patient_id AS id,
                surname,
                firstname AS firstname,
                middlename AS middlename,
                birthdate AS dob,
                sex AS gender,
                civil_status AS civilStatus,
                religion,
                nationality,
                college_dept AS department,
                job_position_course AS position,
                home_address AS address,
                tel_no AS contact,
                ice_guardian_name AS emergencyName,
                ice_address AS emergencyAddress,
                ice_tel_no AS emergencyNumber,
                created_at AS createdAt,
                is_active AS status
            FROM patients
            WHERE is_active = 1
            ORDER BY patient_id ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Create a new patient record.
     *
     * @param array $patient
     * @return int
     */
    public function create(array $patient): int
    {

        $sql = "INSERT INTO patients (
                surname, 
                firstname, 
                middlename, 
                birthdate, 
                sex, 
                civil_status, 
                religion, 
                nationality, 
                college_dept, 
                job_position_course, 
                home_address, 
                tel_no, 
                ice_guardian_name, 
                ice_address, 
                ice_tel_no, 
                is_active
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->pdo->prepare($sql);

        $executed = $stmt->execute([
            $patient['surname'] ?? null,
            $patient['firstname'] ?? $patient['first_name'] ?? null,
            $patient['middlename'] ?? $patient['middle_name'] ?? null,
            $patient['birthdate'] ?? $patient['dob'] ?? null,
            $patient['sex'] ?? $patient['gender'] ?? null,
            $patient['civil_status'] ?? $patient['civilStatus'] ?? null,
            $patient['religion'] ?? null,
            $patient['nationality'] ?? 'Filipino',
            $patient['college_dept'] ?? $patient['department'] ?? null,
            $patient['job_position_course'] ?? $patient['position'] ?? null,
            $patient['home_address'] ?? $patient['address'] ?? null,
            $patient['tel_no'] ?? $patient['contact'] ?? null,
            $patient['ice_guardian_name'] ?? $patient['emergencyName'] ?? null,
            $patient['ice_address'] ?? $patient['emergencyAddress'] ?? null,
            $patient['ice_tel_no'] ?? $patient['emergencyNumber'] ?? null,
            1
        ]);

        return $executed ? (int) $this->pdo->lastInsertId() : 0;
    }


    public function update(
        int $patientId,
        array $patient
    ): bool {

        $sql = "UPDATE patients SET 
            surname             = ?, 
            firstname           = ?, 
            middlename          = ?, 
            birthdate           = ?, 
            sex                 = ?, 
            civil_status        = ?, 
            religion            = ?, 
            nationality         = ?, 
            college_dept        = ?, 
            job_position_course = ?, 
            home_address        = ?, 
            tel_no              = ?, 
            ice_guardian_name   = ?, 
            ice_address         = ?, 
            ice_tel_no          = ?
        WHERE patient_id = ?";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            $patient['surname'] ?? null,
            $patient['firstname'] ?? $patient['first_name'] ?? null,
            $patient['middlename'] ?? $patient['middle_name'] ?? null,
            $patient['birthdate'] ?? $patient['dob'] ?? null,
            $patient['sex'] ?? $patient['gender'] ?? null,
            $patient['civil_status'] ?? $patient['civilStatus'] ?? null,
            $patient['religion'] ?? null,
            $patient['nationality'] ?? 'Filipino',
            $patient['college_dept'] ?? $patient['department'] ?? null,
            $patient['job_position_course'] ?? $patient['position'] ?? null,
            $patient['home_address'] ?? $patient['address'] ?? null,
            $patient['tel_no'] ?? $patient['contact'] ?? null,
            $patient['ice_guardian_name'] ?? $patient['emergencyName'] ?? null,
            $patient['ice_address'] ?? $patient['emergencyAddress'] ?? null,
            $patient['ice_tel_no'] ?? $patient['emergencyNumber'] ?? null,
            $patientId
        ]);


    }

}
