(function () {
    const forms = document.querySelectorAll('[data-admin-form-builder]');

    if (!forms.length) {
        return;
    }

    const debounce = (callback, delay) => {
        let timeoutId = null;

        return (...args) => {
            window.clearTimeout(timeoutId);
            timeoutId = window.setTimeout(() => callback(...args), delay);
        };
    };

    const syncFieldDependencies = (form) => {
        const dependencyTargets = form.querySelectorAll('[data-depends-on]');

        dependencyTargets.forEach((wrapper) => {
            const sourceName = wrapper.getAttribute('data-depends-on');
            if (!sourceName) {
                return;
            }

            const source = form.querySelector(`[name="${sourceName}"]`);
            if (!source) {
                return;
            }

            const acceptedValues = (wrapper.getAttribute('data-depends-values') || '')
                .split(' ')
                .map((value) => value.trim())
                .filter(Boolean);

            const currentValue = source.type === 'checkbox'
                ? (source.checked ? source.value : '0')
                : source.value;

            const visible = acceptedValues.length === 0
                ? currentValue !== ''
                : acceptedValues.includes(currentValue);

            wrapper.classList.toggle('d-none', !visible);

            wrapper.querySelectorAll('input, select, textarea').forEach((input) => {
                if (input.type === 'hidden') {
                    return;
                }

                input.disabled = !visible;
            });
        });
    };

    const refreshRepeaterIndexes = (repeater) => {
        repeater.querySelectorAll('[data-repeater-item]').forEach((item, index) => {
            item.querySelectorAll('[name], [id], label[for]').forEach((element) => {
                ['name', 'id', 'for'].forEach((attribute) => {
                    if (!element.hasAttribute(attribute)) {
                        return;
                    }

                    const currentValue = element.getAttribute(attribute);
                    if (!currentValue) {
                        return;
                    }

                    const nextValue = currentValue
                        .replace(/\[\d+\]/g, (match, offset) => {
                            const itemName = repeater.getAttribute('data-repeater-name') || '';
                            if (itemName === '' || offset < itemName.length) {
                                return match;
                            }

                            return '[' + index + ']';
                        })
                        .replace(/-\d+$/g, '-' + index)
                        .replace(/__INDEX__/g, String(index))
                        .replace(/__INDEX_LABEL__/g, String(index + 1));

                    element.setAttribute(attribute, nextValue);
                });
            });

            const label = item.querySelector('.small.text-uppercase');
            if (label) {
                label.textContent = `Item ${index + 1}`;
            }
        });
    };

    const syncRepeaterEmptyState = (repeater) => {
        const itemsContainer = repeater.querySelector('[data-repeater-items]');
        if (!itemsContainer) {
            return;
        }

        const items = itemsContainer.querySelectorAll('[data-repeater-item]');
        let emptyState = itemsContainer.querySelector('[data-repeater-empty]');

        if (!items.length) {
            if (!emptyState) {
                emptyState = document.createElement('div');
                emptyState.className = 'border rounded-3 p-3 text-muted small';
                emptyState.setAttribute('data-repeater-empty', '1');
                emptyState.textContent = 'No items added yet.';
                itemsContainer.appendChild(emptyState);
            }
        } else if (emptyState) {
            emptyState.remove();
        }
    };

    const syncRepeaterActions = (repeater) => {
        const minItems = Number.parseInt(repeater.getAttribute('data-repeater-min-items') || '0', 10);
        const maxItems = Number.parseInt(repeater.getAttribute('data-repeater-max-items') || '0', 10);
        const items = repeater.querySelectorAll('[data-repeater-item]');
        const addButton = repeater.querySelector('[data-repeater-add]');

        if (addButton) {
            addButton.disabled = maxItems > 0 && items.length >= maxItems;
        }

        items.forEach((item) => {
            const removeButton = item.querySelector('[data-repeater-remove]');
            if (removeButton) {
                removeButton.disabled = items.length <= minItems;
            }
        });
    };

    const bindRepeater = (form, repeater) => {
        const itemsContainer = repeater.querySelector('[data-repeater-items]');
        const template = repeater.querySelector('template[data-repeater-template]');
        const addButton = repeater.querySelector('[data-repeater-add]');

        if (!itemsContainer || !template || !addButton) {
            return;
        }

        const sync = () => {
            refreshRepeaterIndexes(repeater);
            syncRepeaterEmptyState(repeater);
            syncRepeaterActions(repeater);
            syncFieldDependencies(form);
        };

        addButton.addEventListener('click', () => {
            const index = itemsContainer.querySelectorAll('[data-repeater-item]').length;
            const html = template.innerHTML
                .replace(/__INDEX__/g, String(index))
                .replace(/__INDEX_LABEL__/g, String(index + 1));

            itemsContainer.insertAdjacentHTML('beforeend', html);
            sync();
        });

        itemsContainer.addEventListener('click', (event) => {
            const target = event.target;
            if (!(target instanceof HTMLElement)) {
                return;
            }

            const button = target.closest('[data-repeater-remove]');
            if (!button) {
                return;
            }

            const item = button.closest('[data-repeater-item]');
            if (!item) {
                return;
            }

            item.remove();
            sync();
        });

        sync();
    };

    const restoreAutosaveDraft = (form, key) => {
        const raw = window.localStorage.getItem(key);
        if (!raw) {
            return;
        }

        let payload = null;

        try {
            payload = JSON.parse(raw);
        } catch {
            return;
        }

        if (!payload || typeof payload !== 'object' || typeof payload.fields !== 'object') {
            return;
        }

        const currentInputs = Array.from(form.querySelectorAll('input, select, textarea'))
            .filter((input) => input instanceof HTMLElement && input.getAttribute('name'));
        const hasMeaningfulValue = currentInputs.some((input) => {
            if (!(input instanceof HTMLInputElement || input instanceof HTMLSelectElement || input instanceof HTMLTextAreaElement)) {
                return false;
            }

            if (input.type === 'hidden') {
                return false;
            }

            if (input.type === 'checkbox' || input.type === 'radio') {
                return input.checked;
            }

            return input.value.trim() !== '';
        });

        if (hasMeaningfulValue) {
            return;
        }

        Object.entries(payload.fields).forEach(([name, value]) => {
            const inputs = form.querySelectorAll(`[name="${CSS.escape(name)}"]`);

            inputs.forEach((input) => {
                if (!(input instanceof HTMLInputElement || input instanceof HTMLSelectElement || input instanceof HTMLTextAreaElement)) {
                    return;
                }

                if (input.type === 'file') {
                    return;
                }

                if (input.type === 'checkbox' || input.type === 'radio') {
                    input.checked = Array.isArray(value)
                        ? value.includes(input.value)
                        : String(value) === input.value;
                    return;
                }

                input.value = Array.isArray(value) ? String(value[0] || '') : String(value ?? '');
            });
        });
    };

    const bindAutosave = (form) => {
        const key = form.getAttribute('data-admin-form-autosave-key');
        if (!key) {
            return;
        }

        restoreAutosaveDraft(form, key);

        const saveDraft = debounce(() => {
            const data = new FormData(form);
            const fields = {};

            data.forEach((value, name) => {
                if (value instanceof File) {
                    return;
                }

                if (Object.prototype.hasOwnProperty.call(fields, name)) {
                    const current = fields[name];
                    fields[name] = Array.isArray(current)
                        ? [...current, value]
                        : [current, value];
                    return;
                }

                fields[name] = value;
            });

            window.localStorage.setItem(key, JSON.stringify({
                savedAt: Date.now(),
                fields,
            }));
        }, 250);

        form.addEventListener('input', saveDraft);
        form.addEventListener('change', saveDraft);
        form.addEventListener('submit', () => window.localStorage.removeItem(key));
    };

    forms.forEach((form) => {
        form.querySelectorAll('[data-admin-repeater="1"]').forEach((repeater) => {
            bindRepeater(form, repeater);
        });

        syncFieldDependencies(form);

        if (form.hasAttribute('data-admin-form-autosave')) {
            bindAutosave(form);
        }

        form.addEventListener('change', () => syncFieldDependencies(form));
        form.addEventListener('input', () => syncFieldDependencies(form));
    });
})();
