/**
 * Contract:
 * - Init: DOMContentLoaded or immediate boot when the DemoUi shell is present.
 * - DOM: `.demo-ui-shell-body`, `.app-topbar`, `.content-page` and status-bar theme toggle.
 * - Events/Payload: delegates shell behavior to `ui-runtime.js` with static options.
 * - CSP: dynamic import uses versioned local assets; no inline handlers.
 */
async function bootDemoUi() {
    const demoShell = document.querySelector('.demo-ui-shell-body');
    if (!(demoShell instanceof HTMLElement)) {
        return;
    }

    const version = typeof window.__catalystModuleVersion === 'string' && window.__catalystModuleVersion !== ''
        ? `?v=${encodeURIComponent(window.__catalystModuleVersion)}`
        : '';
    const { initShellRuntime } = await import(`/assets/js/catalyst/modules/ui-runtime.js${version}`);

    await initShellRuntime({
        root: demoShell,
        defaultDoc: 'ui-alerts.html',
        mobileMaxWidth: 767,
        noScrollClass: 'demo-ui-no-scroll',
        backdropClass: 'demo-ui-backdrop',
        topbarSelector: '.app-topbar',
        scrollContainerSelector: '.content-page',
        topbarActiveClass: 'topbar-active',
        themeStorageKey: '__THEME_CONFIG__',
        quickToggleSelector: '.catalyst-status-bar [data-demoui-theme-toggle]',
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootDemoUi, { once: true });
} else {
    bootDemoUi();
}
