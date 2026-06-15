# Demo UI JavaScript Inventory

## Scope

This document inventories the active `/demo-ui` surface after the Catalyst UI
runtime cutover and the shared shell normalization. It is the route, view,
asset and Playwright coverage map for Demo UI. The same canonical document and
runtime contract now applies to Privileged, Public, Auth, Account and Error
surfaces; their focused coverage remains in separate specs.

## Route And View Inventory

The module declares 40 GET/HEAD route patterns. Its parameterized chart and
table patterns expand to an executable catalog of 85 active URLs backed by 84
unique generated preview documents. `/demo-ui` and `/demo-ui/alerts` both
render `ui-alerts.html`.

| Family | URLs | Preview documents | Resolver |
|---|---:|---:|---|
| Index alias | 1 | 0 additional | `DemoUiController::index()` |
| Base UI | 28 | 28 | Static controller methods |
| Forms | 8 | 8 | Static controller methods |
| Apex charts | 20 | 20 | `/demo-ui/charts/{family}/{page}` |
| ECharts | 11 | 11 | `/demo-ui/charts/{family}/{page}` |
| Static/custom tables | 2 | 2 | `/demo-ui/tables/{page}` |
| DataTables | 15 | 15 | `/demo-ui/tables/datatables/{page}` |
| Total expanded catalog | 85 | 84 | Produced by 40 declared route patterns on the shared Catalyst document shell |

The versioned executable catalog is
`test/framework/Playwright/fixtures/demo-ui-catalog.cjs`. It contains every
expanded URL, its `data-catalyst-inspinia-document` value and its conditional
script contract.

### Base UI

`accordions`, `alerts`, `images`, `badges`, `breadcrumb`, `buttons`, `cards`,
`carousel`, `collapse`, `colors`, `dropdowns`, `videos`, `grid-options`,
`links`, `list-group`, `modals`, `notifications`, `offcanvas`, `pagination`,
`placeholders`, `popovers`, `progress`, `scrollspy`, `spinners`, `tabs`,
`tooltips`, `typography`, `utilities`.

### Forms

`basic-elements`, `pickers`, `select`, `validation`, `wizard`, `file-uploads`,
`text-editors`, `range-slider`.

### Charts

Apex: `area`, `bar`, `bubble`, `candlestick`, `column`, `heatmap`, `line`,
`mixed`, `timeline`, `boxplot`, `treemap`, `pie`, `radar`, `radialbar`,
`scatter`, `polar-area`, `sparklines`, `range`, `funnel`, `slope`.

ECharts: `line`, `bar`, `pie`, `scatter`, `geo-map`, `gauge`, `candlestick`,
`area`, `radar`, `heatmap`, `other`.

### Tables

Static/custom: `static`, `custom`.

DataTables: `basic`, `export-data`, `select`, `ajax`, `javascript`,
`rendering`, `scroll`, `fixed-columns`, `fixed-header`, `columns`,
`child-rows`, `column-searching`, `range-search`, `rows-add`,
`checkbox-select`.

`Repository/Framework/DemoUi/generated/theme-previews/form-layout.html` has no
route or controller mapping and remains a non-surface.

## Shared Shell Rendering Stack

Every Demo UI URL now renders through the common Catalyst document/shell flow:

```text
Controller::view(...)
  -> View::render(...)
  -> boot-core/template/document.phtml
  -> boot-core/template/shell.phtml
     -> boot-core/template/_topbar.phtml
     -> boot-core/template/_sidebar.phtml
     -> boot-core/template/_content.phtml
        -> Repository/Framework/DemoUi/Views/pages/demo-ui.phtml
           -> {{{ preview_html }}}
     -> boot-core/template/_status-bar.phtml
```

There is no active layout selection, Demo UI document wrapper or Demo UI CSS
scope. The body uses the shared `catalyst-shell-body` contract and the surface
is identified semantically through `data-surface-context="demo-ui"`.

The generated preview documents under
`Repository/Framework/DemoUi/generated/theme-previews/` are fragment files. They
must not contain `<!doctype>`, `<html>`, `<head>`, `<body>` or inline `<script>`
blocks. Shell, head assets, body scripts, sidebar, topbar and status bar are
provided by the shared Catalyst templates.

## Common Asset Stack

Every Demo UI URL initializes through the shared shell and runtime:

1. Common document metadata from `_head-meta.phtml`.
2. Common head assets from `_head-assets.phtml`, including Inspinia vendor CSS,
   runtime compatibility CSS and the global neutral UI reference CSS.
3. The shared shell templates: `_topbar.phtml`, `_sidebar.phtml`,
   `_content.phtml` and `_status-bar.phtml`.
4. Common body scripts from `_body-scripts.phtml`, including the local
   Bootstrap bundle and the canonical module runtime.
5. `public/assets/js/catalyst/runtime/ui-runtime.js`, which imports the
   Catalyst facade and starts once when the document is ready.
6. `Repository/Framework/DemoUi/front/script.js`, limited to behavior specific
   to Demo UI examples.

Module CSS loads after common generic CSS. Global theme CSS loads after module
CSS so every theme can override the same neutral UI contract.

The runtime imports these adapters once and activates them only when matching
DOM is present:

`shell.navigation`, `vendor.simplebar`, `bootstrap.primitives`,
`bootstrap.components`, `inspinia.code-preview`, `inspinia.card-actions`,
`forms.visual-validation`, `inspinia.charts`, `inspinia.tables`, `shell.topbar`,
`shell.theme-customizer`, `shell.status-bar`, `inspinia.pickers`,
`inspinia.selects`, `inspinia.uploads`, `inspinia.editors`, `inspinia.wizard`,
`inspinia.sliders`.

An import does not imply a vendor plugin is activated. Each adapter inspects
the current DOM and initializes only matching components.

## Generated Preview Markup Rules

Generated Demo UI previews are allowed to contain real navigation links when a
route or external documentation target exists. Non-navigation actions must not
use fake `javascript:` hrefs.

Use buttons for inert/actions-only controls:

```html
<button type="button" class="dropdown-item">View</button>
<button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
<button type="button" class="border-0 bg-transparent p-0 align-baseline text-primary">Charts</button>
```

This keeps CSP clean and avoids fake links while preserving Bootstrap-compatible
layout for dropdown actions, modal dismiss controls and breadcrumb placeholders.

## Conditional Assets

| Surface | Detection | Scripts initialized |
|---|---|---|
| Bootstrap components | Bootstrap component markup or `data-bs-*` triggers | Existing local Bootstrap bundle |
| Pickers | Date, time or color picker markers | jQuery, Moment, DateRangePicker, Flatpickr, Pickr |
| Select | `data-choices`, Select2 markers | Choices; jQuery plus Select2 |
| Wizard | `[data-wizard]` | Runtime adapter only |
| File uploads | Dropzone/FilePond markers | Dropzone and FilePond plugin family |
| Text editors | Quill/Summernote markers | Quill; jQuery plus Summernote |
| Range slider | noUiSlider markers | noUiSlider |
| Apex chart | `charts-apex-*.html` | ApexCharts plus matching `chart-apex-*.js` |
| EChart | `charts-echart-*.html` | ECharts plus matching `chart-echart-*.js` |
| EChart geo map | `charts-echart-geo-map.html` | ECharts, world map CDN asset and page script |
| Static table | `tables-static.html` | No page script |
| Custom table | `tables-custom.html` | `custom-table.js` |
| DataTable | `tables-datatables-*.html` | Common DataTables stack, optional extension scripts and matching page script |

The exact DataTables extensions and page-script names are kept in the
Playwright catalog so each URL has an individual resource assertion.

## Playwright Inventory

All files live under `test/framework/Playwright` and must be run only through
the workspace runner with `--suite framework`.

| Spec | Cases declared | Contract |
|---|---:|---|
| `demo-ui-runtime.spec.cjs` | 1 | Shared document, shell, central runtime and Demo UI work assets |
| `demo-ui-routes.spec.cjs` | 85 | URL, real route, preview document, visible heading, shared runtime ready |
| `demo-ui-components.spec.cjs` | 7 | Accordion, collapse, dropdown, offcanvas, popover, tab and tooltip behavior/cleanup |
| `demo-ui-forms.spec.cjs` | 7 | Conditional plugin resources and initialized DOM |
| `demo-ui-charts.spec.cjs` | 31 | Chart engine, individual page script and rendered chart |
| `demo-ui-tables.spec.cjs` | 17 | Table presence, declared resources, individual page script and DataTables wrapper |
| `demo-ui-modals.spec.cjs` | 23 | Full modal trigger and cleanup contract |

The route, component, form, chart and table cases were executed individually
through the workspace runner. After removal of the legacy parallel governor,
all 23 modal cases were executed three times each: 69/69 passed.

## Maintenance Rule

When a Demo UI route, preview document or conditional asset changes:

1. Update `DemoUiController`.
2. Update `fixtures/demo-ui-catalog.cjs`.
3. Update the owning focused spec.
4. Update this inventory and `test/framework/Playwright/SURFACES.md`.
5. Confirm generated preview fragments still avoid fake `javascript:` links.
6. Execute only the affected focused spec through the authorized runner when
   test execution is approved.
