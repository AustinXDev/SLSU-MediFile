<?php

namespace App\Services\Dashboard;

use App\Repositories\DashboardRepositories\CsmDashboardRepository;
use DateTimeImmutable;
use InvalidArgumentException;

class CsmDashboardService
{
    private CsmDashboardRepository $repository;

    public function __construct(CsmDashboardRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getDashboard(array $query): array
    {
        $from = $this->validDate($query['from'] ?? null);
        $to = $this->validDate($query['to'] ?? null);
        if ($from !== null && $to !== null && $from > $to) {
            throw new InvalidArgumentException('The start date must not be later than the end date.');
        }

        $trend = in_array($query['trend'] ?? 'daily', ['daily', 'weekly', 'monthly'], true)
            ? ($query['trend'] ?? 'daily')
            : 'daily';
        $page = filter_var($query['page'] ?? 1, FILTER_VALIDATE_INT, [
            'options' => ['default' => 1, 'min_range' => 1],
        ]);
        $suggestionPage = filter_var($query['suggestionPage'] ?? 1, FILTER_VALIDATE_INT, [
            'options' => ['default' => 1, 'min_range' => 1],
        ]);
        $filters = [
            'from' => $from,
            'to' => $to,
            'region' => $this->validFilter($query['region'] ?? ''),
            'clientType' => $this->validFilter($query['clientType'] ?? ''),
        ];

        return $this->repository->getDashboard(
            $filters,
            $trend,
            $page,
            filter_var($query['allSuggestions'] ?? false, FILTER_VALIDATE_BOOLEAN),
            $suggestionPage
        );
    }

    private function validDate(mixed $value): ?string
    {
        if (!is_string($value) || $value === '') {
            return null;
        }

        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        if (!$date || $date->format('Y-m-d') !== $value) {
            throw new InvalidArgumentException('Dates must use YYYY-MM-DD format.');
        }

        return $value;
    }

    private function validFilter(mixed $value): string
    {
        return is_string($value) ? trim(mb_substr($value, 0, 100)) : '';
    }
}
