import { loadScript } from '../core/asset-loader.js';

const SIMPLEBAR_URL = 'https://cdn.jsdelivr.net/npm/simplebar@6.3.3/dist/simplebar.min.js';

function simpleBarElements(root) {
    const elements = root.matches?.('[data-simplebar]') === true ? [root] : [];

    return elements.concat(Array.from(root.querySelectorAll('[data-simplebar]')));
}

async function ensureSimpleBar() {
    if (window.SimpleBar) {
        return window.SimpleBar;
    }

    await loadScript(SIMPLEBAR_URL);

    if (!window.SimpleBar) {
        throw new Error('SimpleBar did not expose window.SimpleBar');
    }

    return window.SimpleBar;
}

export async function initSimpleBar(options = {}) {
    const root = options.root instanceof HTMLElement ? options.root : document.body;
    if (!(root instanceof HTMLElement)) {
        return;
    }

    const elements = simpleBarElements(root);
    if (elements.length === 0) {
        return;
    }

    const SimpleBar = await ensureSimpleBar();

    elements.forEach((element) => {
        if (!(element instanceof HTMLElement)) {
            return;
        }

        if (element.__catalystSimpleBarInstance) {
            element.__catalystSimpleBarInstance.recalculate?.();
            return;
        }

        element.__catalystSimpleBarInstance = new SimpleBar(element);
    });
}

export function destroySimpleBar(options = {}) {
    const root = options.root instanceof HTMLElement ? options.root : document.body;
    if (!(root instanceof HTMLElement)) {
        return;
    }

    simpleBarElements(root).forEach((element) => {
        const instance = element.__catalystSimpleBarInstance;
        instance?.unMount?.();
        delete element.__catalystSimpleBarInstance;
    });
}
