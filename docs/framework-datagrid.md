# Catalyst Framework DataGrid

## Purpose

Document the current DataGrid runtime as a public facade with focused collaborator classes.

## Runtime Owners

| Concern | Owner |
|---|---|
| Public grid facade | `Catalyst\Framework\Admin\Grid\DataGrid` |
| State resolution | `Catalyst\Framework\Admin\Grid\DataGridStateResolver` |
| Columns, filters and rows | `DataGridColumnNormalizer`, `DataGridFilterNormalizer`, `DataGridRowNormalizer` |
| Row and bulk actions | `DataGridRowActionNormalizer`, `DataGridBulkActionNormalizer` |
| Pagination and URLs | `DataGridPaginationBuilder`, `DataGridUrlBuilder` |
| CSV/XLS exports | `DataGridCsvExporter`, `DataGridHtmlExportRenderer` |
| Export toolbar | `DataGridExportNormalizer` |

## Current Behavior

`DataGrid` remains the public controller-facing API. It coordinates configuration, request state, provider results, presentation metadata and CSV/XLS responses while delegating normalization and export rendering to collaborators. XLS-compatible HTML export is rendered from a tokenized template instead of manually concatenating HTML in the facade.

## Operational Notes

Controllers should use `DataGrid`, not internal normalizers directly. View/CSP behavior for grid toolbar actions is covered by `docs/framework-view.md` and `docs/security-conventions.md`.

## Related Documentation

- `docs/framework-view.md`
- `docs/security-conventions.md`
- `docs/runtime-inventory.md`