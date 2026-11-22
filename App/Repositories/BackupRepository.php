<?php

namespace App\Repositories;

use PDO;

class BackupRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function create(string $filename, int $sizeBytes, int $adminId): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO backup_archives (filename, size_bytes, created_by)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$filename, $sizeBytes, $adminId]);

        return (int) $this->pdo->lastInsertId();
    }

    public function list(): array
    {
        $stmt = $this->pdo->query("
            SELECT b.backup_id, b.filename, b.size_bytes, b.backup_type, b.status,
                   b.created_at, a.username AS created_by
            FROM backup_archives b
            LEFT JOIN admin a ON a.admin_id = b.created_by
            ORDER BY b.created_at DESC, b.backup_id DESC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function findById(int $backupId): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT backup_id, filename, size_bytes, backup_type, status, created_at, created_by
            FROM backup_archives
            WHERE backup_id = ?
            LIMIT 1
        ");
        $stmt->execute([$backupId]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function delete(int $backupId): bool
    {
        $stmt = $this->pdo->prepare("
            DELETE FROM backup_archives
            WHERE backup_id = ?
        ");
        $stmt->execute([$backupId]);

        return $stmt->rowCount() === 1;
    }
}
