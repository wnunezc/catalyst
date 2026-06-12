const buttonStates = new WeakMap();

function isButtonLike(element) {
    return element instanceof HTMLButtonElement || element instanceof HTMLInputElement;
}

export function setButtonLoading(button, options = {}) {
    if (!(button instanceof HTMLElement) || buttonStates.has(button)) {
        return;
    }

    const state = {
        html: button.innerHTML,
        disabled: isButtonLike(button) ? button.disabled : null,
        minWidth: button.style.minWidth,
        ariaBusy: button.getAttribute('aria-busy'),
    };

    buttonStates.set(button, state);

    if (options.lockWidth !== false) {
        const width = Math.ceil(button.getBoundingClientRect().width);
        if (width > 0) {
            button.style.minWidth = `${width}px`;
        }
    }

    if (isButtonLike(button)) {
        button.disabled = options.disabled ?? true;
    }

    button.setAttribute('aria-busy', 'true');
    button.dataset.catalystLoading = 'true';
    button.innerHTML = options.html ?? '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
}

export function clearButtonLoading(button) {
    if (!(button instanceof HTMLElement)) {
        return;
    }

    const state = buttonStates.get(button);
    if (!state) {
        return;
    }

    button.innerHTML = state.html;
    button.style.minWidth = state.minWidth;

    if (isButtonLike(button) && state.disabled !== null) {
        button.disabled = state.disabled;
    }

    if (state.ariaBusy === null) {
        button.removeAttribute('aria-busy');
    } else {
        button.setAttribute('aria-busy', state.ariaBusy);
    }

    delete button.dataset.catalystLoading;
    buttonStates.delete(button);
}

export function isButtonLoading(button) {
    return buttonStates.has(button);
}
