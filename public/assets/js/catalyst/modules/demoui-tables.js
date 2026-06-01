import { loadAssets, loadScript } from './asset-loader.js';

const BOOTSTRAP_BUNDLE_URL = '/assets/vendor/bootstrap/js/bootstrap.bundle.min.js';

const TABLE_PAGE_ASSETS = {
    'tables-static.html': {
        styles: [],
        scripts: [],
        pageScript: null,
    },
    'tables-custom.html': {
        styles: [],
        scripts: [],
        pageScript: '/assets/vendor/inspinia/js/pages/custom-table.js',
    },
    'tables-datatables-basic.html': {
        styles: [],
        scripts: [
            '/assets/vendor/inspinia/plugins/jquery/jquery.min.js',
            '/assets/vendor/inspinia/plugins/datatables/dataTables.min.js',
            '/assets/vendor/inspinia/plugins/datatables/dataTables.bootstrap5.min.js',
            '/assets/vendor/inspinia/plugins/datatables/dataTables.responsive.min.js',
            '/assets/vendor/inspinia/plugins/datatables/responsive.bootstrap5.min.js',
        ],
        pageScript: '/assets/vendor/inspinia/js/pages/datatables-basic.js',
    },
    'tables-datatables-export-data.html': {
        styles: [],
        scripts: [
            '/assets/vendor/inspinia/plugins/jquery/jquery.min.js',
            '/assets/vendor/inspinia/plugins/datatables/dataTables.min.js',
            '/assets/vendor/inspinia/plugins/datatables/dataTables.bootstrap5.min.js',
            '/assets/vendor/inspinia/plugins/datatables/dataTables.responsive.min.js',
            '/assets/vendor/inspinia/plugins/datatables/responsive.bootstrap5.min.js',
            '/assets/vendor/inspinia/plugins/datatables/dataTables.buttons.min.js',
            '/assets/vendor/inspinia/plugins/datatables/buttons.bootstrap5.min.js',
            '/assets/vendor/inspinia/plugins/datatables/jszip.min.js',
            '/assets/vendor/inspinia/plugins/datatables/pdfmake.min.js',
            '/assets/vendor/inspinia/plugins/datatables/vfs_fonts.js',
            '/assets/vendor/inspinia/plugins/datatables/buttons.html5.min.js',
            '/assets/vendor/inspinia/plugins/datatables/buttons.print.min.js',
        ],
        pageScript: '/assets/vendor/inspinia/js/pages/datatables-export-data.js',
    },
    'tables-datatables-select.html': {
        styles: [],
        scripts: [
            '/assets/vendor/inspinia/plugins/jquery/jquery.min.js',
            '/assets/vendor/inspinia/plugins/datatables/dataTables.min.js',
            '/assets/vendor/inspinia/plugins/datatables/dataTables.bootstrap5.min.js',
            '/assets/vendor/inspinia/plugins/datatables/dataTables.responsive.min.js',
            '/assets/vendor/inspinia/plugins/datatables/responsive.bootstrap5.min.js',
            '/assets/vendor/inspinia/plugins/datatables/dataTables.select.min.js',
            '/assets/vendor/inspinia/plugins/datatables/select.bootstrap5.min.js',
        ],
        pageScript: '/assets/vendor/inspinia/js/pages/datatables-select.js',
    },
    'tables-datatables-ajax.html': {
        styles: [],
        scripts: [
            '/assets/vendor/inspinia/plugins/jquery/jquery.min.js',
            '/assets/vendor/inspinia/plugins/datatables/dataTables.min.js',
            '/assets/vendor/inspinia/plugins/datatables/dataTables.bootstrap5.min.js',
            '/assets/vendor/inspinia/plugins/datatables/dataTables.responsive.min.js',
            '/assets/vendor/inspinia/plugins/datatables/responsive.bootstrap5.min.js',
        ],
        pageScript: '/assets/vendor/inspinia/js/pages/datatables-ajax.js',
    },
    'tables-datatables-javascript.html': {
        styles: [],
        scripts: [
            '/assets/vendor/inspinia/plugins/jquery/jquery.min.js',
            '/assets/vendor/inspinia/plugins/datatables/dataTables.min.js',
            '/assets/vendor/inspinia/plugins/datatables/dataTables.bootstrap5.min.js',
            '/assets/vendor/inspinia/plugins/datatables/dataTables.responsive.min.js',
            '/assets/vendor/inspinia/plugins/datatables/responsive.bootstrap5.min.js',
        ],
        pageScript: '/assets/vendor/inspinia/js/pages/datatables-javascript-source.js',
    },
    'tables-datatables-rendering.html': {
        styles: [],
        scripts: [
            '/assets/vendor/inspinia/plugins/jquery/jquery.min.js',
            '/assets/vendor/inspinia/plugins/datatables/dataTables.min.js',
            '/assets/vendor/inspinia/plugins/datatables/dataTables.bootstrap5.min.js',
            '/assets/vendor/inspinia/plugins/datatables/dataTables.responsive.min.js',
            '/assets/vendor/inspinia/plugins/datatables/responsive.bootstrap5.min.js',
        ],
        pageScript: '/assets/vendor/inspinia/js/pages/datatables-rendering.js',
    },
    'tables-datatables-scroll.html': {
        styles: [],
        scripts: [
            '/assets/vendor/inspinia/plugins/jquery/jquery.min.js',
            '/assets/vendor/inspinia/plugins/datatables/dataTables.min.js',
            '/assets/vendor/inspinia/plugins/datatables/dataTables.bootstrap5.min.js',
            '/assets/vendor/inspinia/plugins/datatables/dataTables.responsive.min.js',
            '/assets/vendor/inspinia/plugins/datatables/responsive.bootstrap5.min.js',
        ],
        pageScript: '/assets/vendor/inspinia/js/pages/datatables-scroll.js',
    },
    'tables-datatables-fixed-columns.html': {
        styles: [],
        scripts: [
            '/assets/vendor/inspinia/plugins/jquery/jquery.min.js',
            '/assets/vendor/inspinia/plugins/datatables/dataTables.min.js',
            '/assets/vendor/inspinia/plugins/datatables/dataTables.bootstrap5.min.js',
            '/assets/vendor/inspinia/plugins/datatables/dataTables.responsive.min.js',
            '/assets/vendor/inspinia/plugins/datatables/responsive.bootstrap5.min.js',
            '/assets/vendor/inspinia/plugins/datatables/dataTables.fixedColumns.min.js',
            '/assets/vendor/inspinia/plugins/datatables/fixedColumns.bootstrap5.min.js',
        ],
        pageScript: '/assets/vendor/inspinia/js/pages/datatables-fixed-columns.js',
    },
    'tables-datatables-fixed-header.html': {
        styles: [],
        scripts: [
            '/assets/vendor/inspinia/plugins/jquery/jquery.min.js',
            '/assets/vendor/inspinia/plugins/datatables/dataTables.min.js',
            '/assets/vendor/inspinia/plugins/datatables/dataTables.bootstrap5.min.js',
            '/assets/vendor/inspinia/plugins/datatables/dataTables.responsive.min.js',
            '/assets/vendor/inspinia/plugins/datatables/responsive.bootstrap5.min.js',
            '/assets/vendor/inspinia/plugins/datatables/dataTables.fixedHeader.min.js',
            '/assets/vendor/inspinia/plugins/datatables/fixedHeader.bootstrap5.min.js',
        ],
        pageScript: '/assets/vendor/inspinia/js/pages/datatables-fixed-header.js',
    },
    'tables-datatables-columns.html': {
        styles: [],
        scripts: [
            '/assets/vendor/inspinia/plugins/jquery/jquery.min.js',
            '/assets/vendor/inspinia/plugins/datatables/dataTables.min.js',
            '/assets/vendor/inspinia/plugins/datatables/dataTables.bootstrap5.min.js',
            '/assets/vendor/inspinia/plugins/datatables/dataTables.responsive.min.js',
            '/assets/vendor/inspinia/plugins/datatables/responsive.bootstrap5.min.js',
        ],
        pageScript: '/assets/vendor/inspinia/js/pages/datatables-show-hide-columns.js',
    },
    'tables-datatables-child-rows.html': {
        styles: [],
        scripts: [
            '/assets/vendor/inspinia/plugins/jquery/jquery.min.js',
            '/assets/vendor/inspinia/plugins/datatables/dataTables.min.js',
            '/assets/vendor/inspinia/plugins/datatables/dataTables.bootstrap5.min.js',
            '/assets/vendor/inspinia/plugins/datatables/dataTables.responsive.min.js',
            '/assets/vendor/inspinia/plugins/datatables/responsive.bootstrap5.min.js',
        ],
        pageScript: '/assets/vendor/inspinia/js/pages/datatables-child-rows.js',
    },
    'tables-datatables-column-searching.html': {
        styles: [],
        scripts: [
            '/assets/vendor/inspinia/plugins/jquery/jquery.min.js',
            '/assets/vendor/inspinia/plugins/datatables/dataTables.min.js',
            '/assets/vendor/inspinia/plugins/datatables/dataTables.bootstrap5.min.js',
            '/assets/vendor/inspinia/plugins/datatables/dataTables.responsive.min.js',
            '/assets/vendor/inspinia/plugins/datatables/responsive.bootstrap5.min.js',
        ],
        pageScript: '/assets/vendor/inspinia/js/pages/datatables-column-search.js',
    },
    'tables-datatables-range-search.html': {
        styles: [],
        scripts: [
            '/assets/vendor/inspinia/plugins/jquery/jquery.min.js',
            '/assets/vendor/inspinia/plugins/datatables/dataTables.min.js',
            '/assets/vendor/inspinia/plugins/datatables/dataTables.bootstrap5.min.js',
            '/assets/vendor/inspinia/plugins/datatables/dataTables.responsive.min.js',
            '/assets/vendor/inspinia/plugins/datatables/responsive.bootstrap5.min.js',
        ],
        pageScript: '/assets/vendor/inspinia/js/pages/datatables-range-search.js',
    },
    'tables-datatables-rows-add.html': {
        styles: [],
        scripts: [
            '/assets/vendor/inspinia/plugins/jquery/jquery.min.js',
            '/assets/vendor/inspinia/plugins/datatables/dataTables.min.js',
            '/assets/vendor/inspinia/plugins/datatables/dataTables.bootstrap5.min.js',
            '/assets/vendor/inspinia/plugins/datatables/dataTables.responsive.min.js',
            '/assets/vendor/inspinia/plugins/datatables/responsive.bootstrap5.min.js',
        ],
        pageScript: '/assets/vendor/inspinia/js/pages/datatables-rows-add.js',
    },
    'tables-datatables-checkbox-select.html': {
        styles: [],
        scripts: [
            '/assets/vendor/inspinia/plugins/jquery/jquery.min.js',
            '/assets/vendor/inspinia/plugins/datatables/dataTables.min.js',
            '/assets/vendor/inspinia/plugins/datatables/dataTables.bootstrap5.min.js',
            '/assets/vendor/inspinia/plugins/datatables/dataTables.responsive.min.js',
            '/assets/vendor/inspinia/plugins/datatables/responsive.bootstrap5.min.js',
            '/assets/vendor/inspinia/plugins/datatables/dataTables.select.min.js',
            '/assets/vendor/inspinia/plugins/datatables/select.bootstrap5.min.js',
        ],
        pageScript: '/assets/vendor/inspinia/js/pages/datatables-checkbox-select.js',
    },
};

function resolveTableDocument(root) {
    const shell = root instanceof HTMLElement
        ? root.closest('.demo-ui-shell-body')
        : null;
    const source = shell instanceof HTMLElement ? shell : document.body;

    if (!(source instanceof HTMLElement)) {
        return '';
    }

    return source.dataset.demouiDoc ?? '';
}

async function ensureBootstrapBundle() {
    if (window.bootstrap) {
        return window.bootstrap;
    }

    await loadScript(BOOTSTRAP_BUNDLE_URL);
    return window.bootstrap ?? null;
}

function invokeReadyListener(listener) {
    const event = new Event('DOMContentLoaded');

    if (typeof listener === 'function') {
        queueMicrotask(() => listener(event));
        return;
    }

    if (listener && typeof listener.handleEvent === 'function') {
        queueMicrotask(() => listener.handleEvent(event));
    }
}

async function loadLegacyPageScript(url) {
    if (document.readyState === 'loading') {
        await loadScript(url);
        return;
    }

    const originalAddEventListener = document.addEventListener.bind(document);

    document.addEventListener = function patchedAddEventListener(type, listener, options) {
        if (type === 'DOMContentLoaded') {
            invokeReadyListener(listener);
            return;
        }

        return originalAddEventListener(type, listener, options);
    };

    try {
        await loadScript(url);
    } finally {
        document.addEventListener = originalAddEventListener;
    }
}

export async function initDemoUiTables(options = {}) {
    const root = options.root instanceof HTMLElement ? options.root : document.body;
    const docFile = resolveTableDocument(root);
    const pageAssets = TABLE_PAGE_ASSETS[docFile] ?? null;

    if (!pageAssets) {
        return null;
    }

    await ensureBootstrapBundle();
    await loadAssets({
        styles: pageAssets.styles,
        scripts: pageAssets.scripts,
    });

    if (typeof pageAssets.pageScript === 'string' && pageAssets.pageScript !== '') {
        await loadLegacyPageScript(pageAssets.pageScript);
    }

    return { docFile };
}
