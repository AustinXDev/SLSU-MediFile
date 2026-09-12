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

}