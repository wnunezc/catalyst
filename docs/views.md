# Views Index

## Purpose

Serve as the broad entry point for Catalyst view, template, frontend resource and CSP documentation.

## Runtime Owners

| Concern | Owner |
|---|---|
| View rendering | `Catalyst\Framework\View\View` |
| Module view registration | `Catalyst\Framework\View\ModuleViewPathRegistrar` |
| Token rendering | `Catalyst\Framework\View\ViewTokenRenderer` |
| Trusted HTML and inline JSON boundaries | `Catalyst\Framework\View\TrustedHtml`, `Catalyst\Framework\View\InlineJson` |
| Work assets | `Catalyst\Framework\Traits\FrontResourceTrait` |

## Current Behavior

Runtime inventory currently reports 230 templates and 54 scripts. Module-specific frontend assets belong in `Repository/{Framework|App}/{Module}/front/` and are published under `public/assets/*/work/{slug}/`. CSP and trusted HTML rules are documented in `docs/security-conventions.md`.

## Operational Notes

After template/script changes, run `php public/cli.php docs:inventory --json`, `php public/cli.php inspect:lint` and `git diff --check`.

## Related Documentation

- `docs/framework-view.md`
- `docs/security-conventions.md`
- `docs/runtime-inventory.md`