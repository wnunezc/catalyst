# Task 6C.3 - Residual Architecture Batch Plan

Date: 2026-06-02

Status: residual batches prioritized / committed in `be29b02`.

## Objective

Translate the remaining Phase 6A evidence into bounded implementation batches
after the verified completion of `6B`, `6C.1` and `6C.2`. This task changes
planning documents only. It does not modify runtime behavior.

## Closed Findings

The following findings no longer require residual runtime work:

| Findings | Resolution |
| --- | --- |
| `ARQ-01`, `ARQ-08`, `ARQ-09`, `ARQ-10`, `ARQ-12`, `ARQ-19` | Closed by Phase `6B`: cache-safe middleware and view namespace registrars, explicit route order and core redirects. |
| `ARQ-02`, `ARQ-13`, `ARQ-14` | Closed by Phase `6B`: manifests, global ownership and throttles normalized. |
| `ARQ-03`, `ARQ-04`, `ARQ-20`, `ARQ-21`, `ARQ-23`, `ARQ-26` | Closed by Phase `6B`: hot docs reconciled, shared App support moved, CLI overlay moved and approved residues removed. |
| Automation portion of `ARQ-05`, `ARQ-15`, `ARQ-16` | Closed by Task `6C.1` in `9906e83`. |
| Documents portion of `ARQ-05`, `ARQ-15`, `ARQ-16` | Closed by Task `6C.2` in `e4466aa`. |

## Current Measurements

| Surface | Evidence | Priority |
| --- | --- | --- |
| Roles | `UserManagementController.php`: 451 lines; `RolesController.php`: 405 lines; enrollment, bulk delete and permission sync still normalize payloads in controllers. | High |
| Media | `MetadataFieldController.php`: 435 lines; `MediaLibraryController.php`: 421 lines; grid/form assembly remains in controllers and media bulk delete normalizes IDs inline. | Medium |
| Operations | `FeatureFlagsController.php`: 264 lines; `PluginsController.php`: 173 lines; selected mutations still use base `Request`. | Medium |
| Manifest localization | `ModuleLocalizationDecorator` still mutates Settings, Roles, Operations and deferred DevTools by positional indexes. App manifests still retain literal display strings. | Medium |
| Executable templates | 58 `Repository/**/Views/scope/**/*.php` files plus 31 `boot-core/template/**/*.php` files: 89 total. | Medium, incremental |
| Canonical JS contracts | 16 non-DevTools `Repository/**/front/script.js` files; 10 still lack a useful header contract. | Medium, documentation |
| Settings duplicate scope companions | Two tiny CSRF-only companions remain structurally identical. | Low |
| Short class name `Validator` | Two namespaced validators remain intentionally distinct. | Low |

## Recommended Runtime Order

### Task 6C.4 - Roles MVC And Mutation Requests

Risk: high. Roles changes affect users, authorization assignments and
permission synchronization.

Scope:

- Add `roles:mvc-regression` before production edits and observe RED.
- Extract grid/form factories from `UserManagementController`,
  `RolesController` and `PermissionsController`.
- Move enrollment validation into a dedicated `FormRequest`.
- Move role bulk-delete IDs and permission-sync IDs into dedicated Requests.
- Move user-role assignment/removal route parameters into a focused mutation
  service only if controller orchestration remains duplicated after Requests.
- Preserve routes, middleware, claims and HTML/JSON response contracts.
- Do not redesign RBAC persistence.

Verification:

```powershell
php public/cli.php roles:mvc-regression
php public/cli.php quality:check
php public/cli.php route:list --json
php public/cli.php inspect:lint
php public/cli.php security:check
composer validate --strict
composer audit
git diff --check
```

### Task 6C.5 - Media MVC And Bulk Mutation Requests

Risk: medium. Media affects uploaded files and dynamic metadata definitions.

Scope:

- Add `media:mvc-regression` before production edits and observe RED.
- Extract grid/form factories from `MediaLibraryController` and
  `MetadataFieldController`.
- Move bulk-delete ID normalization into a dedicated Request.
- Keep upload validation, storage behavior, claims and metadata persistence
  unchanged.
- Do not add storage drivers or modify retention behavior.

Verification:

```powershell
php public/cli.php media:mvc-regression
php public/cli.php quality:check
php public/cli.php route:list --json
php public/cli.php inspect:lint
php public/cli.php security:check
composer validate --strict
composer audit
git diff --check
```

### Task 6C.6 - Operations Mutation Requests

Risk: medium. Operations controls platform configuration and plugin state.

Scope:

- Add `operations:request-regression` before production edits and observe RED.
- Move feature-flag default changes, feature-flag override deletion, plugin
  toggles, locale settings/create/sync, appearance updates and module-designer
  preview/generate payloads into dedicated Requests where validation or
  normalization currently lives in controllers.
- Preserve existing routes, middleware and `admin_mutation` throttles.
- Keep deployment execution on its existing `DeploymentRunRequest`.
- Avoid unrelated visual or navigation refactors.

### Task 6C.7 - Manifest Localization Contract

Risk: medium. The current positional decorator is shape-sensitive.

Scope:

- Add a module-localization regression over active manifests.
- Replace positional Settings, Roles and Operations corrections with a generic
  recursive translation-key contract or localized manifest values.
- Normalize literal user-facing strings in active App manifests.
- Keep DevTools as an explicitly deferred surface; do not enter its visual or
  layout internals in this batch.
- Preserve module keys, routes, permissions and navigation ordering.

## Documentation Order

### Task 6D.1 - Documentation Contract And Inventory

- Define hot, warm and cold documentation classes in a repo-local index.
- Add a generated, machine-verifiable symbol/template/script inventory so
  `STRUCTURE.md` remains curated instead of exhaustive.
- Add useful inline contract documentation first to bootstrap, routing, cache,
  middleware, public repositories and security-sensitive services.

### Task 6D.2 - Canonical JavaScript Contracts

- Document initialization trigger, DOM selectors, consumed/emitted events,
  payload shape and CSP assumptions for the 10 non-DevTools canonical scripts
  that still lack a useful header contract.
- Keep comments concise; do not narrate trivial statements.

### Task 6D.3 - Executable Template Migration Plan

- Inventory the 89 remaining executable PHP templates by owner and risk.
- Migrate bounded surfaces incrementally to declarative `.phtml`.
- Preserve the PHP compatibility fallback until the executable inventory
  reaches zero and affected views have regression coverage.

### Task 6D.4 - Inline Asset Extraction Plan

- Classify nonce-backed inline scripts/styles into transport payloads,
  framework boot logic, error pages and debug-only output.
- Extract behavior to versioned assets incrementally while preserving CSP.
- Retain intentional JSON transport blocks when documented.

## Explicitly Deferred Or Accepted Debt

- DemoUi remains an authenticated frozen reference. Do not refactor its large
  controller incidentally.
- DevTools remains a separately approved batch. Do not enter its visual or
  layout surface during the residual normalization sequence.
- Account Request wrappers remain a later Account-focused alignment task.
- Settings CSRF-only duplicate companions remain acceptable until repetition
  justifies a shared helper.
- Namespaced `Argument\Validator` and `Helpers\Validation\Validator` remain
  distinct; no rename is justified without a functional need.

## Exit Criteria For Phase 6

1. User chooses which residual runtime batches are required before Phase 6
   closure and which move to Phase 7.
2. Approved runtime batches are implemented and committed separately.
3. Documentation contract tasks approved for Phase 6 are completed.
4. `6E.1` executes the full verification gate.
5. Phase 6 is marked complete only after explicit user confirmation.

## Runtime Batch Execution Update

Tasks `6C.4` through `6C.7` were implemented in one bounded pass after explicit
user approval. The exact scope and verification evidence are recorded in:

`docs/audits/architecture-first/2026-06-02-6c4-6c7-runtime-normalization.md`.
