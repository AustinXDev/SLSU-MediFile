<?php

namespace App\Repositories\DashboardRepositories;

use PDO;
use App\Helpers\Dashboard\DashboardHelper;

class DashboardRepository
{
    protected PDO $pdo;
    protected DashboardHelper $dashboardHelper;

    public function __construct(
        PDO $pdo,
        DashboardHelper $dashboardHelper
    ) {
        $this->pdo = $pdo;
        $this->dashboardHelper = $dashboardHelper;

    }

    public function getDashboard(): array
    {
        return [
          'kpis' => $this->getKpis(),
          'patientActivity' => $this->getPatientActivity()
        ];
    }

    public function getKpis(): array
    {
        return [
            'totalPatients' => $this->getTotalPatients(),
            'totalUsers' => $this->getTotalUsers(),
            'activePatients' => $this->getActivePatients(),
            'totalDentalServices' => $this->getTotalDentalServices(),
            'todayPatientRecords' => $this->getTodayPatientRecords(),
        ];
    }

    public function getPatientActivity(): array
    {
        return [
          "weekly" => $this->getWeeklyPatientActivity(),
          "monthly" => $this->getMonthlyPatientActivity(),
          "yearly" => $this->getYearlyPatientActivity()
        ];
    }

    public function getMedicalRecordsOverview(): array
    {
        return [
         'newThisMonth' => $this->getNewRecordsThisMonth(),
         'updatedThisMonth' => $this->getUpdatedRecordsThisMonth(),
         'medicalExaminations' => $this->getMedicalExaminationCount(),
         'dentalRecords' => $this->getDentalRecordCount(),
         'totalRecords' => $this->getTotalMedicalRecords(),
        ];
    }


    public function getRecentPatients(int $limit = 5): array
    {
        $limit = max(1, min($limit, 10));

        $sql = "
        SELECT
            patient_id,
            surname,
            firstname,
            middlename,
            college_dept,
            created_at,
            is_active
        FROM patients
        ORDER BY created_at DESC
        LIMIT {$limit}
    ";

        $stmt = $this->pdo->query($sql);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Total patients.
     *
     * The monthly comparison represents
     * new patient registrations this month
     * versus last month.
     */
    private function getTotalPatients(): array
    {
        $stmt = $this->pdo->query("
            SELECT
                COUNT(*) AS total,

                SUM(
                    created_at >= DATE_FORMAT(
                        CURDATE(),
                        '%Y-%m-01'
                    )
                    AND created_at < DATE_FORMAT(
                        DATE_ADD(CURDATE(), INTERVAL 1 MONTH),
                        '%Y-%m-01'
                    )
                ) AS current_month,

                SUM(
                    created_at >= DATE_FORMAT(
                        DATE_SUB(CURDATE(), INTERVAL 1 MONTH),
                        '%Y-%m-01'
                    )
                    AND created_at < DATE_FORMAT(
                        CURDATE(),
                        '%Y-%m-01'
                    )
                ) AS previous_month

            FROM patients
        ");

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        $total = (int) ($data['total'] ?? 0);
        $currentMonth = (int) ($data['current_month'] ?? 0);
        $previousMonth = (int) ($data['previous_month'] ?? 0);

        return $this->dashboardHelper->buildKpi(
            $total,
            $currentMonth,
            $previousMonth
        );
    }


    /**
     * Total users.
     *
     * The monthly comparison represents
     * users created this month
     * versus last month.
     */
    private function getTotalUsers(): array
    {
        $stmt = $this->pdo->query("
            SELECT
                COUNT(*) AS total,

                SUM(
                    created_at >= DATE_FORMAT(
                        CURDATE(),
                        '%Y-%m-01'
                    )
                    AND created_at < DATE_FORMAT(
                        DATE_ADD(CURDATE(), INTERVAL 1 MONTH),
                        '%Y-%m-01'
                    )
                ) AS current_month,

                SUM(
                    created_at >= DATE_FORMAT(
                        DATE_SUB(CURDATE(), INTERVAL 1 MONTH),
                        '%Y-%m-01'
                    )
                    AND created_at < DATE_FORMAT(
                        CURDATE(),
                        '%Y-%m-01'
                    )
                ) AS previous_month

            FROM admin
        ");

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        $total = (int) ($data['total'] ?? 0);
        $currentMonth = (int) ($data['current_month'] ?? 0);
        $previousMonth = (int) ($data['previous_month'] ?? 0);

        return $this->dashboardHelper->buildKpi(
            $total,
            $currentMonth,
            $previousMonth
        );
    }


    /**
     * Total active patients.
     *
     * The monthly comparison represents
     * active patients registered this month
     * versus last month.
     */
    private function getActivePatients(): array
    {
        $stmt = $this->pdo->query("
            SELECT
                COUNT(*) AS total,

                SUM(
                    is_active = 1
                    AND created_at >= DATE_FORMAT(
                        CURDATE(),
                        '%Y-%m-01'
                    )
                    AND created_at < DATE_FORMAT(
                        DATE_ADD(CURDATE(), INTERVAL 1 MONTH),
                        '%Y-%m-01'
                    )
                ) AS current_month,

                SUM(
                    is_active = 1
                    AND created_at >= DATE_FORMAT(
                        DATE_SUB(CURDATE(), INTERVAL 1 MONTH),
                        '%Y-%m-01'
                    )
                    AND created_at < DATE_FORMAT(
                        CURDATE(),
                        '%Y-%m-01'
                    )
                ) AS previous_month

            FROM patients

            WHERE is_active = 1
        ");

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        $total = (int) ($data['total'] ?? 0);
        $currentMonth = (int) ($data['current_month'] ?? 0);
        $previousMonth = (int) ($data['previous_month'] ?? 0);

        return $this->dashboardHelper->buildKpi(
            $total,
            $currentMonth,
            $previousMonth
        );
    }

    /**
     * Total dental services.
     *
     * The monthly comparison represents
     * services recorded this month
     * versus last month.
     */
    private function getTotalDentalServices(): array
    {
        $stmt = $this->pdo->query("
            SELECT
                COUNT(*) AS total,

                SUM(
                    service_date >= DATE_FORMAT(
                        CURDATE(),
                        '%Y-%m-01'
                    )
                    AND service_date < DATE_FORMAT(
                        DATE_ADD(CURDATE(), INTERVAL 1 MONTH),
                        '%Y-%m-01'
                    )
                ) AS current_month,

                SUM(
                    service_date >= DATE_FORMAT(
                        DATE_SUB(CURDATE(), INTERVAL 1 MONTH),
                        '%Y-%m-01'
                    )
                    AND service_date < DATE_FORMAT(
                        CURDATE(),
                        '%Y-%m-01'
                    )
                ) AS previous_month

            FROM patient_dental_services
        ");

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        $total = (int) ($data['total'] ?? 0);
        $currentMonth = (int) ($data['current_month'] ?? 0);
        $previousMonth = (int) ($data['previous_month'] ?? 0);

        return $this->dashboardHelper->buildKpi(
            $total,
            $currentMonth,
            $previousMonth
        );
    }

    /**
     * Patient recorded this day.
     *
     * The comparison is still this day
     */
    private function getTodayPatientRecords(): array
    {
        $stmt = $this->pdo->query("
        SELECT
            SUM(
                created_at >= CURDATE()
                AND created_at < DATE_ADD(CURDATE(), INTERVAL 1 DAY)
            ) AS today,

            SUM(
                created_at >= DATE_SUB(CURDATE(), INTERVAL 1 DAY)
                AND created_at < CURDATE()
            ) AS yesterday

        FROM patients
    ");

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        $today = (int) ($data['today'] ?? 0);
        $yesterday = (int) ($data['yesterday'] ?? 0);

        return $this->dashboardHelper->buildKpi(
            $today,
            $today,
            $yesterday
        );
    }


    /**
     * Get weekly patient record
     */
    private function getWeeklyPatientActivity(): array
    {
        $stmt = $this->pdo->query("
            SELECT
                DAYOFWEEK(created_at) AS day_number,
                COUNT(*) AS total
            FROM patients
            WHERE created_at >= DATE_SUB(
                CURDATE(),
                INTERVAL WEEKDAY(CURDATE()) DAY
            )
            AND created_at < DATE_ADD(
                DATE_SUB(
                    CURDATE(),
                    INTERVAL WEEKDAY(CURDATE()) DAY
                ),
                INTERVAL 7 DAY
            )
            GROUP BY DAYOFWEEK(created_at)
            ORDER BY day_number
        ");

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $days = [
            'Mon' => 0,
            'Tue' => 0,
            'Wed' => 0,
            'Thu' => 0,
            'Fri' => 0,
            'Sat' => 0,
            'Sun' => 0,
        ];

        foreach ($rows as $row) {
            $dayNumber = (int) $row['day_number'];

            $map = [
                2 => 'Mon',
                3 => 'Tue',
                4 => 'Wed',
                5 => 'Thu',
                6 => 'Fri',
                7 => 'Sat',
                1 => 'Sun',
            ];

            if (isset($map[$dayNumber])) {
                $days[$map[$dayNumber]] = (int) $row['total'];
            }
        }

        return $days;
    }


    /**
     * Get monthly patient records
     */
    private function getMonthlyPatientActivity(): array
    {
        $stmt = $this->pdo->query("
        SELECT
            MONTH(created_at) AS month,
            COUNT(*) AS total
        FROM patients
        WHERE created_at >= DATE_FORMAT(
            CURDATE(),
            '%Y-01-01'
        )
        AND created_at < DATE_ADD(
            DATE_FORMAT(CURDATE(), '%Y-01-01'),
            INTERVAL 1 YEAR
        )
        GROUP BY MONTH(created_at)
        ORDER BY month
    ");

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $months = [
            'Jan' => 0,
            'Feb' => 0,
            'Mar' => 0,
            'Apr' => 0,
            'May' => 0,
            'June' => 0,
            'July' => 0,
            'Aug' => 0,
            'Sept' => 0,
            'Oct' => 0,
            'Nov' => 0,
            'Dec' => 0,
        ];

        $monthNames = array_keys($months);

        foreach ($rows as $row) {
            $monthNumber = (int) $row['month'];

            $months[$monthNames[$monthNumber - 1]] =
                (int) $row['total'];
        }

        return $months;
    }


    /**
     * Get yearly patient records
     */
    private function getYearlyPatientActivity(): array
    {
        $stmt = $this->pdo->query("
        SELECT
            YEAR(created_at) AS year,
            COUNT(*) AS total
        FROM patients
        WHERE created_at >= DATE_SUB(
            DATE_FORMAT(CURDATE(), '%Y-01-01'),
            INTERVAL 4 YEAR
        )
        AND created_at < DATE_ADD(
            DATE_FORMAT(CURDATE(), '%Y-01-01'),
            INTERVAL 1 YEAR
        )
        GROUP BY YEAR(created_at)
        ORDER BY year ASC
    ");

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $currentYear = (int) date('Y');

        $years = [];

        // Last 5 years
        for ($year = $currentYear - 4; $year <= $currentYear; $year++) {
            $years[(string) $year] = 0;
        }

        foreach ($rows as $row) {
            $year = (string) $row['year'];

            if (isset($years[$year])) {
                $years[$year] = (int) $row['total'];
            }
        }

        return $years;
    }


    /**
     * Get the total new record this month
     */
    private function getNewRecordsThisMonth(): int
    {
        $stmt = $this->pdo->query("
          SELECT COUNT(*)
          FROM patients
          WHERE created_at >= DATE_FORMAT(
              CURDATE(),
              '%Y-%m-01'
          )
          AND created_at < DATE_ADD(
              DATE_FORMAT(CURDATE(), '%Y-%m-01'),
              INTERVAL 1 MONTH
          )
        ");

        return (int) $stmt->fetchColumn();
    }

    /**
     * Get total updated record this month
     */
    private function getUpdatedRecordsThisMonth(): int
    {
        $stmt = $this->pdo->query("
        SELECT COUNT(*)
        FROM patients
        WHERE updated_at >= DATE_FORMAT(
            CURDATE(),
            '%Y-%m-01'
        )
        AND updated_at < DATE_ADD(
            DATE_FORMAT(CURDATE(), '%Y-%m-01'),
            INTERVAL 1 MONTH
        )
    ");

        return (int) $stmt->fetchColumn();
    }


    /**
     * Get total medical examination count
     */
    private function getMedicalExaminationCount(): int
    {
        $stmt = $this->pdo->query("
        SELECT COUNT(*)
        FROM patient_medical_examinations
    ");

        return (int) $stmt->fetchColumn();
    }


    /**
     * Get total dental record
     */
    private function getDentalRecordCount(): int
    {
        $stmt = $this->pdo->query("
        SELECT COUNT(*)
        FROM patient_dental_records
    ");

        return (int) $stmt->fetchColumn();
    }


    /**
     * Get total medical records
     */
    private function getTotalMedicalRecords(): int
    {
        $stmt = $this->pdo->query("
        SELECT COUNT(*)
        FROM patients
        WHERE is_active = 1
    ");

        return (int) $stmt->fetchColumn();
    }


}
