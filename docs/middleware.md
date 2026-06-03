# Middleware Index

## Purpose

Describe the current middleware boundary and route readers to guard-specific runtime evidence.

## Runtime Owners

| Concern | Owner |
|---|---|
| Authentication guard | `Catalyst\Framework\Middleware\AuthMiddleware` |
| Guest guard | `Catalyst\Framework\Middleware\GuestMiddleware` |
| Role/permission guard | `Catalyst\Framework\Middleware\RoleMiddleware` |
| API token guard | `Catalyst\Framework\Middleware\ApiTokenMiddleware` |
| Feature flag guard | `Catalyst\Framework\Middleware\RouteFeatureMiddleware` |
| Setup guard | `Catalyst\Framework\Middleware\SetupGuardMiddleware` |
| Global middleware registration | `Catalyst\Framework\Route\GlobalMiddlewareRegistrar` |

## Current Behavior

Middleware enforces request boundaries after route matching and before controller execution. The runtime module catalog lists middleware per representative route and per module. Auth, role, API token, feature flag and setup guards are separate responsibilities and should not be documented as controller-owned behavior.

## Operational Notes

Use `php public/cli.php inspect:lint` to validate route guard coherence and `php public/cli.php route:list --json` to inspect concrete middleware stacks. Security-specific CSP, nonce and input-output rules live in `docs/security-conventions.md`.

## Related Documentation

- `docs/routing.md`
- `docs/framework-auth.md`
- `docs/repository-auth.md`
- `docs/security-conventions.md`