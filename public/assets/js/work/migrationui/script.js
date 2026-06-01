async function bootMigrationUi() {
    const migrationShell = document.querySelector('.migration-ui-shell-body');
    if (!(migrationShell instanceof HTMLElement)) {
        return;
    }

    const version = typeof window.__catalystModuleVersion === 'string' && window.__catalystModuleVersion !== ''
        ? `?v=${encodeURIComponent(window.__catalystModuleVersion)}`
        : '';
    const { initShellRuntime } = await import(`/assets/js/catalyst/modules/ui-runtime.js${version}`);

    await initShellRuntime({
        root: migrationShell,
        defaultDoc: 'ui-alerts.html',
        mobileMaxWidth: 767,
        noScrollClass: 'migration-ui-no-scroll',
        backdropClass: 'migration-ui-backdrop',
        topbarSelector: '.app-topbar',
        scrollContainerSelector: '.content-page',
        topbarActiveClass: 'topbar-active',
        themeStorageKey: '__THEME_CONFIG__',
        quickToggleSelector: '.catalyst-status-bar [data-migrationui-theme-toggle]',
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootMigrationUi, { once: true });
} else {
    bootMigrationUi();
}
