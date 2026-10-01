<?php

namespace App\Services\Dashboard;

use App\Repositories\DashboardRepositories\DashboardRepository;
use App\Services\Logs\LogsService;

class DashboardService
{
    protected DashboardRepository $dashboardRepo;
    protected LogsService $logService;

    public function __construct(
        DashboardRepository $dashboardRepo,
        LogsService $logService
    ) {
        $this->dashboardRepo = $dashboardRepo;
        $this->logService = $logService;
    }

    public function getDashboard(): array
    {
        return [
          'kpis' => $this->dashboardRepo->getKpis(),
          'patientActivity' => $this->dashboardRepo->getPatientActivity(),
          'recordsOverview' => $this->dashboardRepo->getMedicalRecordsOverview(),
          'recentActivity' => $this->logService->getRecent(10),
          'recentPatients' =>
            $this->dashboardRepo->getRecentPatients(5),
        ];
    }

}
