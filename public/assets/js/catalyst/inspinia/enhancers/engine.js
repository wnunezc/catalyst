import { appendVersion, loadAssets } from '../../core/asset-loader.js';

const moduleUrl = new URL(import.meta.url);
const moduleVersion = moduleUrl.searchParams.get('v') ?? '';

const choicesInstances = new WeakMap();
const pickrInstances = new WeakMap();
const filePondInstances = new WeakMap();
const wizardInstances = new WeakMap();
const quillSnapshots = new WeakMap();

function vendorAsset(path) {
    return appendVersion(`/assets/vendor/inspinia/${path}`, moduleVersion);
}

function themeColor(name, opacity = 1) {
    const computed = getComputedStyle(document.documentElement);
    const rgbValue = computed.getPropertyValue(`--theme-${name}-rgb`).trim();
    if (rgbValue !== '') {
        return `rgba(${rgbValue}, ${opacity})`;
    }

    return computed.getPropertyValue(`--theme-${name}`).trim();
}

function getTextSelector(root, selector) {
    return Array.from(root.querySelectorAll(selector))
        .filter((element) => element instanceof HTMLElement);
}

function detectRequirements(root, capabilities = null) {
    const detected = {
        dateRange: root.querySelector('[data-toggle="date-picker"], [data-plugin="date-picker"], [data-toggle="date-picker-range"], [data-plugin="date-picker-range"]') !== null,
        flatpickr: root.querySelector('[data-provider="flatpickr"], [data-provider="timepickr"]') !== null,
        pickr: root.querySelector('.classic-colorpicker, .monolith-colorpicker, .nano-colorpicker, .colorpicker-demo, .colorpicker-opacity-hue, .colorpicker-switch, .colorpicker-input, .colorpicker-format') !== null,
        choices: root.querySelector('[data-choices]') !== null,
        select2: root.querySelector('[data-toggle="select2"], [data-plugin="select2"]') !== null,
        wizard: root.querySelector('[data-wizard]') !== null,
        dropzone: root.querySelector('[data-plugin="dropzone"]') !== null,
        filepond: root.querySelector('input.filepond') !== null,
        quill: root.querySelector('#snow-editor, #bubble-editor') !== null,
        summernote: root.querySelector('.summernote') !== null,
        sliders: root.querySelector('[data-slider="default"], #rangeslider_multielement, #nonlinear, #slider1, #slider2, #slider-merging-tooltips, #soft, #slider-vertical, #slider-connect-upper, #slider-vertical-tooltip, #slider-vertical-limit') !== null,
    };

    if (!Array.isArray(capabilities)) {
        return detected;
    }

    return Object.fromEntries(
        Object.entries(detected).map(([name, enabled]) => [name, enabled && capabilities.includes(name)])
    );
}

async function ensureRequiredAssets(requirements) {
    if (requirements.dateRange || requirements.select2 || requirements.summernote) {
        await loadAssets({
            scripts: [vendorAsset('plugins/jquery/jquery.min.js')],
        });
    }

    if (requirements.dateRange) {
        await loadAssets({
            scripts: [
                vendorAsset('plugins/moment/moment.min.js'),
                vendorAsset('plugins/daterangepicker/daterangepicker.js'),
            ],
        });
    }

    if (requirements.flatpickr) {
        await loadAssets({
            scripts: [vendorAsset('plugins/flatpickr/flatpickr.min.js')],
        });
    }

    if (requirements.pickr) {
        await loadAssets({
            scripts: [vendorAsset('plugins/pickr/pickr.min.js')],
        });
    }

    if (requirements.choices) {
        await loadAssets({
            scripts: [vendorAsset('plugins/choices/choices.min.js')],
        });
    }

    if (requirements.select2) {
        await loadAssets({
            scripts: [vendorAsset('plugins/select2/select2.min.js')],
        });
    }

    if (requirements.dropzone) {
        await loadAssets({
            scripts: [vendorAsset('plugins/dropzone/dropzone-min.js')],
        });
    }

    if (requirements.filepond) {
        await loadAssets({
            scripts: [
                vendorAsset('plugins/filepond/filepond.min.js'),
                vendorAsset('plugins/filepond/filepond-plugin-image-preview.min.js'),
                vendorAsset('plugins/filepond/filepond-plugin-file-validate-size.min.js'),
                vendorAsset('plugins/filepond/filepond-plugin-image-exif-orientation.min.js'),
                vendorAsset('plugins/filepond/filepond-plugin-file-encode.min.js'),
            ],
        });
    }

    if (requirements.quill) {
        await loadAssets({
            scripts: [vendorAsset('plugins/quill/quill.js')],
        });
    }

    if (requirements.summernote) {
        await loadAssets({
            scripts: [vendorAsset('plugins/summernote/summernote-bs5.min.js')],
        });
    }

    if (requirements.sliders) {
        await loadAssets({
            scripts: [vendorAsset('plugins/nouislider/nouislider.min.js')],
        });
    }
}

function initDateRangePickers(root) {
    const $ = window.jQuery;
    if (typeof $ !== 'function' || typeof $.fn.daterangepicker !== 'function' || typeof window.moment !== 'function') {
        return;
    }

    const startDate = window.moment().subtract(29, 'days');
    const endDate = window.moment();
    const rangeDefaults = {
        startDate,
        endDate,
        ranges: {
            Today: [window.moment(), window.moment()],
            Yesterday: [window.moment().subtract(1, 'days'), window.moment().subtract(1, 'days')],
            'Last 7 Days': [window.moment().subtract(6, 'days'), window.moment()],
            'Last 30 Days': [window.moment().subtract(29, 'days'), window.moment()],
            'This Month': [window.moment().startOf('month'), window.moment().endOf('month')],
            'Last Month': [window.moment().subtract(1, 'month').startOf('month'), window.moment().subtract(1, 'month').endOf('month')],
        },
        locale: { format: 'MM/DD/YYYY' },
        cancelClass: 'btn-light',
        applyButtonClasses: 'btn-success',
    };

    root.querySelectorAll('[data-toggle="date-picker-range"], [data-plugin="date-picker-range"]').forEach((element) => {
        const target = $(element);
        const existing = target.data('daterangepicker');
        if (existing && typeof existing.remove === 'function') {
            existing.remove();
        }

        const config = $.extend(true, {}, rangeDefaults, target.data());
        const displayTarget = element.getAttribute('data-target-display');
        target.daterangepicker(config, (start, end) => {
            if (displayTarget) {
                $(displayTarget).html(`${start.format('MMMM D, YYYY')} - ${end.format('MMMM D, YYYY')}`);
            }
        });

        if (displayTarget) {
            $(displayTarget).html(`${startDate.format('MMMM D, YYYY')} - ${endDate.format('MMMM D, YYYY')}`);
        }
    });

    const singleDefaults = {
        singleDatePicker: true,
        showDropdowns: true,
        locale: { format: 'MM/DD/YYYY' },
        cancelClass: 'btn-light',
        applyButtonClasses: 'btn-success',
    };

    root.querySelectorAll('[data-toggle="date-picker"], [data-plugin="date-picker"]').forEach((element) => {
        const target = $(element);
        const existing = target.data('daterangepicker');
        if (existing && typeof existing.remove === 'function') {
            existing.remove();
        }

        const config = $.extend(true, {}, singleDefaults, target.data());
        if (typeof config.locale === 'string') {
            try {
                config.locale = JSON.parse(config.locale.replace(/'/g, '"'));
            } catch (error) {
                console.warn('Catalyst UI: invalid daterangepicker locale config', error);
            }
        }

        target.daterangepicker(config);
    });
}

function initFlatpickrInputs(root) {
    if (typeof window.flatpickr !== 'function') {
        return;
    }

    root.querySelectorAll('[data-provider="flatpickr"], [data-provider="timepickr"]').forEach((element) => {
        if (!(element instanceof HTMLElement)) {
            return;
        }

        if (element._flatpickr && typeof element._flatpickr.destroy === 'function') {
            element._flatpickr.destroy();
        }

        const provider = element.getAttribute('data-provider');
        if (provider === 'flatpickr') {
            const config = {
                disableMobile: true,
                defaultDate: new Date(),
            };

            const dateFormat = element.getAttribute('data-date-format');
            if (dateFormat) {
                config.dateFormat = dateFormat;
            }

            if (element.hasAttribute('data-enable-time')) {
                config.enableTime = true;
                config.dateFormat = `${config.dateFormat ?? 'Y-m-d'} H:i`;
            }

            const altFormat = element.getAttribute('data-altformat') ?? element.getAttribute('data-altFormat');
            if (altFormat) {
                config.altInput = true;
                config.altFormat = altFormat;
            }

            const minDate = element.getAttribute('data-mindate') ?? element.getAttribute('data-minDate');
            if (minDate) {
                config.minDate = minDate;
            }

            const maxDate = element.getAttribute('data-maxdate') ?? element.getAttribute('data-maxDate');
            if (maxDate) {
                config.maxDate = maxDate;
            }

            const defaultDate = element.getAttribute('data-default-date');
            if (defaultDate) {
                config.defaultDate = defaultDate;
            }

            if (element.hasAttribute('data-multiple-date')) {
                config.mode = 'multiple';
            }

            if (element.hasAttribute('data-range-date')) {
                config.mode = 'range';
            }

            if (element.hasAttribute('data-inline-date')) {
                config.inline = true;
            }

            const disableDate = element.getAttribute('data-disable-date');
            if (disableDate) {
                config.disable = disableDate.split(',');
            }

            if (element.hasAttribute('data-week-number')) {
                config.weekNumbers = true;
            }

            window.flatpickr(element, config);
            return;
        }

        const timeConfig = {
            enableTime: true,
            noCalendar: true,
            dateFormat: 'H:i',
            defaultDate: new Date(),
        };

        if (element.hasAttribute('data-time-hrs')) {
            timeConfig.time_24hr = true;
        }

        const minTime = element.getAttribute('data-min-time');
        if (minTime) {
            timeConfig.minTime = minTime;
        }

        const maxTime = element.getAttribute('data-max-time');
        if (maxTime) {
            timeConfig.maxTime = maxTime;
        }

        const defaultTime = element.getAttribute('data-default-time');
        if (defaultTime) {
            timeConfig.defaultDate = defaultTime;
        }

        const inlineTime = element.getAttribute('data-time-inline');
        if (inlineTime) {
            timeConfig.inline = true;
            timeConfig.defaultDate = inlineTime;
        }

        window.flatpickr(element, timeConfig);
    });
}

function initColorPickers(root) {
    if (!window.Pickr || typeof window.Pickr.create !== 'function') {
        return;
    }

    const swatches = [
        'rgba(244, 67, 54, 1)',
        'rgba(233, 30, 99, 0.95)',
        'rgba(156, 39, 176, 0.9)',
        'rgba(103, 58, 183, 0.85)',
        'rgba(63, 81, 181, 0.8)',
        'rgba(33, 150, 243, 0.75)',
        'rgba(3, 169, 244, 0.7)',
    ];

    const definitions = [
        ['.classic-colorpicker', {
            theme: 'classic',
            default: themeColor('primary'),
            swatches: [
                ...swatches,
                'rgba(0, 188, 212, 0.7)',
                'rgba(0, 150, 136, 0.75)',
                'rgba(76, 175, 80, 0.8)',
                'rgba(139, 195, 74, 0.85)',
                'rgba(205, 220, 57, 0.9)',
                'rgba(255, 235, 59, 0.95)',
                'rgba(255, 193, 7, 1)',
            ],
            components: {
                preview: true,
                opacity: true,
                hue: true,
                interaction: {
                    hex: true,
                    rgba: true,
                    hsva: true,
                    input: true,
                    clear: true,
                    save: true,
                },
            },
        }],
        ['.monolith-colorpicker', {
            theme: 'monolith',
            default: themeColor('danger'),
            swatches,
            defaultRepresentation: 'HEXA',
            components: {
                preview: true,
                opacity: true,
                hue: true,
                interaction: {
                    input: true,
                    clear: true,
                    save: true,
                },
            },
        }],
        ['.nano-colorpicker', {
            theme: 'nano',
            default: themeColor('info'),
            swatches,
            defaultRepresentation: 'HEXA',
            components: {
                preview: true,
                opacity: true,
                hue: true,
                interaction: {
                    input: true,
                    clear: true,
                    save: true,
                },
            },
        }],
        ['.colorpicker-demo', {
            theme: 'monolith',
            default: themeColor('primary'),
            components: {
                preview: true,
                interaction: {
                    clear: true,
                    save: true,
                },
            },
        }],
        ['.colorpicker-opacity-hue', {
            theme: 'monolith',
            default: themeColor('danger'),
            components: {
                preview: true,
                opacity: true,
                hue: true,
                interaction: {
                    clear: true,
                    save: true,
                },
            },
        }],
        ['.colorpicker-switch', {
            theme: 'monolith',
            default: themeColor('info'),
            swatches,
            components: {
                preview: true,
                opacity: true,
                hue: true,
                interaction: {
                    clear: true,
                    save: true,
                },
            },
        }],
        ['.colorpicker-input', {
            theme: 'monolith',
            default: '#f7b84b',
            swatches,
            components: {
                preview: true,
                opacity: true,
                hue: true,
                interaction: {
                    input: true,
                    clear: true,
                    save: true,
                },
            },
        }],
        ['.colorpicker-format', {
            theme: 'monolith',
            default: '#f06548',
            swatches,
            components: {
                preview: true,
                opacity: true,
                hue: true,
                interaction: {
                    hex: true,
                    rgba: true,
                    hsva: true,
                    input: true,
                    clear: true,
                    save: true,
                },
            },
        }],
    ];

    definitions.forEach(([selector, options]) => {
        root.querySelectorAll(selector).forEach((element) => {
            const existing = pickrInstances.get(element);
            if (existing && typeof existing.destroyAndRemove === 'function') {
                existing.destroyAndRemove();
            }

            const instance = window.Pickr.create({
                el: element,
                ...options,
            });
            pickrInstances.set(element, instance);
        });
    });
}

function initChoicesInputs(root) {
    const Choices = window.Choices;
    if (typeof Choices !== 'function') {
        return;
    }

    root.querySelectorAll('[data-choices]').forEach((element) => {
        const existing = choicesInstances.get(element);
        if (existing && typeof existing.destroy === 'function') {
            existing.destroy();
        }

        const instance = new Choices(element, {
            placeholderValue: element.hasAttribute('data-choices-groups')
                ? 'This is a placeholder set in the config'
                : undefined,
            searchEnabled: element.hasAttribute('data-choices-search-true'),
            removeItemButton: element.hasAttribute('data-choices-removeitem') || element.hasAttribute('data-choices-multiple-remove'),
            shouldSort: !element.hasAttribute('data-choices-sorting-false'),
            maxItemCount: element.getAttribute('data-choices-limit') ?? undefined,
            duplicateItemsAllowed: !element.hasAttribute('data-choices-text-unique-true'),
            addItems: !element.hasAttribute('data-choices-text-disabled-true'),
        });

        if (element.hasAttribute('data-choices-text-disabled-true')) {
            instance.disable();
        }

        choicesInstances.set(element, instance);
    });
}

function initSelect2Inputs(root) {
    const $ = window.jQuery;
    if (typeof $ !== 'function' || typeof $.fn.select2 !== 'function') {
        return;
    }

    root.querySelectorAll('[data-toggle="select2"], [data-plugin="select2"]').forEach((element) => {
        const target = $(element);
        if (target.hasClass('select2-hidden-accessible')) {
            target.select2('destroy');
        }

        target.select2();
    });
}

function ensureJqueryTooltipBridge($) {
    if (typeof $ !== 'function' || typeof $.fn !== 'object') {
        return;
    }

    if (typeof $.fn.tooltip === 'function') {
        return;
    }

    const BootstrapTooltip = window.bootstrap?.Tooltip;
    if (typeof BootstrapTooltip !== 'function') {
        $.fn.tooltip = function tooltipNoop() {
            return this;
        };
        return;
    }

    $.fn.tooltip = function tooltipBridge(option) {
        this.each(function bindTooltip() {
            if (!(this instanceof HTMLElement)) {
                return;
            }

            const existing = BootstrapTooltip.getInstance(this);

            if (typeof option === 'string') {
                if (option === 'dispose') {
                    existing?.dispose();
                    return;
                }

                if (existing && typeof existing[option] === 'function') {
                    existing[option]();
                }
                return;
            }

            existing?.dispose();
            BootstrapTooltip.getOrCreateInstance(this, option ?? {});
        });

        return this;
    };
}

function ensureJqueryModalBridge($) {
    if (typeof $ !== 'function' || typeof $.fn !== 'object' || typeof $.fn.modal === 'function') {
        return;
    }

    const BootstrapModal = window.bootstrap?.Modal;
    if (typeof BootstrapModal !== 'function') {
        return;
    }

    $.fn.modal = function modalBridge(option) {
        this.each(function bindModal() {
            if (!(this instanceof HTMLElement)) {
                return;
            }

            const existing = BootstrapModal.getInstance(this);
            if (typeof option === 'string') {
                if (option === 'dispose') {
                    existing?.dispose();
                    return;
                }

                const instance = existing ?? BootstrapModal.getOrCreateInstance(this);
                if (typeof instance[option] === 'function') {
                    instance[option]();
                }
                return;
            }

            BootstrapModal.getOrCreateInstance(this, option ?? {});
        });

        return this;
    };
}

class WizardController {
    constructor(wizardRoot) {
        this.root = wizardRoot;
        this.form = wizardRoot.closest('form');
        this.validate = this.form?.hasAttribute('data-wizard-validation') ?? false;
        this.tabs = Array.from(wizardRoot.querySelectorAll('[data-wizard-nav] .nav-link'));
        this.tabPanes = Array.from(wizardRoot.querySelectorAll('[data-wizard-content] .tab-pane'));
        this.progressBar = wizardRoot.querySelector('[data-wizard-progress]');
        this.currentIndex = this.tabs.findIndex((tab) => tab.classList.contains('active'));
        this.currentIndex = this.currentIndex >= 0 ? this.currentIndex : 0;
        this.abortController = new AbortController();
    }

    destroy() {
        this.abortController.abort();
    }

    init() {
        this.disableFutureTabs();
        this.bindTabClicks();
        this.bindButtons();
        this.updateProgress(this.currentIndex);
        this.showTab(this.currentIndex);
    }

    disableFutureTabs() {
        if (!this.validate) {
            return;
        }

        this.tabs.forEach((tab, index) => {
            tab.classList.toggle('disabled', index > 0);
        });
    }

    bindTabClicks() {
        this.tabs.forEach((tab, index) => {
            tab.addEventListener('click', (event) => {
                event.preventDefault();
                if (this.validate && index > this.currentIndex && !this.validateStep(this.currentIndex)) {
                    event.stopImmediatePropagation();
                    return;
                }

                this.showTab(index);
            }, { signal: this.abortController.signal });

            tab.addEventListener('shown.bs.tab', () => {
                this.currentIndex = index;
                this.updateProgress(index);
            }, { signal: this.abortController.signal });
        });
    }

    bindButtons() {
        this.root.querySelectorAll('[data-wizard-next]').forEach((button) => {
            button.addEventListener('click', () => this.nextStep(), { signal: this.abortController.signal });
        });

        this.root.querySelectorAll('[data-wizard-prev]').forEach((button) => {
            button.addEventListener('click', () => this.prevStep(), { signal: this.abortController.signal });
        });

        this.form?.addEventListener('submit', () => {
            if (this.progressBar instanceof HTMLElement) {
                this.progressBar.style.width = '100%';
            }
        }, { signal: this.abortController.signal });
    }

    nextStep() {
        if (this.currentIndex >= this.tabs.length - 1) {
            return;
        }

        if (this.validate && !this.validateStep(this.currentIndex)) {
            return;
        }

        if (this.validate) {
            this.tabs[this.currentIndex + 1]?.classList.remove('disabled');
        }

        this.tabs[this.currentIndex]?.classList.add('wizard-item-done');
        this.showTab(this.currentIndex + 1);
    }

    prevStep() {
        if (this.currentIndex <= 0) {
            return;
        }

        this.tabs[this.currentIndex - 1]?.classList.remove('wizard-item-done');
        this.showTab(this.currentIndex - 1);
    }

    validateStep(index) {
        if (!this.validate) {
            return true;
        }

        const inputs = this.tabPanes[index]?.querySelectorAll('input, select, textarea') ?? [];
        let isValid = true;

        inputs.forEach((input) => {
            input.classList.remove('is-invalid', 'is-valid');
            if (typeof input.checkValidity === 'function' && input.checkValidity()) {
                input.classList.add('is-valid');
                return;
            }

            input.classList.add('is-invalid');
            isValid = false;
        });

        return isValid;
    }

    updateProgress(index) {
        if (!(this.progressBar instanceof HTMLElement) || this.tabs.length <= 1) {
            return;
        }

        this.progressBar.style.width = `${Math.min((index / (this.tabs.length - 1)) * 100, 100)}%`;
    }

    showTab(index) {
        if (index < 0 || index >= this.tabs.length) {
            return;
        }

        if (this.validate && this.tabs[index]?.classList.contains('disabled')) {
            return;
        }

        const bootstrapTab = window.bootstrap?.Tab;
        if (typeof bootstrapTab === 'function') {
            new bootstrapTab(this.tabs[index]).show();
            return;
        }

        this.applyTabState(index);
    }

    applyTabState(index) {
        if (index < 0 || index >= this.tabs.length) {
            return;
        }

        const previousIndex = this.currentIndex;
        const previousTab = this.tabs[previousIndex] ?? null;
        const nextTab = this.tabs[index] ?? null;
        const previousPane = this.tabPanes[previousIndex] ?? null;
        const nextPane = this.tabPanes[index] ?? null;

        this.tabs.forEach((tab, tabIndex) => {
            const isActive = tabIndex === index;
            tab.classList.toggle('active', isActive);
            tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
        });

        if (!(nextPane instanceof HTMLElement)) {
            this.currentIndex = index;
            this.updateProgress(index);
            return;
        }

        if (!(previousPane instanceof HTMLElement) || previousPane === nextPane || !previousPane.classList.contains('fade') || !nextPane.classList.contains('fade')) {
            this.tabPanes.forEach((pane, paneIndex) => {
                const isActive = paneIndex === index;
                pane.classList.toggle('active', isActive);
                pane.classList.toggle('show', isActive);
            });

            this.finishTabTransition(index, nextTab, previousTab);
            return;
        }

        this.tabPanes.forEach((pane, paneIndex) => {
            if (paneIndex !== previousIndex && paneIndex !== index) {
                pane.classList.remove('active', 'show');
            }
        });

        previousPane.classList.remove('show');
        nextPane.classList.add('active');
        nextPane.scrollTop = 0;
        void nextPane.offsetWidth;
        nextPane.classList.add('show');

        const duration = Math.max(
            this.resolveTransitionDuration(previousPane),
            this.resolveTransitionDuration(nextPane),
            150
        );

        window.setTimeout(() => {
            previousPane.classList.remove('active');
            this.finishTabTransition(index, nextTab, previousTab);
        }, duration);
    }

    finishTabTransition(index, nextTab, previousTab) {
        this.currentIndex = index;
        this.updateProgress(index);

        if (nextTab instanceof HTMLElement) {
            nextTab.dispatchEvent(new CustomEvent('shown.bs.tab', {
                bubbles: true,
                detail: {
                    relatedTarget: previousTab,
                },
            }));
        }
    }

    resolveTransitionDuration(element) {
        if (!(element instanceof HTMLElement)) {
            return 0;
        }

        const computed = window.getComputedStyle(element);
        const durationParts = computed.transitionDuration.split(',').map((value) => value.trim());
        const delayParts = computed.transitionDelay.split(',').map((value) => value.trim());
        const toMs = (value) => {
            if (value.endsWith('ms')) {
                return Number.parseFloat(value);
            }

            if (value.endsWith('s')) {
                return Number.parseFloat(value) * 1000;
            }

            return 0;
        };

        return durationParts.reduce((max, part, idx) => {
            const total = toMs(part) + toMs(delayParts[idx] ?? delayParts[0] ?? '0s');
            return total > max ? total : max;
        }, 0);
    }
}

function initWizards(root) {
    root.querySelectorAll('[data-wizard]').forEach((wizardRoot) => {
        const existing = wizardInstances.get(wizardRoot);
        if (existing instanceof WizardController) {
            existing.destroy();
        }

        const controller = new WizardController(wizardRoot);
        controller.init();
        wizardInstances.set(wizardRoot, controller);
    });
}

function initDropzones(root) {
    const Dropzone = window.Dropzone;
    if (typeof Dropzone !== 'function') {
        return;
    }

    Dropzone.autoDiscover = false;

    root.querySelectorAll('[data-plugin="dropzone"]').forEach((element) => {
        if (!(element instanceof HTMLElement)) {
            return;
        }

        if (element.dropzone && typeof element.dropzone.destroy === 'function') {
            element.dropzone.destroy();
        }

        const previewsContainer = element.dataset.previewsContainer;
        const previewTemplateSelector = element.dataset.uploadPreviewTemplate;
        const previewTemplate = previewTemplateSelector
            ? document.querySelector(previewTemplateSelector)
            : null;

        const options = {
            url: element.getAttribute('action') || '/',
            acceptedFiles: 'image/*',
            autoProcessQueue: false,
        };

        if (previewsContainer) {
            options.previewsContainer = previewsContainer;
        }

        if (previewTemplate instanceof HTMLElement) {
            options.previewTemplate = previewTemplate.innerHTML;
        }

        new Dropzone(element, options);
    });
}

function registerFilePondPlugins() {
    if (window.__catalystFilePondPluginsRegistered) {
        return;
    }

    if (!window.FilePond) {
        return;
    }

    const plugins = [
        window.FilePondPluginImagePreview,
        window.FilePondPluginFileValidateSize,
        window.FilePondPluginImageExifOrientation,
    ].filter((plugin) => typeof plugin !== 'undefined');

    if (plugins.length > 0) {
        window.FilePond.registerPlugin(...plugins);
    }

    window.__catalystFilePondPluginsRegistered = true;
}

function initFilePondInputs(root) {
    if (!window.FilePond) {
        return;
    }

    registerFilePondPlugins();

    root.querySelectorAll('input.filepond').forEach((element) => {
        const existing = filePondInstances.get(element);
        if (existing && typeof existing.destroy === 'function') {
            existing.destroy();
        }

        const isCircle = element.classList.contains('filepond-input-circle');
        const isRoundedSquare = isCircle && element.classList.contains('rounded');
        const instance = window.FilePond.create(element, isCircle
            ? {
                imageCropAspectRatio: '1:1',
                imageResizeTargetWidth: 200,
                imageResizeTargetHeight: 200,
                stylePanelLayout: isRoundedSquare ? 'compact' : 'compact circle',
                styleLoadIndicatorPosition: 'center center',
                styleProgressIndicatorPosition: 'center center',
                styleButtonRemoveItemPosition: 'left center',
                styleButtonProcessItemPosition: 'right center',
                allowImagePreview: true,
                allowProcess: false,
                imagePreviewHeight: 100,
                instantUpload: false,
                credits: false,
                labelIdle: '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-muted"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 7h1a2 2 0 0 0 2 -2a1 1 0 0 1 1 -1h6a1 1 0 0 1 1 1a2 2 0 0 0 2 2h1a2 2 0 0 1 2 2v9a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-9a2 2 0 0 1 2 -2" /><path d="M9 13a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" /></svg>',
            }
            : {
                instantUpload: false,
                credits: false,
            });

        filePondInstances.set(element, instance);
    });
}

function initQuillEditors(root) {
    const Quill = window.Quill;
    if (typeof Quill !== 'function') {
        return;
    }

    const snowEditor = root.querySelector('#snow-editor');
    const bubbleEditor = root.querySelector('#bubble-editor');
    if (!(snowEditor instanceof HTMLElement) && !(bubbleEditor instanceof HTMLElement)) {
        return;
    }

    const icons = Quill.import('ui/icons');
    icons.bold = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 5h6a3.5 3.5 0 0 1 0 7h-6z" /><path d="M13 12h1a3.5 3.5 0 0 1 0 7h-7v-7" /></svg>';
    icons.italic = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M11 5l6 0" /><path d="M7 19l6 0" /><path d="M14 5l-4 14" /></svg>';
    icons.underline = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 5v5a5 5 0 0 0 10 0v-5" /><path d="M5 19h14" /></svg>';
    icons.strike = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l14 0" /><path d="M16 6.5a4 2 0 0 0 -4 -1.5h-1a3.5 3.5 0 0 0 0 7h2a3.5 3.5 0 0 1 0 7h-1.5a4 2 0 0 1 -4 -1.5" /></svg>';
    icons.link = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 15l6 -6" /><path d="M11 6l.463 -.536a5 5 0 0 1 7.071 7.072l-.534 .464" /><path d="M13 18l-.397 .534a5.068 5.068 0 0 1 -7.127 0a4.972 4.972 0 0 1 0 -7.071l.524 -.463" /></svg>';
    icons.image = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M15 8h.01" /><path d="M3 6a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v12a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3v-12z" /><path d="M3 16l5 -5c.928 -.893 2.072 -.893 3 0l5 5" /><path d="M14 14l1 -1c.928 -.893 2.072 -.893 3 0l3 3" /></svg>';
    icons.video = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M15 10l4.553 -2.276a1 1 0 0 1 1.447 .894v6.764a1 1 0 0 1 -1.447 .894l-4.553 -2.276v-4z" /><path d="M3 6m0 2a2 2 0 0 1 2 -2h8a2 2 0 0 1 2 2v8a2 2 0 0 1 -2 2h-8a2 2 0 0 1 -2 -2z" /></svg>';
    icons['code-block'] = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 8l-4 4l4 4" /><path d="M17 8l4 4l-4 4" /><path d="M14 4l-4 16" /></svg>';

    [snowEditor, bubbleEditor].forEach((editor) => {
        if (!(editor instanceof HTMLElement)) {
            return;
        }

        if (!quillSnapshots.has(editor)) {
            quillSnapshots.set(editor, {
                html: editor.innerHTML,
                className: editor.className,
            });
        }

        const snapshot = quillSnapshots.get(editor);
        if (snapshot) {
            const toolbar = editor.previousElementSibling;
            if (toolbar instanceof HTMLElement && toolbar.classList.contains('ql-toolbar')) {
                toolbar.remove();
            }

            editor.className = snapshot.className;
            editor.innerHTML = snapshot.html;
        }
    });

    if (snowEditor instanceof HTMLElement) {
        new Quill(snowEditor, {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{ font: [] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ color: [] }, { background: [] }],
                    [{ script: 'super' }, { script: 'sub' }],
                    [{ header: [false, 1, 2, 3, 4, 5, 6] }],
                    ['blockquote', 'code-block'],
                    [{ list: 'ordered' }, { list: 'bullet' }, { indent: '-1' }, { indent: '+1' }],
                    [{ align: [] }],
                    ['link', 'image', 'video'],
                    ['clean'],
                ],
            },
        });
    }

    if (bubbleEditor instanceof HTMLElement) {
        new Quill(bubbleEditor, { theme: 'bubble' });
    }
}

function initSummernoteEditors(root) {
    const $ = window.jQuery;
    if (typeof $ !== 'function' || typeof $.fn.summernote !== 'function') {
        return;
    }

    ensureJqueryTooltipBridge($);
    ensureJqueryModalBridge($);

    root.querySelectorAll('.summernote').forEach((element) => {
        const target = $(element);
        if (target.data('summernote')) {
            target.summernote('destroy');
        }

        target.summernote({
            height: 300,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'italic', 'underline', 'strikethrough', 'superscript', 'subscript', 'clear']],
                ['fontname', ['fontname']],
                ['fontsize', ['fontsize']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['height', ['height']],
                ['table', ['table']],
                ['insert', ['link', 'picture', 'video']],
                ['view', ['fullscreen', 'codeview', 'help']],
                ['misc', ['undo', 'redo']],
            ],
            callbacks: {
                onInit() {
                    const editor = $(this).closest('.note-editor');
                    editor.find('.note-btn').each(function patchButtons() {
                        this.classList.add('btn-light');
                        this.classList.remove('btn-outline-secondary');
                    });
                },
            },
        });
    });
}

function createSlider(element, options, explicitHeight = null) {
    if (!(element instanceof HTMLElement) || typeof window.noUiSlider === 'undefined') {
        return;
    }

    if (element.noUiSlider && typeof element.noUiSlider.destroy === 'function') {
        element.noUiSlider.destroy();
    }

    if (explicitHeight !== null) {
        element.style.height = explicitHeight;
    }

    window.noUiSlider.create(element, options);
}

function mergeTooltips(slider, threshold, separator) {
    if (!(slider instanceof HTMLElement) || !slider.noUiSlider) {
        return;
    }

    const isRtl = getComputedStyle(slider).direction === 'rtl';
    const isDirectionRtl = slider.noUiSlider.options.direction === 'rtl';
    const isVertical = slider.noUiSlider.options.orientation === 'vertical';
    const tooltips = slider.noUiSlider.getTooltips();
    const origins = slider.noUiSlider.getOrigins();

    tooltips.forEach((tooltip, index) => {
        if (tooltip) {
            origins[index].appendChild(tooltip);
        }
    });

    slider.noUiSlider.on('update', (values, handle, unencoded, tap, positions) => {
        const pools = [[]];
        const poolPositions = [[]];
        const poolValues = [[]];
        let poolIndex = 0;

        if (tooltips[0]) {
            pools[0][0] = 0;
            poolPositions[0][0] = positions[0];
            poolValues[0][0] = values[0];
        }

        for (let index = 1; index < positions.length; index += 1) {
            if (!tooltips[index] || positions[index] - positions[index - 1] > threshold) {
                poolIndex += 1;
                pools[poolIndex] = [];
                poolValues[poolIndex] = [];
                poolPositions[poolIndex] = [];
            }

            if (tooltips[index]) {
                pools[poolIndex].push(index);
                poolValues[poolIndex].push(values[index]);
                poolPositions[poolIndex].push(positions[index]);
            }
        }

        pools.forEach((pool, poolNumber) => {
            const handlesInPool = pool.length;
            pool.forEach((index, handleIndex) => {
                if (handleIndex < handlesInPool - 1) {
                    tooltips[index].style.display = 'none';
                    return;
                }

                const anchorIndex = isDirectionRtl ? 0 : handlesInPool - 1;
                const offset = poolPositions[poolNumber].reduce((sum, position) => sum + (1000 - position), 0);
                const lastOffset = 1000 - poolPositions[poolNumber][anchorIndex];
                const side = isVertical ? 'bottom' : 'right';
                const correction = (isRtl && !isVertical ? 100 : 0) + (offset / handlesInPool) - lastOffset;

                tooltips[index].innerHTML = poolValues[poolNumber].join(separator);
                tooltips[index].style.display = 'block';
                tooltips[index].style[side] = `${correction}%`;
            });
        });
    });
}

function initRangeSliders(root) {
    if (typeof window.noUiSlider === 'undefined') {
        return;
    }

    root.querySelectorAll('[data-slider="default"]').forEach((element) => {
        const value = Number.parseInt(element.getAttribute('data-value') || '50', 10) || 50;
        createSlider(element, {
            start: value,
            connect: 'lower',
            range: { min: 0, max: 255 },
        });
    });

    createSlider(root.querySelector('#rangeslider_multielement'), {
        start: [20, 80],
        connect: true,
        range: { min: 0, max: 100 },
    });

    const nonlinear = root.querySelector('#nonlinear');
    if (nonlinear instanceof HTMLElement) {
        const lowerValue = root.querySelector('#lower-value');
        const upperValue = root.querySelector('#upper-value');
        createSlider(nonlinear, {
            connect: true,
            behaviour: 'tap',
            start: [500, 4000],
            range: {
                min: [0],
                '10%': [500, 500],
                '50%': [4000, 1000],
                max: [10000],
            },
        });

        if (lowerValue instanceof HTMLElement && upperValue instanceof HTMLElement && nonlinear.noUiSlider) {
            nonlinear.noUiSlider.on('update', (values, handle, unencoded, tap, positions) => {
                [lowerValue, upperValue][handle].innerHTML = `${values[handle]}, ${positions[handle].toFixed(2)}%`;
            });
        }
    }

    const slider1 = root.querySelector('#slider1');
    const slider2 = root.querySelector('#slider2');
    const lockButton = root.querySelector('#lockbutton');
    const slider1Value = root.querySelector('#slider1-span');
    const slider2Value = root.querySelector('#slider2-span');
    let lockedState = false;
    let lockedValues = [60, 80];

    function setLockedValues() {
        if (!(slider1 instanceof HTMLElement) || !(slider2 instanceof HTMLElement) || !slider1.noUiSlider || !slider2.noUiSlider) {
            return;
        }

        lockedValues = [Number(slider1.noUiSlider.get()), Number(slider2.noUiSlider.get())];
    }

    function crossUpdate(value, slider) {
        if (!lockedState || !(slider instanceof HTMLElement) || !slider.noUiSlider) {
            return;
        }

        const index = slider1 === slider ? 0 : 1;
        const otherIndex = 1 - index;
        const nextValue = value - (lockedValues[otherIndex] - lockedValues[index]);
        slider.noUiSlider.set(nextValue);
    }

    createSlider(slider1, { start: 60, animate: false, range: { min: 50, max: 100 } });
    createSlider(slider2, { start: 80, animate: false, range: { min: 50, max: 100 } });

    if (slider1 instanceof HTMLElement && slider1.noUiSlider && slider1Value instanceof HTMLElement) {
        slider1.noUiSlider.on('update', (values, handle) => {
            slider1Value.innerHTML = values[handle];
        });
        slider1.noUiSlider.on('change', setLockedValues);
        slider1.noUiSlider.on('slide', (values, handle) => crossUpdate(values[handle], slider2));
    }

    if (slider2 instanceof HTMLElement && slider2.noUiSlider && slider2Value instanceof HTMLElement) {
        slider2.noUiSlider.on('update', (values, handle) => {
            slider2Value.innerHTML = values[handle];
        });
        slider2.noUiSlider.on('change', setLockedValues);
        slider2.noUiSlider.on('slide', (values, handle) => crossUpdate(values[handle], slider1));
    }

    if (lockButton instanceof HTMLElement) {
        lockButton.addEventListener('click', () => {
            lockedState = !lockedState;
            lockButton.innerHTML = lockedState ? 'Unlock' : 'Lock';
        });
    }

    const mergingSlider = root.querySelector('#slider-merging-tooltips');
    if (mergingSlider instanceof HTMLElement) {
        createSlider(mergingSlider, {
            start: [20, 75],
            connect: true,
            tooltips: [true, true],
            range: { min: 0, max: 100 },
        });
        mergeTooltips(mergingSlider, 5, ' - ');
    }

    const softSlider = root.querySelector('#soft');
    if (softSlider instanceof HTMLElement) {
        createSlider(softSlider, {
            start: 50,
            range: { min: 0, max: 100 },
            pips: { mode: 'values', values: [20, 80], density: 4 },
        });

        softSlider.noUiSlider?.on('change', (values, handle) => {
            if (values[handle] < 20) {
                softSlider.noUiSlider.set(20);
            } else if (values[handle] > 80) {
                softSlider.noUiSlider.set(80);
            }
        });
    }

    createSlider(root.querySelector('#slider-vertical'), {
        start: [40, 60],
        connect: true,
        behaviour: 'drag',
        orientation: 'vertical',
        range: { min: 0, max: 100 },
    });

    createSlider(root.querySelector('#slider-connect-upper'), {
        start: 40,
        orientation: 'vertical',
        behaviour: 'drag',
        connect: 'upper',
        range: { min: 0, max: 100 },
    }, '200px');

    createSlider(root.querySelector('#slider-vertical-tooltip'), {
        start: 10,
        orientation: 'vertical',
        behaviour: 'drag',
        tooltips: true,
        range: { min: 0, max: 100 },
    }, '200px');

    createSlider(root.querySelector('#slider-vertical-limit'), {
        start: [0, 40],
        orientation: 'vertical',
        behaviour: 'drag',
        limit: 60,
        connect: true,
        tooltips: true,
        range: { min: 0, max: 100 },
    }, '200px');
}

export async function initUiEnhancers(options = {}) {
    const root = options.root instanceof HTMLElement ? options.root : document.body;
    const requirements = detectRequirements(root, options.capabilities);
    if (!Object.values(requirements).some(Boolean)) {
        return null;
    }

    await ensureRequiredAssets(requirements);

    if (requirements.dateRange) {
        initDateRangePickers(root);
    }

    if (requirements.flatpickr) {
        initFlatpickrInputs(root);
    }

    if (requirements.pickr) {
        initColorPickers(root);
    }

    if (requirements.choices) {
        initChoicesInputs(root);
    }

    if (requirements.select2) {
        initSelect2Inputs(root);
    }

    if (requirements.wizard) {
        initWizards(root);
    }

    if (requirements.dropzone) {
        initDropzones(root);
    }

    if (requirements.filepond) {
        initFilePondInputs(root);
    }

    if (requirements.quill) {
        initQuillEditors(root);
    }

    if (requirements.summernote) {
        initSummernoteEditors(root);
    }

    if (requirements.sliders) {
        initRangeSliders(root);
    }

    return requirements;
}

export function destroyUiEnhancers(options = {}) {
    const root = options.root instanceof HTMLElement ? options.root : document.body;
    if (!(root instanceof HTMLElement)) {
        return;
    }

    root.querySelectorAll('[data-provider="flatpickr"], [data-provider="timepickr"]').forEach((element) => {
        element._flatpickr?.destroy?.();
    });

    root.querySelectorAll('.classic-colorpicker, .monolith-colorpicker, .nano-colorpicker, .colorpicker-demo, .colorpicker-opacity-hue, .colorpicker-switch, .colorpicker-input, .colorpicker-format').forEach((element) => {
        pickrInstances.get(element)?.destroyAndRemove?.();
        pickrInstances.delete(element);
    });

    root.querySelectorAll('[data-choices]').forEach((element) => {
        choicesInstances.get(element)?.destroy?.();
        choicesInstances.delete(element);
    });

    root.querySelectorAll('[data-wizard]').forEach((element) => {
        wizardInstances.get(element)?.destroy?.();
        wizardInstances.delete(element);
    });

    root.querySelectorAll('[data-plugin="dropzone"]').forEach((element) => {
        element.dropzone?.destroy?.();
    });

    root.querySelectorAll('input.filepond').forEach((element) => {
        filePondInstances.get(element)?.destroy?.();
        filePondInstances.delete(element);
    });

    root.querySelectorAll('[data-slider="default"], #rangeslider_multielement, #nonlinear, #slider1, #slider2, #slider-merging-tooltips, #soft, #slider-vertical, #slider-connect-upper, #slider-vertical-tooltip, #slider-vertical-limit').forEach((element) => {
        element.noUiSlider?.destroy?.();
    });

    const $ = window.jQuery;
    if (typeof $ === 'function') {
        root.querySelectorAll('[data-toggle="select2"], [data-plugin="select2"]').forEach((element) => {
            const target = $(element);
            if (target.hasClass('select2-hidden-accessible')) {
                target.select2('destroy');
            }
        });

        root.querySelectorAll('[data-toggle="date-picker"], [data-plugin="date-picker"], [data-toggle="date-picker-range"], [data-plugin="date-picker-range"]').forEach((element) => {
            $(element).data('daterangepicker')?.remove?.();
        });

        root.querySelectorAll('.summernote').forEach((element) => {
            const target = $(element);
            if (target.data('summernote')) {
                target.summernote('destroy');
            }
        });
    }

    root.querySelectorAll('#snow-editor, #bubble-editor').forEach((editor) => {
        const snapshot = quillSnapshots.get(editor);
        if (!snapshot) {
            return;
        }

        const toolbar = editor.previousElementSibling;
        if (toolbar instanceof HTMLElement && toolbar.classList.contains('ql-toolbar')) {
            toolbar.remove();
        }

        editor.className = snapshot.className;
        editor.innerHTML = snapshot.html;
        quillSnapshots.delete(editor);
    });
}
