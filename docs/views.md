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

Every complete view renders through `boot-core/template/document.phtml` and the
shared `shell.phtml`. `Controller::view()` is the default complete-page API;
`Controller::viewFragment()` is the explicit API for HTML that must not contain
the document or shell. Catalyst does not expose layout profiles.

Module-specific frontend assets belong in
`Repository/{Framework|App}/{Module}/front/` and are published under
`public/assets/*/work/{slug}/`. `DocumentScope` appends those assets to the
shared head/body lists. CSP and trusted HTML rules are documented in
`docs/security-conventions.md`.

## Operational Notes

After template/script changes, run `php public/cli.php docs:inventory --json`, `php public/cli.php inspect:lint` and `git diff --check`.

## Related Documentation

- `docs/framework-view.md`
- `docs/security-conventions.md`
- `docs/runtime-inventory.md`
