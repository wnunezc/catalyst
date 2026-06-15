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

function copyWithFallback(value) {
    const textarea = document.createElement('textarea');
    const activeElement = document.activeElement;

    textarea.value = value;
    textarea.className = 'visually-hidden';
    textarea.setAttribute('readonly', '');
    textarea.setAttribute('aria-hidden', 'true');
    document.body.append(textarea);
    textarea.select();
    textarea.setSelectionRange(0, textarea.value.length);

    let copied = false;
    try {
        copied = document.execCommand('copy');
    } catch {
        copied = false;
    } finally {
        textarea.remove();
        if (activeElement instanceof HTMLElement) {
            activeElement.focus();
        }
    }

    return copied;
}

async function writeClipboard(value) {
    if (navigator.clipboard && typeof navigator.clipboard.writeText === 'function') {
        try {
            await navigator.clipboard.writeText(value);
            return true;
        } catch {
            return copyWithFallback(value);
        }
    }

    return copyWithFallback(value);
}

async function copyCellValue(trigger) {
    const value = trigger.dataset.gridCopyValue ?? '';

    if (value === '' || !(await writeClipboard(value))) {
        return false;
    }

    const copiedLabel = trigger.dataset.gridCopiedLabel ?? trigger.dataset.gridCopyLabel ?? '';
    const originalLabel = trigger.dataset.gridCopyLabel ?? '';
    const icon = trigger.querySelector('.ti');

    trigger.setAttribute('aria-label', copiedLabel);
    trigger.setAttribute('title', copiedLabel);
    trigger.setAttribute('data-bs-original-title', copiedLabel);
    icon?.classList.replace('ti-copy', 'ti-check');

    window.setTimeout(() => {
        trigger.setAttribute('aria-label', originalLabel);
        trigger.setAttribute('title', originalLabel);
        trigger.setAttribute('data-bs-original-title', originalLabel);
        icon?.classList.replace('ti-check', 'ti-copy');
    }, 1500);

    return true;
}

async function handleClick(event) {
    const target = event.target instanceof Element
        ? event.target.closest('[data-grid-print]')
        : null;

    const copyTrigger = event.target instanceof Element
        ? event.target.closest('[data-grid-copy]')
        : null;

    if (copyTrigger instanceof HTMLElement && resolveGrid(copyTrigger)) {
        event.preventDefault();

        try {
            await copyCellValue(copyTrigger);
        } catch {
            return;
        }

        return;
    }

    if (!(target instanceof HTMLElement) || !resolveGrid(target)) {
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
