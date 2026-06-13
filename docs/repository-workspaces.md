# Workspaces Owner

## Purpose

Document the single framework owner for workspace authoring and content-management surfaces.

## Contract

`Repository/Framework/Workspaces` and `Catalyst\Repository\Workspaces` own 49 routes across six connected surfaces:

| Surface | HTML | Public `/api/v1/*` |
|---|---:|---:|
| Catalogs | 14 | 0 |
| Module Designer | 3 | 0 |
| Media Fields | 6 | 0 |
| Media Library | 7 | 0 |
| Document Templates | 11 | 4 |
| Locale Tools | 4 | 0 |

All HTML routes use `/workspaces/*`. The module exposes six canonical permissions and contributes navigation, breadcrumbs, translations and one published work asset owner.

## Boundaries

- Module Designer validates destinations and requires a signed preview before generation.
- Locale Tools uses bounded locale roots, dry-run support and atomic writes.
- The four Document Templates APIs preserve `ApiTokenMiddleware`, abilities, throttling and response contracts.
- Workspaces does not own Configuration, Users, Account/Application or internal runtime transports.

## Verification

```powershell
php public/cli.php route:list --json
php public/cli.php inspect:lint
php public/cli.php route:lint
php test/framework/UnitTest/run.php
```
