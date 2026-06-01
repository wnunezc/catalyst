import { loadScript } from './asset-loader.js';

const SIMPLEBAR_URL = 'https://cdn.jsdelivr.net/npm/simplebar@6.3.3/dist/simplebar.min.js';

function requiresSimpleBar(root) {
    return root.querySelector('[data-simplebar]') !== null;
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
    if (!(root instanceof HTMLElement) || !requiresSimpleBar(root)) {
        return;
    }

    const SimpleBar = await ensureSimpleBar();

    root.querySelectorAll('[data-simplebar]').forEach((element) => {
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
