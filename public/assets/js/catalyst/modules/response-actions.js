import { applyTrustedHtml, readTrustedHtmlContractFromJson } from './trusted-dom.js';

export function applyDomInjection(responseData) {
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

    if (!applyTrustedHtml(target, html, readTrustedHtmlContractFromJson(responseData))) {
        return false;
    }

    document.dispatchEvent(new CustomEvent('catalyst:dom:updated', {
        detail: { selector, target, html, responseData }
    }));

    return true;
}
