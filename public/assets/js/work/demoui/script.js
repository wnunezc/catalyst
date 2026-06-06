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

    initModalExamples(demoShell);
}

function initModalExamples(root) {
    const varyingModal = root.querySelector('#exampleModal');
    if (!(varyingModal instanceof HTMLElement) || varyingModal.dataset.demoUiVaryingModalBound === '1') {
        return;
    }

    varyingModal.dataset.demoUiVaryingModalBound = '1';
    varyingModal.addEventListener('show.bs.modal', (event) => {
        const trigger = event.relatedTarget;
        const recipient = trigger instanceof HTMLElement ? trigger.dataset.bsWhatever || '' : '';
        const title = varyingModal.querySelector('.modal-title');
        const input = varyingModal.querySelector('#recipient-name');

        if (title) {
            title.textContent = recipient !== '' ? `New message to ${recipient}` : 'New message';
        }

        if (input instanceof HTMLInputElement) {
            input.value = recipient;
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootDemoUi, { once: true });
} else {
    bootDemoUi();
}
