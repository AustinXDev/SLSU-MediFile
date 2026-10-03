<?php

namespace App\Controllers;

use App\Services\Logs\ActivityLogService;

class ActivityLogController
{
    public function __construct(
        private ActivityLogService $service
    ) {
    }

    public function getDashboardData(array $params): array
    {
        return $this->service->getDashboardData($params);
    }

    public function getDetails(int $logId): ?array
    {
        return $this->service->getDetails($logId);
    }
}
