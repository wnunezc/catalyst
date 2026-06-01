<?php

declare(strict_types=1);

namespace Catalyst\Framework\Admin\Grid;

use Catalyst\Framework\Http\Request;

final class DataGridStateResolver
{
    /**
     * Resolve the grid state from the current HTTP request and grid config.
     *
     * @param array<string, mixed> $config
     * @return array{
     *     page:int,
     *     per_page:int,
     *     sort:string,
     *     direction:string,
     *     search:string,
     *     filters:array<string, mixed>,
     *     query:array<string, mixed>
     * }
     */
    public function resolve(Request $request, array $config): array
    {
        $query = $request->getAllGet();
        $allowedSorts = $this->allowedSorts((array) ($config['columns'] ?? []));

        $defaultSort = (string) ($config['default_sort'] ?? 'id');
        $sort = (string) ($request->get('sort', $defaultSort) ?? $defaultSort);

        if (!in_array($sort, $allowedSorts, true)) {
            $sort = $defaultSort;
        }

        $direction = strtolower(
            (string) ($request->get('direction', $config['default_direction'] ?? 'asc') ?? 'asc')
        );
        $direction = $direction === 'desc' ? 'desc' : 'asc';

        $defaultPerPage = max(1, (int) ($config['per_page'] ?? 10));
        $perPageOptions = array_map('intval', (array) ($config['per_page_options'] ?? [10, 25, 50]));

        if ($perPageOptions === []) {
            $perPageOptions = [$defaultPerPage];
        }

        $perPage = max(
            1,
            (int) (
                $request->get(
                    (string) ($config['query_param_per_page'] ?? 'per_page'),
                    $defaultPerPage
                ) ?? $defaultPerPage
            )
        );

        if (!in_array($perPage, $perPageOptions, true)) {
            $perPage = $defaultPerPage;
        }

        $page = max(
            1,
            (int) (
                $request->get(
                    (string) ($config['query_param_page'] ?? 'page'),
                    1
                ) ?? 1
            )
        );

        $filters = $this->resolveFilters($request, (array) ($config['filters'] ?? []));

        return [
            'page' => $page,
            'per_page' => $perPage,
            'sort' => $sort,
            'direction' => $direction,
            'search' => trim(
                (string) (
                    $request->get(
                        (string) ($config['search_param'] ?? 'q'),
                        ''
                    ) ?? ''
                )
            ),
            'filters' => $filters,
            'query' => is_array($query) ? $query : [],
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $columns
     * @return array<int, string>
     */
    private function allowedSorts(array $columns): array
    {
        $allowedSorts = [];

        foreach ($columns as $column) {
            if (!empty($column['sortable']) && !empty($column['key'])) {
                $allowedSorts[] = (string) $column['key'];
            }
        }

        return $allowedSorts;
    }

    /**
     * @param array<int, array<string, mixed>> $filters
     * @return array<string, mixed>
     */
    private function resolveFilters(Request $request, array $filters): array
    {
        $resolved = [];

        foreach ($filters as $filter) {
            $name = (string) ($filter['name'] ?? '');

            if ($name === '') {
                continue;
            }

            $resolved[$name] = $request->get($name, $filter['default'] ?? '');
        }

        return $resolved;
    }
}