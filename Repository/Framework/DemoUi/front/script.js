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
