<?php

namespace App\Repositories\PatientRepositories;

use PDO;
use RuntimeException;

class PatientRepository
{
    protected PDO $pdo;

    public function __construct(
        PDO $pdo
    ) {
        $this->pdo = $pdo;
    }

    public function getAll(array $params = []): array
    {
        $page = filter_var($params['page'] ?? 1, FILTER_VALIDATE_INT);
        $page = $page === false || $page < 1 ? 1 : $page;

        $limit = filter_var($params['limit'] ?? 10, FILTER_VALIDATE_INT);
        $limit = $limit === false || $limit < 1 ? 10 : min(100, $limit);

        $search = trim((string) ($params['search'] ?? ''));
        $gender = trim((string) ($params['gender'] ?? ''));
        $ageGroup = trim((string) ($params['ageGroup'] ?? ''));
        $status = trim((string) ($params['status'] ?? ''));

        $conditions = ['1 = 1'];
        $values = [];

        if ($search !== '') {
            $conditions[] = "(
                CONCAT_WS(' ', p.firstname, p.middlename, p.surname) LIKE ?
                OR CAST(p.patient_id AS CHAR) LIKE ?
                OR p.tel_no LIKE ?
            )";
            $searchValue = '%' . $search . '%';
            array_push($values, $searchValue, $searchValue, $searchValue);
        }

        if ($gender !== '') {
            $conditions[] = 'p.sex = ?';
            $values[] = $gender;
        }

        $ageConditions = [
            'child' => 'TIMESTAMPDIFF(YEAR, p.birthdate, CURDATE()) < 18',
            'adult' => 'TIMESTAMPDIFF(YEAR, p.birthdate, CURDATE()) BETWEEN 18 AND 59',
            'senior' => 'TIMESTAMPDIFF(YEAR, p.birthdate, CURDATE()) >= 60',
        ];
        if ($ageGroup !== '') {
            if (!isset($ageConditions[$ageGroup])) {
                throw new RuntimeException('Unsupported patient age filter.');
            }
            $conditions[] = $ageConditions[$ageGroup];
        }

        if ($status !== '') {
            if (!in_array($status, ['Active', 'Inactive'], true)) {
                throw new RuntimeException('Unsupported patient status filter.');
            }
            $conditions[] = 'p.is_active = ?';
            $values[] = $status === 'Active' ? 1 : 0;
        }

        $where = implode(' AND ', $conditions);
        $countStmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM patients p WHERE {$where}"
        );
        $countStmt->execute($values);
        $total = (int) $countStmt->fetchColumn();
        $totalPages = max(1, (int) ceil($total / $limit));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $limit;

        $sql = "SELECT
                pe.*,
                pd.*,
                pd.dental_id,
                pe.id AS examinationId,
                p.patient_id AS id,
                p.patient_id AS patient_id,
                p.surname,
                p.firstname AS firstname,
                p.middlename AS middlename,
                p.birthdate AS dob,
                p.sex AS gender,
                p.civil_status AS civilStatus,
                p.religion,
                p.nationality,
                p.college_dept AS department,
                p.job_position_course AS position,
                p.home_address AS address,
                p.tel_no AS contact,
                p.ice_guardian_name AS emergencyName,
                p.ice_address AS emergencyAddress,
                p.ice_tel_no AS emergencyNumber,
                p.created_at AS createdAt,
                p.is_active AS status
            FROM patients p
            LEFT JOIN patient_medical_examinations pe
                ON pe.id = (
                    SELECT pe_latest.id
                    FROM patient_medical_examinations pe_latest
                    WHERE pe_latest.patient_id = p.patient_id
                    ORDER BY pe_latest.id DESC
                    LIMIT 1
                )
            LEFT JOIN patient_dental_records pd
                ON pd.dental_id = (
                    SELECT pd_latest.dental_id
                    FROM patient_dental_records pd_latest
                    WHERE pd_latest.patient_id = p.patient_id
                    ORDER BY pd_latest.dental_id DESC
                    LIMIT 1
                )
            WHERE {$where}
            ORDER BY p.created_at DESC, p.patient_id DESC
            LIMIT ? OFFSET ?";

        $stmt = $this->pdo->prepare($sql);
        foreach ($values as $index => $value) {
            $stmt->bindValue($index + 1, $value);
        }
        $stmt->bindValue(count($values) + 1, $limit, PDO::PARAM_INT);
        $stmt->bindValue(count($values) + 2, $offset, PDO::PARAM_INT);
        $stmt->execute();

        $patients = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        foreach ($patients as &$patient) {
            $patient['id'] = (int) $patient['id'];
            $patient['patient_id'] = (int) $patient['patient_id'];
            $patient['status'] = (int) $patient['status'];

            $dentalId = !empty($patient['dental_id'])
                ? (int) $patient['dental_id']
                : null;

            $patient['treatmentPlan'] = $this->getTreatmentPlan(
                $dentalId
            );

            $patient['dentalServices'] = $this->getDentalService(
                $dentalId
            );
        }

        unset($patient);

        $maxPatientId = (int) $this->pdo
            ->query('SELECT COALESCE(MAX(patient_id), 0) FROM patients')
            ->fetchColumn();

        return [
            'patients' => $patients,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'totalPages' => $totalPages,
            ],
            'maxPatientId' => $maxPatientId,
        ];
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


    public function delete(
        int $patientId
    ): bool {

        $sql = "DELETE FROM patients
                WHERE patient_id = ?";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            $patientId
        ]);

    }


    /**
     * Get the treatment plan
     */
    private function getTreatmentPlan(?int $dentalId): array
    {
        if (!$dentalId) {
            return [];
        }

        $sql = "
        SELECT
            dc.clinic_key,
            ptp.treatment
        FROM patient_dental_treatment_plans ptp

        INNER JOIN dental_clinics dc
            ON dc.clinic_id = ptp.clinic_id

        WHERE ptp.dental_id = ?

        ORDER BY dc.sort_order ASC
    ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$dentalId]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $treatmentPlan = [];

        foreach ($rows as $row) {
            $treatmentPlan[$row['clinic_key']] = $row['treatment'];
        }

        return $treatmentPlan;
    }


    private function getDentalService(
        ?int $dentalId
    ): array {

        if (!$dentalId) {
            return [];
        }

        $sql = '

        SELECT 
            service_id AS serviceId,
            service_date AS serviceDate,
            service_rendered AS serviceRendered,
            patient_signature_path AS signaturePath

        FROM patient_dental_services
        WHERE dental_id = ?
        ORDER BY service_id DESC
    ';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$dentalId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }


}
