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

/**
 * Builds pagination metadata for DataGrid listings.
 *
 * @package Catalyst\Framework\DataGrid
 * Responsibility: Calculates page bounds, result ranges, per-page options, and navigation URLs.
 */
final class DataGridPaginationBuilder
{
    /**
     * Receives the URL builder used to generate pagination links from grid query state.
     *
     * Responsibility: Receives the URL builder used to generate pagination links from grid query state.
     */
    public function __construct(
        private readonly DataGridUrlBuilder $urlBuilder
    ) {
    }

    /**
     * Builds pagination counters, available page sizes, and first/previous/next/last links.
     *
     * Responsibility: Builds pagination counters, available page sizes, and first/previous/next/last links.
     * @param array<string, mixed> $state
     * @param array<string, mixed> $config
     * @return array<string, mixed>
     */
    public function build(array $state, int $total, array $config): array
    {
        $perPage = max(1, (int) ($state['per_page'] ?? ($config['per_page'] ?? 10)));
        $currentPage = max(1, (int) ($state['page'] ?? 1));
        $lastPage = max(1, (int) ceil($total / $perPage));

        if ($currentPage > $lastPage) {
            $currentPage = $lastPage;
        }

        $from = $total === 0 ? 0 : (($currentPage - 1) * $perPage) + 1;
        $to = min($total, $currentPage * $perPage);

        $baseUrl = (string) ($config['base_url'] ?? '');
        $pageParam = (string) ($config['query_param_page'] ?? 'page');
        $query = (array) ($state['query'] ?? []);

        $pages = [];

        foreach ($this->pageWindow($currentPage, $lastPage) as $page) {
            $pages[] = [
                'page' => $page,
                'label' => (string) $page,
                'active' => $page === $currentPage,
                'url' => $this->urlBuilder->build(
                    $baseUrl,
                    $this->urlBuilder->mergeQuery($query, [
                        $pageParam => $page,
                    ])
                ),
            ];
        }

        $perPageOptions = array_values(array_filter(
            array_map('intval', (array) ($config['per_page_options'] ?? [10, 25, 50])),
            static fn (int $value): bool => $value > 0
        ));

        if ($perPageOptions === []) {
            $perPageOptions = [$perPage];
        }

        if (!in_array($perPage, $perPageOptions, true)) {
            $perPageOptions[] = $perPage;
            sort($perPageOptions);
        }

        return [
            'total' => $total,
            'per_page' => $perPage,
            'per_page_options' => $perPageOptions,
            'current_page' => $currentPage,
            'last_page' => $lastPage,
            'from' => $from,
            'to' => $to,
            'has_pages' => $lastPage > 1,
            'has_previous' => $currentPage > 1,
            'has_next' => $currentPage < $lastPage,

            'first_url' => $currentPage > 1
                ? $this->urlBuilder->build(
                    $baseUrl,
                    $this->urlBuilder->mergeQuery($query, [
                        $pageParam => 1,
                    ])
                )
                : null,

            'prev_url' => $currentPage > 1
                ? $this->urlBuilder->build(
                    $baseUrl,
                    $this->urlBuilder->mergeQuery($query, [
                        $pageParam => $currentPage - 1,
                    ])
                )
                : null,

            'next_url' => $currentPage < $lastPage
                ? $this->urlBuilder->build(
                    $baseUrl,
                    $this->urlBuilder->mergeQuery($query, [
                        $pageParam => $currentPage + 1,
                    ])
                )
                : null,

            'last_url' => $currentPage < $lastPage
                ? $this->urlBuilder->build(
                    $baseUrl,
                    $this->urlBuilder->mergeQuery($query, [
                        $pageParam => $lastPage,
                    ])
                )
                : null,

            'pages' => $pages,
        ];
    }

    /**
     * Returns the compact set of page numbers that should be displayed around the current page.
     *
     * Responsibility: Returns the compact set of page numbers that should be displayed around the current page.
     * @return array<int, int>
     */
    private function pageWindow(int $currentPage, int $lastPage): array
    {
        $start = max(1, $currentPage - 2);
        $end = min($lastPage, $currentPage + 2);

        if ($currentPage <= 3) {
            $end = min($lastPage, 5);
        }

        if ($currentPage >= $lastPage - 2) {
            $start = max(1, $lastPage - 4);
        }

        return range($start, $end);
    }
}
