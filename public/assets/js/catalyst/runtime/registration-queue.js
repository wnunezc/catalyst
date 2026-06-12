const componentAdapters = new Map();
const eventAdapters = new Map();
const componentListeners = new Set();
const eventListeners = new Set();

function requireName(definition, kind) {
    const name = typeof definition?.name === 'string' ? definition.name.trim() : '';
    if (name === '') {
        throw new TypeError(`A Catalyst UI ${kind} requires a unique name.`);
    }

    return name;
}

export function registerUiComponent(componentAdapter) {
    const name = requireName(componentAdapter, 'component');
    if (typeof componentAdapter.mount !== 'function') {
        throw new TypeError('A Catalyst UI component requires mount().');
    }

    componentAdapters.set(name, { ...componentAdapter, name });
    componentListeners.forEach((listener) => listener(componentAdapters.get(name)));
}

export function registerUiEvent(eventAdapter) {
    const name = requireName(eventAdapter, 'event');
    const type = typeof eventAdapter?.type === 'string' ? eventAdapter.type.trim() : '';
    if (type === '' || typeof eventAdapter.listener !== 'function') {
        throw new TypeError('A Catalyst UI event requires a type and listener().');
    }

    eventAdapters.set(name, { ...eventAdapter, name, type });
    eventListeners.forEach((listener) => listener(eventAdapters.get(name)));
}

export function consumeUiRegistrations(handlers = {}) {
    const onComponent = typeof handlers.onComponent === 'function' ? handlers.onComponent : null;
    const onEvent = typeof handlers.onEvent === 'function' ? handlers.onEvent : null;

    if (onComponent) {
        componentAdapters.forEach(onComponent);
        componentListeners.add(onComponent);
    }

    if (onEvent) {
        eventAdapters.forEach(onEvent);
        eventListeners.add(onEvent);
    }

    return () => {
        if (onComponent) {
            componentListeners.delete(onComponent);
        }
        if (onEvent) {
            eventListeners.delete(onEvent);
        }
    };
}
