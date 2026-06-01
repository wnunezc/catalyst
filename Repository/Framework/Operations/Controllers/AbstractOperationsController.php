<?php

declare(strict_types=1);

namespace Catalyst\Repository\Operations\Controllers;

use Catalyst\Framework\Controllers\Controller;

abstract class AbstractOperationsController extends Controller
{
    /**
     * @param array<int, array<string, mixed>> $rows
     * @param array<string, mixed> $state
     * @param string[] $sortableColumns
     * @return array{rows: array<int, array<string, mixed>>, total: int}
     */
    protected function sliceArrayGridRows(array $rows, array $state, array $sortableColumns, callable $filter): array
    {
        $search = strtolower(trim((string) ($state['search'] ?? '')));
        $filters = (array) ($state['filters'] ?? []);
        $rows = array_values(array_filter($rows, static fn (array $row): bool => $filter($row, $search, $filters)));

        $sort = (string) ($state['sort'] ?? '');
        $direction = strtolower((string) ($state['direction'] ?? 'asc')) === 'desc' ? 'desc' : 'asc';

        if (in_array($sort, $sortableColumns, true)) {
            usort($rows, static function (array $left, array $right) use ($sort, $direction): int {
                $comparison = ($left[$sort] ?? null) <=> ($right[$sort] ?? null);

                return $direction === 'desc' ? -$comparison : $comparison;
            });
        }

        $total = count($rows);
        $page = max(1, (int) ($state['page'] ?? 1));
        $perPage = max(1, (int) ($state['per_page'] ?? 10));
        $offset = ($page - 1) * $perPage;

        return [
            'rows' => array_slice($rows, $offset, $perPage),
            'total' => $total,
        ];
    }

    protected function checkboxValue(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return in_array(strtolower((string) $value), ['1', 'true', 'on', 'yes'], true);
    }
}
