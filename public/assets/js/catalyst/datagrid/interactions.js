const interactionRoots = new WeakSet();

function resolveGrid(target) {
    return target instanceof Element ? target.closest('[data-datagrid]') : null;
}

function updateSelection(grid) {
    const checkboxes = Array.from(grid.querySelectorAll('[data-grid-row-checkbox]'));
    const selected = checkboxes.filter((checkbox) => checkbox.checked);
    const selectAll = grid.querySelector('[data-grid-select-all]');
    const summary = grid.querySelector('[data-grid-selection-summary]');
    const actions = grid.querySelectorAll('[data-grid-bulk-action]');

    if (selectAll instanceof HTMLInputElement) {
        selectAll.checked = checkboxes.length > 0 && selected.length === checkboxes.length;
        selectAll.indeterminate = selected.length > 0 && selected.length < checkboxes.length;
    }

    actions.forEach((action) => {
        if (action instanceof HTMLButtonElement) {
            action.disabled = selected.length === 0;
        }
    });

    if (summary instanceof HTMLElement) {
        const emptyLabel = grid.dataset.gridSelectionEmpty ?? '';
        const template = grid.dataset.gridSelectionTemplate ?? ':count';
        summary.textContent = selected.length === 0
            ? emptyLabel
            : template.replace(':count', String(selected.length));
    }
}

function handleChange(event) {
    const target = event.target;
    const grid = resolveGrid(target);
    if (!(grid instanceof HTMLElement) || !(target instanceof HTMLInputElement || target instanceof HTMLSelectElement)) {
        return;
    }

    if (target.matches('[data-grid-select-all]')) {
        grid.querySelectorAll('[data-grid-row-checkbox]').forEach((checkbox) => {
            if (checkbox instanceof HTMLInputElement) {
                checkbox.checked = target.checked;
            }
        });
        updateSelection(grid);
        return;
    }

    if (target.matches('[data-grid-row-checkbox]')) {
        updateSelection(grid);
        return;
    }

    if (target.matches('[data-grid-per-page]') && target.form instanceof HTMLFormElement) {
        target.form.requestSubmit();
    }
}

function handleClick(event) {
    const trigger = event.target instanceof Element
        ? event.target.closest('[data-grid-print]')
        : null;

    if (!(trigger instanceof HTMLElement) || !resolveGrid(trigger)) {
        return;
    }

    event.preventDefault();
    window.print();
}

export function initDataGridInteractions(options = {}) {
    const root = options.root instanceof HTMLElement ? options.root : document.body;
    const eventRoot = options.eventRoot instanceof HTMLElement ? options.eventRoot : root;

    if (!(root instanceof HTMLElement) || !(eventRoot instanceof HTMLElement)) {
        return null;
    }

    root.querySelectorAll('[data-datagrid]').forEach((grid) => updateSelection(grid));
    if (root.matches('[data-datagrid]')) {
        updateSelection(root);
    }

    if (!interactionRoots.has(eventRoot)) {
        eventRoot.addEventListener('change', handleChange);
        eventRoot.addEventListener('click', handleClick);
        interactionRoots.add(eventRoot);
    }

    return { root, eventRoot };
}
