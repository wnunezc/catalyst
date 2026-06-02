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

namespace Catalyst\Framework\Admin\Grid;

/**
 * Builds URLs and query arrays for DataGrid controls.
 *
 * @package Catalyst\Framework\Admin\Grid
 * Responsibility: Applies query overrides and generates links for pagination, sorting, exports, and actions.
 */
final class DataGridUrlBuilder
{
    /**
     * Merges query overrides into the current query and removes keys explicitly cleared by callers. A null or empty-string override removes the query key.
     *
     * Responsibility: Merges query overrides into the current query and removes keys explicitly cleared by callers. A null or empty-string override removes the query key.
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
     * Builds the final URL for a grid control from a base path and query array.
     *
     * Responsibility: Builds the final URL for a grid control from a base path and query array.
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
