<?php 

namespace App\Models;

class Admin {

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
  )
  {
  }

  public static function fromArray(
    array $data
  ): self {

    return new self(
      (int) $data['admin_id'],
      $data['username'],
      $data['email'],
      $data['first_name'],
      $data['last_name'],
      $data['role'],
      $data['password_hash'],
      $data['status'],
      $data['last_login_at'],
      $data['created_at'],
      $data['updated_at']
    );

  }

}

?>