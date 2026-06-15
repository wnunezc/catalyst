import { ComponentRegistry } from './component-registry.js';
import { EventRegistry } from './event-registry.js';
import {
    consumeUiRegistrations,
    registerUiComponent as queueUiComponent,
    registerUiEvent as queueUiEvent,
} from './registration-queue.js';

const moduleUrl = new URL(import.meta.url);
const moduleVersion = moduleUrl.searchParams.get('v') ?? '';
const moduleSuffix = moduleVersion !== ''
    ? `?v=${encodeURIComponent(moduleVersion)}`
    : '';

const runtimes = new WeakMap();
const runtimeInstances = new Set();
const extensionAdapters = new Map();
const extensionEvents = new Map();
const statusBarInstances = new WeakMap();
const modulePromises = new Map();
let activity = null;

function loadRuntimeModule(name, path) {
    if (!modulePromises.has(name)) {
        const modulePath = `${path}${moduleSuffix}`;
        const modulePromise = import(modulePath).catch((error) => {
            modulePromises.delete(name);
            console.error(
                `[Catalyst UI] Unable to load local runtime dependency "${name}" from "${modulePath}".`,
                error
            );
            throw error;
        });
        modulePromises.set(name, modulePromise);
    }

    return modulePromises.get(name);
}

const { default: Catalyst, bindUiRuntimeApi } = await loadRuntimeModule(
    'core.catalyst',
    '../catalyst.js'
);

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

function adapter(name, phase, selector, mount, destroy = null) {
    return { name, phase, selector, mount, destroy };
}

function firstSelector(root, selectors) {
    return selectors.find((selector) => root.querySelector(selector) !== null) ?? '';
}

function mountStatusBar(root, statusBarModule) {
    if (statusBarInstances.has(root)) {
        return;
    }

    const config = readJsonTransport('catalyst-status-bar-config');
    const statusBar = new statusBarModule.StatusBarManager();
    statusBar.init(config);
    statusBarInstances.set(root, statusBar);
    window.CatalystStatusBar = statusBar;
}

function destroyStatusBar(root) {
    const statusBar = statusBarInstances.get(root);
    if (statusBar) {
        statusBar.destroy();
    }
    statusBarInstances.delete(root);
    if (window.CatalystStatusBar === statusBar) {
        window.CatalystStatusBar = undefined;
    }
}

class CatalystUiRuntime {
    constructor(root, options) {
        this.root = root;
        this.options = { ...options, root };
        this.components = new ComponentRegistry();
        this.events = new EventRegistry();
        this.queue = Promise.resolve();
        this.started = false;

        this.registerCoreAdapters();
        extensionAdapters.forEach((extension) => this.components.register(extension));
    }

    registerCoreAdapters() {
        this.components
            .register(adapter('shell.navigation', 'start', '.sidenav-menu', async (_root, context) => {
                const navigation = await loadRuntimeModule('shell.navigation', '../shell/navigation.js');
                navigation.initShellNavigation(context.options);
            }))
            .register(adapter(
                'vendor.simplebar',
                'scan',
                '[data-simplebar]',
                async (root) => (await loadRuntimeModule('shell.simplebar', '../shell/simplebar.js')).initSimpleBar({ root }),
                async (root) => (await loadRuntimeModule('shell.simplebar', '../shell/simplebar.js')).destroySimpleBar({ root })
            ))
            .register(adapter(
                'bootstrap.primitives',
                'scan',
                '#liveAlertBtn',
                async (root) => {
                const primitives = await loadRuntimeModule('bootstrap.primitives', '../bootstrap/primitives.js');
                primitives.initBootstrapPrimitives({ root });
                }
            ))
            .register(adapter(
                'core.declarative-actions',
                'scan',
                '[data-confirm], [data-history-back], [data-catalyst-href]',
                async (_root, context) => {
                    const actions = await loadRuntimeModule('core.declarative-actions', '../core/declarative-actions.js');
                    actions.initDeclarativeActions({ eventRoot: context.runtime.root });
                }
            ))
            .register(adapter(
                'forms.password',
                'scan',
                '[data-password-toggle], input[data-strength]',
                async (root, context) => {
                    await loadRuntimeModule('forms.password', '../forms/password.js');
                    Catalyst.passwords.init({
                        scanRoot: root,
                        eventRoot: context.runtime.root,
                    });
                }
            ))
            .register(adapter('forms.builder', 'scan', '[data-form-builder]', async (root, context) => {
                const builder = await loadRuntimeModule('forms.builder', '../forms/builder.js');
                return builder.initFormBuilder({
                    root,
                    eventRoot: context.runtime.root,
                });
            }))
            .register(adapter(
                'notifications.flash',
                'start',
                '#catalyst-ssr-state',
                async (root, context) => {
                    const flash = await loadRuntimeModule('notifications.flash', '../notifications/flash.js');
                    flash.initFlashMessages({
                        root,
                        eventRoot: context.runtime.root,
                        state: context.options.ssrState?.flash,
                        catalyst: Catalyst,
                        http: Catalyst.http,
                    });
                }
            ))
            .register(adapter(
                'bootstrap.components',
                'scan',
                '.accordion, .carousel, .modal, .offcanvas, [data-bs-spy="scroll"], [data-bs-toggle="collapse"], [data-bs-toggle="modal"], [data-bs-toggle="offcanvas"], [data-bs-toggle="popover"], [data-bs-toggle="tooltip"], [data-bs-toggle="tab"], [data-bs-toggle="pill"], [data-bs-toggle="dropdown"]',
                async (root) => (await loadRuntimeModule('bootstrap.components', '../bootstrap/components.js')).initBootstrapComponents({ root }),
                async (root) => (await loadRuntimeModule('bootstrap.components', '../bootstrap/components.js')).destroyBootstrapComponents({ root })
            ))
            .register(adapter('inspinia.code-preview', 'scan', 'code.language-markup', async (root) => {
                const codePreview = await loadRuntimeModule('inspinia.code-preview', '../inspinia/code-preview.js');
                codePreview.initMarkupCodePreview({ root });
            }))
            .register(adapter(
                'inspinia.card-actions',
                'scan',
                '[data-action="card-close"], [data-action="card-toggle"], [data-action="card-refresh"], [data-action="code-collapse"]',
                async (root) => {
                const cardActions = await loadRuntimeModule('inspinia.card-actions', '../inspinia/card-actions.js');
                cardActions.initCardActions({ root });
                }
            ))
            .register(adapter('forms.visual-validation', 'scan', '.needs-validation', async (root) => {
                const validation = await loadRuntimeModule('forms.validation', '../forms/validation.js');
                validation.initFormValidation({ root });
            }))
            .register(adapter('datagrid.interactions', 'scan', '[data-datagrid]', async (root, context) => {
                const interactions = await loadRuntimeModule('datagrid.interactions', '../datagrid/interactions.js');
                return interactions.initDataGridInteractions({
                    root,
                    eventRoot: context.runtime.root,
                });
            }))
            .register(adapter('inspinia.charts', 'scan', '.apex-charts, [data-catalyst-inspinia-document^="charts-"], [id^="echart-"], [id^="chart-"], [data-chart]', async (root) => {
                return (await loadRuntimeModule('inspinia.charts', '../inspinia/charts.js')).initInspiniaCharts({ root });
            }))
            .register(adapter('inspinia.tables', 'scan', 'table', async (root) => {
                return (await loadRuntimeModule('inspinia.tables', '../inspinia/tables.js')).initInspiniaTables({ root });
            }))
            .register(adapter('shell.topbar', 'start', '.app-topbar, .catalyst-public-nav', async (root) => {
                const topbar = await loadRuntimeModule('shell.topbar', '../shell/topbar.js');
                const topbarSelector = firstSelector(root, [
                    '.app-topbar',
                    '.catalyst-public-nav',
                ]);
                const scrollContainerSelector = firstSelector(root, [
                    '.content-page',
                    '.catalyst-public-shell__main',
                    '#catalyst-public-content',
                ]);
                topbar.initTopbarState({
                    root,
                    topbarSelector,
                    scrollContainerSelector,
                    activeClass: 'topbar-active',
                });
            }))
            .register(adapter('shell.theme-customizer', 'start', '#theme-settings-offcanvas', async (root) => {
                const themeCustomizer = await loadRuntimeModule('shell.theme-customizer', '../shell/theme-customizer.js');
                themeCustomizer.initShellThemeCustomizer({
                    root,
                    themeStorageKey: '__THEME_CONFIG__',
                    quickToggleSelector: '.catalyst-status-bar [data-catalyst-theme-toggle], .catalyst-status-bar [data-theme-quick-toggle]',
                });
            }))
            .register(adapter(
                'shell.status-bar',
                'start',
                '#catalyst-status-bar',
                async (root) => mountStatusBar(root, await loadRuntimeModule('shell.status-bar', '../shell/status-bar.js')),
                destroyStatusBar
            ))
            .register(adapter(
                'presence.record',
                'scan',
                '[data-record-presence]',
                async (root) => (await loadRuntimeModule('presence.record', '../presence/record-presence.js')).initRecordPresence({ root }),
                async (root) => (await loadRuntimeModule('presence.record', '../presence/record-presence.js')).destroyRecordPresence({ root })
            ));

        const enhancerDefinitions = [
            ['inspinia.pickers', '[data-toggle="date-picker"], [data-plugin="date-picker"], [data-toggle="date-picker-range"], [data-plugin="date-picker-range"], [data-provider="flatpickr"], [data-provider="timepickr"], .classic-colorpicker, .monolith-colorpicker, .nano-colorpicker, .colorpicker-demo, .colorpicker-opacity-hue, .colorpicker-switch, .colorpicker-input, .colorpicker-format'],
            ['inspinia.selects', '[data-choices], [data-toggle="select2"], [data-plugin="select2"]'],
            ['inspinia.uploads', '[data-plugin="dropzone"], input.filepond'],
            ['inspinia.editors', '#snow-editor, #bubble-editor, .summernote'],
            ['inspinia.wizard', '[data-wizard]'],
            ['inspinia.sliders', '[data-slider="default"], #rangeslider_multielement, #nonlinear, #slider1, #slider2, #slider-merging-tooltips, #soft, #slider-vertical, #slider-connect-upper, #slider-vertical-tooltip, #slider-vertical-limit'],
        ];

        enhancerDefinitions.forEach(([name, selector]) => {
            this.components.register(adapter(name, 'scan', selector, async (root) => {
                const enhancers = await loadRuntimeModule('inspinia.enhancers', '../inspinia/enhancers/index.js');
                const definition = enhancers.adapters.find(([adapterName]) => adapterName === name)?.[1];
                return definition?.init(root);
            }, async (root) => {
                const enhancers = await loadRuntimeModule('inspinia.enhancers', '../inspinia/enhancers/index.js');
                const definition = enhancers.adapters.find(([adapterName]) => adapterName === name)?.[1];
                return definition?.destroy(root);
            }));
        });
    }

    context() {
        return {
            options: this.options,
            runtime: this,
        };
    }

    enqueue(operation) {
        const next = this.queue.catch(() => undefined).then(operation);
        this.queue = next;
        return next;
    }

    resolveEventTarget(target) {
        if (target === 'window') {
            return window;
        }
        if (target === 'root') {
            return this.root;
        }
        return document;
    }

    registerEvent(eventAdapter) {
        const target = this.resolveEventTarget(eventAdapter.target);
        const bindingName = `extension.${eventAdapter.name}`;
        this.events.unregister(bindingName);
        this.events.register(
            bindingName,
            target,
            eventAdapter.type,
            (event) => eventAdapter.listener(event, this.context()),
            eventAdapter.options ?? {}
        );
    }

    registerComponent(componentAdapter) {
        this.components.register(componentAdapter);
        if (this.started) {
            const registeredAdapter = this.components.get(componentAdapter.name);
            void this.enqueue(async () => {
                if (!registeredAdapter || !this.components.matchesCapability(registeredAdapter, this.root)) {
                    return;
                }

                await registeredAdapter.mount(this.root, this.context());
            }).catch((error) => {
                console.error(`[Catalyst UI] Unable to mount registered component: ${componentAdapter.name}.`, error);
            });
        }
    }

    start() {
        return this.enqueue(async () => {
            if (this.started) {
                return this;
            }

            extensionEvents.forEach((eventAdapter) => this.registerEvent(eventAdapter));
            this.events.register(
                'runtime.dom-updated',
                document,
                'catalyst:dom:updated',
                (event) => this.handleDomUpdated(event)
            );
            await this.components.start(this.root, this.context());

            this.started = true;
            this.root.setAttribute('data-catalyst-ui-runtime', 'ready');
            document.dispatchEvent(new CustomEvent('catalyst:ui:ready', {
                detail: { root: this.root, runtime: this },
            }));

            return this;
        });
    }

    scan(root = this.root) {
        const scanRoot = root instanceof HTMLElement ? root : this.root;
        return this.enqueue(async () => {
            await this.components.scan(scanRoot, this.context());
            document.dispatchEvent(new CustomEvent('catalyst:ui:scanned', {
                detail: { root: scanRoot, runtime: this },
            }));

            return this;
        });
    }

    destroy(root = this.root) {
        const destroyRoot = root instanceof HTMLElement ? root : this.root;
        return this.enqueue(async () => {
            await this.components.destroy(destroyRoot, this.context());

            if (destroyRoot === this.root) {
                this.events.destroy();
                this.root.removeAttribute('data-catalyst-ui-runtime');
                this.started = false;
                runtimes.delete(this.root);
                runtimeInstances.delete(this);
            }
        });
    }

    handleDomUpdated(event) {
        const target = event?.detail?.target;
        if (!(target instanceof HTMLElement)) {
            return;
        }

        if (target === this.root || this.root.contains(target)) {
            void this.scan(target);
        }
    }
}

export async function initUiRuntime(options = {}) {
    const root = resolveRoot(options);
    if (!(root instanceof HTMLElement)) {
        return null;
    }

    const existing = runtimes.get(root);
    if (existing) {
        await existing.start();
        return existing;
    }

    const runtime = new CatalystUiRuntime(root, options);
    runtimes.set(root, runtime);
    runtimeInstances.add(runtime);
    await runtime.start();

    return runtime;
}

export async function scanUiRuntime(root = document.body) {
    const target = root instanceof HTMLElement ? root : document.body;
    if (!(target instanceof HTMLElement)) {
        return null;
    }

    const owner = Array.from(runtimeInstances).find((runtime) => (
        target === runtime.root || runtime.root.contains(target)
    ));

    return owner ? owner.scan(target) : null;
}

export async function destroyUiRuntime(root = document.body) {
    const target = root instanceof HTMLElement ? root : document.body;
    if (!(target instanceof HTMLElement)) {
        return null;
    }

    const owner = Array.from(runtimeInstances).find((runtime) => (
        target === runtime.root || runtime.root.contains(target)
    ));

    return owner ? owner.destroy(target) : null;
}

bindUiRuntimeApi({
    initRuntime: initUiRuntime,
    scan: scanUiRuntime,
    destroy: destroyUiRuntime,
});

export function registerUiComponent(componentAdapter) {
    queueUiComponent(componentAdapter);
}

export function registerUiEvent(eventAdapter) {
    queueUiEvent(eventAdapter);
}

consumeUiRegistrations({
    onComponent(componentAdapter) {
        extensionAdapters.set(componentAdapter.name, componentAdapter);
        runtimeInstances.forEach((runtime) => runtime.registerComponent(componentAdapter));
    },
    onEvent(eventAdapter) {
        extensionEvents.set(eventAdapter.name, eventAdapter);
        runtimeInstances.forEach((runtime) => {
            if (runtime.started) {
                runtime.registerEvent(eventAdapter);
            }
        });
    },
});

function readJsonTransport(id) {
    const source = document.getElementById(id);
    if (!(source instanceof HTMLScriptElement)) {
        return {};
    }

    try {
        const config = JSON.parse(source.textContent || '{}');
        return config && typeof config === 'object' ? config : {};
    } catch (error) {
        console.error(`[Catalyst UI] Invalid JSON transport: ${id}.`, error);
        return {};
    }
}

function flushPendingToasts(pendingToasts) {
    if (!Array.isArray(pendingToasts)) {
        return;
    }

    pendingToasts.forEach((toast) => {
        const type = typeof toast?.type === 'string' ? toast.type : 'info';
        const message = typeof toast?.message === 'string' ? toast.message : '';
        const notifier = Catalyst[type];

        if (message !== '' && typeof notifier === 'function') {
            notifier.call(Catalyst, message);
        }
    });
}

export async function bootCatalystUiRuntime() {
    if (!(document.body instanceof HTMLElement)) {
        return null;
    }

    const ssrState = readJsonTransport('catalyst-ssr-state');
    window.__catalystModuleVersion = moduleVersion;
    const { ActivityManager } = await loadRuntimeModule('core.activity', './activity-manager.js');
    activity = activity ?? new ActivityManager(document.body).init();

    if (!Catalyst.initialized) {
        Catalyst.init();
    }

    const runtime = await initUiRuntime({ root: document.body, ssrState });
    activity.ready();
    flushPendingToasts(ssrState.toasts);
    return runtime;
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        void bootCatalystUiRuntime();
    }, { once: true });
} else {
    void bootCatalystUiRuntime();
}
