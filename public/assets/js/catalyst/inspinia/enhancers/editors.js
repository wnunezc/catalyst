import { initUiEnhancers, destroyUiEnhancers } from './engine.js';

export const selector = '#snow-editor, #bubble-editor, .summernote';

export function init(root) {
    return initUiEnhancers({ root, capabilities: ['quill', 'summernote'] });
}

export function destroy(root) {
    return destroyUiEnhancers({ root });
}
