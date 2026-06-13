const boundRoots = new WeakSet();

function confirmAction(event) {
    const origin = event.target instanceof Element ? event.target : null;
    const candidate = event.type === 'submit'
        ? origin?.closest('form[data-confirm]')
            ?? (event.submitter instanceof Element ? event.submitter.closest('[data-confirm]') : null)
        : origin?.closest('[data-confirm]:not(form)');
    if (!(candidate instanceof HTMLElement)) {
        return true;
    }

    if (event.type === 'click' && candidate.matches('button[type="submit"], input[type="submit"]')) {
        return true;
    }

    const message = candidate.dataset.confirm ?? '';
    return message === '' || window.confirm(message);
}

function navigate(trigger) {
    const href = trigger.dataset.catalystHref ?? '';
    if (href === '' || href === '#') {
        return;
    }

    let url;
    try {
        url = new URL(href, window.location.href);
    } catch {
        return;
    }

    if (!['http:', 'https:'].includes(url.protocol)) {
        return;
    }

    if (trigger.dataset.catalystTarget === '_blank') {
        window.open(url.href, '_blank', 'noopener,noreferrer');
        return;
    }

    document.dispatchEvent(new CustomEvent('catalyst:navigation:start'));
    window.location.assign(url.href);
}

async function runModalAction(trigger) {
    const catalyst = window.Catalyst;
    if (!catalyst?.initialized) {
        throw new Error('Catalyst modal runtime is not initialized.');
    }

    const action = trigger.dataset.catalystModalAction;
    if (action === 'confirm') {
        const confirmed = await catalyst.confirm(trigger.dataset.modalMessage ?? '', {
            title: trigger.dataset.modalTitle,
            confirmText: trigger.dataset.modalConfirmText,
            cancelText: trigger.dataset.modalCancelText,
            type: trigger.dataset.modalType,
        });
        const message = confirmed
            ? trigger.dataset.modalConfirmedMessage
            : trigger.dataset.modalCancelledMessage;
        if (message) {
            catalyst.info(message);
        }
        return;
    }

    if (action === 'alert') {
        await catalyst.alert(trigger.dataset.modalMessage ?? '', {
            title: trigger.dataset.modalTitle,
            buttonText: trigger.dataset.modalButtonText,
            type: trigger.dataset.modalType,
        });
        const message = trigger.dataset.modalDismissedMessage;
        if (message) {
            catalyst.success(message);
        }
        return;
    }

    if (action === 'load') {
        const url = trigger.dataset.modalUrl ?? '';
        if (url === '') {
            throw new Error('Dynamic modal action requires data-modal-url.');
        }
        await catalyst.loadModal(url, {
            title: trigger.dataset.modalTitle,
        });
    }
}

export function initDeclarativeActions(options = {}) {
    const eventRoot = options.eventRoot instanceof HTMLElement
        ? options.eventRoot
        : document.body;
    if (!(eventRoot instanceof HTMLElement) || boundRoots.has(eventRoot)) {
        return;
    }

    boundRoots.add(eventRoot);
    eventRoot.addEventListener('submit', (event) => {
        if (!confirmAction(event)) {
            event.preventDefault();
            event.stopImmediatePropagation();
        }
    }, true);

    eventRoot.addEventListener('click', (event) => {
        const origin = event.target instanceof Element ? event.target : null;
        const trigger = origin?.closest(
            '[data-confirm], [data-history-back], [data-catalyst-href], [data-catalyst-modal-action]'
        );
        if (!(trigger instanceof HTMLElement) || !eventRoot.contains(trigger)) {
            return;
        }

        if (!confirmAction(event)) {
            event.preventDefault();
            event.stopImmediatePropagation();
            return;
        }

        if (trigger.hasAttribute('data-catalyst-modal-action')) {
            event.preventDefault();
            void runModalAction(trigger).catch((error) => {
                console.error('[Catalyst UI] Declarative modal action failed.', error);
            });
            return;
        }

        if (trigger.hasAttribute('data-history-back')) {
            event.preventDefault();
            document.dispatchEvent(new CustomEvent('catalyst:navigation:start'));
            window.history.back();
            return;
        }

        if (trigger.hasAttribute('data-catalyst-href')) {
            event.preventDefault();
            navigate(trigger);
        }
    }, true);
}
