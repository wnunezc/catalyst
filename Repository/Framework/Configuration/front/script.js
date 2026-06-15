/**
 * Configuration Module — Front Script
 *
 * Modal saves and pretests flow through the canonical FormHandler.
 * This module only reacts to the response envelope to keep local UI in sync.
 *
 * @package Catalyst\Repository\Configuration
 */

import { createElement } from '../../catalyst/core/utils.js';
import { registerUiComponent } from '../../catalyst/runtime/registration-queue.js';

let configurationEventsBound = false;

function bootstrapConfigurationModule(root = document.body) {
    bindSettingsModalEvents();
    initSessionDriverState(root);
    initDkimForms(root);
    hoistSettingsModals(root);
}

registerUiComponent({
    name: 'configuration.module',
    phase: 'scan',
    selector: '[data-settings-modal-form], #dkim-form',
    mount: bootstrapConfigurationModule,
});

const boundAppearanceForms = new WeakSet();
const closedAppearancePresets = {
    'red-cross': { theme: 'light', topbar: 'light', sidenav: 'light' },
    'civil-protection': { theme: 'light', topbar: 'dark', sidenav: 'light' },
    firefighters: { theme: 'light', topbar: 'dark', sidenav: 'dark' },
    grempa: { theme: 'dark', topbar: 'dark', sidenav: 'dark' },
};

registerUiComponent({
    name: 'configuration.appearance',
    phase: 'scan',
    selector: '[data-platform-appearance-form]',
    mount: mountAppearanceForms,
});

function mountAppearanceForms(root) {
    findElements(root, '[data-platform-appearance-form]').forEach((form) => {
        if (!(form instanceof HTMLFormElement) || boundAppearanceForms.has(form)) {
            return;
        }
        boundAppearanceForms.add(form);

        const customizer = form.querySelector('[data-platform-customizer-enabled]');
        const lockedPanel = form.querySelector('[data-platform-locked-customizer]');
        const summary = form.querySelector('[data-platform-policy-summary]');
        const skinInputs = form.querySelectorAll('[data-platform-skin]');
        const themeInputs = form.querySelectorAll('[data-platform-theme]');
        const topbarInputs = form.querySelectorAll('[data-platform-topbar]');
        const sidenavInputs = form.querySelectorAll('[data-platform-sidenav]');
        const brandAssetUploads = form.querySelectorAll('[data-brand-asset-upload]');
        const brandAssetResets = form.querySelectorAll('[data-brand-asset-reset]');

        const selectValue = (inputs, value) => {
            inputs.forEach((input) => {
                if (input instanceof HTMLInputElement) {
                    input.checked = input.value === value;
                }
            });
        };
        const syncPreset = () => {
            const skin = Array.from(skinInputs).find((input) => input.checked)?.value || 'default';
            const preset = closedAppearancePresets[skin] || null;

            if (preset) {
                selectValue(themeInputs, preset.theme);
                selectValue(topbarInputs, preset.topbar);
                selectValue(sidenavInputs, preset.sidenav);
            }

            [themeInputs, topbarInputs, sidenavInputs].forEach((inputs) => {
                inputs.forEach((input) => {
                    if (input instanceof HTMLInputElement) {
                        input.disabled = preset !== null && !input.checked;
                        input.closest('.form-check')?.classList.toggle('is-disabled', input.disabled);
                    }
                });
            });
        };
        const syncPolicy = () => {
            const enabled = customizer instanceof HTMLInputElement && customizer.checked;
            lockedPanel?.classList.toggle('d-none', enabled);
            if (summary instanceof HTMLElement) {
                summary.textContent = enabled
                    ? summary.dataset.enabledText || ''
                    : summary.dataset.lockedText || '';
            }
        };

        customizer?.addEventListener('change', syncPolicy);
        skinInputs.forEach((input) => input.addEventListener('change', syncPreset));
        brandAssetUploads.forEach((upload) => upload.addEventListener('change', () => {
            const asset = upload.dataset.brandAssetUpload || '';
            const reset = form.querySelector(`[data-brand-asset-reset="${asset}"]`);
            if (reset instanceof HTMLInputElement && upload instanceof HTMLInputElement && upload.files?.length) {
                reset.checked = false;
            }
        }));
        brandAssetResets.forEach((reset) => reset.addEventListener('change', () => {
            const asset = reset.dataset.brandAssetReset || '';
            const upload = form.querySelector(`[data-brand-asset-upload="${asset}"]`);
            if (reset instanceof HTMLInputElement && reset.checked && upload instanceof HTMLInputElement) {
                upload.value = '';
            }
        }));
        syncPolicy();
        syncPreset();
    });
}

function hoistSettingsModals(root) {
    findElements(root, '.modal[data-section]').forEach((modal) => {
        if (!(modal instanceof HTMLElement) || modal.dataset.settingsHoisted === '1') {
            return;
        }

        document.body.appendChild(modal);
        modal.dataset.settingsHoisted = '1';
    });
}

function bindSettingsModalEvents() {
    if (configurationEventsBound) {
        return;
    }

    configurationEventsBound = true;

    document.addEventListener('click', (event) => {
        const button = event.target.closest('[data-settings-submit]');
        if (!(button instanceof HTMLElement)) {
            return;
        }

        const form = button.closest('form[data-settings-modal-form]');
        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        form.dataset.settingsLastSubmit = button.dataset.settingsSubmit || 'save';
        form.dataset.settingsLastAction = button.getAttribute('formaction') || form.action || '';
    });

    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || !form.hasAttribute('data-settings-modal-form')) {
            return;
        }

        clearModalAlert(form);
    });

    document.addEventListener('catalyst:form:response', (event) => {
        const detail = event.detail || {};
        const form = detail.form;
        const data = detail.data || {};
        const submitter = detail.submitter;

        if (!(form instanceof HTMLFormElement) || !form.hasAttribute('data-settings-modal-form')) {
            return;
        }

        const modal = form.closest('.modal');
        if (!modal) {
            return;
        }

        clearModalAlert(form);

        const submitKind = submitter?.dataset.settingsSubmit
            || form.dataset.settingsLastSubmit
            || (detail.action?.endsWith('/pretest') || form.dataset.settingsLastAction?.endsWith('/pretest') ? 'pretest' : 'save');

        if (!data.success) {
            if (submitKind === 'pretest' && data.message && !data.errors) {
                showModalAlert(form, data.message, 'danger');
            }

            clearPendingSubmitState(form);
            return;
        }

        const section = form.dataset.section || modal.dataset.section || '';

        if (submitKind === 'pretest') {
            const cleanupWarning = data.data?.cleanup_warning;
            if (cleanupWarning) {
                showModalAlert(form, cleanupWarning, 'warning');
                clearPendingSubmitState(form);
                return;
            }

            showModalAlert(form, data.message ?? getFormText(form, 'settingsPretestSuccess', ''), 'success');
            clearPendingSubmitState(form);
            return;
        }

        updateDisplayValues(section, form);
        clearPendingSubmitState(form);

        bootstrap.Modal.getOrCreateInstance(modal).hide();
    });
}

function initSessionDriverState(root) {
    findElements(root, '#modal-session').forEach((modal) => {
    if (!(modal instanceof HTMLElement) || modal.dataset.configurationSessionBound === '1') {
        return;
    }
    const driver = modal.querySelector('[name="session_driver"]');
    const connection = modal.querySelector('[name="session_connection"]');
    const table = modal.querySelector('[name="session_table"]');

    if (!driver || !connection || !table) {
        return;
    }
    modal.dataset.configurationSessionBound = '1';

    const sync = () => {
        const usesDatabase = driver.value === 'database';

        [connection, table].forEach((input) => {
            input.readOnly = !usesDatabase;
            input.setAttribute('aria-disabled', usesDatabase ? 'false' : 'true');
            input.classList.toggle('bg-body-tertiary', !usesDatabase);
            input.classList.toggle('text-muted', !usesDatabase);
        });
    };

    driver.addEventListener('change', sync);
    modal.addEventListener('shown.bs.modal', sync);
    sync();
    });
}

function updateDisplayValues(section, form) {
    const yesLabel = getFormText(form, 'settingsYesLabel', 'Yes');
    const noLabel = getFormText(form, 'settingsNoLabel', 'No');
    const emptyLabel = getFormText(form, 'settingsEmptyLabel', '—');

    form.querySelectorAll('[name]').forEach((element) => {
        if (element.disabled) {
            return;
        }

        const name = element.getAttribute('name');
        const displayEl = document.getElementById('d-' + section + '-' + name);
        if (!displayEl) {
            return;
        }

        if (element.type === 'password') {
            if (element.value === '') {
                return;
            }

            renderMaskedValue(displayEl);
        } else if (element.type === 'checkbox') {
            renderBadge(displayEl, element.checked ? yesLabel : noLabel, element.checked ? 'success' : 'secondary');
        } else if (element.tagName === 'SELECT') {
            const text = element.options[element.selectedIndex]?.text ?? element.value;
            renderTextValue(displayEl, text, emptyLabel);
        } else {
            renderTextValue(displayEl, element.value, emptyLabel);
        }
    });

    if (section === 'ftp') {
        const protocol = form.querySelector('[name="ftp_protocol"]')?.value || 'ftp';
        const sslDisplay = document.getElementById('d-ftp-ftp_ssl');
        if (sslDisplay) {
            renderBadge(sslDisplay, protocol === 'ftps' ? yesLabel : noLabel, protocol === 'ftps' ? 'success' : 'secondary');
        }
    }
}

function renderTextValue(displayEl, value, emptyLabel) {
    const normalizedValue = typeof value === 'string' ? value : String(value ?? '');

    if (normalizedValue !== '') {
        displayEl.replaceChildren(document.createTextNode(normalizedValue));
        return;
    }

    displayEl.replaceChildren(createElement('span', { className: 'text-muted' }, emptyLabel));
}

function renderMaskedValue(displayEl) {
    displayEl.replaceChildren(createElement('span', { className: 'text-secondary font-monospace' }, '••••••••'));
}

function renderBadge(displayEl, text, variant) {
    displayEl.replaceChildren(createElement('span', { className: `badge text-bg-${variant}` }, text));
}

function getFormText(form, key, fallback = '') {
    if (!(form instanceof HTMLFormElement)) {
        return fallback;
    }

    const value = form.dataset[key];

    return typeof value === 'string' && value !== '' ? value : fallback;
}

function clearModalAlert(form) {
    form.querySelector('.modal-global-alert')?.remove();
}

function clearPendingSubmitState(form) {
    delete form.dataset.settingsLastSubmit;
    delete form.dataset.settingsLastAction;
}

function showModalAlert(form, message, variant = 'danger') {
    clearModalAlert(form);

    const alert = document.createElement('div');
    alert.className = `modal-global-alert alert alert-${variant} py-2 small mb-3`;
    alert.textContent = message;

    const body = form.querySelector('.modal-body');
    if (body) {
        body.prepend(alert);
    }
}

function initDkimForms(root) {
    findElements(root, '#dkim-form').forEach((form) => {
    if (!(form instanceof HTMLFormElement) || form.dataset.configurationDkimBound === '1') {
        return;
    }
    form.dataset.configurationDkimBound = '1';
    const resultEl = document.getElementById('dkim-result');
    const textarea = document.getElementById('dkim-dns-record');

    form.addEventListener('submit', (event) => {
        if (event.defaultPrevented) {
            return;
        }

        if (resultEl && textarea) {
            resultEl.classList.add('d-none');
            textarea.value = '';
        }
    });

    document.addEventListener('catalyst:form:response', (event) => {
        const detail = event.detail || {};
        if (detail.form !== form) {
            return;
        }

        if (detail.data?.success && detail.data?.data?.dnsRecord && resultEl && textarea) {
            textarea.value = detail.data.data.dnsRecord;
            resultEl.classList.remove('d-none');
            textarea.select();
            return;
        }

        if (resultEl && textarea) {
            resultEl.classList.add('d-none');
            textarea.value = '';
        }
    });
    });
}

function findElements(root, selector) {
    const elements = [];
    if (root instanceof Element && root.matches(selector)) {
        elements.push(root);
    }
    if (root?.querySelectorAll) {
        elements.push(...root.querySelectorAll(selector));
    }

    return elements;
}
