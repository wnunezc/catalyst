import { initUiEnhancers, destroyUiEnhancers } from './engine.js';

export const selector = '[data-toggle="date-picker"], [data-plugin="date-picker"], [data-toggle="date-picker-range"], [data-plugin="date-picker-range"], [data-provider="flatpickr"], [data-provider="timepickr"], .classic-colorpicker, .monolith-colorpicker, .nano-colorpicker, .colorpicker-demo, .colorpicker-opacity-hue, .colorpicker-switch, .colorpicker-input, .colorpicker-format';

export function init(root) {
    return initUiEnhancers({ root, capabilities: ['dateRange', 'flatpickr', 'pickr'] });
}

export function destroy(root) {
    return destroyUiEnhancers({ root });
}
