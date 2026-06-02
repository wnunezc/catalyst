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

use Catalyst\Framework\Sensitivity\SensitiveDataPolicy;
use Closure;

/**
 * Defines the Data Grid Row Normalizer class contract.
 *
 * @package Catalyst\Framework\Admin\Grid
 * Responsibility: Coordinates the data grid row normalizer behavior within its module boundary.
 */
final class DataGridRowNormalizer
{
    /**
     * Initializes the Data Grid Row Normalizer instance.
     */
    public function __construct(
        private readonly DataGridRowActionNormalizer $rowActionNormalizer
    ) {
    }

    /**
     * Normalize provider rows into render-ready grid rows.
     *
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
     * Resolve the raw value for a grid cell.
     *
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
     * Convert structured cell values into plain text for exports.
     *
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
     * Apply sensitive-data policy before exporting rows.
     *
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
