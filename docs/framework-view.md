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

The runtime inventory reports 230 templates and 54 scripts. Module templates live under module `Views` directories and boot templates live under `boot-core/template`. Feature-specific CSS/JS belongs under `Repository/{Framework|App}/{Module}/front/{style.css,script.js}` and is published to `public/assets/*/work/{slug}/` through the established front-resource path.

`ViewTokenRenderer` is used for tokenized non-PHP templates such as generated HTML export output. `TrustedHtml`, `InlineJson` and the allowlist sanitizer are security boundary helpers; they do not make arbitrary HTML safe by default.

## Operational Notes

When creating or moving templates/scripts, regenerate `docs/runtime-inventory.md` with `php public/cli.php docs:inventory`. Keep CSP and `data-*` conventions in `docs/security-conventions.md` instead of duplicating them in module docs.

## Related Documentation

- `docs/views.md`
- `docs/security-conventions.md`
- `docs/framework-datagrid.md`
- `docs/runtime-inventory.md`