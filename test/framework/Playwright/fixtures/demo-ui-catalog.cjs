const baseUiPages = [
    ['accordions', 'ui-accordions.html'],
    ['alerts', 'ui-alerts.html'],
    ['images', 'ui-images.html'],
    ['badges', 'ui-badges.html'],
    ['breadcrumb', 'ui-breadcrumb.html'],
    ['buttons', 'ui-buttons.html'],
    ['cards', 'ui-cards.html'],
    ['carousel', 'ui-carousel.html'],
    ['collapse', 'ui-collapse.html'],
    ['colors', 'ui-colors.html'],
    ['dropdowns', 'ui-dropdowns.html'],
    ['videos', 'ui-videos.html'],
    ['grid-options', 'ui-grid.html'],
    ['links', 'ui-links.html'],
    ['list-group', 'ui-list-group.html'],
    ['modals', 'ui-modals.html'],
    ['notifications', 'ui-notifications.html'],
    ['offcanvas', 'ui-offcanvas.html'],
    ['pagination', 'ui-pagination.html'],
    ['placeholders', 'ui-placeholders.html'],
    ['popovers', 'ui-popovers.html'],
    ['progress', 'ui-progress.html'],
    ['scrollspy', 'ui-scrollspy.html'],
    ['spinners', 'ui-spinners.html'],
    ['tabs', 'ui-tabs.html'],
    ['tooltips', 'ui-tooltips.html'],
    ['typography', 'ui-typography.html'],
    ['utilities', 'ui-utilities.html'],
].map(([slug, doc]) => ({
    group: 'base-ui',
    slug,
    path: `/demo-ui/${slug}`,
    doc,
}));

const formPages = [
    ['basic-elements', 'form-elements.html'],
    ['pickers', 'form-pickers.html'],
    ['select', 'form-select.html'],
    ['validation', 'form-validation.html'],
    ['wizard', 'form-wizard.html'],
    ['file-uploads', 'form-fileuploads.html'],
    ['text-editors', 'form-text-editors.html'],
    ['range-slider', 'form-range-slider.html'],
].map(([slug, doc]) => ({
    group: 'forms',
    slug,
    path: `/demo-ui/${slug}`,
    doc,
}));

const chartFamilies = {
    apex: [
        'area', 'bar', 'bubble', 'candlestick', 'column', 'heatmap', 'line',
        'mixed', 'timeline', 'boxplot', 'treemap', 'pie', 'radar', 'radialbar',
        'scatter', 'polar-area', 'sparklines', 'range', 'funnel', 'slope',
    ],
    echart: [
        'line', 'bar', 'pie', 'scatter', 'geo-map', 'gauge', 'candlestick',
        'area', 'radar', 'heatmap', 'other',
    ],
};

const chartPages = Object.entries(chartFamilies).flatMap(([family, slugs]) =>
    slugs.map((slug) => ({
        group: 'charts',
        family,
        slug,
        path: `/demo-ui/charts/${family}/${slug}`,
        doc: `charts-${family}-${slug}.html`,
        vendorScript: family === 'apex'
            ? '/assets/vendor/inspinia/plugins/apexcharts/apexcharts.min.js'
            : '/assets/vendor/inspinia/plugins/echarts/echarts.min.js',
        pageScript: `/assets/vendor/inspinia/js/pages/chart-${family}-${slug}.js`,
        renderedSelector: family === 'apex'
            ? '.apexcharts-canvas'
            : 'canvas',
        extraScripts: family === 'echart' && slug === 'geo-map'
            ? ['https://cdn.jsdelivr.net/npm/echarts/map/js/world.js']
            : [],
    }))
);

const dataTableScripts = [
    '/assets/vendor/inspinia/plugins/jquery/jquery.min.js',
    '/assets/vendor/inspinia/plugins/datatables/dataTables.min.js',
    '/assets/vendor/inspinia/plugins/datatables/dataTables.bootstrap5.min.js',
    '/assets/vendor/inspinia/plugins/datatables/dataTables.responsive.min.js',
    '/assets/vendor/inspinia/plugins/datatables/responsive.bootstrap5.min.js',
];

const tableDefinitions = [
    ['static', 'tables-static.html', null, []],
    ['custom', 'tables-custom.html', 'custom-table.js', []],
    ['datatables/basic', 'tables-datatables-basic.html', 'datatables-basic.js', dataTableScripts],
    ['datatables/export-data', 'tables-datatables-export-data.html', 'datatables-export-data.js', [
        ...dataTableScripts,
        '/assets/vendor/inspinia/plugins/datatables/dataTables.buttons.min.js',
        '/assets/vendor/inspinia/plugins/datatables/buttons.bootstrap5.min.js',
        '/assets/vendor/inspinia/plugins/datatables/jszip.min.js',
        '/assets/vendor/inspinia/plugins/datatables/pdfmake.min.js',
        '/assets/vendor/inspinia/plugins/datatables/vfs_fonts.js',
        '/assets/vendor/inspinia/plugins/datatables/buttons.html5.min.js',
        '/assets/vendor/inspinia/plugins/datatables/buttons.print.min.js',
    ]],
    ['datatables/select', 'tables-datatables-select.html', 'datatables-select.js', [
        ...dataTableScripts,
        '/assets/vendor/inspinia/plugins/datatables/dataTables.select.min.js',
        '/assets/vendor/inspinia/plugins/datatables/select.bootstrap5.min.js',
    ]],
    ['datatables/ajax', 'tables-datatables-ajax.html', 'datatables-ajax.js', dataTableScripts],
    ['datatables/javascript', 'tables-datatables-javascript.html', 'datatables-javascript-source.js', dataTableScripts],
    ['datatables/rendering', 'tables-datatables-rendering.html', 'datatables-rendering.js', dataTableScripts],
    ['datatables/scroll', 'tables-datatables-scroll.html', 'datatables-scroll.js', dataTableScripts],
    ['datatables/fixed-columns', 'tables-datatables-fixed-columns.html', 'datatables-fixed-columns.js', [
        ...dataTableScripts,
        '/assets/vendor/inspinia/plugins/datatables/dataTables.fixedColumns.min.js',
        '/assets/vendor/inspinia/plugins/datatables/fixedColumns.bootstrap5.min.js',
    ]],
    ['datatables/fixed-header', 'tables-datatables-fixed-header.html', 'datatables-fixed-header.js', [
        ...dataTableScripts,
        '/assets/vendor/inspinia/plugins/datatables/dataTables.fixedHeader.min.js',
        '/assets/vendor/inspinia/plugins/datatables/fixedHeader.bootstrap5.min.js',
    ]],
    ['datatables/columns', 'tables-datatables-columns.html', 'datatables-show-hide-columns.js', dataTableScripts],
    ['datatables/child-rows', 'tables-datatables-child-rows.html', 'datatables-child-rows.js', dataTableScripts],
    ['datatables/column-searching', 'tables-datatables-column-searching.html', 'datatables-column-search.js', dataTableScripts],
    ['datatables/range-search', 'tables-datatables-range-search.html', 'datatables-range-search.js', dataTableScripts],
    ['datatables/rows-add', 'tables-datatables-rows-add.html', 'datatables-rows-add.js', dataTableScripts],
    ['datatables/checkbox-select', 'tables-datatables-checkbox-select.html', 'datatables-checkbox-select.js', [
        ...dataTableScripts,
        '/assets/vendor/inspinia/plugins/datatables/dataTables.select.min.js',
        '/assets/vendor/inspinia/plugins/datatables/select.bootstrap5.min.js',
    ]],
];

const tablePages = tableDefinitions.map(([slug, doc, pageScript, scripts]) => ({
    group: 'tables',
    slug,
    path: `/demo-ui/tables/${slug}`,
    doc,
    scripts,
    pageScript: pageScript === null
        ? null
        : `/assets/vendor/inspinia/js/pages/${pageScript}`,
}));

const indexPage = {
    group: 'index',
    slug: 'index',
    path: '/demo-ui',
    doc: 'ui-alerts.html',
};

module.exports = {
    allPages: [indexPage, ...baseUiPages, ...formPages, ...chartPages, ...tablePages],
    baseUiPages,
    chartPages,
    formPages,
    indexPage,
    tablePages,
};
