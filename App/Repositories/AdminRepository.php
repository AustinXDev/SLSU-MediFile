<?php

namespace App\Repositories;

use App\Models\Admin;
use PDO;

class AdminRepository
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    /**
     * Gets the list of supported roles.
     */
    public function getSupportedRoles(): array
    {
        return $this->getEnumValues('role');
    }


    /**
     * Gets the list of supported statuses.
     */
    public function getSupportedStatuses(): array
    {
        return $this->getEnumValues('status');
    }


    /**
     * Lists user accounts with optional filtering and pagination.
     */
    public function listAccounts(
        string $search = '',
        string $role = '',
        string $status = '',
        int $limit = 10,
        int $offset = 0
    ): array {
        $sql = "
            SELECT admin_id, username, email, first_name, last_name, role, status,
                   created_at, updated_at
            FROM admin
            WHERE 1 = 1";

        $params = [];

        if ($search !== '') {
            $sql .= " AND (
                username LIKE ?
                OR email LIKE ?
                OR CONCAT(first_name, ' ', last_name) LIKE ?
                OR CONCAT(last_name, ' ', first_name) LIKE ?
            )";
            $like = '%' . $search . '%';
            $params = array_merge($params, [$like, $like, $like, $like]);
        }

        if ($role !== '') {
            $sql .= " AND role = ?";
            $params[] = $role;
        }

        if ($status !== '') {
            $sql .= " AND status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        return array_map(static fn (array $row): array => Admin::fromArray($row)->toArray(), $rows);
    }


    /**
     * Counts the total number of user accounts with optional filtering.
     */
    public function countAccounts(
        string $search = '',
        string $role = '',
        string $status = ''
    ): int {
        $sql = "SELECT COUNT(*) FROM admin WHERE 1 = 1";
        $params = [];

        if ($search !== '') {
            $sql .= " AND (
                username LIKE ?
                OR email LIKE ?
                OR CONCAT(first_name, ' ', last_name) LIKE ?
                OR CONCAT(last_name, ' ', first_name) LIKE ?
            )";
            $like = '%' . $search . '%';
            $params = array_merge($params, [$like, $like, $like, $like]);
        }

        if ($role !== '') {
            $sql .= " AND role = ?";
            $params[] = $role;
        }

        if ($status !== '') {
            $sql .= " AND status = ?";
            $params[] = $status;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }


    /**
     * Find use by username
     */
    public function findByUsername(
        string $username
    ): ?Admin {
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM admin
            WHERE username = ?
            LIMIT 1
        ");

        $stmt->execute([$username]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row
            ? Admin::fromArray($row)
            : null;
    }


    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?Admin
    {
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM admin
            WHERE email = ?
            LIMIT 1
        ");

        $stmt->execute([$email]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? Admin::fromArray($row) : null;
    }


    /**
     * Find user by ID
     */
    public function findById(
        int $adminId
    ): ?Admin {
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM admin
            WHERE admin_id = ?
            LIMIT 1
        ");

        $stmt->execute([$adminId]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row
            ? Admin::fromArray($row)
            : null;
    }


    /**
     * Check if the user exist
     */
    public function usernameExists(string $username, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM admin WHERE username = ?";
        $params = [$username];

        if ($excludeId) {
            $sql .= " AND admin_id != ?";
            $params[] = $excludeId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn() > 0;
    }


    /**
     * Check if the emailExists
     */
    public function emailExists(string $email, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM admin WHERE email = ?";
        $params = [$email];

        if ($excludeId) {
            $sql .= " AND admin_id != ?";
            $params[] = $excludeId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn() > 0;
    }


    /**
     * Create new user
     */
    public function create(array $data): int
    {
        $sql = "
            INSERT INTO admin (
                first_name,
                last_name,
                username,
                email,
                password_hash,
                role,
                status,
                created_at,
                updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ";

        $stmt = $this->pdo->prepare($sql);
        $executed = $stmt->execute([
            $data['first_name'] ?? null,
            $data['last_name'] ?? null,
            $data['username'] ?? null,
            $data['email'] ?? null,
            $data['password_hash'] ?? null,
            $data['role'] ?? null,
            $data['status'] ?? 'Active',
        ]);

        return $executed ? (int) $this->pdo->lastInsertId() : 0;
    }


    /**
     * update current user
     */
    public function update(int $adminId, array $data): bool
    {
        $params = [
            $data['first_name'] ?? null,
            $data['last_name'] ?? null,
            $data['username'] ?? null,
            $data['email'] ?? null,
            $data['role'] ?? null,
            $data['status'] ?? 'Active',
        ];

        $sql = "
            UPDATE admin SET
                first_name = ?,
                last_name = ?,
                username = ?,
                email = ?,
                role = ?,
                status = ?,
                updated_at = NOW()";

        if (isset($data['password_hash'])) {
            $sql .= ", password_hash = ?";
            $params[] = $data['password_hash'];
        }

        $sql .= " WHERE admin_id = ?";
        $params[] = $adminId;

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute($params);
    }


    /**
     * Delete user
     *
     */
    public function deleteUser(int $adminId): bool
    {
        $sql = "
            DELETE FROM admin
            WHERE admin_id = ?
        ";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([$adminId]);
    }


    /**
     * Delete method
     */
    public function Delete(int $adminId): bool
    {
        return $this->deleteUser($adminId);
    }


    /**
     * Get all Enum Values
     */
    private function getEnumValues(string $column): array
    {
        $stmt = $this->pdo->query("SHOW COLUMNS FROM admin LIKE " . $this->pdo->quote($column));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row || empty($row['Type'])) {
            return [];
        }

        if (!preg_match('/^enum\((.*)\)$/i', $row['Type'], $matches)) {
            return [];
        }

        return array_map(
            static fn (string $value): string => trim($value, "'"),
            explode(',', $matches[1])
        );
    }
}
