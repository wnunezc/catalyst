# Task 6D.3 - Executable Template Migration Plan

Date: 2026-06-02

Status: implemented and verified / included in the Task 6D.3 checkpoint.

## Scope

This task is planning-only. It inventories executable PHP templates and defines a
bounded migration order toward declarative `.phtml` templates while preserving
the current PHP compatibility fallback until inventory reaches zero.

## Inventory Source

Source of truth:

```powershell
php public/cli.php docs:inventory --json
```

Current template inventory:

- Total templates: 229
- Declarative `.phtml`: 140
- Executable `.php`: 89

## Executable Template Owners

| Owner | Executable `.php` | Risk | Notes |
|---|---:|---|---|
| `boot-core/template` | 31 | High | Shared layouts, components, errors and debug templates. Regressions affect all surfaces. |
| `Framework/ApiPlatform` | 1 | Low | Single scope page; candidate for early migration. |
| `Framework/Audit` | 2 | Low | Read-only list/show scopes; good early validation surface. |
| `Framework/Auth` | 8 | High | Auth, MFA and reset flows are security-sensitive. |
| `Framework/Automation` | 3 | Medium | Already refactored in 6C; use MVC regression before removal. |
| `Framework/Catalogs` | 4 | Medium | CRUD/workflow surface; preserve metadata and transition payloads. |
| `Framework/DevTools` | 11 | Deferred | Explicitly deferred visual/layout surface. |
| `Framework/Documents` | 3 | Medium | Already refactored in 6C; preserve preview/export workflows. |
| `Framework/Media` | 4 | Medium | Dynamic metadata and upload forms need focused regression. |
| `Framework/Operations` | 9 | High | Platform settings, appearance, localization and module designer. |
| `Framework/Roles` | 8 | High | RBAC and enrollment flows; preserve permissions and bulk actions. |
| `Framework/Settings` | 5 | High | Setup/health/runtime config boundaries. |

## Migration Order

### Wave T1 - Low-Risk Read-Only Scopes

Targets:

- `Repository/Framework/ApiPlatform/Views/scope/pages/index.php`
- `Repository/Framework/Audit/Views/scope/pages/index.php`
- `Repository/Framework/Audit/Views/scope/pages/show.php`

Goal:

- Move scope preparation into controller/support factories or explicit view
  model classes.
- Keep existing `.phtml` render targets unchanged.
- Add or reuse route/view smoke before deleting each `.php` scope.

### Wave T2 - 6C-Stabilized Admin Modules

Targets:

- `Repository/Framework/Automation/Views/scope/pages/*.php`
- `Repository/Framework/Documents/Views/scope/pages/*.php`
- `Repository/Framework/Media/Views/scope/pages/*.php`

Goal:

- Use the factories and Requests introduced in 6C as migration anchors.
- Preserve `automation:mvc-regression`, `documents:mvc-regression` and
  `media:mvc-regression` as gates.
- Do not modify persistence or upload/export behavior in the same wave.

### Wave T3 - RBAC And Platform Configuration

Targets:

- `Repository/Framework/Roles/Views/scope/**/*.php`
- `Repository/Framework/Operations/Views/scope/**/*.php`
- `Repository/Framework/Settings/Views/scope/**/*.php`

Goal:

- Convert scope builders into named support/view-model classes.
- Preserve CSRF, RBAC gates, i18n labels, appearance payloads and setup state.
- Gate with `roles:mvc-regression`, `operations:requests-regression`,
  `quality:check` and targeted route smokes.

### Wave T4 - Auth

Targets:

- `Repository/Framework/Auth/Views/scope/**/*.php`

Goal:

- Move page payload assembly into Auth support classes only after a focused
  auth/MFA/reset regression exists.
- Preserve CSRF, throttle state, OAuth redirects, MFA QR payloads and validation
  replay.

### Wave T5 - Shared Shell And Error Templates

Targets:

- `boot-core/template/scope/**/*.php`

Goal:

- Split shared shell/component scope into framework view-model factories.
- Preserve all layouts and universal partials first; migrate debug and error
  templates after shell parity is proven.
- Gate with `route:bootstrap-regression`, `quality:check`, route smokes and CSP
  checks.

### Wave T6 - DevTools Deferred Batch

Targets:

- `Repository/Framework/DevTools/Views/scope/**/*.php`

Goal:

- Keep deferred until DevTools visual/layout internals are explicitly approved.
- Use this wave to retire remaining executable template fallback if previous
  waves reach zero outside DevTools.

## Compatibility Rule

The PHP scope fallback remains supported until all of the following are true:

1. `docs/runtime-inventory.md` reports zero executable `.php` templates under
   `boot-core/template` and `Repository/**/Views`.
2. Affected surfaces have route/view regression coverage.
3. `quality:check`, `route:bootstrap-regression`, `docs:inventory --json` and
   `git diff --check` pass.
4. User explicitly approves removal of the fallback behavior.

## Immediate Next Batch Candidate

Start with Wave T1 in a future runtime batch because it is small, read-only and
validates the migration pattern before touching auth, RBAC, setup or shell
contracts.
