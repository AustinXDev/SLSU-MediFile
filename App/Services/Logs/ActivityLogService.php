<?php

namespace App\Services\Logs;

use App\Repositories\LogRepositories\ActivityLogRepository;
use DateTimeImmutable;
use InvalidArgumentException;

class ActivityLogService
{
    public function __construct(
        private ActivityLogRepository $repository
    ) {
    }

    public function getDashboardData(array $params): array
    {
        $filters = $this->normalizeFilters($params);
        $view = $this->scalarString($params['view'] ?? '');

        if ($view === 'patients') {
            $filters['module'] = 'patients';
            $result = $this->repository->getRecentPatientActivity($filters);
        } else {
            $result = $this->repository->getActivityLogs($filters);
            $view = 'logs';
        }

        return [
            'view' => $view,
            'records' => $result['records'],
            'pagination' => $result['pagination'],
            'summary' => $this->repository->getSummary($filters),
            'filters' => $this->repository->getFilterOptions(),
        ];
    }

    public function getDetails(int $logId): ?array
    {
        if ($logId < 1) {
            throw new InvalidArgumentException('Invalid activity record ID.');
        }

        return $this->repository->getById($logId);
    }

    private function normalizeFilters(array $params): array
    {
        $page = filter_var(
            $this->scalarString($params['page'] ?? 1),
            FILTER_VALIDATE_INT
        );
        $page = $page === false || $page < 1 ? 1 : $page;

        $limit = filter_var(
            $this->scalarString($params['limit'] ?? 20),
            FILTER_VALIDATE_INT
        );
        $limit = $limit === false || $limit < 1 ? 20 : min(100, $limit);

        $dateFrom = $this->normalizeDate($params['date_from'] ?? '');
        $dateTo = $this->normalizeDate($params['date_to'] ?? '');

        if ($dateFrom !== '' && $dateTo !== '' && $dateFrom > $dateTo) {
            throw new InvalidArgumentException(
                'The start date must be before or equal to the end date.'
            );
        }

        return [
            'page' => $page,
            'limit' => $limit,
            'search' => mb_substr($this->scalarString($params['search'] ?? ''), 0, 120),
            'action' => mb_substr($this->scalarString($params['action'] ?? ''), 0, 80),
            'module' => mb_substr($this->scalarString($params['module'] ?? ''), 0, 80),
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ];
    }

    private function normalizeDate(mixed $value): string
    {
        $date = $this->scalarString($value);
        if ($date === '') {
            return '';
        }

        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        $errors = DateTimeImmutable::getLastErrors();

        if (
            !$parsed
            || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))
            || $parsed->format('Y-m-d') !== $date
        ) {
            throw new InvalidArgumentException('Dates must use the YYYY-MM-DD format.');
        }

        return $date;
    }

    private function scalarString(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (!is_scalar($value)) {
            throw new InvalidArgumentException('Filter values must be scalar.');
        }

        return trim((string) $value);
    }
}
