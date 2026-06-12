const boundRoots = new WeakSet();
const autosaveTimers = new WeakMap();

function builderForms(root) {
    const forms = Array.from(root.querySelectorAll('[data-form-builder]'));
    if (root.matches?.('[data-form-builder]')) {
        forms.unshift(root);
    }

    return forms.filter((form) => form instanceof HTMLFormElement);
}

function controlValues(form, name) {
    const controls = Array.from(form.elements).filter((control) => control.name === name);

    return controls.flatMap((control) => {
        if (control instanceof HTMLInputElement && ['checkbox', 'radio'].includes(control.type)) {
            return control.checked ? [control.value] : [];
        }
        if (control instanceof HTMLSelectElement && control.multiple) {
            return Array.from(control.selectedOptions, (option) => option.value);
        }
        return 'value' in control ? [String(control.value)] : [];
    });
}

function setDependencyState(wrapper, visible) {
    wrapper.toggleAttribute('hidden', !visible);
    wrapper.toggleAttribute('data-form-dependency-hidden', !visible);

    wrapper.querySelectorAll('input, select, textarea, button').forEach((control) => {
        if (!visible && !control.disabled) {
            control.dataset.formDependencyDisabled = '1';
            control.disabled = true;
            return;
        }

        if (visible && control.dataset.formDependencyDisabled === '1') {
            control.disabled = false;
            delete control.dataset.formDependencyDisabled;
        }
    });
}

function updateDependencies(form) {
    form.querySelectorAll('[data-depends-on]').forEach((wrapper) => {
        const source = wrapper.dataset.dependsOn ?? '';
        const expected = (wrapper.dataset.dependsValues ?? '')
            .split(/\s+/)
            .filter((value) => value !== '');
        const current = controlValues(form, source);
        const visible = expected.length === 0
            ? current.some((value) => value !== '' && value !== '0')
            : expected.some((value) => current.includes(value));

        setDependencyState(wrapper, visible);
    });
}

function repeaterItems(repeater) {
    const container = repeater.querySelector('[data-repeater-items]');
    if (!(container instanceof HTMLElement)) {
        return [];
    }

    return Array.from(container.children).filter((item) => item.matches('[data-repeater-item]'));
}

function nextRepeaterIndex(repeater) {
    let maximum = -1;
    const name = repeater.dataset.repeaterName ?? '';

    repeater.querySelectorAll('[name]').forEach((control) => {
        const fieldName = control.getAttribute('name') ?? '';
        if (!fieldName.startsWith(`${name}[`)) {
            return;
        }

        const match = fieldName.slice(name.length).match(/^\[(\d+)]/);
        if (match) {
            maximum = Math.max(maximum, Number.parseInt(match[1], 10));
        }
    });

    return maximum + 1;
}

function updateRepeaterState(repeater) {
    const items = repeaterItems(repeater);
    const minimum = Number.parseInt(repeater.dataset.repeaterMinItems ?? '0', 10) || 0;
    const maximum = Number.parseInt(repeater.dataset.repeaterMaxItems ?? '0', 10) || 0;
    const add = repeater.querySelector('[data-repeater-add]');
    const empty = repeater.querySelector('[data-repeater-empty]');

    if (add instanceof HTMLButtonElement) {
        add.disabled = maximum > 0 && items.length >= maximum;
    }

    items.forEach((item) => {
        const remove = item.querySelector('[data-repeater-remove]');
        if (remove instanceof HTMLButtonElement) {
            remove.disabled = items.length <= minimum;
        }
    });

    if (empty instanceof HTMLElement) {
        empty.hidden = items.length > 0;
    }
}

function addRepeaterItem(repeater) {
    const template = repeater.querySelector('[data-repeater-template]');
    const container = repeater.querySelector('[data-repeater-items]');
    const maximum = Number.parseInt(repeater.dataset.repeaterMaxItems ?? '0', 10) || 0;
    const items = repeaterItems(repeater);

    if (!(template instanceof HTMLTemplateElement) || !(container instanceof HTMLElement)) {
        return;
    }
    if (maximum > 0 && items.length >= maximum) {
        return;
    }

    const index = nextRepeaterIndex(repeater);
    const markup = template.innerHTML
        .replaceAll('__INDEX__', String(index))
        .replaceAll('__INDEX_LABEL__', String(items.length + 1));
    container.insertAdjacentHTML('beforeend', markup);
    const added = repeaterItems(repeater).at(-1);
    updateRepeaterState(repeater);

    if (added instanceof HTMLElement) {
        document.dispatchEvent(new CustomEvent('catalyst:dom:updated', {
            detail: { target: added },
        }));
    }
}

function removeRepeaterItem(trigger) {
    const repeater = trigger.closest('[data-form-repeater]');
    const item = trigger.closest('[data-repeater-item]');
    if (!(repeater instanceof HTMLElement) || !(item instanceof HTMLElement)) {
        return;
    }

    const minimum = Number.parseInt(repeater.dataset.repeaterMinItems ?? '0', 10) || 0;
    if (repeaterItems(repeater).length <= minimum) {
        return;
    }

    item.remove();
    updateRepeaterState(repeater);
}

function autosaveStorageKey(form) {
    const key = form.getAttribute('data-form-autosave-key') ?? '';
    return key === '' ? '' : `catalyst:form-autosave:${key}`;
}

function isAutosaveControl(control) {
    if (!(control instanceof HTMLInputElement || control instanceof HTMLSelectElement || control instanceof HTMLTextAreaElement)) {
        return false;
    }
    if (control.name === '' || control.disabled) {
        return false;
    }
    if (control instanceof HTMLInputElement && ['file', 'password', 'submit', 'button', 'reset'].includes(control.type)) {
        return false;
    }

    return !['_token', 'csrf_token', '_method'].includes(control.name);
}

function serializeForm(form) {
    return Array.from(form.elements)
        .filter(isAutosaveControl)
        .flatMap((control) => {
            if (control instanceof HTMLInputElement && ['checkbox', 'radio'].includes(control.type)) {
                return control.checked ? [[control.name, control.value]] : [];
            }
            if (control instanceof HTMLSelectElement && control.multiple) {
                return Array.from(control.selectedOptions, (option) => [control.name, option.value]);
            }
            return [[control.name, control.value]];
        });
}

function saveForm(form) {
    const key = autosaveStorageKey(form);
    if (key === '') {
        return;
    }

    try {
        localStorage.setItem(key, JSON.stringify(serializeForm(form)));
    } catch (error) {
        console.warn('[Catalyst FormBuilder] Unable to persist autosave state.', error);
    }
}

function scheduleAutosave(form) {
    if (!(form instanceof HTMLFormElement) || !form.hasAttribute('data-form-autosave')) {
        return;
    }

    const pending = autosaveTimers.get(form);
    if (pending) {
        clearTimeout(pending);
    }
    autosaveTimers.set(form, setTimeout(() => saveForm(form), 250));
}

function restoreForm(form) {
    const key = autosaveStorageKey(form);
    if (!form.hasAttribute('data-form-autosave') || key === '') {
        return;
    }

    let entries;
    try {
        entries = JSON.parse(localStorage.getItem(key) ?? '[]');
    } catch {
        entries = [];
    }
    if (!Array.isArray(entries) || entries.length === 0) {
        return;
    }

    const values = new Map();
    entries.forEach((entry) => {
        if (!Array.isArray(entry) || entry.length !== 2) {
            return;
        }
        const name = String(entry[0]);
        const group = values.get(name) ?? [];
        group.push(String(entry[1]));
        values.set(name, group);
    });

    form.querySelectorAll('[data-form-repeater]').forEach((repeater) => {
        const repeaterName = repeater.dataset.repeaterName ?? '';
        let maximum = -1;

        values.forEach((_saved, name) => {
            if (!name.startsWith(`${repeaterName}[`)) {
                return;
            }
            const match = name.slice(repeaterName.length).match(/^\[(\d+)]/);
            if (match) {
                maximum = Math.max(maximum, Number.parseInt(match[1], 10));
            }
        });

        while (repeaterItems(repeater).length <= maximum) {
            const before = repeaterItems(repeater).length;
            addRepeaterItem(repeater);
            if (repeaterItems(repeater).length === before) {
                break;
            }
        }
    });

    Array.from(form.elements).filter(isAutosaveControl).forEach((control) => {
        if (!values.has(control.name)) {
            return;
        }
        const saved = values.get(control.name);
        if (control instanceof HTMLInputElement && ['checkbox', 'radio'].includes(control.type)) {
            control.checked = saved.includes(control.value);
        } else if (control instanceof HTMLSelectElement && control.multiple) {
            Array.from(control.options).forEach((option) => {
                option.selected = saved.includes(option.value);
            });
        } else {
            control.value = saved[0] ?? '';
        }
    });

    updateDependencies(form);
}

function initializeForm(form) {
    form.querySelectorAll('[data-form-repeater]').forEach(updateRepeaterState);
    restoreForm(form);
    updateDependencies(form);
}

function handleClick(event) {
    const trigger = event.target instanceof Element ? event.target : null;
    const add = trigger?.closest('[data-repeater-add]');
    if (add) {
        const repeater = add.closest('[data-form-repeater]');
        if (repeater instanceof HTMLElement) {
            event.preventDefault();
            addRepeaterItem(repeater);
            scheduleAutosave(repeater.closest('form'));
        }
        return;
    }

    const remove = trigger?.closest('[data-repeater-remove]');
    if (remove) {
        event.preventDefault();
        const form = remove.closest('form');
        removeRepeaterItem(remove);
        if (form instanceof HTMLFormElement) {
            scheduleAutosave(form);
        }
    }
}

function handleMutation(event) {
    const form = event.target instanceof Element
        ? event.target.closest('[data-form-builder]')
        : null;
    if (!(form instanceof HTMLFormElement)) {
        return;
    }

    updateDependencies(form);
    scheduleAutosave(form);
}

function handleFormResponse(event) {
    const form = event.detail?.form;
    if (!(form instanceof HTMLFormElement) || event.detail?.data?.success !== true) {
        return;
    }

    const key = autosaveStorageKey(form);
    if (key !== '') {
        localStorage.removeItem(key);
    }
}

export function initFormBuilder(options = {}) {
    const root = options.root instanceof HTMLElement ? options.root : document.body;
    const eventRoot = options.eventRoot instanceof HTMLElement ? options.eventRoot : root;
    if (!(root instanceof HTMLElement) || !(eventRoot instanceof HTMLElement)) {
        return null;
    }

    builderForms(root).forEach(initializeForm);

    if (!boundRoots.has(eventRoot)) {
        eventRoot.addEventListener('click', handleClick);
        eventRoot.addEventListener('change', handleMutation);
        eventRoot.addEventListener('input', handleMutation);
        document.addEventListener('catalyst:form:response', handleFormResponse);
        boundRoots.add(eventRoot);
    }

    return { root, eventRoot };
}
