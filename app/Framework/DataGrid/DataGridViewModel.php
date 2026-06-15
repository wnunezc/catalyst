<?php

declare(strict_types=1);

namespace Catalyst\Framework\DataGrid;

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

use Catalyst\Framework\View\TrustedHtml;
use Catalyst\Helpers\Security\CsrfProtection;

final class DataGridViewModel
{
    /**
     * @param array<string, mixed> $scope
     * @return array<string, mixed>
     */
    public static function build(array $scope): array
    {
    $grid = is_array($scope['grid'] ?? null) ? $scope['grid'] : [];
    $pagination = (array) ($grid['pagination'] ?? []);
    $bulk = (array) ($grid['bulk'] ?? []);
    $bulkActions = array_is_list($bulk) ? $bulk : (array) ($bulk['actions'] ?? []);
    $bulkName = (string) ($bulk['name'] ?? 'selected');
    $exports = [];
    $filters = [];
    $rows = [];
    $pages = [];
    $csrfField = TrustedHtml::fromString(CsrfProtection::getInstance()->getTokenField());
    $total = (int) ($grid['total'] ?? 0);
    $gridBulkFormId = 'grid-bulk-' . substr(md5((string) ($grid['base_url'] ?? 'grid')), 0, 10);
    $rawRows = (array) ($grid['rows'] ?? []);
    $gridHasBulk = $bulkActions !== [];
    $gridHasRowActions = false;
    foreach ($rawRows as $rawRow) {
        if (!empty($rawRow['has_actions']) || (array) ($rawRow['actions'] ?? []) !== []) {
            $gridHasRowActions = true;
            break;
        }
    }
    $visibleColumnCount = count((array) ($grid['columns'] ?? []))
        + ($gridHasBulk ? 1 : 0)
        + ($gridHasRowActions ? 1 : 0);
    $visibleCharacterLimit = max(15, 35 - (max(0, $visibleColumnCount - 6) * 5));
    $visibleRowCount = count($rawRows);
    $tableScrollClasses = ['datagrid-table-scroll'];
    $tableClasses = ['table', 'table-striped', 'table-hover', 'dt-responsive', 'align-middle', 'mb-0', 'datagrid-table'];

    if ($visibleColumnCount >= 11) {
        $tableScrollClasses[] = 'datagrid-table-scroll--wide';
        $tableClasses[] = 'datagrid-table--wide';
    }

    if ($visibleColumnCount >= 16) {
        $tableClasses[] = 'datagrid-table--extreme';

        if ($visibleRowCount > 15) {
            $tableScrollClasses[] = 'datagrid-table-scroll--vertical';
        }
    }
    $normalizeButtonClass = static function (string $class, string $fallback = 'btn btn-outline-secondary btn-sm'): string {
        $class = trim($class) !== '' ? trim($class) : $fallback;

        if (!str_contains($class, 'btn')) {
            $class = trim('btn btn-sm ' . $class);
        }

        if (!str_contains($class, 'btn-sm') && !str_contains($class, 'btn-lg')) {
            $class .= ' btn-sm';
        }

        return trim(preg_replace('/\s+/', ' ', $class) ?? $class);
    };
    $applyTruncate = static function (array $normalized, array $truncate): array {
        $fullText = $normalized['is_code']
            ? (string) $normalized['code_text']
            : (string) $normalized['text'];
        $threshold = (int) ($truncate['threshold'] ?? 35);
        $exceedsThreshold = mb_strlen($fullText) > $threshold;
        $enabled = !empty($truncate['enabled'])
            && trim($fullText) !== ''
            && (empty($truncate['automatic']) || $exceedsThreshold);
        $width = in_array((string) ($truncate['width'] ?? 'md'), ['sm', 'md', 'lg'], true)
            ? (string) $truncate['width']
            : 'md';

        $normalized['has_truncated_text'] = $enabled;
        $normalized['full_text'] = $fullText;
        $normalized['display_text'] = $enabled ? mb_substr($fullText, 0, $threshold) . '...' : $fullText;
        $normalized['text_width_class'] = 'datagrid-cell-text--' . $width;
        $normalized['tooltip'] = $enabled && !empty($truncate['tooltip']);
        $normalized['copyable'] = $enabled && !empty($truncate['copyable']);
        $normalized['copy_label'] = __('ui.datagrid.copy');
        $normalized['copied_label'] = __('ui.datagrid.copied');

        return $normalized;
    };
    $normalizeStackLine = static function (string $text, array $truncate): array {
        $threshold = (int) ($truncate['threshold'] ?? 35);
        $exceedsThreshold = mb_strlen($text) > $threshold;
        $truncated = !empty($truncate['enabled'])
            && trim($text) !== ''
            && (empty($truncate['automatic']) || $exceedsThreshold);
        $width = in_array((string) ($truncate['width'] ?? 'md'), ['sm', 'md', 'lg'], true)
            ? (string) $truncate['width']
            : 'md';

        return [
            'truncated' => $truncated,
            'full_text' => $text,
            'display_text' => $truncated ? mb_substr($text, 0, $threshold) . '...' : $text,
            'text_width_class' => 'datagrid-cell-text--' . $width,
            'tooltip' => $truncated && !empty($truncate['tooltip']),
            'copyable' => $truncated && !empty($truncate['copyable']),
        ];
    };
    $normalizeCell = static function (mixed $value, string $empty, array $truncate = []) use ($applyTruncate, $normalizeStackLine): array {
        $normalized = [
            'class' => '',
            'is_stack' => false,
            'primary' => '',
            'primary_class' => '',
            'primary_is_code' => false,
            'primary_truncated' => false,
            'primary_full_text' => '',
            'primary_display_text' => '',
            'primary_text_width_class' => 'datagrid-cell-text--md',
            'primary_tooltip' => false,
            'primary_copyable' => false,
            'has_secondary' => false,
            'secondary' => '',
            'secondary_class' => '',
            'secondary_is_code' => false,
            'secondary_truncated' => false,
            'secondary_full_text' => '',
            'secondary_display_text' => '',
            'secondary_text_width_class' => 'datagrid-cell-text--md',
            'secondary_tooltip' => false,
            'secondary_copyable' => false,
            'has_badges' => false,
            'badges' => [],
            'is_code' => false,
            'code_text' => '',
            'code_class' => '',
            'text' => $empty,
            'display_text' => $empty,
            'has_truncated_text' => false,
            'full_text' => '',
            'text_width_class' => 'datagrid-cell-text--md',
            'tooltip' => false,
            'copyable' => false,
            'copy_label' => __('ui.datagrid.copy'),
            'copied_label' => __('ui.datagrid.copied'),
        ];

        if ($value === null || $value === '') {
            return $normalized;
        }

        if (is_array($value)) {
            $kind = (string) ($value['kind'] ?? '');

            if ($kind === 'stack') {
                $normalized['is_stack'] = true;
                $normalized['primary'] = (string) ($value['primary'] ?? '');
                $normalized['primary_class'] = (string) ($value['primary_class'] ?? '');
                $normalized['primary_is_code'] = !empty($value['primary_is_code']);
                $normalized['secondary'] = (string) ($value['secondary'] ?? '');
                $normalized['secondary_class'] = (string) ($value['secondary_class'] ?? '');
                $normalized['secondary_is_code'] = !empty($value['secondary_is_code']);
                $normalized['has_secondary'] = trim($normalized['secondary']) !== '';
                $primary = $normalizeStackLine($normalized['primary'], $truncate);
                $secondary = $normalizeStackLine($normalized['secondary'], $truncate);
                $normalized['primary_truncated'] = $primary['truncated'];
                $normalized['primary_full_text'] = $primary['full_text'];
                $normalized['primary_display_text'] = $primary['display_text'];
                $normalized['primary_text_width_class'] = $primary['text_width_class'];
                $normalized['primary_tooltip'] = $primary['tooltip'];
                $normalized['primary_copyable'] = $primary['copyable'];
                $normalized['secondary_truncated'] = $secondary['truncated'];
                $normalized['secondary_full_text'] = $secondary['full_text'];
                $normalized['secondary_display_text'] = $secondary['display_text'];
                $normalized['secondary_text_width_class'] = $secondary['text_width_class'];
                $normalized['secondary_tooltip'] = $secondary['tooltip'];
                $normalized['secondary_copyable'] = $secondary['copyable'];

                return $normalized;
            }

            if ($kind === 'code') {
                $normalized['is_code'] = true;
                $normalized['code_text'] = (string) ($value['text'] ?? '');
                $normalized['code_class'] = (string) ($value['class'] ?? '');

                return $applyTruncate($normalized, $truncate);
            }

            if ($kind === 'badge') {
                $normalized['has_badges'] = true;
                $normalized['badges'] = [[
                    'label' => (string) ($value['label'] ?? ''),
                    'class' => (string) ($value['class'] ?? 'text-bg-light'),
                ]];

                return $normalized;
            }

            if ($kind === 'badges') {
                $badges = [];

                foreach ((array) ($value['items'] ?? []) as $badge) {
                    if (!is_array($badge)) {
                        continue;
                    }

                    $label = trim((string) ($badge['label'] ?? ''));
                    if ($label === '') {
                        continue;
                    }

                    $badges[] = [
                        'label' => $label,
                        'class' => (string) ($badge['class'] ?? 'text-bg-light'),
                    ];
                }

                $normalized['has_badges'] = $badges !== [];
                $normalized['badges'] = $badges;

                if ($badges !== []) {
                    $normalized['text'] = '';
                }

                return $normalized;
            }
        }

        $normalized['text'] = (string) $value;

        return $applyTruncate($normalized, $truncate);
    };

    foreach ((array) ($grid['exports'] ?? []) as $export) {
        $isPrint = !empty($export['is_print']);

        $exports[] = [
            'href' => (string) ($export['href'] ?? '#'),
            'class' => (string) ($export['class'] ?? 'dropdown-item'),
            'has_icon' => (string) ($export['icon'] ?? '') !== '',
            'icon' => (string) ($export['icon'] ?? ''),
            'label' => (string) ($export['label'] ?? __('ui.datagrid.export')),
            'is_print' => $isPrint,
        ];
    }

    foreach ((array) ($grid['filters'] ?? []) as $filter) {
        $options = [];
        foreach ((array) ($filter['options'] ?? []) as $option) {
            $optionValue = (string) ($option['value'] ?? '');
            $options[] = [
                'value' => $optionValue,
                'label' => (string) ($option['label'] ?? ''),
                'selected' => (string) ($filter['value'] ?? '') === $optionValue,
            ];
        }

        $filters[] = [
            'name' => (string) ($filter['name'] ?? ''),
            'label' => (string) ($filter['label'] ?? ''),
            'is_select' => ($filter['type'] ?? 'text') === 'select',
            'value' => (string) ($filter['value'] ?? ''),
            'placeholder' => (string) ($filter['placeholder'] ?? ''),
            'options' => $options,
        ];
    }

    $query = (array) ($grid['query'] ?? []);
    $baseUrl = (string) ($grid['base_url'] ?? '');

    $perPageOptions = array_values(array_filter(
        array_map('intval', (array) ($pagination['per_page_options'] ?? [])),
        static fn (int $value): bool => $value > 0
    ));

    $currentPerPage = max(1, (int) ($pagination['per_page'] ?? 10));

    if ($perPageOptions === []) {
        $perPageOptions = [$currentPerPage];
    }

    if (!in_array($currentPerPage, $perPageOptions, true)) {
        $perPageOptions[] = $currentPerPage;
        sort($perPageOptions);
    }

    foreach ($perPageOptions as $perPage) {
        $queryForPerPage = $query;
        $queryForPerPage['per_page'] = $perPage;
        $queryForPerPage['page'] = 1;

        $queryString = http_build_query($queryForPerPage);

        $pages[] = [
            'value' => $perPage,
            'url' => $queryString === '' ? $baseUrl : $baseUrl . '?' . $queryString,
            'selected' => $currentPerPage === $perPage,
        ];
    }

    $normalizedRows = [];
    foreach ($rawRows as $row) {
        $cells = [];
        foreach ((array) ($row['cells'] ?? []) as $cell) {
            $value = $cell['value'] ?? null;
            $empty = (string) ($cell['empty'] ?? '—');
            $truncate = (array) ($cell['truncate'] ?? []);
            if (!empty($truncate['automatic'])) {
                $truncate['threshold'] = $visibleCharacterLimit;
            }
            $normalizedCell = $normalizeCell($value, $empty, $truncate);
            $normalizedCell['class'] = (string) ($cell['class'] ?? '');
            $cells[] = $normalizedCell;
        }

        $actions = [];
        foreach ((array) ($row['actions'] ?? []) as $action) {
            $method = strtoupper((string) ($action['method'] ?? 'GET'));
            $confirm = (string) ($action['confirm'] ?? '');
            $actions[] = [
                'is_post' => $method === 'POST',
                'href' => (string) ($action['href'] ?? '#'),
                'method' => strtolower($method),
                'class' => $normalizeButtonClass((string) ($action['class'] ?? 'btn btn-outline-secondary btn-sm')),
                'has_confirm' => $confirm !== '',
                'confirm' => $confirm,
                'has_icon' => (string) ($action['icon'] ?? '') !== '',
                'icon' => (string) ($action['icon'] ?? ''),
                'label' => (string) ($action['label'] ?? __('ui.datagrid.action')),
                'csrf_field' => $csrfField,
            ];
        }

        $normalizedRows[] = [
            'has_checkbox' => ($row['key'] ?? null) !== null && ($row['key'] ?? '') !== '',
            'key' => (string) ($row['key'] ?? ''),
            'cells' => $cells,
            'actions' => $actions,
            'has_actions' => $actions !== [],
        ];
    }

    $paginationPages = [];
    foreach ((array) ($pagination['pages'] ?? []) as $page) {
        $paginationPages[] = [
            'url' => (string) ($page['url'] ?? '#'),
            'page' => (int) ($page['page'] ?? 1),
            'active' => !empty($page['active']),
        ];
    }

    $normalizedBulkActions = [];
    foreach ($bulkActions as $action) {
        $confirm = (string) ($action['confirm'] ?? '');
        $normalizedBulkActions[] = [
            'href' => (string) ($action['href'] ?? '#'),
            'method' => strtolower((string) ($action['method'] ?? 'post')),
            'class' => $normalizeButtonClass((string) ($action['class'] ?? 'btn btn-outline-danger btn-sm'), 'btn btn-outline-danger btn-sm'),
            'has_confirm' => $confirm !== '',
            'confirm' => $confirm,
            'has_icon' => (string) ($action['icon'] ?? '') !== '',
            'icon' => (string) ($action['icon'] ?? ''),
            'label' => (string) ($action['label'] ?? __('ui.datagrid.bulk_action')),
            'form_id' => $gridBulkFormId,
        ];
    }

    $normalizedRows = array_map(
        static fn (array $row): array => array_merge($row, [
            'row_has_bulk' => $gridHasBulk,
            'row_has_action_slot' => $gridHasRowActions,
            'row_bulk_form_id' => $gridBulkFormId,
            'row_bulk_name' => $bulkName,
        ]),
        $normalizedRows
    );

        return [
        'grid_total' => $total,
        'grid_title' => (string) ($grid['title'] ?? __('ui.datagrid.listing')),
        'grid_subtitle' => (string) ($grid['subtitle'] ?? ''),
        'grid_base_url' => (string) ($grid['base_url'] ?? ''),
        'grid_is_empty' => $total === 0,
        'grid_table_body_class' => !empty($grid['inset_table']) ? 'card-body' : 'card-body p-0',
        'grid_table_scroll_class' => implode(' ', $tableScrollClasses),
        'grid_table_class' => implode(' ', $tableClasses),
        'grid_visible_column_count' => $visibleColumnCount,
        'grid_visible_character_limit' => $visibleCharacterLimit,
        'grid_has_bulk' => $gridHasBulk,
        'grid_has_row_actions' => $gridHasRowActions,
        'grid_bulk_name' => $bulkName,
        'grid_bulk_form_id' => $gridBulkFormId,
        'grid_records_label' => __('ui.datagrid.records', ['count' => $total]),
        'grid_search_name' => (string) ($grid['search']['name'] ?? 'q'),
        'grid_search_value' => (string) ($grid['search']['value'] ?? ''),
        'grid_search_placeholder' => (string) ($grid['search']['placeholder'] ?? __('ui.datagrid.search')),
        'grid_query_sort' => (string) ($grid['query']['sort'] ?? ''),
        'grid_query_direction' => (string) ($grid['query']['direction'] ?? ''),
        'grid_empty_title' => (string) ($grid['empty_title'] ?? __('ui.datagrid.no_records_title')),
        'grid_empty_message' => (string) ($grid['empty_message'] ?? ''),
        'grid_empty_action' => is_array($grid['empty_action'] ?? null) ? [
            'href' => (string) ($grid['empty_action']['href'] ?? '#'),
            'class' => $normalizeButtonClass((string) ($grid['empty_action']['class'] ?? 'btn btn-primary'), 'btn btn-primary btn-sm'),
            'has_icon' => (string) ($grid['empty_action']['icon'] ?? '') !== '',
            'icon' => (string) ($grid['empty_action']['icon'] ?? ''),
            'label' => (string) ($grid['empty_action']['label'] ?? __('ui.datagrid.create')),
        ] : null,
        'grid_exports' => $exports,
        'grid_has_tools' => $exports !== [],
        'grid_filters' => $filters,
        'grid_per_page_options' => $pages,
        'grid_current_per_page' => (int) ($pagination['per_page'] ?? 10),
        'grid_columns' => array_map(static fn (array $column): array => [
            'header_class' => (string) ($column['header_class'] ?? ''),
            'sortable' => !empty($column['sortable']) && !empty($column['sort_url']),
            'sort_url' => (string) ($column['sort_url'] ?? '#'),
            'label' => (string) ($column['label'] ?? ''),
            'sort_active' => !empty($column['sort_active']),
            'sort_icon_class' => (string) ($column['sort_direction'] ?? 'asc') === 'desc'
                ? 'ti-arrow-down'
                : 'ti-arrow-up',
        ], (array) ($grid['columns'] ?? [])),
        'grid_rows' => $normalizedRows,
        'grid_bulk_actions' => $normalizedBulkActions,
        'grid_bulk_csrf_field' => $csrfField,
        'grid_selection_empty_label' => __('ui.datagrid.no_rows_selected'),
        'grid_selection_template_label' => __('ui.datagrid.rows_selected'),
        'grid_pagination_summary' => __(
            'ui.datagrid.pagination_summary',
            [
                'from' => (int) ($pagination['from'] ?? 0),
                'to' => (int) ($pagination['to'] ?? 0),
                'total' => (int) ($pagination['total'] ?? 0),
            ]
        ),
        'grid_first_url' => (string) ($pagination['first_url'] ?? '#'),
        'grid_first_disabled' => empty($pagination['first_url']),

        'grid_prev_url' => (string) ($pagination['prev_url'] ?? '#'),
        'grid_prev_disabled' => empty($pagination['prev_url']),

        'grid_next_url' => (string) ($pagination['next_url'] ?? '#'),
        'grid_next_disabled' => empty($pagination['next_url']),

        'grid_last_url' => (string) ($pagination['last_url'] ?? '#'),
        'grid_last_disabled' => empty($pagination['last_url']),

        'grid_pages' => $paginationPages,
        ];
    }
}
