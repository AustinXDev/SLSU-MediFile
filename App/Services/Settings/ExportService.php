<?php

namespace App\Services\Settings;

use App\Repositories\ExportRepository;
use App\Services\Logs\LogsService;
use App\Session\SessionManager;
use InvalidArgumentException;
use RuntimeException;

class ExportService
{
    public function __construct(
        private ExportRepository $exports,
        private LogsService $logs,
        private SessionManager $session
    ) {
    }

    public function availableDatasets(): array
    {
        $this->requireExportRole();
        return $this->exports->availableDatasets();
    }

    public function streamCsv(string $dataset, $output): string
    {
        $adminId = $this->requireExportRole();
        if (!is_resource($output)) {
            throw new RuntimeException('Unable to create the export stream.');
        }

        $columns = $this->exports->columns($dataset);
        $label = $this->exports->label($dataset);
        $this->logs->record(
            $adminId,
            'EXPORT DATA',
            'Exported application data: ' . $label . '.',
            'settings',
            $adminId
        );

        if (fwrite($output, "\xEF\xBB\xBF") === false || fputcsv($output, $columns, ',', '"', '') === false) {
            throw new RuntimeException('Unable to write the CSV export.');
        }
        $this->exports->streamRows($dataset, static function (array $row) use ($output, $columns): void {
            $values = [];
            foreach ($columns as $column) {
                $value = (string) ($row[$column] ?? '');
                if (preg_match('/^[\s]*[=+\-@]/u', $value)) {
                    $value = "'" . $value;
                }
                $values[] = $value;
            }
            if (fputcsv($output, $values, ',', '"', '') === false) {
                throw new RuntimeException('Unable to write the CSV export.');
            }
        });

        return $dataset;
    }

    private function requireExportRole(): int
    {
        $adminId = (int) $this->session->get('admin_id', 0);
        if (!$this->session->get('admin_authenticated') || $adminId <= 0) {
            throw new RuntimeException('Authentication is required.', 401);
        }
        if (!in_array($this->session->get('role'), ['Super Admin', 'Administrator'], true)) {
            throw new RuntimeException('Access denied.', 403);
        }

        return $adminId;
    }
}
