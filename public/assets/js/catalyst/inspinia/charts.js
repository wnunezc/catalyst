const scriptRegistry = new Map();
let chartGlobalsBound = false;

function theme(name, opacity = 1) {
    const value = getComputedStyle(document.documentElement).getPropertyValue(`--theme-${name}`).trim();

    if (name.includes('-rgb')) {
        return `rgba(${value}, ${opacity})`;
    }

    return value;
}

function debounce(callback, wait) {
    let timeoutId = null;

    return function debouncedCallback(...args) {
        if (timeoutId !== null) {
            window.clearTimeout(timeoutId);
        }

        timeoutId = window.setTimeout(() => {
            callback.apply(this, args);
        }, wait);
    };
}

function ensureScript(src) {
    if (scriptRegistry.has(src)) {
        return scriptRegistry.get(src);
    }

    const promise = new Promise((resolve, reject) => {
        const existing = document.querySelector(`script[data-catalyst-script-src="${src}"]`);
        if (existing instanceof HTMLScriptElement) {
            if (existing.dataset.loaded === 'true') {
                resolve(existing);
                return;
            }

            existing.addEventListener('load', () => resolve(existing), { once: true });
            existing.addEventListener('error', () => reject(new Error(`Failed to load ${src}`)), { once: true });
            return;
        }

        const script = document.createElement('script');
        script.src = src;
        script.defer = true;
        script.dataset.catalystScriptSrc = src;
        script.addEventListener('load', () => {
            script.dataset.loaded = 'true';
            resolve(script);
        }, { once: true });
        script.addEventListener('error', () => {
            reject(new Error(`Failed to load ${src}`));
        }, { once: true });
        document.head.appendChild(script);
    });

    scriptRegistry.set(src, promise);
    return promise;
}

class CustomApexChart {
    static instances = [];

    constructor({ selector, series = [], options = {}, colors = [] }) {
        if (!selector) {
            return;
        }

        this.selector = selector;
        this.series = series;
        this.getOptions = options;
        this.colors = colors;
        this.element = selector instanceof HTMLElement ? selector : document.querySelector(selector);
        this.chart = null;

        this.render();
        CustomApexChart.instances.push(this);
    }

    resolveOptions() {
        const options = typeof this.getOptions === 'function' ? this.getOptions() : this.getOptions;
        return options && typeof options === 'object' ? options : {};
    }

    getColors() {
        const options = this.resolveOptions();
        if (Array.isArray(options.colors) && options.colors.length > 0) {
            return options.colors;
        }

        if (this.element instanceof HTMLElement) {
            const dataColors = this.element.getAttribute('data-colors');
            if (dataColors) {
                const resolved = dataColors
                    .split(',')
                    .map((color) => color.trim())
                    .filter((color) => color !== '')
                    .map((color) => (color.startsWith('#') || color.includes('rgb') ? color : theme(color)));

                if (resolved.length > 0) {
                    return resolved;
                }
            }
        }

        return [theme('chart-primary'), theme('chart-secondary'), theme('chart-beta')];
    }

    injectDynamicColors(options, colors) {
        if (options.chart?.type?.toLowerCase() === 'boxplot') {
            options.plotOptions = options.plotOptions || {};
            options.plotOptions.boxPlot = options.plotOptions.boxPlot || {};
            options.plotOptions.boxPlot.colors = options.plotOptions.boxPlot.colors || {};
            options.plotOptions.boxPlot.colors.upper = options.plotOptions.boxPlot.colors.upper || colors[0];
            options.plotOptions.boxPlot.colors.lower = options.plotOptions.boxPlot.colors.lower || colors[1];
        }

        if (Array.isArray(options.yaxis)) {
            options.yaxis.forEach((axis, index) => {
                const axisColor = colors[index] || this.colors[index] || '#999';
                axis.axisBorder = axis.axisBorder || {};
                axis.axisBorder.color = axis.axisBorder.color || axisColor;
                axis.labels = axis.labels || {};
                axis.labels.style = axis.labels.style || {};
                axis.labels.style.color = axis.labels.style.color || axisColor;
            });
        }

        if (options.markers && !options.markers.strokeColor) {
            options.markers.strokeColor = colors;
        }

        if (options.fill?.type === 'gradient' && options.fill.gradient) {
            options.fill.gradient.gradientToColors = options.fill.gradient.gradientToColors || colors;
        }

        const treemapRanges = options.plotOptions?.treemap?.colorScale?.ranges;
        if (Array.isArray(treemapRanges) && treemapRanges.length > 0) {
            if (!treemapRanges[0].color) {
                treemapRanges[0].color = colors[0];
            }

            if (treemapRanges.length > 1 && !treemapRanges[1].color) {
                treemapRanges[1].color = colors[1];
            }
        }

        return options;
    }

    render() {
        if (!(this.element instanceof HTMLElement) || typeof window.ApexCharts !== 'function') {
            return;
        }

        if (this.chart && typeof this.chart.destroy === 'function') {
            this.chart.destroy();
        }

        const options = this.injectDynamicColors(this.resolveOptions(), this.getColors());
        if (!options.series) {
            options.series = this.series;
        }

        this.chart = new window.ApexCharts(this.element, options);
        this.chart.render();
    }

    static rerenderAll() {
        CustomApexChart.instances.forEach((instance) => instance.render());
    }
}

class CustomEChart {
    static instances = [];

    constructor({ selector, options = {}, theme: chartTheme = null, initOptions = {} }) {
        if (!selector) {
            return;
        }

        this.selector = selector;
        this.getOptions = options;
        this.theme = chartTheme;
        this.initOptions = initOptions;
        this.element = selector instanceof HTMLElement ? selector : document.querySelector(selector);
        this.chart = null;

        this.render();
        CustomEChart.instances.push(this);
    }

    resolveOptions() {
        return typeof this.getOptions === 'function' ? this.getOptions() : this.getOptions;
    }

    render() {
        if (!(this.element instanceof HTMLElement) || !window.echarts?.init) {
            return;
        }

        if (this.chart && typeof this.chart.dispose === 'function') {
            this.chart.dispose();
        }

        this.chart = window.echarts.init(this.element, this.theme, this.initOptions);
        this.chart.setOption(this.resolveOptions());
    }

    static reSizeAll() {
        CustomEChart.instances.forEach((instance) => {
            if (!(instance.chart && instance.element instanceof HTMLElement && instance.element.offsetParent !== null)) {
                return;
            }

            requestAnimationFrame(() => {
                instance.chart.resize();
            });
        });
    }

    static rerenderAll() {
        CustomEChart.instances.forEach((instance) => instance.render());
    }
}

function bindChartGlobals() {
    if (chartGlobalsBound) {
        return;
    }

    window.theme = theme;
    window.CustomApexChart = CustomApexChart;
    window.CustomEChart = CustomEChart;
    window.__CatalystInspiniaVendorBase = '/assets/vendor/inspinia';

    const refreshCharts = () => {
        CustomApexChart.rerenderAll();
        CustomEChart.rerenderAll();
    };

    const resizeCharts = () => {
        CustomApexChart.rerenderAll();
        CustomEChart.reSizeAll();
    };

    const themeObserverStartedAt = performance.now();
    const themeObserver = new MutationObserver(() => {
        if (performance.now() - themeObserverStartedAt < 1000) {
            return;
        }

        refreshCharts();
    });

    themeObserver.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['data-skin', 'data-bs-theme'],
    });

    ['shown.bs.tab', 'shown.bs.collapse', 'shown.bs.offcanvas', 'shown.bs.modal'].forEach((eventName) => {
        document.addEventListener(eventName, () => {
            resizeCharts();
        }, true);
    });

    window.addEventListener('resize', debounce(() => {
        CustomEChart.reSizeAll();
    }, 200));

    chartGlobalsBound = true;
}

function resolveChartDocument(root) {
    const source = root instanceof HTMLElement
        ? root.closest('[data-catalyst-inspinia-document]') ?? document.body
        : document.body;

    if (!(source instanceof HTMLElement)) {
        return '';
    }

    return source.dataset.catalystInspiniaDocument ?? '';
}

function resolveChartScript(docFile) {
    if (!/^charts-(apex|echart)-.+\.html$/i.test(docFile)) {
        return null;
    }

    return `/assets/vendor/inspinia/js/pages/${docFile.replace(/^charts-/i, 'chart-').replace(/\.html$/i, '.js')}`;
}

async function loadChartDependencies(engine, docFile) {
    if (engine === 'apex') {
        await ensureScript('/assets/vendor/inspinia/plugins/apexcharts/apexcharts.min.js');
        return;
    }

    if (engine !== 'echart') {
        return;
    }

    await ensureScript('/assets/vendor/inspinia/plugins/echarts/echarts.min.js');

    if (docFile === 'charts-echart-geo-map.html') {
        await ensureScript('https://cdn.jsdelivr.net/npm/echarts/map/js/world.js');
    }
}

export async function initInspiniaCharts(options = {}) {
    const root = options.root instanceof HTMLElement ? options.root : document.body;
    const docFile = resolveChartDocument(root);
    const pageScript = resolveChartScript(docFile);
    const engine = docFile.startsWith('charts-apex-') || root.querySelector('.apex-charts')
        ? 'apex'
        : docFile.startsWith('charts-echart-')
            || root.matches?.('[id^="echart-"], [id^="chart-"], [data-chart-engine="echarts"]')
            || root.querySelector('[id^="echart-"], [id^="chart-"], [data-chart-engine="echarts"]')
            ? 'echart'
            : null;

    if (engine === null) {
        return null;
    }

    bindChartGlobals();
    await loadChartDependencies(engine, docFile);
    if (pageScript) {
        await ensureScript(pageScript);
    }

    requestAnimationFrame(() => {
        CustomEChart.reSizeAll();
    });

    return { docFile, engine };
}
