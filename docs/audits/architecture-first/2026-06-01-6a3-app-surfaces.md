# Phase 6A.3 - App Surfaces And Shared Support Audit

Date: 2026-06-01

Status: initial App surface audit completed / remediation pending.

## Executive Summary

The App inventory contains five runtime modules: Account, Dashboard, Home,
Landing and Store. Two directories under `Repository/App/Surface` are not
runtime modules:

- `PublicSupport` is legitimate shared support consumed by several public
  surfaces, but its location under `Surface/` misrepresents its responsibility.
- `Demo` mixes one active development CLI command with one controller that has
  no detected consumer.

No files were moved or deleted during this audit.

## Runtime Surface Inventory

| Surface | Manifest | Routes | Owned routes | Classification |
| --- | --- | --- | ---: | --- |
| Account | yes | yes | 23 | Runtime module |
| Dashboard | yes | yes | 2 | Runtime module |
| Home | yes | yes | 3 | Runtime module |
| Landing | yes | yes | 2 | Runtime module |
| Store | yes | yes | 2 | Runtime module |
| PublicSupport | no | no | 0 | Shared support |
| Demo | no | no | 0 | Misplaced CLI plus dead-code candidate |

## Findings

### High - ARQ-19: App view namespaces also depend on cold route loading

**Files and lines**

- `Repository/App/Surface/Account/routes.php:14`
- `Repository/App/Surface/Dashboard/routes.php:12`
- `Repository/App/Surface/Home/routes.php:11`
- `Repository/App/Surface/Landing/routes.php:11`
- `Repository/App/Surface/Store/routes.php:11`

**Evidence**

Each App runtime surface registers its view namespace through
`View::getInstance()->addPath(...)` inside `routes.php`. This extends ARQ-12:
cached production routes also lose App view namespaces.

**Recommendation**

Include App metadata in the `6B.0` bootstrap registrar.

### Medium - ARQ-20: PublicSupport is shared support stored as a surface

**Files**

- `Repository/App/Surface/PublicSupport/Controllers/PublicPageController.php`
- `Repository/App/Surface/PublicSupport/Support/PublicDemoCatalog.php`
- `Repository/App/Surface/PublicSupport/Support/PublicNavigationBuilder.php`
- `Repository/App/Surface/PublicSupport/Support/PublicRuntimeSnapshot.php`

**Evidence**

`PublicPageController` and `PublicDemoCatalog` are consumed by Home, Landing,
Store and Dashboard. `PublicSupport` has no `module.php`, no `routes.php` and no
views. It is not discovered as a module.

**Recommendation**

Move the used shared code to an App support namespace outside `Surface/`, for
example `Repository/App/Support/PublicSurface`. Update consumers and
documentation in one batch.

### Medium - ARQ-21: Development overlay CLI is misplaced under App Demo

**Files and lines**

- `Repository/App/Surface/Demo/Commands/ExportDevelopmentOverlayCommand.php:13`
- `app/Framework/Cli/CliKernel.php:182`
- `public/cli.php:126`

**Evidence**

`dev:export-overlay` captures and renders an auth/RBAC development snapshot
through `Catalyst\Framework\Testing\AuthFixtureManager`. It is active because
`CliKernel::autoDiscover()` scans `Repository/App/Surface/*/Commands/*.php`.
This is framework testing tooling, not an App demo surface.

**Recommendation**

Move the command next to Framework CLI/testing commands and register it
explicitly in `public/cli.php`. Reassess whether App surface command
autodiscovery is still a useful supported extension point.

### Medium - ARQ-22: App manifests retain hard-coded user-facing labels

**Files**

- `Repository/App/Surface/Account/module.php`
- `Repository/App/Surface/Dashboard/module.php`
- `Repository/App/Surface/Home/module.php`
- `Repository/App/Surface/Landing/module.php`
- `Repository/App/Surface/Store/module.php`

**Evidence**

Surface content uses the translation system, but several manifest
descriptions, navigation labels, group labels and hints are literal English
strings.

**Recommendation**

Normalize manifest display text through i18n during `6B.3`.

### Low - ARQ-23: Three unused App classes are deletion candidates

**Files**

- `Repository/App/Surface/Demo/Controllers/AppDemoController.php`
- `Repository/App/Surface/PublicSupport/Support/PublicNavigationBuilder.php`
- `Repository/App/Surface/PublicSupport/Support/PublicRuntimeSnapshot.php`

**Evidence**

Repository search found no runtime consumers for these classes.

**Recommendation**

Present the final candidate list before deletion. Remove only after explicit
destructive-change confirmation.

### Low - ARQ-24: Account Requests encapsulate validation but do not implement a common Request contract

**Files**

- `Repository/App/Surface/Account/Requests/MfaRecoveryRequest.php`
- `Repository/App/Surface/Account/Requests/SupportRecoveryRequest.php`
- `Repository/App/Surface/Account/Controllers/AccountCenterController.php`
- `Repository/App/Surface/Account/Controllers/AccountRecoveryController.php`

**Evidence**

Account keeps validation outside controllers, which is directionally correct.
However, controllers instantiate validator wrappers manually and pass the base
framework `Request`, while newer Framework modules inject dedicated Request
objects directly.

**Recommendation**

Keep current behavior during stabilization. Align with the common Request
contract in a later Account-focused batch.

## Positive Evidence

- Account separates repositories, services, Requests, support view models and
  controllers.
- Public recovery mutations declare `auth_recovery` throttles.
- Administrative recovery mutations declare `admin_mutation` throttles.
- Dashboard API is protected with `AuthMiddleware`.
- Home, Landing and Store public JSON companions are intentional public
  surfaces.
- `dev:export-overlay` is active and documented; it must be moved, not removed.

## Commands Executed

```powershell
php public/cli.php inspect:modules --json
php public/cli.php route:list --json
php public/cli.php help
Get-ChildItem Repository\App ...
Get-Content ...
rg -n ...
```

## Destructive Changes Requiring Confirmation

Before deletion, request explicit confirmation for:

- `Repository/App/Surface/Demo/Controllers/AppDemoController.php`
- `Repository/App/Surface/PublicSupport/Support/PublicNavigationBuilder.php`
- `Repository/App/Surface/PublicSupport/Support/PublicRuntimeSnapshot.php`
- Empty directories left after approved moves or deletions.

## Next Step

Proceed to `6A.4`: audit inline documentation and `/docs`, including stale hot
documents and historical snapshots that must remain cold evidence.
