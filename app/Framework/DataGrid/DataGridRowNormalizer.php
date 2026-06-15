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

use Catalyst\Framework\Sensitivity\SensitiveDataPolicy;
use Closure;

/**
 * Normalizes provider rows for DataGrid templates and exports.
 *
 * @package Catalyst\Framework\DataGrid
 * Responsibility: Maps raw provider rows into cells, row keys, row actions, and sanitized export values.
 */
final class DataGridRowNormalizer
{
    /**
     * Receives the row action normalizer used to attach per-row action metadata.
     *
     * Responsibility: Receives the row action normalizer used to attach per-row action metadata.
     */
    public function __construct(
        private readonly DataGridRowActionNormalizer $rowActionNormalizer
    ) {
    }

    /**
     * Converts provider rows into render-ready rows with cell values, actions, and selection metadata.
     *
     * Responsibility: Converts provider rows into render-ready rows with cell values, actions, and selection metadata.
     * @param array<int, array<string, mixed>> $rows
     * @param array<string, mixed> $state
     * @param array<string, mixed> $config
     * @return array<int, array<string, mixed>>
     */
    public function normalize(array $rows, array $state, array $config): array
    {
        $normalized = [];
        $rowKey = trim((string) ($config['row_key'] ?? 'id'));
        $columns = (array) ($config['columns'] ?? []);
        $actions = (array) ($config['actions'] ?? []);

        foreach ($rows as $row) {
            $row = (array) $row;
            $cells = [];

            foreach ($columns as $column) {
                $column = (array) $column;
                $value = $this->resolveCellValue($row, $state, $column);

                $cells[] = [
                    'value' => $value,
                    'class' => (string) ($column['cell_class'] ?? $column['class'] ?? ''),
                    'empty' => (string) ($column['empty'] ?? '—'),
                    'truncate' => DataGridColumnNormalizer::normalizeTruncateConfig($column['truncate'] ?? null),
                ];
            }

            $key = $rowKey !== '' ? ($row[$rowKey] ?? null) : null;
            $rowActions = $this->rowActionNormalizer->normalize($actions, $row, $state);

            $normalized[] = [
                'key' => $key,
                'cells' => $cells,
                'actions' => $rowActions,
                'has_actions' => $rowActions !== [],
                'has_checkbox' => $key !== null && $key !== '',
            ];
        }

        return $normalized;
    }

    /**
     * Resolves a cell value from a configured closure or the row field keyed by the column.
     *
     * Responsibility: Resolves a cell value from a configured closure or the row field keyed by the column.
     * @param array<string, mixed> $row
     * @param array<string, mixed> $state
     * @param array<string, mixed> $column
     */
    public function resolveCellValue(array $row, array $state, array $column): mixed
    {
        $resolver = $column['value'] ?? null;

        if ($resolver instanceof Closure) {
            return $resolver($row, $state);
        }

        $key = (string) ($column['key'] ?? '');

        return $key !== '' ? ($row[$key] ?? null) : null;
    }

    /**
     * Converts structured display values into plain text suitable for CSV and XLS exports.
     *
     * Responsibility: Converts structured display values into plain text suitable for CSV and XLS exports.
     * @param array<string, mixed> $value
     */
    public function stringifyStructuredValue(array $value): string
    {
        $kind = (string) ($value['kind'] ?? '');

        return match ($kind) {
            'stack' => trim(implode(' | ', array_values(array_filter([
                (string) ($value['primary'] ?? ''),
                (string) ($value['secondary'] ?? ''),
            ], static fn (string $part): bool => trim($part) !== '')))),

            'code' => (string) ($value['text'] ?? ''),

            'badge' => (string) ($value['label'] ?? ''),

            'badges' => implode(', ', array_values(array_filter(array_map(
                static fn (mixed $badge): string => is_array($badge)
                    ? (string) ($badge['label'] ?? '')
                    : '',
                (array) ($value['items'] ?? [])
            ), static fn (string $label): bool => $label !== ''))),

            default => json_encode($value, JSON_UNESCAPED_UNICODE) ?: '',
        };
    }

    /**
     * Applies the resource sensitive-data policy before a row is written to an export.
     *
     * Responsibility: Applies the resource sensitive-data policy before a row is written to an export.
     * @param array<string, mixed> $row
     * @param array<string, mixed> $config
     * @return array<string, mixed>
     */
    public function sanitizeExportRow(array $row, array $config): array
    {
        $resourceKey = trim((string) ($config['resource_key'] ?? ''));

        if ($resourceKey === '') {
            return $row;
        }

        return SensitiveDataPolicy::getInstance()->sanitize(
            $resourceKey,
            $row,
            SensitiveDataPolicy::CHANNEL_EXPORT
        );
    }
}
