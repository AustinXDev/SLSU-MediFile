<?php

namespace App\Controllers;

use App\Services\Settings\SettingsService;

class SettingsController
{
    public function __construct(private SettingsService $service)
    {
    }

    public function getProfile(): array
    {
        return $this->service->profile();
    }

    public function updateProfile(array $input): array
    {
        return $this->service->updateProfile($input);
    }

    public function changePassword(array $input): void
    {
        $this->service->changePassword($input);
    }

    public function getSystemSettings(): array
    {
        return $this->service->systemSettings();
    }

    public function updateSystemSettings(array $input): array
    {
        return $this->service->updateSystemSettings($input);
    }

    public function updateSchoolLogo(array $file): array
    {
        return $this->service->updateSchoolLogo($file);
    }
}
