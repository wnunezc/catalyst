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

namespace Catalyst\Repository\Operations\Controllers;

use Catalyst\Framework\Controllers\Controller;

/**
 * Provides shared grid and checkbox normalization helpers for operations pages.
 *
 * @package Catalyst\Repository\Operations\Controllers
 * Responsibility: Centralizes reusable operations-controller presentation helpers.
 */
abstract class AbstractOperationsController extends Controller
{
    /**
     * Filters, sorts and paginates an in-memory grid row collection.
     *
     * Responsibility: Filters, sorts and paginates an in-memory grid row collection.
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

    /**
     * Normalizes checkbox-like input into a boolean value.
     *
     * Responsibility: Normalizes checkbox-like input into a boolean value.
     */
    protected function checkboxValue(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return in_array(strtolower((string) $value), ['1', 'true', 'on', 'yes'], true);
    }
}
