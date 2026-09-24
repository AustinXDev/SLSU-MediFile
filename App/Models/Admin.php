<?php

namespace App\Models;

class Admin
{
    public function __construct(
        public int $adminId,
        public string $username,
        public string $email,
        public string $firstName,
        public string $lastName,
        public string $role,
        public string $passwordHash,
        public string $status = 'Active',
        public ?string $lastLoginAt = null,
        public ?string $createdAt = null,
        public ?string $updatedAt = null
    ) {
    }


    public static function fromArray(array $data): self
    {
        return new self(
            adminId:      (int) ($data['admin_id'] ?? 0),
            username:     (string) ($data['username'] ?? ''),
            email:        (string) ($data['email'] ?? ''),
            firstName:    (string) ($data['first_name'] ?? ''),
            lastName:     (string) ($data['last_name'] ?? ''),
            role:         (string) ($data['role'] ?? ''),
            passwordHash: (string) ($data['password_hash'] ?? ''),
            status:       (string) ($data['status'] ?? 'Active'),
            lastLoginAt:  $data['last_login_at'] ?? null,
            createdAt:    $data['created_at'] ?? null,
            updatedAt:    $data['updated_at'] ?? null
        );
    }


    public function toArray(bool $includeSensitive = false): array
    {
        $data = [
            'admin_id'      => $this->adminId,
            'username'      => $this->username,
            'email'         => $this->email,
            'first_name'    => $this->firstName,
            'last_name'     => $this->lastName,
            'full_name'     => $this->firstName . ' ' . $this->lastName,
            'role'          => $this->role,
            'status'        => $this->status,
            'last_login_at' => $this->lastLoginAt,
            'created_at'    => $this->createdAt,
            'updated_at'    => $this->updatedAt,
        ];

        if ($includeSensitive) {
            $data['password_hash'] = $this->passwordHash;
        }

        return $data;
    }
}
