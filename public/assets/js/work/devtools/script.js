import { getHttpClient, summarizeResponseError } from '../../catalyst/core/http.js';
import { setButtonLoading, clearButtonLoading } from '../../catalyst/core/loading.js';
import {
    registerUiComponent,
    registerUiEvent,
} from '../../catalyst/runtime/registration-queue.js';

const http = getHttpClient();
const MERMAID_SCRIPT_SRC = 'https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.min.js';

let uploadHandlerBound = false;
let umlTheme = null;
let umlInitialized = false;

registerUiEvent({
    name: 'devtools.actions',
    target: 'document',
    type: 'click',
    listener: handleDevToolsAction,
});

registerUiEvent({
    name: 'devtools.form-submit',
    target: 'document',
    type: 'submit',
    listener: handleDevToolsFormSubmit,
});

registerUiEvent({
    name: 'devtools.form-response',
    target: 'document',
    type: 'catalyst:form:response',
    listener: handleDevToolsFormResponse,
});

registerUiComponent({
    name: 'devtools.upload',
    phase: 'scan',
    selector: '#u17-form',
    mount: bindUploadHandler,
});

registerUiComponent({
    name: 'devtools.uml',
    phase: 'scan',
    selector: '.uml-showcase',
    mount: initUmlShowcase,
});

function handleDevToolsAction(event) {
    const origin = event.target instanceof Element ? event.target : null;
    const element = origin?.closest('[data-devtools-action]');
    if (!(element instanceof HTMLElement)) {
        return;
    }

    const action = element.dataset.devtoolsAction;
    switch (action) {
        case 'api-call':
            event.preventDefault();
            void apiCall(element, element.dataset.url);
            break;
        case 'toast':
            event.preventDefault();
            catalystToast(element.dataset.type, element.dataset.message);
            break;
        case 'inspect-json':
            event.preventDefault();
            void inspectJson(element.dataset.url, element.dataset.resultId, element.dataset.preId, element);
            break;
        case 'partial-refresh':
            event.preventDefault();
            void runPartialRefresh(element);
            break;
        case 'activity-foreground':
            event.preventDefault();
            void runActivityDiagnostic(element, 'foreground');
            break;
        case 'activity-background':
            event.preventDefault();
            void runActivityDiagnostic(element, 'background');
            break;
        case 'activity-concurrent':
            event.preventDefault();
            void runActivityDiagnostic(element, 'concurrent');
            break;
        case 'activity-error':
            event.preventDefault();
            void runActivityDiagnostic(element, 'error');
            break;
        case 'clear-validator':
            event.preventDefault();
            document.getElementById('v3-form')?.reset();
            document.getElementById('v3-unique-form')?.reset();
            clearValidatorResultPanels();
            clearUniqueValidatorResultPanel();
            break;
        case 'orm-status':
            event.preventDefault();
            void ormGet('/test-features/orm/status');
            break;
        case 'orm-find-or-fail':
            event.preventDefault();
            void ormGet('/test-features/orm/find-or-fail');
            break;
        case 'orm-user-demo':
            event.preventDefault();
            void ormGet('/test-features/orm/user-demo');
            break;
    }
}

function handleDevToolsFormSubmit(event) {
    const form = event.target;
    if (!(form instanceof HTMLFormElement) || event.defaultPrevented) {
        return;
    }

    switch (form.id) {
        case 'v3-form':
            clearValidatorResultPanels();
            break;
        case 'v3-unique-form':
            clearUniqueValidatorResultPanel();
            break;
        case 'm4-form':
            clearMailResult();
            break;
        case 'orm-mutation-form':
            clearOrmResult();
            break;
    }
}

function handleDevToolsFormResponse(event) {
    const detail = event.detail || {};
    const form = detail.form;
    const data = detail.data || {};

    if (!(form instanceof HTMLFormElement)) {
        return;
    }

    if (form.hasAttribute('data-modal-demo-form') && data.success) {
        window.Catalyst?.closeModal?.();
        return;
    }

    switch (form.id) {
        case 'v3-form':
            handleValidatorResponse(data);
            break;
        case 'v3-unique-form':
            handleUniqueValidatorResponse(data);
            break;
        case 'u17-form':
            handleUploadResponse(form, data);
            break;
        case 'm4-form':
            handleMailResponse(data);
            break;
        case 'orm-mutation-form':
            ormShowResult(data);
            break;
    }
}

async function apiCall(trigger, url) {
    try {
        if (trigger) {
            setButtonLoading(trigger);
        }

        await http.request(url);
    } catch (error) {
        console.error('[devtools] apiCall error:', url, error);
    } finally {
        if (trigger) {
            clearButtonLoading(trigger);
        }
    }
}

function catalystToast(type, message) {
    if (window.Catalyst && typeof window.Catalyst[type] === 'function') {
        window.Catalyst[type](message);
    }
}

async function inspectJson(url, resultId, preId, trigger = null) {
    try {
        if (trigger) {
            setButtonLoading(trigger);
        }

        const { data } = await http.json(url);
        const result = document.getElementById(resultId);
        const pre = document.getElementById(preId);

        if (result && pre) {
            pre.textContent = JSON.stringify(data, null, 2);
            result.style.display = '';
        }
    } catch (error) {
        console.error('[devtools] inspectJson error:', url, error);
    } finally {
        if (trigger) {
            clearButtonLoading(trigger);
        }
    }
}

async function runPartialRefresh(trigger) {
    try {
        if (trigger) {
            setButtonLoading(trigger);
        }

        const { data } = await http.json(trigger.dataset.url);
        if (data.success === false) {
            window.Catalyst?.error(data.message ?? 'Partial refresh failed.');
        }
    } catch (error) {
        console.error('[devtools] partial refresh error:', error);
        window.Catalyst?.error(summarizeResponseError(error));
    } finally {
        if (trigger) {
            clearButtonLoading(trigger);
        }
    }
}

async function runActivityDiagnostic(trigger, mode) {
    const result = document.querySelector('[data-activity-diagnostic-message]');
    const endpoint = '/test-features/api/js-enhancements/partial-refresh';
    const successUrl = `${endpoint}?activity_probe=success`;
    const errorUrl = `${endpoint}?activity_probe=error`;

    if (result instanceof HTMLElement) {
        result.textContent = trigger.dataset.runningMessage || `Running ${mode} activity diagnostic...`;
    }

    try {
        let responses;

        if (mode === 'background') {
            responses = [await http.json(successUrl, { background: true })];
        } else if (mode === 'concurrent') {
            responses = await Promise.all([
                http.json(successUrl),
                http.json(successUrl),
            ]);
        } else if (mode === 'error') {
            responses = [await http.json(errorUrl)];
        } else {
            responses = [await http.json(successUrl)];
        }

        const data = responses.at(-1)?.data ?? {};
        if (result instanceof HTMLElement) {
            result.textContent = data.message ?? `Activity diagnostic ${mode} finished.`;
        }
    } catch (error) {
        console.error('[devtools] activity diagnostic error:', mode, error);
        if (result instanceof HTMLElement) {
            result.textContent = summarizeResponseError(error);
        }
    }
}

function v3RenderErrors(errors, containerEl, errorsEl, successEl) {
    containerEl.classList.remove('d-none');
    errorsEl.innerHTML = '';
    successEl.classList.add('d-none');
    errorsEl.classList.remove('d-none');

    const fields = Object.keys(errors);
    if (fields.length === 0) {
        errorsEl.classList.add('d-none');
        return;
    }

    const ul = document.createElement('ul');
    ul.className = 'list-unstyled mb-0';

    fields.forEach(field => {
        (errors[field] || []).forEach(message => {
            const li = document.createElement('li');
            li.className = 'text-danger small';
            li.textContent = '• ' + message;
            ul.appendChild(li);
        });
    });

    errorsEl.appendChild(ul);
}

function v3RenderSuccess(message, containerEl, errorsEl, successEl) {
    containerEl.classList.remove('d-none');
    errorsEl.innerHTML = '';
    errorsEl.classList.add('d-none');
    successEl.classList.remove('d-none');
    successEl.textContent = '✓ ' + message;
}

function clearValidatorResultPanels() {
    const result = document.getElementById('v3-result');
    const errors = document.getElementById('v3-errors');
    const success = document.getElementById('v3-success');

    result?.classList.add('d-none');
    if (errors) {
        errors.classList.add('d-none');
        errors.innerHTML = '';
    }

    if (success) {
        success.classList.add('d-none');
        success.textContent = '';
    }
}

function clearUniqueValidatorResultPanel() {
    const result = document.getElementById('v3-unique-result');
    const errors = document.getElementById('v3-unique-errors');
    const success = document.getElementById('v3-unique-success');

    result?.classList.add('d-none');
    if (errors) {
        errors.classList.add('d-none');
        errors.innerHTML = '';
    }

    if (success) {
        success.classList.add('d-none');
        success.textContent = '';
    }
}

function handleValidatorResponse(data) {
    const result = document.getElementById('v3-result');
    const errors = document.getElementById('v3-errors');
    const success = document.getElementById('v3-success');

    if (!result || !errors || !success) {
        return;
    }

    if (data.errors) {
        v3RenderErrors(data.errors, result, errors, success);
        return;
    }

    if (data.success) {
        v3RenderSuccess(data.message ?? 'Validation passed!', result, errors, success);
        return;
    }

    v3RenderErrors({ form: [data.message ?? 'Validation failed.'] }, result, errors, success);
}

function handleUniqueValidatorResponse(data) {
    const result = document.getElementById('v3-unique-result');
    const errors = document.getElementById('v3-unique-errors');
    const success = document.getElementById('v3-unique-success');

    if (!result || !errors || !success) {
        return;
    }

    if (data.errors) {
        v3RenderErrors(data.errors, result, errors, success);
        return;
    }

    if (data.success) {
        v3RenderSuccess(data.message ?? 'Email is unique!', result, errors, success);
        return;
    }

    v3RenderErrors({ form: [data.message ?? 'Unique validation failed.'] }, result, errors, success);
}

function u17RenderErrors(errors, containerEl, errorsEl, preEl) {
    containerEl.classList.remove('d-none');
    errorsEl.innerHTML = '';
    errorsEl.classList.remove('d-none');
    preEl.classList.add('d-none');
    preEl.textContent = '';

    const ul = document.createElement('ul');
    ul.className = 'list-unstyled mb-0';

    Object.keys(errors || {}).forEach(field => {
        (errors[field] || []).forEach(message => {
            const li = document.createElement('li');
            li.className = 'text-danger small';
            li.textContent = '• ' + message;
            ul.appendChild(li);
        });
    });

    errorsEl.appendChild(ul);
}

function u17RenderSuccess(payload, containerEl, errorsEl, preEl) {
    containerEl.classList.remove('d-none');
    errorsEl.innerHTML = '';
    errorsEl.classList.add('d-none');
    preEl.classList.remove('d-none');
    preEl.textContent = JSON.stringify(payload, null, 2);
}

function bindUploadHandler() {
    if (uploadHandlerBound) {
        return;
    }

    uploadHandlerBound = true;

    const form = document.getElementById('u17-form');
    const clearBtn = document.getElementById('u17-btn-clear');
    const result = document.getElementById('u17-result');
    const errors = document.getElementById('u17-errors');
    const pre = document.getElementById('u17-result-pre');

    if (!(form instanceof HTMLFormElement) || !result || !errors || !pre) {
        return;
    }

    clearBtn?.addEventListener('click', () => {
        form.reset();
        result.classList.add('d-none');
        errors.innerHTML = '';
        errors.classList.add('d-none');
        pre.textContent = '';
        pre.classList.add('d-none');
    });
}

function handleUploadResponse(form, data) {
    const result = document.getElementById('u17-result');
    const errors = document.getElementById('u17-errors');
    const pre = document.getElementById('u17-result-pre');

    if (!result || !errors || !pre) {
        return;
    }

    if (data.success) {
        u17RenderSuccess(data, result, errors, pre);
        form.reset();
        return;
    }

    if (data.errors) {
        u17RenderErrors(data.errors, result, errors, pre);
        return;
    }

    u17RenderSuccess(data, result, errors, pre);
}

function clearMailResult() {
    const result = document.getElementById('m4-result');
    const output = document.getElementById('m4-output');

    if (result && output) {
        result.classList.add('d-none');
        output.textContent = '';
    }
}

function handleMailResponse(data) {
    const result = document.getElementById('m4-result');
    const output = document.getElementById('m4-output');

    if (!result || !output) {
        return;
    }

    output.textContent = data.message ?? JSON.stringify(data);
    result.classList.remove('d-none');
}

function clearOrmResult() {
    const container = document.getElementById('orm-result');
    const pre = document.getElementById('orm-result-pre');

    if (container && pre) {
        container.style.display = '';
        pre.textContent = '';
    }
}

function ormShowResult(data) {
    const container = document.getElementById('orm-result');
    const pre = document.getElementById('orm-result-pre');

    if (container && pre) {
        pre.textContent = JSON.stringify(data, null, 2);
        container.style.display = '';
    }

    if (data.success !== false) {
        window.Catalyst?.success('ORM response received.');
        return;
    }

    window.Catalyst?.error(data.message ?? 'ORM error.');
}

async function ormGet(url) {
    try {
        const { data } = await http.json(url);
        ormShowResult(data);
    } catch (error) {
        console.error('[devtools] ormGet error:', url, error);
    }
}

async function initUmlShowcase() {
    const showcase = document.querySelector('.uml-showcase');
    if (!showcase || umlInitialized) {
        return;
    }

    umlInitialized = true;
    await ensureExternalScript(MERMAID_SCRIPT_SRC, 'catalyst-devtools-mermaid');

    if (!window.mermaid) {
        console.error('[devtools] Mermaid runtime unavailable after script load.');
        return;
    }

    const buttons = Array.from(showcase.querySelectorAll('[data-bs-toggle="tab"]'));
    const root = document.documentElement;

    cacheMermaidSources();
    ensureMermaidTheme(true);

    buttons.forEach(button => {
        button.addEventListener('shown.bs.tab', (event) => {
            const selector = event.target instanceof HTMLElement
                ? event.target.getAttribute('data-bs-target')
                : '';
            const panel = selector ? document.querySelector(selector) : null;
            if (panel instanceof HTMLElement) {
                void renderUmlTab(panel);
            }
        });
    });

    const active = showcase.querySelector('.tab-pane.active');
    if (active) {
        void renderUmlTab(active);
    }

    if (typeof MutationObserver === 'function') {
        new MutationObserver(mutations => {
            const themeChanged = mutations.some(mutation =>
                mutation.type === 'attributes'
                && ['data-bs-theme', 'data-skin'].includes(mutation.attributeName)
            );

            if (!themeChanged) {
                return;
            }

            ensureMermaidTheme(false);

            const current = showcase.querySelector('.tab-pane.active');
            if (current) {
                void renderUmlTab(current);
            }
        }).observe(root, { attributes: true });
    }
}

function resolveThemeColor(customProperty, fallback) {
    const probe = document.createElement('span');
    probe.hidden = true;
    probe.style.color = `var(${customProperty}, ${fallback})`;
    document.body.appendChild(probe);
    const resolved = getComputedStyle(probe).color;
    probe.remove();

    return resolved || fallback;
}

function ensureMermaidTheme(force) {
    const darkMode = document.documentElement.getAttribute('data-bs-theme') === 'dark';
    const primary = resolveThemeColor('--theme-primary', '#0d6efd');
    const primaryStrong = resolveThemeColor('--theme-primary-text-emphasis', primary);
    const bodyBackground = resolveThemeColor('--bs-body-bg', darkMode ? '#212529' : '#ffffff');
    const bodyColor = resolveThemeColor('--bs-body-color', darkMode ? '#dee2e6' : '#212529');
    const surface = resolveThemeColor('--bs-secondary-bg', bodyBackground);
    const surfaceAlt = resolveThemeColor('--bs-tertiary-bg', surface);
    const themeVariables = {
        darkMode,
        background: bodyBackground,
        primaryColor: surface,
        primaryTextColor: bodyColor,
        primaryBorderColor: primary,
        secondaryColor: surfaceAlt,
        secondaryTextColor: bodyColor,
        secondaryBorderColor: primaryStrong,
        tertiaryColor: bodyBackground,
        tertiaryTextColor: bodyColor,
        tertiaryBorderColor: primary,
        lineColor: primary,
        textColor: bodyColor,
        mainBkg: surface,
        nodeBorder: primary,
        clusterBkg: bodyBackground,
        clusterBorder: primaryStrong,
        edgeLabelBackground: bodyBackground,
        noteBkgColor: surfaceAlt,
        noteTextColor: bodyColor,
        noteBorderColor: primary,
        fontSize: '18px',
    };
    const nextThemeSignature = JSON.stringify(themeVariables);

    if (!force && nextThemeSignature === umlTheme) {
        return;
    }

    umlTheme = nextThemeSignature;
    window.mermaid.initialize({
        startOnLoad: false,
        theme: 'base',
        themeVariables,
        flowchart: {
            curve: 'basis',
            padding: 24,
            nodeSpacing: 50,
            rankSpacing: 60,
            useMaxWidth: false,
        },
    });

    document.querySelectorAll('.mermaid').forEach(node => {
        node.innerHTML = '';
        node.removeAttribute('data-processed');
    });
}

function cacheMermaidSources() {
    document.querySelectorAll('.mermaid').forEach(node => {
        if (!node.dataset.mermaidSource) {
            node.dataset.mermaidSource = (node.textContent || '').trim();
        }
    });
}

async function renderMermaidNode(node, index) {
    if (!node || node.querySelector('svg')) {
        return;
    }

    const source = node.dataset.mermaidSource || (node.textContent || '').trim();
    if (source === '') {
        return;
    }

    try {
        const renderId = `catalyst-uml-${Date.now()}-${index}`;
        const renderResult = await window.mermaid.render(renderId, source);
        node.innerHTML = renderResult.svg;
        node.setAttribute('data-processed', 'true');

        if (typeof renderResult.bindFunctions === 'function') {
            renderResult.bindFunctions(node);
        }
    } catch (error) {
        console.error('[devtools] Mermaid render failed:', error);
        node.innerHTML = '<div class="alert alert-danger py-2 small mb-0">Diagram failed to render.</div>';
    }
}

async function renderUmlTab(tabEl) {
    const nodes = Array.from(tabEl.querySelectorAll('.mermaid'));
    for (let index = 0; index < nodes.length; index += 1) {
        await renderMermaidNode(nodes[index], index);
    }
}

function ensureExternalScript(src, datasetKey) {
    const existing = document.querySelector(`script[data-external-script="${datasetKey}"]`);

    if (existing) {
        return existing.dataset.loaded === 'true'
            ? Promise.resolve()
            : waitForExternalScript(existing);
    }

    const script = document.createElement('script');
    script.src = src;
    script.async = true;
    script.defer = true;
    script.dataset.externalScript = datasetKey;
    script.dataset.loaded = 'false';

    document.head.appendChild(script);

    return waitForExternalScript(script);
}

function waitForExternalScript(script) {
    return new Promise((resolve, reject) => {
        if (script.dataset.loaded === 'true') {
            resolve();
            return;
        }

        script.addEventListener('load', () => {
            script.dataset.loaded = 'true';
            resolve();
        }, { once: true });

        script.addEventListener('error', () => {
            reject(new Error(`Failed to load external script: ${script.src}`));
        }, { once: true });
    });
}
