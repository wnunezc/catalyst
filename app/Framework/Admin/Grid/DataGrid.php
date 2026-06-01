<?php

declare(strict_types=1);

namespace Catalyst\Framework\Admin\Grid;

use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use InvalidArgumentException;
use RuntimeException;

final class DataGrid
{
    /**
     * @var array<string, mixed>
     */
    private array $config = [
        'base_url' => '',
        'title' => '',
        'subtitle' => '',
        'empty_title' => 'No records found',
        'empty_message' => 'No records match the current criteria.',
        'empty_action' => null,
        'columns' => [],
        'filters' => [],
        'actions' => [],
        'bulk_actions' => [],
        'export_formats' => [],
        'row_key' => 'id',
        'default_sort' => 'id',
        'default_direction' => 'asc',
        'per_page' => 10,
        'per_page_options' => [10, 25, 50],
        'search_placeholder' => 'Search',
        'search_param' => 'q',
        'query_param_page' => 'page',
        'query_param_per_page' => 'per_page',
        'query_param_export' => 'export',
        'export_filename' => 'grid-export',
        'print_enabled' => false,
        'print_label' => 'Print',
        'print_icon' => 'fa-solid fa-print',
        'bulk_name' => 'selected',
    ];

    /**
     * @var callable|null
     */
    private $provider = null;

    private DataGridUrlBuilder $urlBuilder;

    private DataGridTextFormatter $textFormatter;

    private DataGridCsvExporter $csvExporter;

    private DataGridStateResolver $stateResolver;

    private DataGridFilterNormalizer $filterNormalizer;

    private DataGridExportNormalizer $exportNormalizer;

    private DataGridBulkActionNormalizer $bulkActionNormalizer;

    private DataGridPaginationBuilder $paginationBuilder;

    private DataGridColumnNormalizer $columnNormalizer;

    private DataGridRowActionNormalizer $rowActionNormalizer;

    private DataGridRowNormalizer $rowNormalizer;

    public function __construct(
        ?DataGridUrlBuilder $urlBuilder = null,
        ?DataGridTextFormatter $textFormatter = null,
        ?DataGridCsvExporter $csvExporter = null,
        ?DataGridStateResolver $stateResolver = null,
        ?DataGridFilterNormalizer $filterNormalizer = null,
        ?DataGridExportNormalizer $exportNormalizer = null,
        ?DataGridBulkActionNormalizer $bulkActionNormalizer = null,
        ?DataGridPaginationBuilder $paginationBuilder = null,
        ?DataGridColumnNormalizer $columnNormalizer = null,
        ?DataGridRowActionNormalizer $rowActionNormalizer = null,
        ?DataGridRowNormalizer $rowNormalizer = null
    ) {
        $this->urlBuilder = $urlBuilder ?? new DataGridUrlBuilder();
        $this->textFormatter = $textFormatter ?? new DataGridTextFormatter();
        $this->csvExporter = $csvExporter ?? new DataGridCsvExporter();
        $this->stateResolver = $stateResolver ?? new DataGridStateResolver();

        $this->filterNormalizer = $filterNormalizer
            ?? new DataGridFilterNormalizer($this->textFormatter);

        $this->exportNormalizer = $exportNormalizer
            ?? new DataGridExportNormalizer($this->urlBuilder);

        $this->bulkActionNormalizer = $bulkActionNormalizer
            ?? new DataGridBulkActionNormalizer($this->urlBuilder);

        $this->paginationBuilder = $paginationBuilder
            ?? new DataGridPaginationBuilder($this->urlBuilder);

        $this->columnNormalizer = $columnNormalizer
            ?? new DataGridColumnNormalizer($this->urlBuilder, $this->textFormatter);

        $this->rowActionNormalizer = $rowActionNormalizer
            ?? new DataGridRowActionNormalizer();

        $this->rowNormalizer = $rowNormalizer
            ?? new DataGridRowNormalizer($this->rowActionNormalizer);
    }

    public static function make(): self
    {
        return new self();
    }

    /**
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    public static function stack(string $primary, ?string $secondary = null, array $options = []): array
    {
        return [
            'kind' => 'stack',
            'primary' => $primary,
            'primary_class' => (string) ($options['primary_class'] ?? 'fw-semibold'),
            'primary_is_code' => !empty($options['primary_is_code']),
            'secondary' => $secondary ?? '',
            'secondary_class' => (string) ($options['secondary_class'] ?? 'small text-muted'),
            'secondary_is_code' => !empty($options['secondary_is_code']),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function code(string $text, string $class = ''): array
    {
        return [
            'kind' => 'code',
            'text' => $text,
            'class' => $class,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function badge(string $label, string $class = 'text-bg-light'): array
    {
        return [
            'kind' => 'badge',
            'label' => $label,
            'class' => $class,
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $badges
     * @return array<string, mixed>
     */
    public static function badges(array $badges): array
    {
        return [
            'kind' => 'badges',
            'items' => array_values(array_filter(array_map(
                static fn (array $badge): array => [
                    'label' => (string) ($badge['label'] ?? ''),
                    'class' => (string) ($badge['class'] ?? 'text-bg-light'),
                ],
                $badges
            ), static fn (array $badge): bool => $badge['label'] !== '')),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function booleanBadge(
        bool $value,
        string $trueLabel,
        string $falseLabel,
        string $trueClass = 'text-bg-success',
        string $falseClass = 'text-bg-secondary'
    ): array {
        return self::badge(
            $value ? $trueLabel : $falseLabel,
            $value ? $trueClass : $falseClass
        );
    }

    public function baseUrl(string $baseUrl): self
    {
        $this->config['base_url'] = $baseUrl;

        return $this;
    }

    public function title(string $title, string $subtitle = ''): self
    {
        $this->config['title'] = $title;
        $this->config['subtitle'] = $subtitle;

        return $this;
    }

    public function emptyState(string $title, string $message, ?array $action = null): self
    {
        $this->config['empty_title'] = $title;
        $this->config['empty_message'] = $message;
        $this->config['empty_action'] = $action;

        return $this;
    }

    /**
     * @param array<int, array<string, mixed>> $columns
     */
    public function columns(array $columns): self
    {
        $this->config['columns'] = $columns;

        return $this;
    }

    /**
     * @param array<int, array<string, mixed>> $filters
     */
    public function filters(array $filters): self
    {
        $this->config['filters'] = $filters;

        return $this;
    }

    /**
     * @param array<int, array<string, mixed>> $actions
     */
    public function actions(array $actions): self
    {
        $this->config['actions'] = $actions;

        return $this;
    }

    /**
     * @param array<int, array<string, mixed>> $actions
     */
    public function bulkActions(array $actions): self
    {
        $this->config['bulk_actions'] = $actions;

        return $this;
    }

    /**
     * @param array<int|string, string|array<string, mixed>> $formats
     */
    public function exportFormats(array $formats, ?string $filename = null): self
    {
        $this->config['export_formats'] = $formats;

        if ($filename !== null) {
            $this->config['export_filename'] = $filename;
        }

        return $this;
    }

    public function resourceKey(string $resourceKey): self
    {
        $this->config['resource_key'] = trim($resourceKey);

        return $this;
    }

    public function rowKey(string $rowKey): self
    {
        $this->config['row_key'] = trim($rowKey);

        return $this;
    }

    public function defaultSort(string $column, string $direction = 'asc'): self
    {
        $this->config['default_sort'] = $column;
        $this->config['default_direction'] = strtolower($direction) === 'desc' ? 'desc' : 'asc';

        return $this;
    }

    /**
     * @param int[] $options
     */
    public function pagination(int $perPage, array $options = [10, 25, 50]): self
    {
        $this->config['per_page'] = $perPage;
        $this->config['per_page_options'] = array_values(array_filter(
            array_map('intval', $options),
            static fn (int $value): bool => $value > 0
        ));

        return $this;
    }

    public function searchPlaceholder(string $placeholder): self
    {
        $this->config['search_placeholder'] = $placeholder;

        return $this;
    }

    public function provider(callable $provider): self
    {
        $this->provider = $provider;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function state(Request $request): array
    {
        return $this->resolveState($request);
    }

    public function exportFormat(Request $request): ?string
    {
        $requested = trim((string) ($request->get((string) $this->config['query_param_export'], '') ?? ''));
        $available = $this->normalizeExportFormats();

        return array_key_exists($requested, $available) ? $requested : null;
    }

    public function printEnabled(bool $enabled = true, ?string $label = null): self
    {
        $this->config['print_enabled'] = $enabled;

        if ($label !== null) {
            $this->config['print_label'] = $label;
        }

        return $this;
    }

    public function exportCsv(Request $request): Response
    {
        return $this->export($request);
    }

    public function export(Request $request): Response
    {
        $format = $this->exportFormat($request);

        if (!in_array($format, ['csv', 'xls'], true)) {
            throw new InvalidArgumentException('DataGrid export format is not supported.');
        }

        if (!is_callable($this->provider)) {
            throw new RuntimeException('DataGrid provider is required.');
        }

        $state = $this->resolveState($request);
        $state['page'] = 1;
        $state['per_page'] = max((int) $state['per_page'], 5000);
        $state['export'] = $format;

        $result = ($this->provider)($state);

        if (!is_array($result) || !isset($result['rows'])) {
            throw new InvalidArgumentException('DataGrid export provider must return rows.');
        }

        $rows = is_array($result['rows']) ? $result['rows'] : [];

        return match ($format) {
            'csv' => $this->exportCsvResponse($rows, $state),
            'xls' => $this->exportXlsResponse($rows, $state),
            default => throw new InvalidArgumentException('DataGrid export format is not supported.'),
        };
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     * @param array<string, mixed> $state
     * @return array{filename:string,contents:string}
     */
    public function exportCsvRows(array $rows, array $state = []): array
    {
        $state = array_merge([
            'page' => 1,
            'per_page' => count($rows),
            'sort' => (string) ($this->config['default_sort'] ?? 'id'),
            'direction' => (string) ($this->config['default_direction'] ?? 'asc'),
            'search' => '',
            'filters' => [],
            'query' => [],
        ], $state);

        $columns = $this->normalizeColumns($state);

        $headers = array_map(
            static fn (array $column): string => (string) ($column['label'] ?? ''),
            $columns
        );

        $csvRows = [];

        foreach ($rows as $row) {
            $row = $this->rowNormalizer->sanitizeExportRow((array) $row, $this->config);
            $line = [];

            foreach ((array) ($this->config['columns'] ?? []) as $column) {
                $value = $this->rowNormalizer->resolveCellValue(
                    (array) $row,
                    (array) $state,
                    (array) $column
                );

                if (is_array($value)) {
                    $value = $this->rowNormalizer->stringifyStructuredValue($value);
                }

                $line[] = strip_tags((string) ($value ?? ''));
            }

            $csvRows[] = $line;
        }

        return [
            'filename' => $this->textFormatter->slugify(
                    (string) ($this->config['export_filename'] ?? 'grid-export')
                ) . '.csv',
            'contents' => $this->csvExporter->export($headers, $csvRows),
        ];
    }

    /**
     * Export rows as an Excel-compatible HTML table with .xls extension.
     *
     * This is intentionally not a real XLSX document. It is an HTML table served
     * with the Excel MIME type, which is enough for alpha/RC local usage without
     * adding PhpSpreadsheet yet.
     *
     * @param array<int, array<string, mixed>> $rows
     * @param array<string, mixed> $state
     * @return array{filename:string,contents:string}
     */
    public function exportXlsRows(array $rows, array $state = []): array
    {
        $state = array_merge([
            'page' => 1,
            'per_page' => count($rows),
            'sort' => (string) ($this->config['default_sort'] ?? 'id'),
            'direction' => (string) ($this->config['default_direction'] ?? 'asc'),
            'search' => '',
            'filters' => [],
            'query' => [],
        ], $state);

        $columns = $this->normalizeColumns($state);

        $html = [];
        $html[] = '<!DOCTYPE html>';
        $html[] = '<html>';
        $html[] = '<head>';
        $html[] = '<meta charset="UTF-8">';
        $html[] = '</head>';
        $html[] = '<body>';
        $html[] = '<table border="1">';
        $html[] = '<thead>';
        $html[] = '<tr>';

        foreach ($columns as $column) {
            $html[] = '<th>' . htmlspecialchars((string) ($column['label'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</th>';
        }

        $html[] = '</tr>';
        $html[] = '</thead>';
        $html[] = '<tbody>';

        foreach ($rows as $row) {
            $row = $this->rowNormalizer->sanitizeExportRow((array) $row, $this->config);
            $html[] = '<tr>';

            foreach ((array) ($this->config['columns'] ?? []) as $column) {
                $value = $this->rowNormalizer->resolveCellValue(
                    (array) $row,
                    (array) $state,
                    (array) $column
                );

                if (is_array($value)) {
                    $value = $this->rowNormalizer->stringifyStructuredValue($value);
                }

                $html[] = '<td>' . htmlspecialchars(
                        strip_tags((string) ($value ?? '')),
                        ENT_QUOTES | ENT_SUBSTITUTE,
                        'UTF-8'
                    ) . '</td>';
            }

            $html[] = '</tr>';
        }

        $html[] = '</tbody>';
        $html[] = '</table>';
        $html[] = '</body>';
        $html[] = '</html>';

        return [
            'filename' => $this->textFormatter->slugify(
                    (string) ($this->config['export_filename'] ?? 'grid-export')
                ) . '.xls',
            'contents' => implode(PHP_EOL, $html),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function resolve(Request $request): array
    {
        if (!is_callable($this->provider)) {
            throw new RuntimeException('DataGrid provider is required.');
        }

        $state = $this->resolveState($request);
        $result = ($this->provider)($state);

        if (!is_array($result) || !isset($result['rows'], $result['total'])) {
            throw new InvalidArgumentException('DataGrid provider must return rows and total.');
        }

        $rows = is_array($result['rows']) ? $result['rows'] : [];
        $total = max(0, (int) $result['total']);
        $pagination = $this->buildPagination($state, $total);
        $filters = $this->normalizeFilters($state);
        $columns = $this->normalizeColumns($state);

        return [
            'base_url' => (string) $this->config['base_url'],
            'title' => (string) $this->config['title'],
            'subtitle' => (string) $this->config['subtitle'],
            'columns' => $columns,
            'rows' => $this->normalizeRows($rows, $state),
            'filters' => $filters,
            'search' => [
                'name' => (string) $this->config['search_param'],
                'value' => $state['search'],
                'placeholder' => (string) $this->config['search_placeholder'],
            ],
            'pagination' => $pagination,
            'query' => $state['query'],
            'total' => $total,
            'empty_title' => (string) $this->config['empty_title'],
            'empty_message' => (string) $this->config['empty_message'],
            'empty_action' => $this->normalizeEmptyAction(),
            'bulk' => [
                'name' => (string) ($this->config['bulk_name'] ?? 'selected'),
                'actions' => $this->normalizeBulkActions($state),
            ],
            'exports' => $this->normalizeExports($state),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveState(Request $request): array
    {
        return $this->stateResolver->resolve($request, $this->config);
    }

    /**
     * @param array<string, mixed> $state
     * @return array<int, array<string, mixed>>
     */
    private function normalizeColumns(array $state): array
    {
        return $this->columnNormalizer->normalize(
            (array) ($this->config['columns'] ?? []),
            $state,
            $this->config
        );
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     * @param array<string, mixed> $state
     * @return array<int, array<string, mixed>>
     */
    private function normalizeRows(array $rows, array $state): array
    {
        return $this->rowNormalizer->normalize(
            $rows,
            $state,
            $this->config
        );
    }

    /**
     * @param array<string, mixed> $state
     * @return array<int, array<string, mixed>>
     */
    private function normalizeFilters(array $state): array
    {
        return $this->filterNormalizer->normalize(
            (array) ($this->config['filters'] ?? []),
            $state
        );
    }

    /**
     * @param array<string, mixed> $state
     * @return array<string, mixed>
     */
    private function buildPagination(array $state, int $total): array
    {
        return $this->paginationBuilder->build(
            $state,
            $total,
            $this->config
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    private function normalizeEmptyAction(): ?array
    {
        $action = $this->config['empty_action'] ?? null;

        if (!is_array($action) || $action === []) {
            return null;
        }

        return [
            'label' => (string) ($action['label'] ?? ''),
            'href' => (string) ($action['href'] ?? '#'),
            'class' => (string) ($action['class'] ?? 'btn btn-primary'),
            'icon' => (string) ($action['icon'] ?? ''),
        ];
    }

    /**
     * @param array<string, mixed> $state
     * @return array<int, array<string, mixed>>
     */
    private function normalizeBulkActions(array $state): array
    {
        return $this->bulkActionNormalizer->normalize(
            (array) ($this->config['bulk_actions'] ?? []),
            $state,
            $this->config
        );
    }

    /**
     * @param array<string, mixed> $state
     * @return array<int, array<string, mixed>>
     */
    private function normalizeExports(array $state): array
    {
        return $this->exportNormalizer->normalize(
            (array) ($this->config['export_formats'] ?? []),
            $state,
            $this->config
        );
    }

    /**
     * @return array<string, array<string, string>>
     */
    private function normalizeExportFormats(): array
    {
        $normalized = [];

        foreach ((array) ($this->config['export_formats'] ?? []) as $format => $definition) {
            if (is_array($definition)) {
                $key = is_string($format) ? trim($format) : trim((string) ($definition['format'] ?? ''));
                if ($key === '') {
                    continue;
                }

                $normalized[$key] = [
                    'label' => (string) ($definition['label'] ?? strtoupper($key)),
                    'class' => (string) ($definition['class'] ?? 'btn btn-outline-secondary btn-sm'),
                    'icon' => (string) ($definition['icon'] ?? 'fa-solid fa-download'),
                ];
                continue;
            }

            $key = is_string($format) ? trim($format) : strtolower(trim((string) $definition));
            if ($key === '') {
                continue;
            }

            $normalized[$key] = [
                'label' => is_string($definition) && !is_numeric($format)
                    ? $definition
                    : strtoupper($key),
                'class' => 'btn btn-outline-secondary btn-sm',
                'icon' => 'fa-solid fa-download',
            ];
        }

        return $normalized;
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     * @param array<string, mixed> $state
     */
    private function exportCsvResponse(array $rows, array $state): Response
    {
        $export = $this->exportCsvRows($rows, $state);

        return new Response(
            (string) ($export['contents'] ?? ''),
            200,
            [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . (string) ($export['filename'] ?? 'grid-export.csv') . '"',
            ]
        );
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     * @param array<string, mixed> $state
     */
    private function exportXlsResponse(array $rows, array $state): Response
    {
        $export = $this->exportXlsRows($rows, $state);

        return new Response(
            (string) ($export['contents'] ?? ''),
            200,
            [
                'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . (string) ($export['filename'] ?? 'grid-export.xls') . '"',
            ]
        );
    }
}
