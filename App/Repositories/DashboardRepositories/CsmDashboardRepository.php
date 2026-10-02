<?php

namespace App\Repositories\DashboardRepositories;

use PDO;

class CsmDashboardRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getDashboard(
        array $filters,
        string $trend,
        int $page,
        bool $allSuggestions,
        int $suggestionPage
    ): array {
        $rows = $this->getSqdRowsSql();
        $where = $this->buildWhere($filters, 's');
        $stats = $this->fetchOne(
            "SELECT AVG(s.rating) AS average, COUNT(s.rating) AS applicable,
                SUM(s.rating IN (4, 5)) AS satisfied
             FROM ({$rows}) s{$where['sql']}",
            $where['params']
        );
        $total = $this->countEvaluations($filters);

        return [
            'summary' => [
                'responses' => $total,
                'overallSatisfaction' => $stats['average'] !== null ? round((float) $stats['average'], 2) : null,
                'satisfactionRate' => (int) $stats['applicable'] > 0
                    ? round(((int) $stats['satisfied'] / (int) $stats['applicable']) * 100, 1)
                    : null,
                'responsesToday' => $this->countEvaluations($filters, true),
            ],
            'dimensions' => $this->getDimensions($filters),
            'distribution' => $this->getDistribution($filters),
            'trend' => $this->getTrend($filters, $trend),
            'charter' => $this->getCharterResults($filters),
            'services' => $this->getServiceResults($filters),
            'demographics' => $this->getDemographics($filters),
            'suggestions' => $this->getSuggestions($filters, $allSuggestions, $suggestionPage),
            'recent' => $this->getRecent($filters, $page),
            'options' => $this->getFilterOptions(),
        ];
    }

    private function getDimensions(array $filters): array
    {
        $rows = $this->getSqdRowsSql();
        $where = $this->buildWhere($filters, 's');
        $stmt = $this->pdo->prepare(
            "SELECT s.dimension, AVG(s.rating) AS average, COUNT(s.rating) AS responses
             FROM ({$rows}) s{$where['sql']}
             GROUP BY s.dimension ORDER BY s.dimension"
        );
        $stmt->execute($where['params']);
        $results = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $results[$row['dimension']] = [
                'average' => $row['average'] !== null ? round((float) $row['average'], 2) : null,
                'responses' => (int) $row['responses'],
            ];
        }

        $dimensions = [];
        for ($number = 0; $number <= 8; $number++) {
            $key = 'SQD' . $number;
            $dimensions[] = [
                'id' => $key,
                'average' => $results[$key]['average'] ?? null,
                'responses' => $results[$key]['responses'] ?? 0,
            ];
        }

        return $dimensions;
    }

    private function getDistribution(array $filters): array
    {
        $rows = $this->getSqdRowsSql();
        $where = $this->buildWhere($filters, 's');
        $stmt = $this->pdo->prepare(
            "SELECT s.rating, COUNT(*) AS responses FROM ({$rows}) s{$where['sql']}
             GROUP BY s.rating"
        );
        $stmt->execute($where['params']);
        $counts = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $counts[$row['rating'] === null ? 'na' : (string) (int) $row['rating']] = (int) $row['responses'];
        }

        $distribution = [];
        foreach ([
            '1' => 'Strongly Disagree',
            '2' => 'Disagree',
            '3' => 'Neither Agree nor Disagree',
            '4' => 'Agree',
            '5' => 'Strongly Agree',
            'na' => 'Not Applicable',
        ] as $value => $label) {
            $distribution[] = ['label' => $label, 'responses' => $counts[$value] ?? 0];
        }

        return $distribution;
    }

    private function getTrend(array $filters, string $granularity): array
    {
        $rows = $this->getSqdRowsSql();
        $where = $this->buildWhere($filters, 's');
        $period = match ($granularity) {
            'weekly' => 'DATE_SUB(DATE(s.evaluation_date), INTERVAL WEEKDAY(s.evaluation_date) DAY)',
            'monthly' => "DATE_FORMAT(s.evaluation_date, '%Y-%m-01')",
            default => 'DATE(s.evaluation_date)',
        };
        $limit = match ($granularity) {
            'daily' => 90,
            'weekly' => 52,
            default => 36,
        };
        $stmt = $this->pdo->prepare(
            "SELECT period, average, responses FROM (
                SELECT 
                  {$period} AS period, 
                  AVG(s.rating) AS average, 
                  COUNT(s.rating) AS responses
                FROM ({$rows}) s
                {$where['sql']} 
                AND s.rating IS NOT NULL
                GROUP BY period ORDER BY period DESC LIMIT {$limit}
             ) periods ORDER BY period ASC"
        );
        $stmt->execute($where['params']);

        return array_map(static fn (array $row): array => [
            'period' => $row['period'],
            'average' => round((float) $row['average'], 2),
            'responses' => (int) $row['responses'],
        ], $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    private function getCharterResults(array $filters): array
    {
        $results = [];
        $where = $this->buildWhere($filters, 'e');
        foreach (['CC1' => 'cc1', 'CC2' => 'cc2', 'CC3' => 'cc3'] as $key => $column) {
            $stmt = $this->pdo->prepare(
                "SELECT TRIM(CAST(e.{$column} AS CHAR)) AS response, COUNT(*) AS responses
                 FROM evaluations e{$where['sql']}
                   AND e.{$column} IS NOT NULL AND TRIM(CAST(e.{$column} AS CHAR)) <> ''
                 GROUP BY TRIM(CAST(e.{$column} AS CHAR)) ORDER BY responses DESC, response ASC"
            );
            $stmt->execute($where['params']);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $total = array_sum(array_map(static fn (array $row): int => (int) $row['responses'], $rows));
            $results[] = [
                'id' => $key,
                'items' => array_map(static fn (array $row): array => [
                    'label' => $row['response'],
                    'responses' => (int) $row['responses'],
                    'percentage' => $total > 0 ? round(((int) $row['responses'] / $total) * 100, 1) : 0,
                ], $rows),
            ];
        }

        return $results;
    }

    private function getServiceResults(array $filters): array
    {
        $rows = $this->getSqdRowsSql();
        $where = $this->buildWhere($filters, 's');
        $stmt = $this->pdo->prepare(
            "SELECT COALESCE(NULLIF(TRIM(s.service_availed), ''), 'Not specified') AS service,
                COUNT(DISTINCT s.submission_id) AS responses, AVG(s.rating) AS average
             FROM ({$rows}) s{$where['sql']} AND s.rating IS NOT NULL
             GROUP BY service ORDER BY responses DESC, service ASC"
        );
        $stmt->execute($where['params']);

        return array_map(static fn (array $row): array => [
            'service' => $row['service'],
            'responses' => (int) $row['responses'],
            'average' => round((float) $row['average'], 2),
        ], $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    private function getDemographics(array $filters): array
    {
        $where = $this->buildWhere($filters, 'e');
        $ageGroup = "CASE
            WHEN e.age < 18 THEN 'Below 18'
            WHEN e.age BETWEEN 18 AND 24 THEN '18-24'
            WHEN e.age BETWEEN 25 AND 34 THEN '25-34'
            WHEN e.age BETWEEN 35 AND 44 THEN '35-44'
            WHEN e.age BETWEEN 45 AND 54 THEN '45-54'
            WHEN e.age >= 55 THEN '55+'
            ELSE 'Not specified' END";

        return [
            'clientType' => $this->getGroupCounts('client_type', $where),
            'sex' => $this->getGroupCounts('sex', $where),
            'ageGroup' => $this->getGroupCounts($ageGroup, $where),
            'region' => $this->getGroupCounts('region', $where),
        ];
    }

    private function getGroupCounts(string $expression, array $where): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT COALESCE(NULLIF(TRIM(CAST({$expression} AS CHAR)), ''), 'Not specified') AS label,
                COUNT(*) AS responses FROM evaluations e{$where['sql']}
             GROUP BY label ORDER BY responses DESC, label ASC"
        );
        $stmt->execute($where['params']);

        return array_map(static fn (array $row): array => [
            'label' => $row['label'],
            'responses' => (int) $row['responses'],
        ], $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    private function getSuggestions(array $filters, bool $all, int $page): array
    {
        $where = $this->buildWhere($filters, 'e');
        $countStmt = $this->pdo->prepare(
            "SELECT COUNT(*) AS total FROM evaluations e{$where['sql']}
             AND e.suggestion IS NOT NULL AND TRIM(e.suggestion) <> ''"
        );
        $countStmt->execute($where['params']);
        $total = (int) $countStmt->fetchColumn();
        $pageSize = $all ? 20 : 5;
        $pages = (int) ceil($total / $pageSize);
        $page = $all ? min($page, max(1, $pages)) : 1;
        $offset = ($page - 1) * $pageSize;
        $stmt = $this->pdo->prepare(
            "SELECT suggestion, evaluation_date FROM evaluations e{$where['sql']}
             AND e.suggestion IS NOT NULL AND TRIM(e.suggestion) <> ''
             ORDER BY e.evaluation_date DESC LIMIT {$pageSize} OFFSET {$offset}"
        );
        $stmt->execute($where['params']);
        $items = array_map(static function (array $row): array {
            $row['suggestion'] = preg_replace(
                '/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i',
                '[email removed]',
                $row['suggestion']
            ) ?? '[comment unavailable]';
            return $row;
        }, $stmt->fetchAll(PDO::FETCH_ASSOC));

        return ['items' => $items, 'total' => $total, 'page' => $page, 'pages' => $pages];
    }

    private function getRecent(array $filters, int $page): array
    {
        $rows = $this->getSqdRowsSql();
        $where = $this->buildWhere($filters, 's');
        $pageSize = 10;
        $offset = ($page - 1) * $pageSize;
        $stmt = $this->pdo->prepare(
            "SELECT s.submission_id, s.evaluation_date, s.client_type, s.region,
                s.service_availed, AVG(s.rating) AS average
             FROM ({$rows}) s{$where['sql']}
             GROUP BY s.submission_id, s.evaluation_date, s.client_type, s.region, s.service_availed
             ORDER BY s.evaluation_date DESC LIMIT {$pageSize} OFFSET {$offset}"
        );
        $stmt->execute($where['params']);
        $items = array_map(static fn (array $row): array => [
            'date' => $row['evaluation_date'],
            'clientType' => $row['client_type'],
            'region' => $row['region'],
            'service' => $row['service_availed'],
            'average' => $row['average'] !== null ? round((float) $row['average'], 2) : null,
            'status' => 'Completed',
        ], $stmt->fetchAll(PDO::FETCH_ASSOC));

        $total = $this->countEvaluations($filters);

        return [
            'items' => $items,
            'page' => $page,
            'pageSize' => $pageSize,
            'total' => $total,
            'pages' => (int) ceil($total / $pageSize),
        ];
    }

    private function countEvaluations(array $filters, bool $today = false): int
    {
        $where = $this->buildWhere($filters, 'e');
        $sql = "SELECT COUNT(*) AS total FROM evaluations e{$where['sql']}";
        if ($today) {
            $sql .= ' AND DATE(e.evaluation_date) = CURDATE()';
        }
        $result = $this->fetchOne($sql, $where['params']);

        return (int) $result['total'];
    }

    private function getFilterOptions(): array
    {
        $options = [];
        foreach (['region', 'client_type'] as $column) {
            $stmt = $this->pdo->query(
                "SELECT DISTINCT TRIM(CAST({$column} AS CHAR)) AS value FROM evaluations
                 WHERE {$column} IS NOT NULL AND TRIM(CAST({$column} AS CHAR)) <> ''
                 ORDER BY value ASC"
            );
            $options[$column === 'client_type' ? 'clientTypes' : 'regions'] = array_column(
                $stmt->fetchAll(PDO::FETCH_ASSOC),
                'value'
            );
        }

        return $options;
    }

    private function getSqdRowsSql(): string
    {

        $selects = [];
        for ($number = 0; $number <= 8; $number++) {
            $column = 'sqd' . $number;
            $selects[] = "
              SELECT 
                e.submission_id, 
                e.evaluation_date, 
                e.client_type, 
                e.region,
                e.service_availed, 
                e.sex, 
                e.age, 
                'SQD{$number}' AS dimension, 
                e.{$column} AS response
              FROM evaluations e";
        }

        $rating = "
            CASE UPPER(TRIM(CAST(response AS CHAR)))
              WHEN '1' THEN 1 WHEN 'STRONGLY DISAGREE' THEN 1
              WHEN '2' THEN 2 WHEN 'DISAGREE' THEN 2
              WHEN '3' THEN 3 WHEN 'NEITHER AGREE NOR DISAGREE' THEN 3
              WHEN '4' THEN 4 WHEN 'AGREE' THEN 4
              WHEN '5' THEN 5 WHEN 'STRONGLY AGREE' THEN 5
            ELSE NULL END";

        return '
          SELECT sqd_values.*, ' . $rating . ' AS rating FROM (' . implode(' UNION ALL ', $selects) . ') sqd_values';
    }

    private function buildWhere(array $filters, string $alias): array
    {
        $conditions = [];
        $params = [];
        if ($filters['from'] !== null) {
            $conditions[] = "{$alias}.evaluation_date >= ?";
            $params[] = $filters['from'];
        }
        if ($filters['to'] !== null) {
            $conditions[] = "{$alias}.evaluation_date < DATE_ADD(?, INTERVAL 1 DAY)";
            $params[] = $filters['to'];
        }
        if ($filters['region'] !== '') {
            $conditions[] = "{$alias}.region = ?";
            $params[] = $filters['region'];
        }
        if ($filters['clientType'] !== '') {
            $conditions[] = "{$alias}.client_type = ?";
            $params[] = $filters['clientType'];
        }

        return [
            'sql' => $conditions ? ' WHERE ' . implode(' AND ', $conditions) : ' WHERE 1 = 1',
            'params' => $params,
        ];
    }

    private function fetchOne(string $sql, array $params): array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }
}
