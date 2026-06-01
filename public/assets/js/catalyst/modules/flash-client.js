/**
 * Catalyst Flash Client
 * ----------------------------------------------------------------------------
 * Reads the JSON bridge `<script type="application/json" id="catalyst-flash-data">`
 * emitted by `components/_flash-messages.php` and delivers messages entirely
 * client-side:
 *
 *   - Regular flashes -> dispatched as Catalyst toasters (ephemeral).
 *   - Persistent flashes -> injected as dismissable DOM alerts; dismiss POSTs
 *     to /flash/dismiss so the server stops serializing them
 *     on subsequent requests.
 *
 * CSP-safe:
 *   - Bridge uses `<script type="application/json">` (not executable).
 *   - Dismiss uses event delegation on `[data-flash-dismiss]`.
 *
 */

import { getHttpClient } from './http.js';

const BRIDGE_ID = 'catalyst-flash-data';
const CONTAINER_ID = 'catalyst-flash-banners';
const DISMISS_URL = '/flash/dismiss';
const http = getHttpClient();

const TYPE_ICON = {
    success: 'fa-solid fa-circle-check',
    error: 'fa-solid fa-circle-xmark',
    danger: 'fa-solid fa-circle-xmark',
    warning: 'fa-solid fa-triangle-exclamation',
    info: 'fa-solid fa-circle-info'
};

function readBridge() {
    const el = document.getElementById(BRIDGE_ID);
    if (!el) {
        return null;
    }

    try {
        return JSON.parse(el.textContent || '{}');
    } catch {
        return null;
    }
}

function ensureContainer() {
    let container = document.getElementById(CONTAINER_ID);
    if (container) {
        return container;
    }

    container = document.createElement('div');
    container.id = CONTAINER_ID;
    container.className = 'catalyst-flash-container';
    document.body.insertBefore(container, document.body.firstChild);
    return container;
}

function renderPersistent(message, container) {
    const alert = document.createElement('div');
    alert.className = 'catalyst-alert alert-' + message.type;
    alert.setAttribute('role', 'alert');
    alert.setAttribute('data-id', message.id);

    const icon = document.createElement('span');
    icon.className = 'alert-icon';

    const iconNode = document.createElement('i');
    iconNode.className = TYPE_ICON[message.type] || 'fa-solid fa-circle';
    icon.appendChild(iconNode);

    const body = document.createElement('div');
    body.className = 'alert-content';

    const text = document.createElement('p');
    text.className = 'alert-message';
    text.textContent = message.message;
    body.appendChild(text);

    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'alert-dismiss';
    button.setAttribute('aria-label', 'Dismiss');
    button.setAttribute('data-flash-dismiss', message.id);
    button.textContent = '\u00d7';

    alert.appendChild(icon);
    alert.appendChild(body);
    alert.appendChild(button);
    container.appendChild(alert);
}

function deliverRegular(regular) {
    if (!regular || !window.Catalyst) {
        return;
    }

    Object.keys(regular).forEach(type => {
        const messages = regular[type] || [];
        messages.forEach(message => {
            if (typeof window.Catalyst[type] === 'function') {
                window.Catalyst[type](message);
            }
        });
    });
}

function deliverPersistent(list) {
    if (!list || list.length === 0) {
        return;
    }

    const container = ensureContainer();

    list.forEach(message => {
        if (container.querySelector('[data-id="' + CSS.escape(message.id) + '"]')) {
            return;
        }

        renderPersistent(message, container);
    });
}

async function dismiss(id, alertEl) {
    if (!id || !alertEl) {
        return;
    }

    alertEl.classList.add('dismissing');

    try {
        await http.json(DISMISS_URL, {
            method: 'POST',
            form: { id }
        });

        setTimeout(() => {
            alertEl.remove();
        }, 200);
    } catch {
        alertEl.classList.remove('dismissing');
    }
}

function bindDismiss() {
    document.addEventListener('click', event => {
        let target = event.target;

        while (target && target !== document.body) {
            if (target.hasAttribute && target.hasAttribute('data-flash-dismiss')) {
                event.preventDefault();
                dismiss(target.getAttribute('data-flash-dismiss'), target.closest('.catalyst-alert'));
                return;
            }

            target = target.parentElement;
        }
    });
}

function run() {
    const data = readBridge();
    if (!data) {
        return;
    }

    deliverPersistent(data.persistent || []);

    if (window.Catalyst && typeof window.Catalyst.success === 'function') {
        deliverRegular(data.regular || {});
        return;
    }

    document.addEventListener('catalyst:ready', () => {
        deliverRegular(data.regular || {});
    }, { once: true });
}

bindDismiss();

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', run, { once: true });
} else {
    run();
}
