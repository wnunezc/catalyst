# Security Conventions

## Purpose

Define current security conventions for templates, frontend behavior, trusted HTML, inline JSON and route boundaries.

## Runtime Owners

| Concern | Owner |
|---|---|
| CSRF token generation/validation | `Catalyst\Helpers\Security\CsrfProtection` |
| CSP nonce helper | `Catalyst\Helpers\Security\CspNonce` |
| Sensitive redaction | `Catalyst\Helpers\Security\SensitiveValueRedactor` |
| Trusted HTML marker | `Catalyst\Framework\View\TrustedHtml` |
| Inline JSON transport | `Catalyst\Framework\View\InlineJson` |
| HTML allowlist sanitizer | `Catalyst\Framework\View\HtmlAllowlistSanitizer` |
| Route guards | `Catalyst\Framework\Middleware\*` |

## Current Behavior

Templates should escape output with `e($value)` unless a value is intentionally represented by `TrustedHtml` after a trusted producer or sanitizer boundary. Inline JavaScript/CSS is not the default pattern for module views; module-specific assets belong in module `front/` files and are published as work assets. Inline JSON transport should use the established helper path and preserve CSP compatibility.

Route access is enforced by middleware and verified by runtime lint. API-token routes, authenticated routes, setup routes and public routes must remain distinguishable in route docs and runtime catalog output.

The 13 public `/api/v1/*` routes use `ApiTokenMiddleware`; session-authenticated `/api/notifications*`, `/api/presence*`, `/api/ws-token` and App `/api/public/*` companions are internal transports, not public APIs. Deployments must hide process errors and local paths. Tenancy diagnostics must omit raw configuration, hosts, DSN, credentials and secrets.

## Operational Notes

Run `php public/cli.php security:check` for focused frontend/CSP hotspots when editing view or script behavior. Run `inspect:lint` and `route:lint` after route, guard or asset changes.

## Related Documentation

- `docs/framework-view.md`
- `docs/middleware.md`
- `docs/framework-auth.md`
- `docs/quality-gate.md`
