<?php

namespace App\Repositories\PatientRepositories;

use PDO;

class PhysicalExaminationRepository
{
    protected PDO $pdo;

    public function __construct(
        PDO $pdo
    ) {
        $this->pdo = $pdo;
    }

    /**
     * Create a new physical examination
     */
    public function create(
        int $patientId,
        array $exam,
        array $histories
    ): int {

        $pastHistory   = !empty($histories['conditions']) ? json_encode($histories['conditions']) : null;
        $socialHistory = !empty($histories['socialHistory']['habits']) ? json_encode($histories['socialHistory']['habits']) : null;

        $sql = "INSERT INTO patient_medical_examinations (
                    patient_id, 
                    past_history, 
                    previous_hospitalization, 
                    previous_operation, 
                    previous_trauma, 
                    social_history, 
                    sports_specification,
                    blood_pressure, 
                    temperature, 
                    pulse_rate, 
                    respiratory_rate, 
                    height_cm, 
                    weight_kg, 
                    ideal_body_weight_kg, 
                    head_neck, 
                    respiratory, 
                    cardiovascular, 
                    gastrointestinal, 
                    genitourinary, 
                    extremities, 
                    neurologic, 
                    suggestions_treatment, 
                    laboratory_results
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $patientId,
            $pastHistory,
            $histories['previousHospitalization'] ?? null,
            $histories['previousOperation'] ?? null,
            $histories['previousTrauma'] ?? null,
            $socialHistory,
            $histories['socialHistory']['sportsDefinition'] ?? null,
            $exam['bloodPressure'] ?? null,
            $exam['temperature'] ?? null,
            $exam['pulseRate'] ?? null,
            $exam['respRate'] ?? null,
            $exam['height'] ?? null,
            $exam['weight'] ?? null,
            $exam['idealWeight'] ?? null,
            $exam['headNeck'] ?? null,
            $exam['respiratory'] ?? null,
            $exam['cardioVascular'] ?? null,
            $exam['gastroIntestinal'] ?? null,
            $exam['genitoUrinary'] ?? null,
            $exam['extremities'] ?? null,
            $exam['neurologic'] ?? null,
            $exam['suggestion'] ?? null,
            $exam['laboratory'] ?? null
        ]);

        return (int) $this->pdo->lastInsertId();

    }


    /**
     * Update an existing physical examination entry by primary key `id`
     */
    public function update(
        int $examinationId,
        array $exam,
        array $histories
    ): bool {

        $pastHistory   = !empty($histories['conditions']) ? json_encode($histories['conditions']) : null;
        $socialHistory = !empty($histories['socialHistory']['habits']) ? json_encode($histories['socialHistory']['habits']) : null;

        $sql = "UPDATE patient_medical_examinations
                  SET
                    past_history             = ?,
                    previous_hospitalization = ?,
                    previous_operation       = ?,
                    previous_trauma          = ?,
                    social_history           = ?,
                    sports_specification     = ?,
                    blood_pressure           = ?,
                    temperature              = ?,
                    pulse_rate               = ?,
                    respiratory_rate         = ?,
                    height_cm                = ?,
                    weight_kg                = ?,
                    ideal_body_weight_kg     = ?,
                    head_neck                = ?,
                    respiratory              = ?,
                    cardiovascular           = ?,
                    gastrointestinal         = ?,
                    genitourinary            = ?,
                    extremities              = ?,
                    neurologic               = ?,
                    suggestions_treatment    = ?,
                    laboratory_results       = ?
                WHERE id = ?";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $pastHistory,
            $histories['previousHospitalization'] ?? null,
            $histories['previousOperation'] ?? null,
            $histories['previousTrauma'] ?? null,
            $socialHistory,
            $histories['socialHistory']['sportsDefinition'] ?? null,
            $exam['bloodPressure'] ?? null,
            $exam['temperature'] ?? null,
            $exam['pulseRate'] ?? null,
            $exam['respRate'] ?? null,
            $exam['height'] ?? null,
            $exam['weight'] ?? null,
            $exam['idealWeight'] ?? null,
            $exam['headNeck'] ?? null,
            $exam['respiratory'] ?? null,
            $exam['cardioVascular'] ?? null,
            $exam['gastroIntestinal'] ?? null,
            $exam['genitoUrinary'] ?? null,
            $exam['extremities'] ?? null,
            $exam['neurologic'] ?? null,
            $exam['suggestion'] ?? null,
            $exam['laboratory'] ?? null,
            $examinationId
        ]);
    }


}
