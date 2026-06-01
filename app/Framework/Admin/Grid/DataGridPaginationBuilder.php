<?php

declare(strict_types=1);

namespace Catalyst\Framework\Admin\Grid;

final class DataGridPaginationBuilder
{
    public function __construct(
        private readonly DataGridUrlBuilder $urlBuilder
    ) {
    }

    /**
     * Build pagination metadata and navigation links.
     *
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
     * Return a compact page window around the current page.
     *
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