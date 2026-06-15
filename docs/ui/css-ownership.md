# CSS Ownership

## Purpose

Define the visual ownership contract for complete HTML surfaces rendered by the
shared Catalyst document and shell.

## Common Layout Inventory

The ROADMAP-5 common-layout inventory contains `117` active HTML URLs projected
through `50` page templates, including the 40 expanded Demo UI URLs counted by
that audit. This is narrower than the ROADMAP-7 inventory of every complete
HTML surface outside `/demo-ui*`; the two inventories must not be added or
treated as equivalent.

| Owner | HTML URLs | Page templates | Treatment |
|---|---:|---:|---|
| App Dashboard | 1 | 1 | Evaluated; preserved |
| Framework Account authenticated | 7 | 7 | Evaluated; preserved |
| Framework Configuration | 5 | 5 | Module CSS sanitized |
| Framework Demo UI | 40 | 1 | Preserved as regression reference |
| Framework DevTools | 24 | 4 | Diagnostic CSS sanitized |
| Framework Operations | 9 | 8 | Module CSS sanitized |
| Framework Users | 13 | 11 | Module CSS sanitized |
| Framework Workspaces | 18 | 13 | Module CSS sanitized |

The inventory excludes Auth, Public, Account guest, Error, setup responses that
do not use the common layout, JSON, APIs, runtime transports, downloads,
streams, fragments and mutation responses without a complete HTML document.

### Page title and breadcrumb inventory

Outside Demo UI, the common-layout inventory contains `77` URLs. Of those,
`52` GET/HEAD route definitions produce breadcrumbs and therefore consume the
combined global PageHeader composition:

| Owner | Breadcrumb routes |
|---|---:|
| App Dashboard and Framework Account | 7 |
| Framework Configuration | 5 |
| Framework DevTools | 3 |
| Framework Operations | 9 |
| Framework Users | 10 |
| Framework Workspaces | 18 |
| **Total** | **52** |

The remaining `25` URLs consume PageHeader without breadcrumbs. They use the
same partial and do not render an empty breadcrumb container. Demo UI remains
the native Inspinia reference and is not modified by this composition.

## Ownership

| Layer | Owner |
|---|---|
| General component geometry and visual language | Inspinia and Bootstrap |
| Shared functional surface structure | `public/assets/css/catalyst/surfaces.css` |
| Shell, SimpleBar and overlay integration | `public/assets/css/catalyst/inspinia-runtime-compat.css` |
| CSP replacements and reusable plugin adapters | `public/assets/css/catalyst/ui-reference.css` |
| Reusable capability behavior | Capability CSS such as DataGrid and RecordPresence |
| Module-exclusive layout and states | `Repository/{Framework|App}/{Module}/front/style.css` |
| Institutional identity and colors | `red-cross-theme.css` and `response-skins.css` |

## Rules

- Catalyst CSS does not redesign native cards, tables, buttons, badges,
  breadcrumbs, forms or list groups inside surface wrappers.
- Module CSS does not create a local theme or redefine common components.
- Capability CSS such as `account-shell.css` does not add outer padding or
  margins to common-layout page wrappers.
- Institutional themes change identity and color, not page geometry.
- Purely visual wrappers are not JavaScript contracts; behavior uses `data-*`.
- Source work assets and published work assets must remain byte-identical.
- SimpleBar, CSP replacements and the central runtime remain preserved.

## PageHeader

The global PageHeader uses Inspinia's `page-title-head` and `page-main-title`
composition. It preserves actions, metrics, tabs and `data-page-header` without
representing the title bar as a card.

Informational metrics and secondary navigation are rendered in a compact
context block immediately after the title bar. Breadcrumbs are resolved from
the existing modular `NavigationRegistry` declarations by `DocumentScope` and
rendered once inside the global PageHeader, on the same native
`page-title-head` surface as the title.

Of the `50` authoritative templates, `49` consume the global PageHeader. Demo UI
is the single preserved reference exception. The removed UML local header had
no remaining consumer. The `42` PageHeader producers covering the `77`
common-layout URLs outside Demo UI provide surface-specific descriptions that
the central component presents through the compact help trigger and accessible
modal.
