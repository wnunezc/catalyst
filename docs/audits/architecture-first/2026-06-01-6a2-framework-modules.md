# Phase 6A.2 - Framework Modules Audit

Date: 2026-06-01

Status: initial Framework module audit completed / remediation pending.

## Executive Summary

The audit reviewed the Framework module contract surface by surface without
modifying runtime. Twelve modules were reviewed directly. DevTools was
registered as deferred by user decision: its known UML, manifest and overlay
debt remains ordered, but its internal surface was not audited or changed.

The most important result extends the critical route-cache defect from `6A.1`.
Module `routes.php` files register view namespaces as side effects. On a
route-cache hit those files are skipped, so cached production routes can reach
controllers but fail while resolving module templates.

The remaining findings are normalization and refactor work: hybrid module
declarations, missing throttle declarations on selected mutations, incomplete
centralized Request validation and controllers with excessive responsibilities.

## Scope Reviewed

Directly reviewed Framework modules:

- ApiPlatform
- Audit
- Auth
- Automation
- Catalogs
- DemoUi
- Documents
- Media
- Notification
- Operations
- Roles
- Settings

Deferred surface:

- DevTools: keep UML, manifest and CLI overlay debt ordered for a later batch;
  do not enter or modify the surface during the current audit sequence.

## Findings

### Critical - ARQ-12: Cached routes lose module view namespaces

**Files and lines**

- `app/Kernel.php:304`
- `app/Framework/View/View.php:61`
- `app/Framework/View/View.php:74`
- `Repository/Framework/Auth/routes.php:45`
- `Repository/Framework/Automation/routes.php:15`
- `Repository/Framework/Documents/routes.php:13`
- `Repository/Framework/Operations/routes.php:20`

**Evidence**

Module route files call `View::getInstance()->addPath(...)`. On a route-cache
hit, `Kernel::loadRoutes()` returns before requiring those files. `View`
initializes only the base framework template path. Unlike language paths,
module view namespaces are not reconstructed by the module registry.

**Impact**

A cached production route can dispatch correctly and then fail with a missing
template when it renders a module view.

**Recommendation**

Correct inside roadmap task `6B.0`. Register module view namespaces from module
metadata during bootstrap, independently of cold route loading.

### High - ARQ-13: Framework module declarations remain hybrid

**Files and lines**

- `app/Framework/Module/ModuleRegistry.php:18`
- `app/Framework/Module/ModuleRegistry.php:200`
- `Repository/Framework/Auth/routes.php:1`
- `Repository/Framework/Notification/routes.php:1`

**Evidence**

`framework.auth` and `framework.notification` have routes and language
directories but no `module.php`. Their metadata remains embedded in
`ModuleRegistry::DECLARATIONS`. Other active Framework modules use local
manifests. DevTools follows the same legacy pattern but is deferred.

**Recommendation**

Correct Auth and Notification in `6B.1`. Leave DevTools as an explicitly
ordered later batch.

### High - ARQ-14: Mutation throttling is inconsistent

**Files and lines**

- `Repository/Framework/Operations/routes.php:33`
- `Repository/Framework/Operations/routes.php:34`
- `Repository/Framework/Operations/routes.php:35`
- `Repository/Framework/Operations/routes.php:46`
- `Repository/Framework/Operations/routes.php:49`
- `Repository/Framework/Notification/routes.php:38`
- `Repository/Framework/Notification/routes.php:41`
- `Repository/Framework/Notification/routes.php:44`

**Evidence**

Most administrative mutation routes declare `admin_mutation`, and API
mutations declare `api_mutation`. Operations omits throttle declarations for
feature flags, plugin toggles and deployment execution. Notification omits an
explicit policy for notification state mutations and presence heartbeat.

**Recommendation**

Normalize in a focused remediation batch. Assign an administrative policy to
Operations mutations. Define a purpose-specific Notification policy so a
heartbeat is not forced into an unsuitable generic limit.

### Medium - ARQ-15: Centralized Request validation is incomplete

**Files and lines**

- `Repository/Framework/Roles/Controllers/UserManagementController.php:189`
- `Repository/Framework/Roles/Controllers/UserManagementController.php:266`
- `Repository/Framework/Roles/Controllers/RolesController.php:293`
- `Repository/Framework/Automation/Controllers/AutomationRuleController.php:320`
- `Repository/Framework/Documents/Controllers/DocumentTemplateController.php:278`
- `Repository/Framework/Operations/Controllers/FeatureFlagsController.php:197`
- `Repository/Framework/Operations/Controllers/PluginsController.php:157`

**Evidence**

Several mutations still receive the base `Request` and normalize or validate
payloads inside controllers. This is inconsistent with modules that already
use dedicated Request classes.

**Recommendation**

Move payload validation to dedicated Requests while refactoring the owning
module. Keep controller methods responsible for orchestration only.

### Medium - ARQ-16: Controllers still combine too many responsibilities

**Files**

- `Repository/Framework/Automation/Controllers/AutomationRuleController.php`
  - 731 lines
- `Repository/Framework/Documents/Controllers/DocumentTemplateController.php`
  - 624 lines
- `Repository/Framework/Roles/Controllers/UserManagementController.php`
  - 451 lines
- `Repository/Framework/Media/Controllers/MetadataFieldController.php`
  - 435 lines
- `Repository/Framework/Media/Controllers/MediaLibraryController.php`
  - 421 lines

**Evidence**

Automation and Documents combine web CRUD, API responses, forms, grids,
workflow, versioning and transient state. Roles and Media also combine
presentation construction with mutation flow.

**Recommendation**

Keep planned `6C.1` and `6C.2` for Automation and Documents. Add later focused
batches for Roles and Media after the critical bootstrap fixes.

### Medium - ARQ-17: Manifest localization depends on corrective decorators

**Files and lines**

- `app/Framework/Module/ModuleLocalizationDecorator.php:14`
- `app/Framework/Module/ModuleLocalizationDecorator.php:27`
- `app/Framework/Module/ModuleLocalizationDecorator.php:76`
- `app/Framework/Module/ModuleLocalizationDecorator.php:109`
- `Repository/Framework/Operations/module.php:6`
- `Repository/Framework/Settings/module.php:9`

**Evidence**

Settings, Roles and Operations retain raw labels or translation keys in their
manifests and are corrected afterward by module-specific positional mutation.
The decorator assumes exact navigation indexes and shape.

**Recommendation**

Normalize manifests incrementally. Prefer localized manifest values or a
generic recursive translation contract over positional module-specific
patching.

### Low - ARQ-18: DemoUi is an isolated frozen reference, not a production module

**Files**

- `Repository/Framework/DemoUi/Controllers/DemoUiController.php`
- `Repository/Framework/DemoUi/generated/theme-previews/`

**Evidence**

DemoUi contains a 1234-line controller and 87 generated HTML preview files.
Its manifest explicitly describes a frozen authenticated baseline.

**Recommendation**

Keep isolated and authenticated. Do not evolve it as a normal business module.
Review retention and documentation separately before any destructive cleanup.

## Positive Evidence

- `inspect:lint`: PASS, 18 modules and 212 route guards checked.
- `route:lint`: PASS.
- Framework route inventory: 13 discovered modules including deferred
  DevTools.
- Directly reviewed modules declare route middleware consistently for their
  principal surfaces.
- Settings already separates Requests, writers, probes and setup services.
- Automation, Catalogs and Documents already have mutation-service footholds
  that can support later controller decomposition.
- Existing Documents preview flow retains the phase 5 sanitizer boundary.

## Commands Executed

```powershell
php public/cli.php inspect:modules --json
php public/cli.php inspect:lint
php public/cli.php route:lint
php public/cli.php route:list --json
Get-ChildItem Repository\Framework ...
Get-Content ...
rg -n ...
```

## Remediation Order

1. Complete all approved `6A` audit stages without runtime changes.
2. Execute `6B.0` before broader runtime normalization.
3. Execute `6B.1` for Auth and Notification manifests.
4. Normalize throttles and Request classes in focused batches.
5. Refactor Automation and Documents under `6C.1` and `6C.2`.
6. Keep DevTools ordered as a deferred surface until explicitly resumed.

## Next Step

Proceed to `6A.3`: audit App surfaces and shared support without modifying
runtime or deleting files.
