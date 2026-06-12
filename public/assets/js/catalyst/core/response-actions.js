import { applyTrustedHtml, readTrustedHtmlContractFromJson } from './trusted-dom.js';

export async function applyDomInjection(responseData) {
    if (!responseData || typeof responseData !== 'object') {
        return false;
    }

    const selector = typeof responseData.in === 'string' ? responseData.in.trim() : '';
    const html = typeof responseData.html === 'string' ? responseData.html : null;

    if (selector === '' || html === null) {
        return false;
    }

    const target = document.querySelector(selector);
    if (!target) {
        console.warn(`[Catalyst DOM] Target not found for selector "${selector}".`);
        return false;
    }

    document.dispatchEvent(new CustomEvent('catalyst:dom:before-update', {
        detail: { selector, target, html, responseData }
    }));

    const runtimeRoot = target.closest('[data-catalyst-ui-runtime="ready"]');
    if (runtimeRoot instanceof HTMLElement && typeof window.Catalyst?.ui?.destroy === 'function') {
        await window.Catalyst.ui.destroy(target);
    }

    if (!applyTrustedHtml(target, html, readTrustedHtmlContractFromJson(responseData))) {
        return false;
    }

    document.dispatchEvent(new CustomEvent('catalyst:dom:updated', {
        detail: { selector, target, html, responseData }
    }));

    return true;
}
