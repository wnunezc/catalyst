(function () {
    const grids = document.querySelectorAll('[data-admin-grid]');

    if (!grids.length) {
        return;
    }

    const hasDataTables = () => typeof window.jQuery === 'function' && typeof window.jQuery.fn?.DataTable === 'function';

    const hasConsistentDomTableShape = (table) => {
        const headers = Array.from(table.querySelectorAll('thead tr:first-child th'));
        const headerCount = headers.length;

        if (headerCount === 0) {
            return false;
        }

        return Array.from(table.querySelectorAll('tbody tr')).every((row) => {
            const hasSpan = row.querySelector('[colspan], [rowspan]') !== null;
            const cellCount = Array.from(row.children).filter((cell) => (
                cell instanceof HTMLTableCellElement
            )).length;

            return !hasSpan && cellCount === headerCount;
        });
    };

    const initDataTablesBridge = (grid) => {
        if (!hasDataTables()) {
            return;
        }

        if (window.jQuery?.fn?.dataTable?.ext?.errMode !== undefined) {
            window.jQuery.fn.dataTable.ext.errMode = 'none';
        }

        const tables = grid.querySelectorAll('table[data-catalyst-datatable="progressive"]');

        tables.forEach((table) => {
            if (!(table instanceof HTMLTableElement) || table.dataset.datatableReady === '1') {
                return;
            }

            if (!hasConsistentDomTableShape(table)) {
                table.dataset.datatableReady = '0';
                table.dataset.datatableSkipped = 'shape-mismatch';
                if (window.console && typeof window.console.warn === 'function') {
                    window.console.warn('Catalyst DataGrid DataTables bridge skipped: inconsistent table column count.', table);
                }
                return;
            }

            const $table = window.jQuery(table);

            try {
                $table.DataTable({
                    paging: false,
                    searching: false,
                    ordering: false,
                    info: false,
                    responsive: true,
                    autoWidth: false,
                    retrieve: true,
                    language: {
                        emptyTable: table.dataset.emptyLabel || 'No records available'
                    },
                    columnDefs: [
                        { targets: '_all', defaultContent: '' }
                    ],
                    layout: {
                        topStart: null,
                        topEnd: null,
                        bottomStart: null,
                        bottomEnd: null
                    }
                });
                table.dataset.datatableReady = '1';
            } catch (error) {
                table.dataset.datatableReady = '0';
                if (window.console && typeof window.console.warn === 'function') {
                    window.console.warn('Catalyst DataGrid DataTables bridge skipped:', error);
                }
            }
        });
    };

    const syncGrid = (grid) => {
        const checkboxes = Array.from(grid.querySelectorAll('[data-grid-row-checkbox]'));
        const selectedCount = checkboxes.filter((checkbox) => checkbox.checked).length;
        const selectAll = grid.querySelector('[data-grid-select-all]');
        const summary = grid.querySelector('[data-grid-selection-summary]');
        const actions = grid.querySelectorAll('[data-grid-bulk-action]');
        const summaryEmpty = grid.dataset.gridSelectionEmpty || 'No rows selected';
        const summaryTemplate = grid.dataset.gridSelectionTemplate || ':count row(s) selected';

        if (selectAll instanceof HTMLInputElement) {
            selectAll.checked = checkboxes.length > 0 && selectedCount === checkboxes.length;
            selectAll.indeterminate = selectedCount > 0 && selectedCount < checkboxes.length;
        }

        if (summary) {
            summary.textContent = selectedCount > 0
                ? summaryTemplate.replace(':count', String(selectedCount))
                : summaryEmpty;
        }

        actions.forEach((action) => {
            if (action instanceof HTMLButtonElement) {
                action.disabled = selectedCount === 0;
            }
        });
    };

    grids.forEach((grid) => {
        const selectAll = grid.querySelector('[data-grid-select-all]');

        initDataTablesBridge(grid);

        if (selectAll instanceof HTMLInputElement) {
            selectAll.addEventListener('change', () => {
                grid.querySelectorAll('[data-grid-row-checkbox]').forEach((checkbox) => {
                    if (checkbox instanceof HTMLInputElement) {
                        checkbox.checked = selectAll.checked;
                    }
                });

                syncGrid(grid);
            });
        }

        grid.addEventListener('change', (event) => {
            const target = event.target;

            if (target instanceof HTMLInputElement && target.matches('[data-grid-row-checkbox]')) {
                syncGrid(grid);
                return;
            }

            if (target instanceof HTMLSelectElement && target.matches('[data-grid-per-page]')) {
                const form = target.closest('form');

                if (form instanceof HTMLFormElement) {
                    form.submit();
                }
            }
        });

        grid.addEventListener('click', (event) => {
            const target = event.target;

            if (!(target instanceof Element)) {
                return;
            }

            const printTrigger = target.closest('[data-grid-print]');

            if (printTrigger) {
                event.preventDefault();
                window.print();
            }
        });

        syncGrid(grid);
    });
})();
