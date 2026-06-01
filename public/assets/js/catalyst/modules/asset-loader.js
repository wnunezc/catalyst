const scriptPromises = new Map();
const stylePromises = new Map();

function resolveUrl(url) {
    return new URL(url, window.location.origin).toString();
}

export function appendVersion(url, version = '') {
    if (typeof version !== 'string' || version === '') {
        return url;
    }

    const separator = url.includes('?') ? '&' : '?';
    return `${url}${separator}v=${encodeURIComponent(version)}`;
}

export function loadStyle(url) {
    const resolvedUrl = resolveUrl(url);
    if (stylePromises.has(resolvedUrl)) {
        return stylePromises.get(resolvedUrl);
    }

    const existing = Array.from(document.querySelectorAll('link[rel="stylesheet"][href]'))
        .find((link) => resolveUrl(link.href) === resolvedUrl);
    if (existing instanceof HTMLLinkElement) {
        const resolvedPromise = Promise.resolve(existing);
        stylePromises.set(resolvedUrl, resolvedPromise);
        return resolvedPromise;
    }

    const promise = new Promise((resolve, reject) => {
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = url;
        link.addEventListener('load', () => resolve(link), { once: true });
        link.addEventListener('error', () => reject(new Error(`Failed to load stylesheet: ${url}`)), { once: true });
        document.head.appendChild(link);
    });

    stylePromises.set(resolvedUrl, promise);
    return promise;
}

export function loadScript(url) {
    const resolvedUrl = resolveUrl(url);
    if (scriptPromises.has(resolvedUrl)) {
        return scriptPromises.get(resolvedUrl);
    }

    const existing = Array.from(document.querySelectorAll('script[src]'))
        .find((script) => resolveUrl(script.src) === resolvedUrl);
    if (existing instanceof HTMLScriptElement) {
        const resolvedPromise = Promise.resolve(existing);
        scriptPromises.set(resolvedUrl, resolvedPromise);
        return resolvedPromise;
    }

    const promise = new Promise((resolve, reject) => {
        const script = document.createElement('script');
        script.src = url;
        script.async = false;
        script.addEventListener('load', () => resolve(script), { once: true });
        script.addEventListener('error', () => reject(new Error(`Failed to load script: ${url}`)), { once: true });
        document.head.appendChild(script);
    });

    scriptPromises.set(resolvedUrl, promise);
    return promise;
}

export async function loadAssets(assets = {}) {
    const styles = Array.isArray(assets.styles) ? assets.styles : [];
    const scripts = Array.isArray(assets.scripts) ? assets.scripts : [];

    await Promise.all(styles.map((style) => loadStyle(style)));

    for (const script of scripts) {
        await loadScript(script);
    }
}
