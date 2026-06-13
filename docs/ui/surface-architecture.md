# Surface Architecture

## Purpose

Document the canonical document, shell, frontend runtime and surface ownership
contracts used by Catalyst.

## One Document And One Shell

Every complete HTML response renders through:

```text
boot-core/template/document.phtml
└── boot-core/template/shell.phtml
```

`DocumentScope` prepares metadata, assets, navigation, appearance and shell
capabilities. Topbar, sidebar, status bar and theme customizer are optional
capabilities inside that shell. Auth, Public, Account guest and Error surfaces
may hide capabilities or supply body, shell and content classes, but they do
not select another document, layout profile or shell.

Shared surface structure uses `surface-page`, `surface-page-shell` and the
global styles in:

- `public/assets/css/catalyst/surfaces.css`
- `public/assets/css/catalyst/inspinia-runtime-compat.css`
- `public/assets/css/catalyst/ui-reference.css`

Module `front/style.css` files contain only behavior specific to that module.
They must not recreate document, topbar, sidebar or content-page geometry.
They also must not redesign native Inspinia cards, tables, buttons, badges,
forms or page titles inside module wrappers.

The detailed ownership and common-layout inventory live in
`docs/ui/css-ownership.md`.

## CSS Cascade Contract

Demo UI is the implementation guide for reusable UI elements, not the owner of
an exclusive visual scope. Reusable reference styles use neutral selectors in
`ui-reference.css` and apply to every surface. Active CSS must not introduce
`.demo*` selectors or require a Demo UI body wrapper.

The canonical cascade order is:

1. Framework base and generic component CSS.
2. Module-specific CSS.
3. Global theme CSS.

Theme styles therefore override the generic UI contract without creating a
layout, shell, runtime or surface-specific theme.

## Global Icon Assets

The canonical document loads the locally vendored Font Awesome Free webfont
CSS and Inspinia's locally vendored Tabler Icons CSS. Both icon libraries are
global framework assets; surfaces may consume their existing classes without
loading a CDN, module-specific icon runtime or alternate document.

Font Awesome Free 7.2.0 lives under `public/assets/vendor/fontawesome` with its
license, CSS and required webfonts. Its CSS loads before application and theme
styles.

## Internal Shell Scroll Ownership

Internal surfaces using `body.catalyst-shell-body` keep the document viewport
fixed. The body and `.wrapper` do not own vertical scrolling; `.content-page`
owns the available area between the shared topbar and status bar, and its
existing SimpleBar content wrapper performs vertical scrolling.

Public, Auth and Error surfaces use their own capability classes and retain
document scrolling where their surface contract requires it. Component-level
scroll containers such as tables, editors and modal bodies remain independent.
The global SimpleBar adapter mounts both a scan root carrying `data-simplebar`
and matching descendants, so initial documents and dynamic rescans share the
same scroll behavior without surface-specific branches.

## SSR, AJAX And SPA Flow

The canonical flows are:

1. SSR page: a controller calls `view()`, `View::render()` produces the complete
   document, and the central frontend runtime mounts registered components.
2. AJAX fragment: a controller calls `viewFragment()`,
   `View::renderFragment()` returns insertable HTML, and the response action
   dispatches `catalyst:dom:updated` after insertion.
3. SPA-like interaction: navigation, forms, modals and targeted refreshes may
   replace part of the DOM, but they remain extensions of the SSR document.
   Catalyst does not start a second shell or frontend governor.

`public/assets/js/catalyst/runtime/ui-runtime.js` is the only frontend
governor. It owns ordered registration, initial mounting, targeted rescans,
destruction and extension events. Surface scripts register behavior through
`Catalyst.ui`; they do not call a parallel bootstrap.

## Global Capabilities

Reusable UI capabilities are framework-owned and must be consumed rather than
forked:

- PageHeader
- DataGrid
- FormBuilder
- RecordPresence
- CRUD scaffolding
- shell navigation
- appearance and theme selection

The capability contract is global. A surface may provide data and
surface-specific styling, but it may not create a local component variant or
secondary governor.

## Ownership

Framework-owned modules live under `Repository/Framework` and their contract
tests live under `test/framework`.

Application-owned controllers, services and surfaces live under
`Repository/App` and their tests live under `test/app`.

The same ownership boundary applies to PHP unit tests and Playwright specs.
Application behavior must not be placed in the framework suite.

## Privileged Boundary

Privileged is a role, permission and business authorization concept. It may appear
in protected routes such as `/privileged/account-recovery`, middleware, policies,
labels and operational workflows.

Privileged is never a global namespace, document profile, layout, shell, frontend
runtime or CSS architecture. Privileged routes use the same document,
shell, capabilities and runtime as other internal surfaces.

## Generated Inventories

Generated documentation is refreshed only through its canonical commands:

```powershell
php public/cli.php docs:inventory
php public/cli.php docs:sync-runtime
```

Do not edit `docs/runtime-inventory.md` or
`docs/runtime-module-catalog.md` manually. Summaries, snapshots, backups and
audit history are evidence, not current architecture documentation.
