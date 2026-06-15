const DEFAULT_RECOVERY_TIMEOUT = 120000;

function isInternalNavigation(event, link) {
    const target = (link.getAttribute('target') ?? '').trim().toLowerCase();

    if (event.button !== 0
        || event.metaKey
        || event.ctrlKey
        || event.shiftKey
        || event.altKey
        || link.hasAttribute('download')
        || (target !== '' && target !== '_self')
    ) {
        return false;
    }

    const href = link.getAttribute('href') ?? '';
    if (href === '' || href.startsWith('#') || href.startsWith('javascript:')) {
        return false;
    }

    try {
        const url = new URL(href, window.location.href);
        return ['http:', 'https:'].includes(url.protocol) && url.origin === window.location.origin;
    } catch {
        return false;
    }
}

export class ActivityManager {
    constructor(root = document.body, options = {}) {
        this.root = root instanceof HTMLElement ? root : document.body;
        this.overlay = document.querySelector('[data-catalyst-activity-overlay]');
        this.tokens = new Map();
        this.httpTokens = new Map();
        this.navigationToken = null;
        this.sequence = 0;
        this.recoveryTimeout = options.recoveryTimeout ?? DEFAULT_RECOVERY_TIMEOUT;
        this.bootRecoveryTimer = null;
        this.bound = false;
    }

    init() {
        if (this.bound || !(this.root instanceof HTMLElement)) {
            return this;
        }

        this.bound = true;
        document.addEventListener('click', (event) => this.handleClick(event), true);
        document.addEventListener('submit', (event) => this.handleSubmit(event), true);
        document.addEventListener('catalyst:http:start', (event) => this.handleHttpStart(event));
        document.addEventListener('catalyst:http:finish', (event) => this.handleHttpFinish(event));
        document.addEventListener('catalyst:navigation:start', () => this.beginNavigation());
        window.addEventListener('pageshow', () => this.reset());
        window.addEventListener('beforeunload', () => this.beginNavigation());
        this.bootRecoveryTimer = window.setTimeout(() => this.ready(), this.recoveryTimeout);

        return this;
    }

    ready() {
        if (this.bootRecoveryTimer !== null) {
            clearTimeout(this.bootRecoveryTimer);
            this.bootRecoveryTimer = null;
        }
        this.render();
    }

    begin(options = {}) {
        const token = `activity-${++this.sequence}`;
        const entry = {
            type: options.type ?? 'request',
            message: options.message ?? 'Please wait while Catalyst completes this operation.',
            timer: null,
        };

        if (this.recoveryTimeout > 0) {
            entry.timer = window.setTimeout(() => {
                console.warn(`[Catalyst UI] Activity recovery released ${entry.type}.`);
                this.finish(token);
            }, this.recoveryTimeout);
        }

        this.tokens.set(token, entry);
        this.render();
        document.dispatchEvent(new CustomEvent('catalyst:activity:start', {
            detail: { token, type: entry.type },
        }));

        return token;
    }

    finish(token) {
        const entry = this.tokens.get(token);
        if (!entry) {
            return;
        }

        if (entry.timer !== null) {
            clearTimeout(entry.timer);
        }

        this.tokens.delete(token);
        this.render();
        document.dispatchEvent(new CustomEvent('catalyst:activity:finish', {
            detail: { token, type: entry.type },
        }));

        if (this.tokens.size === 0) {
            document.dispatchEvent(new CustomEvent('catalyst:activity:idle'));
        }
    }

    reset() {
        this.tokens.forEach((entry) => {
            if (entry.timer !== null) {
                clearTimeout(entry.timer);
            }
        });
        this.tokens.clear();
        this.httpTokens.clear();
        this.navigationToken = null;
        if (this.bootRecoveryTimer !== null) {
            clearTimeout(this.bootRecoveryTimer);
            this.bootRecoveryTimer = null;
        }
        this.render();
    }

    handleClick(event) {
        const origin = event.target instanceof Element ? event.target : null;
        const release = origin?.closest('[data-catalyst-activity-release]');
        if (release instanceof HTMLButtonElement && this.overlay?.contains(release)) {
            event.preventDefault();
            event.stopImmediatePropagation();
            this.reset();
            return;
        }

        if (this.tokens.size > 0) {
            event.preventDefault();
            event.stopImmediatePropagation();
            return;
        }

        const link = origin?.closest('a[href]');
        if (!(link instanceof HTMLElement) || !this.root?.contains(link)) {
            return;
        }

        if (!(link instanceof HTMLAnchorElement) || !isInternalNavigation(event, link)) {
            return;
        }

        queueMicrotask(() => {
            if (!event.defaultPrevented) {
                this.beginNavigation();
            }
        });
    }

    handleSubmit(event) {
        if (this.tokens.size > 0) {
            event.preventDefault();
            event.stopImmediatePropagation();
            return;
        }

        const form = event.target instanceof HTMLFormElement ? event.target : null;
        if (!form || !this.root?.contains(form)) {
            return;
        }

        queueMicrotask(() => {
            if (!event.defaultPrevented && form.checkValidity()) {
                this.beginNavigation('submit');
            }
        });
    }

    handleHttpStart(event) {
        const id = event?.detail?.id;
        if (!id || event.detail.foreground !== true) {
            return;
        }

        this.httpTokens.set(id, this.begin({ type: 'request' }));
    }

    handleHttpFinish(event) {
        const id = event?.detail?.id;
        const token = id ? this.httpTokens.get(id) : null;
        if (!token) {
            return;
        }

        this.httpTokens.delete(id);
        this.finish(token);
    }

    beginNavigation(type = 'navigation') {
        if (this.navigationToken) {
            return this.navigationToken;
        }

        this.navigationToken = this.begin({ type });
        return this.navigationToken;
    }

    render() {
        if (!(this.overlay instanceof HTMLElement)) {
            return;
        }

        const active = this.tokens.size > 0;
        const latest = Array.from(this.tokens.values()).at(-1);
        const message = this.overlay.querySelector('[data-catalyst-activity-message]');

        this.overlay.dataset.activityState = active ? (latest?.type ?? 'request') : 'idle';
        this.overlay.setAttribute('aria-hidden', active ? 'false' : 'true');
        if (message instanceof HTMLElement && latest?.message) {
            message.textContent = latest.message;
        }

        if (document.body instanceof HTMLElement) {
            if (active) {
                document.body.setAttribute('aria-busy', 'true');
            } else {
                document.body.removeAttribute('aria-busy');
            }
        }
    }
}
