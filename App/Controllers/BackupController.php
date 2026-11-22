<?php

namespace App\Controllers;

use App\Services\Settings\BackupService;

class BackupController
{
    public function __construct(private BackupService $service)
    {
    }

    public function list(): array
    {
        return $this->service->list();
    }

    public function create(): array
    {
        return $this->service->create();
    }

    public function download(int $backupId): array
    {
        return $this->service->download($backupId);
    }

    public function delete(int $backupId): void
    {
        $this->service->delete($backupId);
    }
}
