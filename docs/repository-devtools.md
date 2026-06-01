# `Catalyst\Repository\DevTools`

## Overview

The DevTools module is the framework's live test harness and architecture viewer.
It currently exposes these user-facing surfaces:

- `GET /test-features` - consolidated interactive harness
- `GET /test-features/ui-showcase` - compact admin shell + UI component showcase
- `GET /uml` - architecture reference viewer

Legacy compatibility aliases remain under `/test-features/module-designer*`, but the canonical Module Designer now lives in `Repository/Framework/Operations/` at `GET /operations/module-designer`.

`GET /test-layout` remains a protected smoke route, but it is not a primary sidebar entry. Opening it directly keeps DevTools context active through the `Test Features` domain.

The current module is controller-per-domain plus one consolidated harness view. It is not the older partial orchestrator described by historical docs.

## Access control

- `GET /test-layout`, `GET /uml`, and every `/test-features*` route are protected by `DevToolsGuardMiddleware`.
- `DevToolsGuardMiddleware` enforces:
  - outside development -> `403`
  - development without login -> framework-standard auth behavior (`401` JSON or redirect to `/login`)
  - development with authenticated user lacking access -> `403`
  - development with `admin` role or explicit permission `access-devtools` -> allowed
  - generated or custom DevTools surfaces may supply extra module permissions through the middleware constructor without opening a parallel access system

## Controllers

### RouteTestController

**File**: `Repository/Framework/DevTools/Controllers/RouteTestController.php`

Purpose: compatibility redirect helper for legacy root aliases only.

Public methods:

- `index(): Response`
- `redirectToRoot(): RedirectResponse`

Live behavior:

- `GET /` is now owned by `Repository/App/Surface/Home` and resolves through `ApplicationEntryService`
- `RouteTestController` only keeps `/index` and `/index.php` compatibility redirects pointed at `/`
- canonical app entry routes live under `Repository/App/Surface/*`
- las peticiones con casing conocido como `/Home`, `/Landing`, `/Dashboard` y `/Store` ya se normalizan por `CanonicalPathRedirectMiddleware` + `CanonicalPathRedirector`; no deben tratarse como rutas propias de DevTools ni como entradas runtime del módulo
- `/Setup` ya no permanece como ruta de compatibilidad viva en el runtime actual

### UmlController

**File**: `Repository/Framework/DevTools/Controllers/UmlController.php`

Purpose: render the Mermaid-based architecture reference at `GET /uml` inside the admin/showcase chrome.

Public methods:

- `index(Request $request): Response`

Notes:

- reads `ConfigManager::all()`, `isConfigured()`, and `getEnvironment()`
- renders through layout `admin`
- page styling and behavior now publish through `Repository/Framework/DevTools/front/*` to `/assets/*/work/devtools/`
- Mermaid is orchestrated by the DevTools work script instead of per-view manual asset injection

### TestFeaturesController

**File**: `Repository/Framework/DevTools/Controllers/TestFeaturesController.php`

Purpose: render the consolidated harness page at `GET /test-features`.

Public methods:

- `index(): Response`

Notes:

- page styling and behavior now publish through `Repository/Framework/DevTools/front/*` to `/assets/*/work/devtools/`
- passes `authCheck` and `authUser` to the view

### ModuleDesignerController (legacy alias bridge)

**File**: `Repository/Framework/Operations/Controllers/ModuleDesignerController.php`

Purpose: the canonical technical UI for `RM-18` now lives in Operations; DevTools only preserves legacy aliases and guard-compatible POST endpoints.

Public methods:

- `legacyIndex(Request $request): Response`
- `legacyPreviewEntry(Request $request): Response`
- `legacyGenerateEntry(Request $request): Response`
- `preview(Request $request): Response`
- `generate(Request $request): Response`

Notes:

- reuses `ModuleScaffoldService`, `ModuleInspector` and `ModuleLinter`
- preview and generation share the same blueprint engine used by `make:module`
- generation writes `module.php`, guard-aware routes, base navigation metadata and publishes initial `work/{slug}` assets immediately
- the canonical administrative surface is `GET /operations/module-designer`
- `GET /test-features/module-designer*` now redirects to the Operations surface instead of rendering inside DevTools

### InfraTestController

**File**: `Repository/Framework/DevTools/Controllers/InfraTestController.php`

Purpose: infrastructure smoke tests and envelope demos.

Public methods:

- `index(): Response` - redirects to `/test-features`
- `uiShowcase(): Response`
- `testEscapeHelper(): JsonResponse`
- `testLayout(): Response`
- `testJson(): JsonResponse`
- `testJsonSuccess(): JsonResponse`
- `testJsonError(): JsonResponse`
- `testValidationError(): JsonResponse`
- `testApiResponse(): JsonResponse`
- `testLoggerEmail(): JsonResponse`
- `testCorsHeaders(): JsonResponse`
- `testRouteCache(): JsonResponse`

### FlashTestController

**File**: `Repository/Framework/DevTools/Controllers/FlashTestController.php`

Public methods:

- `triggerFlash(string $type): Response`
- `triggerFlashPersistent(string $type): Response`
- `clearFlash(): Response`

### ToasterTestController

**File**: `Repository/Framework/DevTools/Controllers/ToasterTestController.php`

Public methods:

- `apiToasterSuccess(): JsonResponse`
- `apiToasterError(): JsonResponse`
- `apiToasterWarning(): JsonResponse`
- `apiToasterInfo(): JsonResponse`
- `apiMultipleToasters(): JsonResponse`
- `apiModalTrigger(): JsonResponse`
- `apiJsEnhancementPartialRefresh(): JsonResponse`

### ModalTestController

**File**: `Repository/Framework/DevTools/Controllers/ModalTestController.php`

Public methods:

- `modalSampleContent(): Response`
- `modalFormContent(): Response`
- `modalFormSubmit(): JsonResponse`

### FormEventTestController

**File**: `Repository/Framework/DevTools/Controllers/FormEventTestController.php`

Traits:

- `HandlesFormEventsTrait`

Public methods:

- `formDemoStore(): Response`

Protected event handlers:

- `onSave(): JsonResponse`
- `onValidate(): JsonResponse`
- `onRefresh(): JsonResponse`
- `onRedirect(): JsonResponse`

### DatabaseTestController

**File**: `Repository/Framework/DevTools/Controllers/DatabaseTestController.php`

Public methods:

- `testDbConnection(): JsonResponse`

### DatabaseResetController

**File**: `Repository/Framework/DevTools/Controllers/DatabaseResetController.php`

Purpose: dev-only destructive reset for the framework demo schema.

Public methods:

- `reset(): Response`

Notes:

- bound to `POST /test-features/db-reset`
- only available when `IS_DEVELOPMENT === true`
- delegates destructive SQL orchestration to `Repository/Framework/DevTools/Services/DatabaseResetService.php` so the controller remains a thin HTTP boundary
- drops known tables, then replays `boot-core/database/create-catalyst-db.sql`
- when present, replays `boot-core/database/create-catalyst-db.development.sql` immediately after the canonical schema to restore the local development auth/RBAC/social-account snapshot
- the overlay can now be regenerated from the live development DB with `php public/cli.php dev:export-overlay`; when host CLI cannot resolve the WSDD DB target, the command falls back to the running WSDD web container

### I18nTestController

**File**: `Repository/Framework/DevTools/Controllers/I18nTestController.php`

Public methods:

- `testI18n(): JsonResponse`
- `setLocale(): JsonResponse`

### ValidatorTestController

**File**: `Repository/Framework/DevTools/Controllers/ValidatorTestController.php`

Public methods:

- `validatorTest(): JsonResponse`
- `validatorUniqueTest(): JsonResponse`

### UploadTestController

**File**: `Repository/Framework/DevTools/Controllers/UploadTestController.php`

Public methods:

- `upload(): JsonResponse`

### MailTestController

**File**: `Repository/Framework/DevTools/Controllers/MailTestController.php`

Public methods:

- `mailTest(): JsonResponse`

### RbacTestController

**File**: `Repository/Framework/DevTools/Controllers/RbacTestController.php`

Public methods:

- `rbacStatus(): JsonResponse`
- `makeAdmin(): JsonResponse`

### OrmTestController

**File**: `Repository/Framework/DevTools/Controllers/OrmTestController.php`

Public methods:

- `ormStatus(): JsonResponse`
- `ormCreate(): JsonResponse`
- `ormUpdate(): JsonResponse`
- `ormDeleteLatest(): JsonResponse`
- `ormFindOrFail(): JsonResponse`
- `ormUserDemo(): JsonResponse`

## Active routes

### Entry and architecture

- `GET /test-layout` -> `InfraTestController::testLayout()`
- `GET /uml` -> `UmlController::index()`
- `GET /test-features` -> `TestFeaturesController::index()`
- `GET /test-features/infra` -> `InfraTestController::index()`
- `GET /test-features/module-designer` -> `Operations\ModuleDesignerController::legacyIndex()`
- `POST /test-features/module-designer/preview` -> `Operations\ModuleDesignerController::preview()`
- `POST /test-features/module-designer/generate` -> `Operations\ModuleDesignerController::generate()`
- `GET /test-features/ui-showcase` -> `InfraTestController::uiShowcase()`

### Infrastructure / response envelopes

- `GET /test-features/e-helper`
- `GET /test-features/layout-test`
- `GET /test-features/ui-showcase`
- `GET /test-features/json`
- `GET /test-features/json-success`
- `GET /test-features/json-error`
- `GET /test-features/validation-error`
- `GET /test-features/api-response`
- `GET /test-features/logger-email`
- `GET /test-features/route-cache`
- `GET /test-features/cors-headers`

### Flash, toaster, and modal demos

- `GET /test-features/flash/clear`
- `GET /test-features/flash/{type}`
- `GET /test-features/flash/{type}/persistent`
- `GET /test-features/api/toaster-success`
- `GET /test-features/api/toaster-error`
- `GET /test-features/api/toaster-warning`
- `GET /test-features/api/toaster-info`
- `GET /test-features/api/multiple-toasters`
- `GET /test-features/api/modal-trigger`
- `GET /test-features/api/js-enhancements/partial-refresh`
- `GET /test-features/modal/sample-content`
- `GET /test-features/modal/form-content`
- `POST /test-features/modal/form-submit`

### Form, DB, i18n, validator, upload

- `POST /test-features/form-demo`
- `GET /test-features/db-connection`
- `POST /test-features/db-reset`
- `GET /test-features/i18n`
- `POST /test-features/i18n/set-locale`
- `POST /test-features/api/validator-test`
- `POST /test-features/api/validator-unique`
- `POST /test-features/upload`

### Mail, RBAC, ORM

- `POST /test-features/mail-test`
- `GET /test-features/rbac-status`
- `POST /test-features/make-admin`
- `GET /test-features/orm/status`
- `GET /test-features/orm/find-or-fail`
- `GET /test-features/orm/user-demo`
- `POST /test-features/orm/create`
- `POST /test-features/orm/update`
- `POST /test-features/orm/delete-latest`

## Views

Top-level views:

- `Views/pages/route-test.phtml`
- `Views/pages/layout-test.phtml`
- `Views/pages/module-designer.phtml`
- `Views/pages/test-features.phtml`
- `Views/pages/ui-showcase.phtml`
- `Views/pages/uml.phtml`

Harness partials used by `Views/pages/test-features.phtml`:

- `Views/partials/_tf-header.phtml`
- `Views/partials/_tf-flash.phtml`
- `Views/partials/_tf-toasters.phtml`
- `Views/partials/_tf-modals.phtml`
- `Views/partials/_tf-js-enhancements.phtml`
- `Views/partials/_tf-form-events.phtml`
- `Views/partials/_tf-json-inspection.phtml`
- `Views/partials/_tf-database.phtml`
- `Views/partials/_tf-infrastructure.phtml`
- `Views/partials/_tf-system-info.phtml`
- `Views/partials/_tf-i18n.phtml`
- `Views/partials/_tf-validator.phtml`
- `Views/partials/_tf-file-upload.phtml`
- `Views/partials/_tf-mail.phtml`
- `Views/partials/_tf-auth.phtml`
- `Views/partials/_tf-rbac.phtml`
- `Views/partials/_tf-orm.phtml`
- `Views/partials/_tf-endpoints.phtml`

## Published assets actually used

- Source:
  - `Repository/Framework/DevTools/front/style.css`
  - `Repository/Framework/DevTools/front/script.js`
- Runtime publish targets:
  - `public/assets/css/work/devtools/style.css`
  - `public/assets/js/work/devtools/script.js`

This module now follows the project-wide work-asset rule through `Controller::view()` + `FrontResourceTrait`.

## Models

- `Repository/Framework/DevTools/Models/DemoEmail.php` -> table `validator_demo_emails`

## Related docs

- `D:/OpsZone/DevWorkspace/Projects/Web/catalyst/Repository/Framework/DevTools/Views/pages/uml.phtml`
- `D:/OpsZone/DevWorkspace/Projects/Web/catalyst/docs/framework-database.md`
