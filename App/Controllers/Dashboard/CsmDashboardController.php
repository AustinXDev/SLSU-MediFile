<?php

namespace App\Controllers\Dashboard;

use App\Services\Dashboard\CsmDashboardService;

class CsmDashboardController
{
    private CsmDashboardService $service;

    public function __construct(CsmDashboardService $service)
    {
        $this->service = $service;
    }

    public function getDashboard(array $query): array
    {
        return $this->service->getDashboard($query);
    }
}
