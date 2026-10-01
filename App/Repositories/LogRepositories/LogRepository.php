<?php

namespace App\Repositories\LogRepositories;

use PDO;

class LogRepository
{
    protected PDO $pdo;

    public function __construct(
        PDO $pdo
    ) {
        $this->pdo = $pdo;
    }

    public function create(
        int $adminId,
        string $action,
        string $description,
        string $module,
        int $recordId,
        string $ipAddress,
        string $userAgent
    ): int {

        $sql = "
        INSERT INTO system_logs (
            admin_id,
            action,
            description,
            module,
            record_id,
            ip_address,
            user_agent
        )
        VALUES (?, ?, ?, ?, ?, ?, ?)
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            $adminId,
            $action,
            $description,
            $module,
            $recordId,
            $ipAddress,
            $userAgent
        ]);

        return (int) $this->pdo->lastInsertId();

    }


    public function getRecent(int $limit = 10): array
    {
        $limit = max(1, min($limit, 100));

        $sql = "
        SELECT
            l.log_id,
            l.admin_id,
            l.action,
            l.description,
            l.module,
            l.record_id,
            l.ip_address,
            l.user_agent,
            l.created_at,
            a.username
        FROM system_logs l
        LEFT JOIN admin a
            ON a.admin_id = l.admin_id
        ORDER BY l.created_at DESC
        LIMIT {$limit}
    ";

        $stmt = $this->pdo->query($sql);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

}
