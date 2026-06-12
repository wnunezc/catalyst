import { initUiEnhancers, destroyUiEnhancers } from './engine.js';

export const selector = '[data-plugin="dropzone"], input.filepond';

export function init(root) {
    return initUiEnhancers({ root, capabilities: ['dropzone', 'filepond'] });
}

export function destroy(root) {
    return destroyUiEnhancers({ root });
}
