# Catalyst Framework DataGrid

## Purpose

Document the current DataGrid runtime as a public facade with focused collaborator classes.

## Runtime Owners

| Concern | Owner |
|---|---|
| Public grid facade | `Catalyst\Framework\DataGrid\DataGrid` |
| State resolution | `Catalyst\Framework\DataGrid\DataGridStateResolver` |
| Columns, filters and rows | `DataGridColumnNormalizer`, `DataGridFilterNormalizer`, `DataGridRowNormalizer` |
| Row and bulk actions | `DataGridRowActionNormalizer`, `DataGridBulkActionNormalizer` |
| Pagination and URLs | `DataGridPaginationBuilder`, `DataGridUrlBuilder` |
| CSV/XLS exports | `DataGridCsvExporter`, `DataGridHtmlExportRenderer` |
| Export toolbar | `DataGridExportNormalizer` |
| View normalization | `DataGridViewModel` |
| Shared template | `boot-core/template/components/_datagrid.phtml` |
| Shared toolbar template | `boot-core/template/components/_datagrid-toolbar.phtml` |
| Runtime interactions | `public/assets/js/catalyst/datagrid/interactions.js` through the central UI runtime |
| Shared styles | `public/assets/css/catalyst/datagrid.css` |

## Current Behavior

`DataGrid` remains the public controller-facing API. It coordinates configuration, request state, provider results, presentation metadata and CSV/XLS responses while delegating normalization and export rendering to collaborators. `DataGridViewModel` owns the render contract used by the thin template companion. XLS-compatible HTML export is rendered from `boot-core/template/exports/datagrid-xls.phtml`.

The shared toolbar partial is rendered above and below every non-empty grid.
Both instances expose the same tools, page-size selector, range summary and
complete pagination controls. Their inner dividers and controls use the same
horizontal inset as the table; consumers cannot redefine either toolbar.

Plain-text and code cells longer than the current visible-character limit
automatically use the global compact-cell presentation. The limit is `35` for
up to six rendered columns, decreases by five for every additional rendered
column, and never falls below `15`. Selection and action columns participate in
the count. The visible value is exactly the calculated limit followed by `...`;
the complete value remains available through an accessible Bootstrap tooltip
and an explicit copy button, and exports always receive the original value.
Consumers do not enable this behavior during view construction. Exceptional
columns may set `truncate => false` to disable it or
`truncate => ['enabled' => true]` to force it below the global threshold.
Structured `stack` values preserve their two-line composition while evaluating
their primary and secondary text independently against the same global rule.
The central interaction runtime uses Clipboard API first and falls back to a
temporary accessible selection when browser permissions or context restrictions
reject the primary API.

The canonical UI runtime entry is cache-busted from the complete published
`/assets/js/catalyst` dependency tree. Changes to lazy DataGrid interactions
therefore invalidate existing browser module caches without surface-specific
scripts or manual asset URLs.

Grids with at least 11 rendered columns force horizontal scrolling through the
shared Bootstrap responsive container. Grids with at least 16 rendered columns
and more than 15 visible rows also receive a bounded vertical scroll region and
a sticky header. These policies are calculated globally and require no
consumer-specific CSS or JavaScript.

## Operational Notes

Controllers should use `DataGrid`, not internal normalizers directly. Consumers render `components._datagrid`. Selection, `per_page` and print behavior are mounted by the central UI runtime and support later DOM scans without autonomous initializers.

## Related Documentation

- `docs/framework-view.md`
- `docs/security-conventions.md`
- `docs/runtime-inventory.md`
