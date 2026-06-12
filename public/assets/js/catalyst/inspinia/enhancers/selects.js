import { initUiEnhancers, destroyUiEnhancers } from './engine.js';

export const selector = '[data-choices], [data-toggle="select2"], [data-plugin="select2"]';

export function init(root) {
    return initUiEnhancers({ root, capabilities: ['choices', 'select2'] });
}

export function destroy(root) {
    return destroyUiEnhancers({ root });
}
