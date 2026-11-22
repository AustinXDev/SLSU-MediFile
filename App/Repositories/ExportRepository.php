<?php

namespace App\Repositories;

use PDO;
use RuntimeException;

class ExportRepository
{
    private const DATASETS = [
        'patients' => [
            'label' => 'Patient Records',
            'columns' => [
                'patient_id', 'student_id', 'surname', 'firstname', 'middlename', 'birthdate', 'age',
                'sex', 'civil_status', 'religion', 'nationality', 'college_dept', 'job_position_course',
                'home_address', 'tel_no', 'ice_guardian_name', 'ice_address', 'ice_tel_no',
                'is_active', 'created_at', 'updated_at',
            ],
            'sql' => "
                SELECT patient_id, student_id, surname, firstname, middlename, birthdate, age,
                       sex, civil_status, religion, nationality, college_dept, job_position_course,
                       home_address, tel_no, ice_guardian_name, ice_address, ice_tel_no,
                       is_active, created_at, updated_at
                FROM patients
                ORDER BY patient_id
            ",
        ],
        'medical_records' => [
            'label' => 'Medical Records',
            'columns' => [
                'medical_record_id', 'patient_id', 'student_id', 'surname', 'firstname',
                'past_history', 'previous_hospitalization', 'previous_operation', 'previous_trauma',
                'social_history', 'sports_specification', 'blood_pressure', 'temperature',
                'pulse_rate', 'respiratory_rate', 'height_cm', 'weight_kg', 'ideal_body_weight_kg',
                'head_neck', 'respiratory', 'cardiovascular', 'gastrointestinal', 'genitourinary',
                'extremities', 'neurologic', 'suggestions_treatment', 'laboratory_results',
                'examination_date', 'updated_at',
            ],
            'sql' => "
                SELECT m.id AS medical_record_id, m.patient_id, p.student_id, p.surname,
                       p.firstname, m.past_history, m.previous_hospitalization,
                       m.previous_operation, m.previous_trauma, m.social_history,
                       m.sports_specification, m.blood_pressure, m.temperature, m.pulse_rate,
                       m.respiratory_rate, m.height_cm, m.weight_kg, m.ideal_body_weight_kg,
                       m.head_neck, m.respiratory, m.cardiovascular, m.gastrointestinal,
                       m.genitourinary, m.extremities, m.neurologic, m.suggestions_treatment,
                       m.laboratory_results, m.examination_date, m.updated_at
                FROM patient_medical_examinations m
                INNER JOIN patients p ON p.patient_id = m.patient_id
                ORDER BY m.id
            ",
        ],
        'dental_records' => [
            'label' => 'Dental Records',
            'columns' => [
                'dental_id', 'patient_id', 'student_id', 'surname', 'firstname', 'teeth_data', 'examined_at',
            ],
            'sql' => "
                SELECT d.dental_id, d.patient_id, p.student_id, p.surname, p.firstname,
                       d.teeth_data, d.examined_at
                FROM patient_dental_records d
                INNER JOIN patients p ON p.patient_id = d.patient_id
                ORDER BY d.dental_id
            ",
        ],
        'dental_treatment_plans' => [
            'label' => 'Dental Treatment Plans',
            'columns' => [
                'treatment_id', 'dental_id', 'patient_id', 'student_id', 'surname', 'firstname',
                'clinic_name', 'treatment', 'created_at', 'updated_at',
            ],
            'sql' => "
                SELECT t.treatment_id, t.dental_id, d.patient_id, p.student_id, p.surname,
                       p.firstname, c.clinic_name, t.treatment, t.created_at, t.updated_at
                FROM patient_dental_treatment_plans t
                INNER JOIN patient_dental_records d ON d.dental_id = t.dental_id
                INNER JOIN patients p ON p.patient_id = d.patient_id
                LEFT JOIN dental_clinics c ON c.clinic_id = t.clinic_id
                ORDER BY t.treatment_id
            ",
        ],
        'service_history' => [
            'label' => 'Service History',
            'columns' => [
                'service_id', 'patient_id', 'student_id', 'surname', 'firstname', 'dental_id',
                'service_date', 'service_rendered', 'dentist_id', 'created_at', 'updated_at',
            ],
            'sql' => "
                SELECT s.service_id, s.patient_id, p.student_id, p.surname, p.firstname,
                       s.dental_id, s.service_date, s.service_rendered, s.dentist_id,
                       s.created_at, s.updated_at
                FROM patient_dental_services s
                INNER JOIN patients p ON p.patient_id = s.patient_id
                ORDER BY s.service_id
            ",
        ],
        'activity_logs' => [
            'label' => 'Activity Logs',
            'columns' => [
                'log_id', 'admin_id', 'username', 'action', 'description', 'module', 'record_id',
                'ip_address', 'user_agent', 'created_at',
            ],
            'sql' => "
                SELECT l.log_id, l.admin_id, a.username, l.action, l.description, l.module,
                       l.record_id, l.ip_address, l.user_agent, l.created_at
                FROM system_logs l
                LEFT JOIN admin a ON a.admin_id = l.admin_id
                ORDER BY l.log_id
            ",
        ],
    ];

    public function __construct(private PDO $pdo)
    {
    }

    public function availableDatasets(): array
    {
        return array_map(
            static fn (string $key, array $dataset): array => [
                'key' => $key,
                'label' => $dataset['label'],
            ],
            array_keys(self::DATASETS),
            array_values(self::DATASETS)
        );
    }

    public function columns(string $dataset): array
    {
        if (!isset(self::DATASETS[$dataset])) {
            throw new RuntimeException('The selected data set is not supported.');
        }

        return self::DATASETS[$dataset]['columns'];
    }

    public function streamRows(string $dataset, callable $writeRow): void
    {
        if (!isset(self::DATASETS[$dataset])) {
            throw new RuntimeException('The selected data set is not supported.');
        }

        $stmt = $this->pdo->query(self::DATASETS[$dataset]['sql']);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $writeRow($row);
        }
    }

    public function label(string $dataset): string
    {
        return self::DATASETS[$dataset]['label']
            ?? throw new RuntimeException('The selected data set is not supported.');
    }
}
