<?php

declare(strict_types=1);

/**
 * Catalyst PHP Framework
 *
 * A modern PHP 8.4 framework for building
 * robust and scalable web applications.
 *
 * PHP Version 8.4 (Required).
 *
 * @package    Catalyst
 *
 * @author     Walter Nuñez (arcanisgk/original founder)
 * @email      <wnunez@lh-2.net>
 * @email      <icarosnet@gmail.com>
 * @copyright  2024-2026 Walter Francisco Nuñez Cruz and Icaros Net
 * @license    Proprietary - https://catalyst.lh-2.net/license
 *
 * @version    GIT: See repository tags
 *
 * @category   Framework
 * @filesource
 *
 * @link       https://catalyst.lh-2.net Project homepage
 * @see        https://catalyst.lh-2.net/docs Documentation
 *
 */

namespace Catalyst\Framework\DataGrid;

use Catalyst\Framework\Http\Request;

/**
 * Resolves DataGrid state from an HTTP request.
 *
 * @package Catalyst\Framework\DataGrid
 * Responsibility: Extracts and validates pagination, sorting, search, filter, and raw query state for providers.
 */
final class DataGridStateResolver
{
    /**
     * Resolves the provider-facing grid state from request query parameters and grid defaults. page:int, per_page:int, sort:string, direction:string, search:string, filters:array<string, mixed>, query:array<string, mixed> }.
     *
     * Responsibility: Resolves the provider-facing grid state from request query parameters and grid defaults. page:int, per_page:int, sort:string, direction:string, search:string, filters:array<string, mixed>, query:array<string, mixed> }.
     * @param array<string, mixed> $config
     * @return array{
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
     * Collects the configured column keys that are allowed to be used for sorting.
     *
     * Responsibility: Collects the configured column keys that are allowed to be used for sorting.
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
     * Resolves filter values from the request while falling back to configured defaults.
     *
     * Responsibility: Resolves filter values from the request while falling back to configured defaults.
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
