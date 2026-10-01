<?php

namespace App\Controllers\Dashboard;

use App\Services\Dashboard\DashboardService;

class DashboardController
{
    protected DashboardService $dashboardService;

    public function __construct(
        DashboardService $dashboardService
    ) {
        $this->dashboardService = $dashboardService;
    }


    public function Dasboard(): array
    {

        return $this->dashboardService->getDashboard();

    }

}
