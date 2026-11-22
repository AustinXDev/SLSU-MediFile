<?php

namespace App\Repositories;

use PDO;

class SettingsRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function getProfile(int $adminId): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT admin_id, first_name, last_name, username, email, role, status, last_login_at
            FROM admin
            WHERE admin_id = ?
            LIMIT 1
        ");
        $stmt->execute([$adminId]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function updateProfile(
        int $adminId,
        string $firstName,
        string $lastName,
        string $username,
        string $email
    ): bool {
        $stmt = $this->pdo->prepare("
            UPDATE admin
            SET first_name = ?, last_name = ?, username = ?, email = ?, updated_at = NOW()
            WHERE admin_id = ?
        ");

        return $stmt->execute([$firstName, $lastName, $username, $email, $adminId]);
    }

    public function updatePassword(int $adminId, string $passwordHash): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE admin
            SET password_hash = ?, updated_at = NOW()
            WHERE admin_id = ?
        ");

        return $stmt->execute([$passwordHash, $adminId]);
    }

    public function getSystemSettings(): array
    {
        $stmt = $this->pdo->query("
            SELECT setting_key, setting_value
            FROM system_settings
        ");

        $settings = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        return $settings;
    }

    public function saveSystemSettings(array $settings, int $adminId): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO system_settings (setting_key, setting_value, updated_by)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE
                setting_value = VALUES(setting_value),
                updated_by = VALUES(updated_by)
        ");

        foreach ($settings as $key => $value) {
            $stmt->execute([$key, $value, $adminId]);
        }
    }
}
