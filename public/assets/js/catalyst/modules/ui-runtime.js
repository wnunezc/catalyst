const runtimeModuleUrl = new URL(import.meta.url);
const runtimeModuleVersion = runtimeModuleUrl.searchParams.get('v') ?? '';
const runtimeModuleSuffix = runtimeModuleVersion !== ''
    ? `?v=${encodeURIComponent(runtimeModuleVersion)}`
    : '';

let runtimeModulesPromise = null;

function loadRuntimeModules() {
    if (runtimeModulesPromise !== null) {
        return runtimeModulesPromise;
    }

    runtimeModulesPromise = Promise.all([
        import(`./bootstrap-primitives.js${runtimeModuleSuffix}`),
        import(`./bootstrap-components.js${runtimeModuleSuffix}`),
        import(`./card-actions.js${runtimeModuleSuffix}`),
        import(`./code-preview.js${runtimeModuleSuffix}`),
        import(`./form-validation.js${runtimeModuleSuffix}`),
        import(`./migrationui-charts.js${runtimeModuleSuffix}`),
        import(`./migrationui-tables.js${runtimeModuleSuffix}`),
        import(`./simplebar.js${runtimeModuleSuffix}`),
        import(`./ui-enhancers.js${runtimeModuleSuffix}`),
        import(`./shell-dropdowns.js${runtimeModuleSuffix}`),
        import(`./shell-navigation.js${runtimeModuleSuffix}`),
        import(`./shell-theme-customizer.js${runtimeModuleSuffix}`),
        import(`./shell-topbar.js${runtimeModuleSuffix}`),
    ]).then(([
        bootstrapPrimitivesModule,
        bootstrapComponentsModule,
        cardActionsModule,
        codePreviewModule,
        formValidationModule,
        migrationUiChartsModule,
        migrationUiTablesModule,
        simpleBarModule,
        uiEnhancersModule,
        dropdownsModule,
        navigationModule,
        themeCustomizerModule,
        topbarModule,
    ]) => ({
        initBootstrapPrimitives: bootstrapPrimitivesModule.initBootstrapPrimitives,
        initBootstrapComponents: bootstrapComponentsModule.initBootstrapComponents,
        initCardActions: cardActionsModule.initCardActions,
        initDropdowns: dropdownsModule.initDropdowns,
        initFormValidation: formValidationModule.initFormValidation,
        initMigrationUiCharts: migrationUiChartsModule.initMigrationUiCharts,
        initMigrationUiTables: migrationUiTablesModule.initMigrationUiTables,
        initSimpleBar: simpleBarModule.initSimpleBar,
        initMarkupCodePreview: codePreviewModule.initMarkupCodePreview,
        initUiEnhancers: uiEnhancersModule.initUiEnhancers,
        initShellNavigation: navigationModule.initShellNavigation,
        initShellThemeCustomizer: themeCustomizerModule.initShellThemeCustomizer,
        initTopbarState: topbarModule.initTopbarState,
    }));

    return runtimeModulesPromise;
}

function resolveRoot(options = {}) {
    if (options.root instanceof HTMLElement) {
        return options.root;
    }

    if (typeof options.rootSelector === 'string' && options.rootSelector !== '') {
        const selected = document.querySelector(options.rootSelector);
        return selected instanceof HTMLElement ? selected : null;
    }

    return document.body instanceof HTMLElement ? document.body : null;
}

export async function initShellRuntime(options = {}) {
    const root = resolveRoot(options);
    if (!(root instanceof HTMLElement)) {
        return null;
    }

    const contentRoot = root.querySelector('.content-page') instanceof HTMLElement
        ? root.querySelector('.content-page')
        : root;

    if (root.hasAttribute('data-catalyst-shell-runtime-initialized')) {
        return { root };
    }

    root.setAttribute('data-catalyst-shell-runtime-initialized', 'true');

    const {
        initCardActions,
        initBootstrapComponents,
        initDropdowns,
        initFormValidation,
        initMigrationUiCharts,
        initMigrationUiTables,
        initMarkupCodePreview,
        initSimpleBar,
        initUiEnhancers,
        initShellNavigation,
        initShellThemeCustomizer,
        initTopbarState,
        initBootstrapPrimitives,
    } = await loadRuntimeModules();

    initShellNavigation({
        root,
        defaultDoc: options.defaultDoc,
        mobileMaxWidth: options.mobileMaxWidth,
        noScrollClass: options.noScrollClass,
        backdropClass: options.backdropClass,
    });
    await initSimpleBar({ root });
    initDropdowns({ root });
    initBootstrapPrimitives({ root });
    await initBootstrapComponents({ root: contentRoot });
    initMarkupCodePreview({ root });
    initCardActions({ root });
    initFormValidation({ root });
    await initMigrationUiCharts({ root: contentRoot });
    await initMigrationUiTables({ root: contentRoot });
    await initUiEnhancers({ root });
    initTopbarState({
        root,
        topbarSelector: options.topbarSelector,
        scrollContainerSelector: options.scrollContainerSelector,
        activeClass: options.topbarActiveClass,
    });
    initShellThemeCustomizer({
        root,
        themeStorageKey: options.themeStorageKey,
        themeDefaults: options.themeDefaults,
        themeConfig: options.themeConfig,
        quickToggleSelector: options.quickToggleSelector,
    });

    return { root };
}
