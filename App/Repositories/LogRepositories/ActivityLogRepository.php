<?php

namespace App\Repositories\LogRepositories;

use PDO;

class ActivityLogRepository
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    public function getActivityLogs(array $filters): array
    {
        [$where, $values] = $this->buildWhere($filters);
        $total = $this->countLogs($where, $values);
        $totalPages = max(1, (int) ceil($total / $filters['limit']));
        $page = min($filters['page'], $totalPages);
        $offset = ($page - 1) * $filters['limit'];

        $sql = "
            SELECT
                l.log_id AS id,
                l.admin_id AS userId,
                l.action,
                l.module,
                l.description,
                l.record_id AS recordId,
                l.created_at AS createdAt,
                COALESCE(
                    NULLIF(
                        TRIM(CONCAT_WS(
                            ' ',
                            NULLIF(TRIM(a.first_name), ''),
                            NULLIF(TRIM(a.last_name), '')
                        )),
                        ''
                    ),
                    a.username,
                    'Unknown user'
                ) AS userName,
                a.username,
                a.role AS userRole
            FROM system_logs l
            LEFT JOIN admin a ON a.admin_id = l.admin_id
            {$where}
            ORDER BY l.created_at DESC, l.log_id DESC
            LIMIT ? OFFSET ?
        ";

        $stmt = $this->pdo->prepare($sql);
        $this->bindValues($stmt, $values);
        $stmt->bindValue(count($values) + 1, $filters['limit'], PDO::PARAM_INT);
        $stmt->bindValue(count($values) + 2, $offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'records' => $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [],
            'pagination' => [
                'page' => $page,
                'limit' => $filters['limit'],
                'total' => $total,
                'totalPages' => $totalPages,
            ],
        ];
    }

    public function getRecentPatientActivity(array $filters): array
    {
        $filters['module'] = 'patients';
        [$where, $values] = $this->buildWhere($filters, true);
        $total = $this->countLogs($where, $values, true);
        $totalPages = max(1, (int) ceil($total / $filters['limit']));
        $page = min($filters['page'], $totalPages);
        $offset = ($page - 1) * $filters['limit'];

        $sql = "
            SELECT
                l.log_id AS id,
                l.action,
                l.description,
                l.record_id AS patientId,
                l.created_at AS createdAt,
                p.surname,
                p.firstname,
                p.middlename,
                p.student_id AS studentId,
                COALESCE(
                    NULLIF(
                        TRIM(CONCAT_WS(
                            ' ',
                            NULLIF(TRIM(a.first_name), ''),
                            NULLIF(TRIM(a.last_name), '')
                        )),
                        ''
                    ),
                    a.username,
                    'Unknown user'
                ) AS userName
            FROM system_logs l
            INNER JOIN patients p ON p.patient_id = l.record_id
            LEFT JOIN admin a ON a.admin_id = l.admin_id
            {$where}
            ORDER BY l.created_at DESC, l.log_id DESC
            LIMIT ? OFFSET ?
        ";

        $stmt = $this->pdo->prepare($sql);
        $this->bindValues($stmt, $values);
        $stmt->bindValue(count($values) + 1, $filters['limit'], PDO::PARAM_INT);
        $stmt->bindValue(count($values) + 2, $offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'records' => $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [],
            'pagination' => [
                'page' => $page,
                'limit' => $filters['limit'],
                'total' => $total,
                'totalPages' => $totalPages,
            ],
        ];
    }

    public function getSummary(array $filters): array
    {
        [$where, $values] = $this->buildWhere($filters);

        $sql = "
            SELECT
                COUNT(*) AS total,
                SUM(
                    CASE
                        WHEN l.created_at >= CURDATE()
                         AND l.created_at < DATE_ADD(CURDATE(), INTERVAL 1 DAY)
                        THEN 1 ELSE 0
                    END
                ) AS today,
                COUNT(DISTINCT l.admin_id) AS activeUsers,
                MAX(l.created_at) AS recentActivity
            FROM system_logs l
            LEFT JOIN admin a ON a.admin_id = l.admin_id
            {$where}
        ";

        $stmt = $this->pdo->prepare($sql);
        $this->bindValues($stmt, $values);
        $stmt->execute();
        $summary = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        return [
            'total' => (int) ($summary['total'] ?? 0),
            'today' => (int) ($summary['today'] ?? 0),
            'activeUsers' => (int) ($summary['activeUsers'] ?? 0),
            'recentActivity' => $summary['recentActivity'] ?? null,
        ];
    }

    public function getById(int $logId): ?array
    {
        $sql = "
            SELECT
                l.log_id AS id,
                l.admin_id AS userId,
                l.action,
                l.module,
                l.description,
                l.record_id AS recordId,
                l.ip_address AS ipAddress,
                l.created_at AS createdAt,
                COALESCE(
                    NULLIF(
                        TRIM(CONCAT_WS(
                            ' ',
                            NULLIF(TRIM(a.first_name), ''),
                            NULLIF(TRIM(a.last_name), '')
                        )),
                        ''
                    ),
                    a.username,
                    'Unknown user'
                ) AS userName,
                a.username,
                a.role AS userRole
            FROM system_logs l
            LEFT JOIN admin a ON a.admin_id = l.admin_id
            WHERE l.log_id = ?
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$logId]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        return $record ?: null;
    }

    public function getFilterOptions(): array
    {
        $actions = $this->pdo->query(
            'SELECT DISTINCT action FROM system_logs WHERE action IS NOT NULL AND action <> \'\' ORDER BY action'
        )->fetchAll(PDO::FETCH_COLUMN);

        $modules = $this->pdo->query(
            'SELECT DISTINCT module FROM system_logs WHERE module IS NOT NULL AND module <> \'\' ORDER BY module'
        )->fetchAll(PDO::FETCH_COLUMN);

        return [
            'actions' => $actions ?: [],
            'modules' => $modules ?: [],
        ];
    }

    private function countLogs(string $where, array $values, bool $patientsOnly = false): int
    {
        $join = $patientsOnly ? 'INNER JOIN patients p ON p.patient_id = l.record_id' : '';
        $sql = "
            SELECT COUNT(*)
            FROM system_logs l
            LEFT JOIN admin a ON a.admin_id = l.admin_id
            {$join}
            {$where}
        ";

        $stmt = $this->pdo->prepare($sql);
        $this->bindValues($stmt, $values);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    private function buildWhere(array $filters, bool $patientsOnly = false): array
    {
        $conditions = [];
        $values = [];

        if ($patientsOnly) {
            $conditions[] = 'l.module = ?';
            $values[] = 'patients';
        }

        if ($filters['search'] !== '') {
            $search = '%' . $filters['search'] . '%';
            $conditions[] = "(
                l.action LIKE ?
                OR l.module LIKE ?
                OR l.description LIKE ?
                OR CAST(l.record_id AS CHAR) LIKE ?
                OR a.username LIKE ?
                OR CONCAT_WS(' ', a.first_name, a.last_name) LIKE ?
            )";
            array_push(
                $values,
                $search,
                $search,
                $search,
                $search,
                $search,
                $search
            );

            if ($patientsOnly) {
                $conditions[count($conditions) - 1] = "(
                    l.action LIKE ?
                    OR l.module LIKE ?
                    OR l.description LIKE ?
                    OR CAST(l.record_id AS CHAR) LIKE ?
                    OR a.username LIKE ?
                    OR CONCAT_WS(' ', a.first_name, a.last_name) LIKE ?
                    OR p.surname LIKE ?
                    OR p.firstname LIKE ?
                    OR p.middlename LIKE ?
                )";
                array_push($values, $search, $search, $search);
            }
        }

        if ($filters['action'] !== '') {
            $conditions[] = 'l.action = ?';
            $values[] = $filters['action'];
        }

        if ($filters['module'] !== '') {
            $conditions[] = 'l.module = ?';
            $values[] = $filters['module'];
        }

        if ($filters['dateFrom'] !== '') {
            $conditions[] = 'l.created_at >= ?';
            $values[] = $filters['dateFrom'];
        }

        if ($filters['dateTo'] !== '') {
            $conditions[] = 'l.created_at < DATE_ADD(?, INTERVAL 1 DAY)';
            $values[] = $filters['dateTo'];
        }

        return [
            $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '',
            $values,
        ];
    }

    private function bindValues(\PDOStatement $stmt, array $values): void
    {
        foreach ($values as $index => $value) {
            $stmt->bindValue($index + 1, $value);
        }
    }
}
