import { initUiEnhancers, destroyUiEnhancers } from './engine.js';

export const selector = '[data-wizard]';

export function init(root) {
    return initUiEnhancers({ root, capabilities: ['wizard'] });
}

export function destroy(root) {
    return destroyUiEnhancers({ root });
}
