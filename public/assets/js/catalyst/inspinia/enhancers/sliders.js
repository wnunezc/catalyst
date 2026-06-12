import { initUiEnhancers, destroyUiEnhancers } from './engine.js';

export const selector = '[data-slider="default"], #rangeslider_multielement, #nonlinear, #slider1, #slider2, #slider-merging-tooltips, #soft, #slider-vertical, #slider-connect-upper, #slider-vertical-tooltip, #slider-vertical-limit';

export function init(root) {
    return initUiEnhancers({ root, capabilities: ['sliders'] });
}

export function destroy(root) {
    return destroyUiEnhancers({ root });
}
