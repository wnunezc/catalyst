export class EventRegistry {
    constructor() {
        this.bindings = new Map();
    }

    register(name, target, type, listener, options = {}) {
        if (this.bindings.has(name)) {
            return this;
        }

        if (!target?.addEventListener || typeof listener !== 'function') {
            throw new TypeError(`Invalid Catalyst UI event registration: ${name}`);
        }

        target.addEventListener(type, listener, options);
        this.bindings.set(name, { target, type, listener, options });

        return this;
    }

    unregister(name) {
        const binding = this.bindings.get(name);
        if (!binding) {
            return this;
        }

        binding.target.removeEventListener(
            binding.type,
            binding.listener,
            binding.options
        );
        this.bindings.delete(name);

        return this;
    }

    destroy() {
        Array.from(this.bindings.keys()).forEach((name) => this.unregister(name));
    }
}
