export class ComponentRegistry {
    constructor() {
        this.adapters = new Map();
    }

    register(adapter) {
        if (!adapter || typeof adapter !== 'object') {
            throw new TypeError('A Catalyst UI component adapter must be an object.');
        }

        const name = typeof adapter.name === 'string' ? adapter.name.trim() : '';
        if (name === '' || typeof adapter.mount !== 'function') {
            throw new TypeError('A Catalyst UI component adapter requires a name and mount().');
        }

        const phase = adapter.phase === 'start' ? 'start' : 'scan';
        const selector = typeof adapter.selector === 'string'
            ? adapter.selector.trim()
            : '';
        this.adapters.set(name, {
            name,
            phase,
            selector,
            mount: adapter.mount,
            destroy: typeof adapter.destroy === 'function' ? adapter.destroy : null,
        });

        return this;
    }

    get(name) {
        return this.adapters.get(name) ?? null;
    }

    async start(root, context = {}) {
        for (const adapter of this.adapters.values()) {
            if (!this.matchesCapability(adapter, root)) {
                continue;
            }
            await adapter.mount(root, context);
        }
    }

    async scan(root, context = {}) {
        for (const adapter of this.adapters.values()) {
            if (adapter.phase === 'scan' && this.matchesCapability(adapter, root)) {
                await adapter.mount(root, context);
            }
        }
    }

    async destroy(root, context = {}) {
        const adapters = Array.from(this.adapters.values()).reverse();

        for (const adapter of adapters) {
            if (adapter.destroy && this.matchesCapability(adapter, root)) {
                await adapter.destroy(root, context);
            }
        }
    }

    matchesCapability(adapter, root) {
        if (adapter.selector === '') {
            return true;
        }

        return root.matches?.(adapter.selector) === true
            || root.querySelector?.(adapter.selector) !== null;
    }
}
