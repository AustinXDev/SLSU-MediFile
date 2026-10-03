<?php

namespace App\Controllers;

use App\Services\Auth\PasswordReset\PasswordResetService;

class PasswordResetController
{
    public function __construct(
        private PasswordResetService $service
    ) {
    }

    public function request(string $email): void
    {
        $this->service->requestReset($email);
    }

    public function validate(string $token): bool
    {
        return $this->service->validateToken($token);
    }

    public function reset(string $token, string $password): void
    {
        $this->service->resetPassword($token, $password);
    }
}
