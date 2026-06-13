# Global PageHeader contract

## Scope

This document describes the canonical HTML/CSS contract for Catalyst framework
and application surfaces.

The goal is structural consistency, not only visual similarity.

## Canonical PageHeader

All HTML surfaces should render their page header through:

- `app/Framework/View/PageHeaderViewModel.php`
- `boot-core/template/components/_page-header.phtml`
- `boot-core/template/scope/components/_page-header.php`

The header supports:

- eyebrow/category;
- title;
- description;
- action buttons;
- compact metrics;
- secondary tabs/navigation.

PageHeader help is required across common-layout surfaces outside Demo UI. The
title bar shows only the page title and a compact help trigger.
Eyebrow/category and description move into one accessible Bootstrap modal
owned by the global PageHeader. Every PageHeader producer must provide a
surface-specific description; empty or generic placeholder help is not an
accepted substitute. Demo UI is excluded.

Metrics and secondary tabs are rendered immediately after the title bar in a
compact contextual content block. They do not enlarge or visually replace the
native Inspinia title bar.

`DocumentScope` resolves the existing modular `NavigationRegistry` breadcrumb
declaration for the current authorized path. The global PageHeader renders the
compact native Bootstrap/Inspinia breadcrumb inside the same `page-title-head`
surface as the title. An explicit `breadcrumb_items` scope remains
authoritative when a surface supplies one.

The template composes with Inspinia's native `page-title-head` and
`h4.page-main-title` markup. PageHeader is not a card, does not add a competing
class to `page-title-head`, and does not own an alternate border, radius,
shadow or content-area theme.

Views should pass a `page_header` array and include:

```text
{{> "components._page-header" }}
```

Surface-specific hero classes such as `rbac-hero`, `operations-hero`,
`catalogs-hero`, `media-hero`, `documents-hero`, `automation-hero`,
`apiplatform-hero`, `settings-console-hero` and `privileged-executive-hero` are legacy
patterns and should not be used for new headers.

## Buttons

Surface actions must render as `<button>` controls. Navigation buttons
use `data-catalyst-href` and are handled by the global `ui-actions.js` module.

Allowed examples:

```html
<button type="button" class="btn btn-sm btn-primary" data-catalyst-href="/users/enroll">
    New user
</button>

<button type="submit" class="btn btn-sm btn-primary">
    Save
</button>
```

Avoid for actions:

```html
<a class="btn btn-primary" href="...">Action</a>
<button class="btn btn-link">Action</button>
```

Semantic anchors are still valid for breadcrumbs, menus, nav links and normal
text links.

## DataGrid

Tables that represent framework or application listings should use the shared
DataGrid component:

- `app/Framework/DataGrid/DataGrid.php`
- `boot-core/template/components/_datagrid.phtml`
- `boot-core/template/scope/components/_datagrid.php`
- `public/assets/js/catalyst/datagrid/interactions.js`

DataTables is a progressive enhancement over the server-driven grid. It should
not own persistence, authorization, filtering or pagination logic.

## CSS placement

Global patterns live in:

- `public/assets/css/catalyst/surfaces.css`
- `public/assets/css/catalyst/inspinia-runtime-compat.css`

Module `front/style.css` files should only keep behavior that is truly specific
to that module.

## Verification checks

Useful searches before closing a UI normalization change:

```powershell
rg -n '<a[^>]*class="[^"]*\bbtn\b' Repository boot-core
rg -n 'btn-link' Repository boot-core public/assets
rg -n 'rbac-hero|operations-hero|catalogs-hero|media-hero|documents-hero|automation-hero|apiplatform-hero|settings-console-hero|privileged-executive-hero' Repository/Framework boot-core/template
```
