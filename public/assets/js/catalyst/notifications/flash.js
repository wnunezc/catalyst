const boundRoots = new WeakSet();
const consumedStates = new WeakSet();

const TYPE_ICON = {
    success: 'fa-solid fa-circle-check',
    error: 'fa-solid fa-circle-xmark',
    danger: 'fa-solid fa-circle-xmark',
    warning: 'fa-solid fa-triangle-exclamation',
    info: 'fa-solid fa-circle-info',
};
const MESSAGE_TYPES = new Set(Object.keys(TYPE_ICON));

function ensureContainer(root) {
    const existing = document.getElementById('catalyst-flash-banners');
    if (existing instanceof HTMLElement) {
        return existing;
    }

    const container = document.createElement('div');
    container.id = 'catalyst-flash-banners';
    container.className = 'catalyst-flash-container';
    root.prepend(container);
    return container;
}

function hasMessage(container, id) {
    return Array.from(container.querySelectorAll('[data-flash-id]'))
        .some((element) => element.getAttribute('data-flash-id') === id);
}

function renderPersistent(message, container) {
    const id = typeof message?.id === 'string' ? message.id : '';
    const text = typeof message?.message === 'string' ? message.message : '';
    const requestedType = typeof message?.type === 'string' ? message.type : 'info';
    const type = MESSAGE_TYPES.has(requestedType) ? requestedType : 'info';
    if (id === '' || text === '' || hasMessage(container, id)) {
        return;
    }

    const alert = document.createElement('div');
    alert.className = `catalyst-alert alert-${type}`;
    alert.setAttribute('role', 'alert');
    alert.setAttribute('data-flash-id', id);

    const icon = document.createElement('span');
    icon.className = 'alert-icon';
    const iconNode = document.createElement('i');
    iconNode.className = TYPE_ICON[type] ?? 'fa-solid fa-circle';
    icon.append(iconNode);

    const body = document.createElement('div');
    body.className = 'alert-content';
    const paragraph = document.createElement('p');
    paragraph.className = 'alert-message';
    paragraph.textContent = text;
    body.append(paragraph);

    const dismiss = document.createElement('button');
    dismiss.type = 'button';
    dismiss.className = 'alert-dismiss';
    dismiss.setAttribute('aria-label', 'Dismiss');
    dismiss.setAttribute('data-flash-dismiss', id);
    dismiss.textContent = '\u00d7';

    alert.append(icon, body, dismiss);
    container.append(alert);
}

function deliverRegular(regular, catalyst) {
    if (!regular || typeof regular !== 'object') {
        return;
    }

    Object.entries(regular).forEach(([type, messages]) => {
        if (!Array.isArray(messages) || typeof catalyst?.[type] !== 'function') {
            return;
        }

        messages.forEach((message) => {
            if (typeof message === 'string' && message !== '') {
                catalyst[type](message);
            }
        });
    });
}

function bindDismiss(eventRoot, http) {
    if (boundRoots.has(eventRoot)) {
        return;
    }

    boundRoots.add(eventRoot);
    eventRoot.addEventListener('click', async (event) => {
        const origin = event.target instanceof Element ? event.target : null;
        const trigger = origin?.closest('[data-flash-dismiss]');
        if (!(trigger instanceof HTMLElement) || !eventRoot.contains(trigger)) {
            return;
        }

        const alert = trigger.closest('[data-flash-id]');
        const id = trigger.getAttribute('data-flash-dismiss') ?? '';
        if (
            !(alert instanceof HTMLElement)
            || id === ''
            || trigger.getAttribute('aria-disabled') === 'true'
        ) {
            return;
        }

        event.preventDefault();
        trigger.setAttribute('aria-disabled', 'true');
        alert.classList.add('dismissing');

        try {
            await http.json('/flash/dismiss', {
                method: 'POST',
                form: { id },
            });
            window.setTimeout(() => alert.remove(), 200);
        } catch (error) {
            trigger.removeAttribute('aria-disabled');
            alert.classList.remove('dismissing');
            console.error('[Catalyst Flash] Unable to dismiss persistent message.', error);
        }
    });
}

export function initFlashMessages(options = {}) {
    const root = options.root instanceof HTMLElement ? options.root : document.body;
    const eventRoot = options.eventRoot instanceof HTMLElement ? options.eventRoot : root;
    const state = options.state && typeof options.state === 'object' ? options.state : {};
    if (!(root instanceof HTMLElement) || !(eventRoot instanceof HTMLElement) || !options.http) {
        return;
    }

    bindDismiss(eventRoot, options.http);
    if (consumedStates.has(state)) {
        return;
    }

    consumedStates.add(state);
    const persistent = Array.isArray(state.persistent) ? state.persistent : [];
    if (persistent.length > 0) {
        const container = ensureContainer(eventRoot);
        persistent.forEach((message) => renderPersistent(message, container));
    }

    deliverRegular(state.regular, options.catalyst);
}
