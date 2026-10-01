<?php

namespace App\Helpers\Dashboard;

class DashboardHelper
{
    /**
       * Build standardized KPI response.
       */
    public function buildKpi(
        int $count,
        int $currentMonth,
        int $previousMonth
    ): array {
        return [
            'count' => $count,
            'currentMonth' => $currentMonth,
            'previousMonth' => $previousMonth,
            'change' => $this->calculatePercentageChange(
                $currentMonth,
                $previousMonth
            ),
            'direction' => $this->getDirection(
                $currentMonth,
                $previousMonth
            ),
        ];
    }

    /**
     * Calculate percentage change between
     * the current and previous month.
     */
    private function calculatePercentageChange(
        int $current,
        int $previous
    ): float {
        if ($previous === 0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round(
            (($current - $previous) / $previous) * 100,
            1
        );
    }

    /**
     * Determine KPI direction.
     */
    private function getDirection(
        int $current,
        int $previous
    ): string {
        if ($current > $previous) {
            return 'up';
        }

        if ($current < $previous) {
            return 'down';
        }

        return 'neutral';
    }

}
