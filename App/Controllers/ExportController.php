<?php

namespace App\Controllers;

use App\Services\Settings\ExportService;

class ExportController
{
    public function __construct(private ExportService $service)
    {
    }

    public function availableDatasets(): array
    {
        return $this->service->availableDatasets();
    }

    public function streamCsv(string $dataset, $output): string
    {
        return $this->service->streamCsv($dataset, $output);
    }
}
