export const TRUSTED_HTML_POLICY = 'trusted-html';
export const TRUSTED_HTML_HEADER = 'X-Catalyst-Fragment-Policy';

export function readTrustedHtmlContractFromJson(responseData) {
    return {
        policy: typeof responseData?.html_policy === 'string' ? responseData.html_policy.trim() : '',
    };
}

export function readTrustedHtmlContractFromResponse(response) {
    return {
        policy: typeof response?.headers?.get === 'function'
            ? String(response.headers.get(TRUSTED_HTML_HEADER) || '').trim()
            : '',
    };
}

export function applyTrustedHtml(target, html, contract = {}) {
    if (!(target instanceof Element) || typeof html !== 'string') {
        return false;
    }

    if ((contract.policy || '') !== TRUSTED_HTML_POLICY) {
        console.warn('[Catalyst DOM] Trusted HTML contract rejected.');
        return false;
    }

    target.innerHTML = html;

    return true;
}
