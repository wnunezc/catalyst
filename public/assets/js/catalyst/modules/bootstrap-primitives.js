function removeDismissTarget(trigger, selector, hiddenClass) {
    const target = trigger.closest(selector);
    if (!(target instanceof HTMLElement)) {
        return;
    }

    target.classList.remove('show');
    target.classList.add(hiddenClass);
    target.setAttribute('aria-hidden', 'true');

    window.setTimeout(() => {
        target.remove();
    }, 180);
}

function hideBootstrapTarget(trigger, selector, componentName) {
    const target = trigger.closest(selector);
    if (!(target instanceof HTMLElement)) {
        return;
    }

    const bootstrapApi = window.bootstrap ?? null;
    const component = bootstrapApi?.[componentName] ?? null;
    if (component && typeof component.getOrCreateInstance === 'function') {
        component.getOrCreateInstance(target).hide();
        return;
    }

    target.classList.remove('show');
    target.setAttribute('aria-hidden', 'true');
}

function toggleButtonState(trigger) {
    if (!(trigger instanceof HTMLButtonElement)) {
        return;
    }

    if (trigger.disabled || trigger.getAttribute('aria-disabled') === 'true') {
        return;
    }

    const willBeActive = !trigger.classList.contains('active');
    trigger.classList.toggle('active', willBeActive);
    trigger.setAttribute('aria-pressed', willBeActive ? 'true' : 'false');
}

function initLiveAlertDemo(root) {
    const alertPlaceholder = root.querySelector('#liveAlertPlaceholder');
    const alertTrigger = root.querySelector('#liveAlertBtn');
    if (!(alertPlaceholder instanceof HTMLElement) || !(alertTrigger instanceof HTMLButtonElement)) {
        return;
    }

    if (alertTrigger.__catalystLiveAlertBound) {
        return;
    }

    const appendAlert = (message, type) => {
        const wrapper = document.createElement('div');
        wrapper.innerHTML = [
            `<div class="alert alert-${type} alert-dismissible" role="alert">`,
            `   <div>${message}</div>`,
            '   <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>',
            '</div>',
        ].join('');
        alertPlaceholder.append(wrapper);
    };

    alertTrigger.__catalystLiveAlertBound = true;
    alertTrigger.addEventListener('click', () => {
        appendAlert('Nice, you triggered this alert message!', 'success');
    });
}

export function initBootstrapPrimitives(options = {}) {
    const root = options.root instanceof HTMLElement ? options.root : document.body;
    if (!(root instanceof HTMLElement)) {
        return;
    }

    if (root.__catalystBootstrapPrimitivesBound) {
        return;
    }

    root.__catalystBootstrapPrimitivesBound = true;
    initLiveAlertDemo(root);

    root.addEventListener('click', (event) => {
        const target = event.target instanceof Element ? event.target : null;
        if (!(target instanceof Element)) {
            return;
        }

        const trigger = target.closest('[data-bs-dismiss], [data-bs-toggle="button"]');
        if (!(trigger instanceof HTMLElement) || !root.contains(trigger)) {
            return;
        }

        const dismissKind = trigger.getAttribute('data-bs-dismiss');
        if (dismissKind === 'alert') {
            event.preventDefault();
            removeDismissTarget(trigger, '.alert', 'hide');
            return;
        }

        if (dismissKind === 'toast') {
            event.preventDefault();
            removeDismissTarget(trigger, '.toast', 'hide');
            return;
        }

        if (dismissKind === 'modal') {
            event.preventDefault();
            hideBootstrapTarget(trigger, '.modal', 'Modal');
            return;
        }

        if (dismissKind === 'offcanvas') {
            event.preventDefault();
            hideBootstrapTarget(trigger, '.offcanvas', 'Offcanvas');
            return;
        }

        if (trigger.getAttribute('data-bs-toggle') === 'button') {
            event.preventDefault();
            toggleButtonState(trigger);
        }
    });
}
