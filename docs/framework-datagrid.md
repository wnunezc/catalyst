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
| Runtime interactions | `public/assets/js/catalyst/datagrid/interactions.js` through the central UI runtime |
| Shared styles | `public/assets/css/catalyst/datagrid.css` |

## Current Behavior

`DataGrid` remains the public controller-facing API. It coordinates configuration, request state, provider results, presentation metadata and CSV/XLS responses while delegating normalization and export rendering to collaborators. `DataGridViewModel` owns the render contract used by the thin template companion. XLS-compatible HTML export is rendered from `boot-core/template/exports/datagrid-xls.phtml`.

## Operational Notes

Controllers should use `DataGrid`, not internal normalizers directly. Consumers render `components._datagrid`. Selection, `per_page` and print behavior are mounted by the central UI runtime and support later DOM scans without autonomous initializers.

## Related Documentation

- `docs/framework-view.md`
- `docs/security-conventions.md`
- `docs/runtime-inventory.md`
