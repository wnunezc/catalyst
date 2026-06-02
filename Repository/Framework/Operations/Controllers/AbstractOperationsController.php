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
 * Defines the Abstract Operations Controller class contract.
 *
 * @package Catalyst\Repository\Operations\Controllers
 * Responsibility: Coordinates the abstract operations controller behavior within its module boundary.
 */
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

    /**
     * Handles the checkbox value workflow.
     */
    protected function checkboxValue(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return in_array(strtolower((string) $value), ['1', 'true', 'on', 'yes'], true);
    }
}
