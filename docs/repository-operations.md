# Operations Owner

## Purpose

Document the single framework owner for operational privileged and its domain APIs.

## Contract

`Repository/Framework/Operations` and `Catalyst\Repository\Operations` own 21 routes across five connected surfaces:

| Surface | HTML | Public `/api/v1/*` |
|---|---:|---:|
| Deployments | 2 | 0 |
| Tenancy | 1 | 0 |
| Audit Log | 2 | 0 |
| API Management | 3 | 0 |
| Automation Rules | 10 | 3 |

All HTML routes use `/operations/*`. `/operations` is not an overview route. The module exposes five canonical permissions and no aliases.

## Security Boundaries

- Deployments validates a configured profile allowlist, preserves mutation throttling, supports artifact-free dry-run and hides process errors and local paths from HTTP output.
- Tenancy is read-only and projects only safe diagnostic fields; it does not expose hosts, DSN, credentials, secrets or raw configuration.
- The three Operations-owned Automation `/api/v1/*` routes preserve `ApiTokenMiddleware`, abilities, throttling and response/error contracts.
- Internal notification, presence, WebSocket and App companion transports remain separate technical debt and are not public APIs.

## Verification

```powershell
php public/cli.php route:list --json
php public/cli.php automation:mvc-regression
php public/cli.php shell-navigation:smoke --json
php test/framework/UnitTest/run.php
```
