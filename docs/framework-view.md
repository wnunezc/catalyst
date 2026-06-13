# Catalyst\Framework\View

## Purpose

Document the current view rendering primitives and their boundaries against security helpers and module asset publication.

## Runtime Owners

| Concern | Owner |
|---|---|
| Template rendering | `Catalyst\Framework\View\View` |
| Module view paths | `Catalyst\Framework\View\ModuleViewPathRegistrar` |
| Tokenized template rendering | `Catalyst\Framework\View\ViewTokenRenderer` |
| Trusted HTML marker | `Catalyst\Framework\View\TrustedHtml` |
| Inline JSON transport | `Catalyst\Framework\View\InlineJson` |
| HTML allowlist sanitizer | `Catalyst\Framework\View\HtmlAllowlistSanitizer` |
| Module work assets | `Catalyst\Framework\Traits\FrontResourceTrait` |

## Current Behavior

The runtime inventory catalogs templates and scripts. Module templates live under module `Views` directories and boot templates live under `boot-core/template`. Feature-specific CSS/JS belongs under `Repository/{Framework|App}/{Module}/front/{style.css,script.js}` and is published to `public/assets/*/work/{slug}/` through the established front-resource path.

`View::render()` always returns a complete HTML document using
`boot-core/template/document.phtml`. Controllers use `view()` for complete
pages and `viewFragment()` only for insertable HTML such as modal bodies or
partial refreshes. Layout names and surface profiles are not part of the view
API.

`DocumentScope::prepare(array $scope)` supplies document metadata, appearance,
theme attributes, shared assets, navigation and shell defaults. A new surface
therefore receives the complete shared shell without declaring a profile.
Exceptional surfaces may explicitly set `show_topbar`, `show_sidebar`,
`show_status_bar`, `show_theme_customizer`, shell classes and navigation data.
Those values control capabilities inside the same document; they do not select
another renderer or template tree.

`ViewTokenRenderer` is used for declarative `.phtml` templates and constrained
fragments. `TrustedHtml`, `InlineJson` and the allowlist sanitizer are security
boundary helpers; they do not make arbitrary HTML safe by default.

`_body-scripts.phtml` loads the common UI runtime before published module work
scripts. Generic shell, Bootstrap and Inspinia behavior belongs to the runtime
registry. Surface scripts are limited to registered extensions or
surface-specific behavior and must not initialize the shell again.

`document.phtml` also owns the single global activity overlay. It is visible
while the initial document runtime mounts; `ui-runtime.js` then delegates its
lifecycle to the single `ActivityManager`. Module views must not render local
full-page loaders or alternate activity overlays.

## Operational Notes

When creating or moving templates/scripts, regenerate `docs/runtime-inventory.md` with `php public/cli.php docs:inventory`. Keep CSP and `data-*` conventions in `docs/security-conventions.md` instead of duplicating them in module docs.

## Related Documentation

- `docs/views.md`
- `docs/security-conventions.md`
- `docs/ui/activity-overlay.md`
- `docs/framework-datagrid.md`
- `docs/runtime-inventory.md`
