<?php

namespace App\Services\Logs;

use App\Repositories\LogRepositories\LogRepository;

class LogsService
{
    protected LogRepository $logRepository;

    public function __construct(
        LogRepository $logRepository
    ) {
        $this->logRepository = $logRepository;
    }

    public function record(
        int $adminId,
        string $action,
        string $description,
        string $module,
        int $recordId
    ): int {

        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';

        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN';

        return $this->logRepository->create(
            $adminId,
            $action,
            $description,
            $module,
            $recordId,
            $ipAddress,
            $userAgent
        );
    }


    public function getRecent(int $limit = 10): array
    {
        return $this->logRepository->getRecent($limit);
    }
}
