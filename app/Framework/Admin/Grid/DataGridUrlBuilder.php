<?php

declare(strict_types=1);

namespace Catalyst\Framework\Admin\Grid;

final class DataGridUrlBuilder
{
    /**
     * Merge an existing query array with overrides.
     *
     * A null or empty-string override removes the query key.
     *
     * @param array<string, mixed> $query
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    public function mergeQuery(array $query, array $overrides): array
    {
        foreach ($overrides as $key => $value) {
            if ($value === null || $value === '') {
                unset($query[$key]);
                continue;
            }

            $query[$key] = $value;
        }

        return $query;
    }

    /**
     * Build a URL from a base URL and query array.
     *
     * @param array<string, mixed> $query
     */
    public function build(string $baseUrl, array $query): string
    {
        $queryString = http_build_query($query);

        return $queryString === ''
            ? $baseUrl
            : $baseUrl . '?' . $queryString;
    }
}