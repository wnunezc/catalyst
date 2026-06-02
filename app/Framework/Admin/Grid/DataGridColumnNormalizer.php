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
 * Defines the Data Grid Column Normalizer class contract.
 *
 * @package Catalyst\Framework\Admin\Grid
 * Responsibility: Coordinates the data grid column normalizer behavior within its module boundary.
 */
final class DataGridColumnNormalizer
{
    /**
     * Initializes the Data Grid Column Normalizer instance.
     */
    public function __construct(
        private readonly DataGridUrlBuilder $urlBuilder,
        private readonly DataGridTextFormatter $textFormatter
    ) {
    }

    /**
     * Normalize configured columns into render-ready definitions.
     *
     * @param array<int, array<string, mixed>> $columns
     * @param array<string, mixed> $state
     * @param array<string, mixed> $config
     * @return array<int, array<string, mixed>>
     */
    public function normalize(array $columns, array $state, array $config): array
    {
        $normalized = [];

        foreach ($columns as $column) {
            $key = (string) ($column['key'] ?? '');

            if ($key === '') {
                continue;
            }

            $sortable = (bool) ($column['sortable'] ?? false);
            $sortActive = $sortable && (string) ($state['sort'] ?? '') === $key;

            $direction = $sortActive && (string) ($state['direction'] ?? 'asc') === 'asc'
                ? 'desc'
                : 'asc';

            $query = $this->urlBuilder->mergeQuery((array) ($state['query'] ?? []), [
                'sort' => $key,
                'direction' => $direction,
                (string) ($config['query_param_page'] ?? 'page') => null,
            ]);

            $normalized[] = [
                'key' => $key,
                'label' => (string) ($column['label'] ?? $this->textFormatter->humanize($key)),
                'sortable' => $sortable,
                'sort_active' => $sortActive,
                'sort_direction' => (string) ($state['direction'] ?? 'asc'),
                'sort_icon_class' => $this->sortIconClass($sortActive, (string) ($state['direction'] ?? 'asc')),
                'sort_url' => $sortable
                    ? $this->urlBuilder->build((string) ($config['base_url'] ?? ''), $query)
                    : null,
                'header_class' => $this->headerClass($column),
                'cell_class' => $this->cellClass($column),
                'align' => (string) ($column['align'] ?? 'start'),
                'width' => (string) ($column['width'] ?? ''),
                'type' => (string) ($column['type'] ?? 'text'),
                'formatter' => $column['formatter'] ?? null,
                'empty' => (string) ($column['empty'] ?? '—'),
            ];
        }

        return $normalized;
    }

    /**
     * @param array<string, mixed> $column
     */
    private function headerClass(array $column): string
    {
        $classes = ['text-nowrap'];

        $align = (string) ($column['align'] ?? 'start');

        if ($align === 'end' || $align === 'right') {
            $classes[] = 'text-end';
        } elseif ($align === 'center') {
            $classes[] = 'text-center';
        }

        $custom = trim((string) ($column['header_class'] ?? ''));

        if ($custom !== '') {
            $classes[] = $custom;
        }

        return implode(' ', array_unique($classes));
    }

    /**
     * @param array<string, mixed> $column
     */
    private function cellClass(array $column): string
    {
        $classes = [];

        $align = (string) ($column['align'] ?? 'start');

        if ($align === 'end' || $align === 'right') {
            $classes[] = 'text-end';
        } elseif ($align === 'center') {
            $classes[] = 'text-center';
        }

        $custom = trim((string) ($column['cell_class'] ?? $column['class'] ?? ''));

        if ($custom !== '') {
            $classes[] = $custom;
        }

        return implode(' ', array_unique($classes));
    }

    /**
     * Handles the sort icon class workflow.
     */
    private function sortIconClass(bool $sortActive, string $direction): string
    {
        if (!$sortActive) {
            return 'fa-sort text-muted';
        }

        return strtolower($direction) === 'desc'
            ? 'fa-sort-down'
            : 'fa-sort-up';
    }
}
