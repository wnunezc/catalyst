# Admin surface contract

## Scope

This document describes the canonical HTML/CSS contract for Catalyst framework
administration surfaces after the compact executive UI normalization.

The goal is structural consistency, not only visual similarity.

## Canonical header

All administration surfaces should render their page header through:

- `boot-core/template/components/_admin-page-header.phtml`
- `boot-core/template/scope/components/_admin-page-header.php`

The header supports:

- eyebrow/category;
- title;
- description;
- action buttons;
- compact metrics;
- secondary tabs/navigation.

Views should pass an `admin_header` array and include:

```text
{{> "components._admin-page-header" }}
```

Surface-specific hero classes such as `rbac-hero`, `operations-hero`,
`catalogs-hero`, `media-hero`, `documents-hero`, `automation-hero`,
`apiplatform-hero`, `settings-console-hero` and `admin-executive-hero` are legacy
patterns and should not be used for new headers.

## Buttons

Administrative actions must render as `<button>` controls. Navigation buttons
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

Avoid for administrative actions:

```html
<a class="btn btn-primary" href="...">Action</a>
<button class="btn btn-link">Action</button>
```

Semantic anchors are still valid for breadcrumbs, menus, nav links and normal
text links.

## DataGrid

Tables that represent framework administration listings should use the shared
DataGrid component:

- `app/Framework/Admin/Grid/DataGrid.php`
- `boot-core/template/components/_admin-datagrid.phtml`
- `boot-core/template/scope/components/_admin-datagrid.php`
- `public/assets/js/catalyst/modules/admin-grid.js`

DataTables is a progressive enhancement over the server-driven grid. It should
not own persistence, authorization, filtering or pagination logic.

## CSS placement

Global patterns live in:

- `public/assets/css/catalyst/admin-surfaces.css`
- `public/assets/css/catalyst/inspinia-runtime-compat.css`

Module `front/style.css` files should only keep behavior that is truly specific
to that module.

## Verification checks

Useful searches before closing a UI normalization change:

```powershell
rg -n '<a[^>]*class="[^"]*\bbtn\b' Repository boot-core
rg -n 'btn-link' Repository boot-core public/assets
rg -n 'rbac-hero|operations-hero|catalogs-hero|media-hero|documents-hero|automation-hero|apiplatform-hero|settings-console-hero|admin-executive-hero' Repository/Framework boot-core/template
```
