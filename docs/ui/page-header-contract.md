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

Views should pass a `page_header` array and include:

```text
{{> "components._page-header" }}
```

Surface-specific hero classes such as `rbac-hero`, `operations-hero`,
`catalogs-hero`, `media-hero`, `documents-hero`, `automation-hero`,
`apiplatform-hero`, `settings-console-hero` and `admin-executive-hero` are legacy
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
rg -n 'rbac-hero|operations-hero|catalogs-hero|media-hero|documents-hero|automation-hero|apiplatform-hero|settings-console-hero|admin-executive-hero' Repository/Framework boot-core/template
```
