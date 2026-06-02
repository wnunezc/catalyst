# Catalyst Framework - Technical Structure Index

> **Purpose**: Quick reference guide to all framework components. Complete technical details are documented in the `docs/` directory.
> **Audience**: Humans and AI agents
> **Critical**: Check this file BEFORE creating new classes to avoid duplication.
> **Last Updated**: 2026-05-21 (security remediation closeout: trusted inline JSON, trusted HTML contract, signed cache payloads, API-token ownership hardening, plus appearance/assets and mature `pages/partials/components/scope` view contract)

---

## Quick Navigation

**Architecture & Setup**
- [docs/architecture.md](docs/architecture.md) — MVC pattern, dual-space architecture, third-party dependencies
- [docs/composer.md](docs/composer.md) — Composer config, PSR-4 autoloading, dependencies
- [docs/entry-points.md](docs/entry-points.md) — public/index.php, public/cli.php bootstrap flows

**Core Framework**
- [docs/kernel.md](docs/kernel.md) — Catalyst\Kernel class
- [docs/framework-controllers.md](docs/framework-controllers.md) — Catalyst\Framework\Controllers\Controller (abstract base)
- [docs/framework-session.md](docs/framework-session.md) — Catalyst\Framework\Session (FlashBag, FlashMessage, ToastQueue)
- [docs/framework-view.md](docs/framework-view.md) — Catalyst\Framework\View (View rendering)
- [docs/helpers-i18n.md](docs/helpers-i18n.md) — runtime i18n contract (Translator + LocalizationManager)
- [docs/framework-appearance.md](docs/framework-appearance.md) — institutional theming, branding and PDF watermark runtime

**Framework Utilities**
- [docs/framework-traits.md](docs/framework-traits.md) — SingletonTrait, OutputCleanerTrait, HandlesFormEventsTrait, FrontResourceTrait, LoadsFeatureConfigTrait
- [docs/framework-enums.md](docs/framework-enums.md) — Catalyst\Framework\Enums (AppEnvironment)
- [docs/framework-notification.md](docs/framework-notification.md) — Toasters, Modals, Alerts system
- [docs/framework-event.md](docs/framework-event.md) — Event bus, listeners and envelopes
- [docs/framework-queue.md](docs/framework-queue.md) — Persistent queue, retries and failed jobs
- [docs/framework-schedule.md](docs/framework-schedule.md) — Schedule registry, locking and runner CLI
- [docs/framework-argument.md](docs/framework-argument.md) — CLI argument parsing
- [docs/framework-database.md](docs/framework-database.md) — DatabaseManager, Connection, QueryBuilder, Transaction
- [docs/framework-geo.md](docs/framework-geo.md) — Coordinate, BoundingBox, GeoManager
- [docs/framework-concurrency.md](docs/framework-concurrency.md) — optimistic locking, record claiming y probes de concurrencia
- [docs/framework-mail.md](docs/framework-mail.md) — MailManager, MailMessage, MailTemplate
- [docs/framework-auth.md](docs/framework-auth.md) — AuthManager, UserProvider, RememberMe, OAuth
- [docs/framework-websocket.md](docs/framework-websocket.md) — WebSocketToken, WebSocketServer, WebSocketPublisher
- [docs/runtime-module-catalog.md](docs/runtime-module-catalog.md) — Auto-generated runtime catalog from registries, harness and lint

**Modules (Repository)**
- [docs/repository-devtools.md](docs/repository-devtools.md) — DevTools controllers and harness aliases (/test-features, /uml) — TestFeaturesController, FlashTestController, ToasterTestController, ModalTestController, FormEventTestController, InfraTestController, DatabaseTestController, I18nTestController, ValidatorTestController, MailTestController, RbacTestController, OrmTestController, UmlController, RouteTestController
- [docs/repository-auth.md](docs/repository-auth.md) — Auth module (login, register, OAuth)
- [docs/repository-notification.md](docs/repository-notification.md) — Notification module (controllers, routes)
- **Settings module** (`Repository/Framework/Settings/`) — Config Panel: ConfigController (GET + effective setup/runtime state), HealthController (`/configuration/application-health`, `/configuration/application-health/live`, `/configuration/application-health/ready`), dedicated setup save controllers under `/configuration/environment-setup/*` backed by `Requests/` + `Support/*Writer`, FtpConfigController for FTP/FTPS/SFTP credentials and upload probes, CorsConfigSaveController, DkimController, SetupCompletionController, DkimGenerator
- **Operations module** (`Repository/Framework/Operations/`) — Canonical platform control plane for RM-36/RM-39: `OperationsController` (`/operations`) with grouped administration children for feature flags, plugins, deployments and tenancy, plus `AppearanceController` (`/operations/appearance`, family-governed branding defaults + compact shell marks + versioned runtime assets + PDF logo watermark), `LocalizationController` (`/operations/localization`) and `ModuleDesignerController` (`/operations/module-designer`), shared `work/operations` assets, `FormBuilder` + `DataGrid` adoption and integration with audit/navigation/permissions
- **Media module** (`Repository/Framework/Media/`) — Reusable media library + dynamic metadata admin surfaces: MediaLibraryController (`/media-library`, upload/edit/delete/bulk delete), MetadataFieldController (`/media-fields`, declarative field definitions), shared `work/media` assets and adoption of `FormBuilder` + `DataGrid`
- **Documents module** (`Repository/Framework/Documents/`) — Reusable document templates admin surface: `DocumentTemplateController` (`/document-templates`, preview/export, workflow transitions, version restore), shared `work/documents` assets and adoption of `FormBuilder` + `DataGrid`
- **Automation module** (`Repository/Framework/Automation/`) — Internal rules admin surface: thin web `AutomationRuleController`, tokenized `AutomationRuleApiController`, module-specific grid/form/show factories, centralized Requests, manual-run state and idempotent execution service; shared `work/automation` assets and adoption of `FormBuilder` + `DataGrid`
- **API Platform module** (`Repository/Framework/ApiPlatform/`) — Versioned API administration: `ApiPlatformController` (`/api-platform`, bearer token management + catalog), `WorkflowApiController`, `VersionApiController`

**Change History**
- [docs/update-log.md](docs/update-log.md) — Full change history table (2025-10-19 to 2026-04-26)

---

## Namespace Map

| Namespace | Directory                                        | File |
|-----------|--------------------------------------------------|------|
| `Catalyst` | `app/`                                           | [kernel.md](docs/kernel.md) |
| `Catalyst\Framework\Controllers` | `app/Framework/Controllers/`                     | [framework-controllers.md](docs/framework-controllers.md) |
| `Catalyst\Framework\Container` | `app/Framework/Container/`                       | Lightweight service container used by route dispatch for progressive DI |
| `Catalyst\Framework\Session` | `app/Framework/Session/`                         | [framework-session.md](docs/framework-session.md) |
| `Catalyst\Framework\View` | `app/Framework/View/`                            | [framework-view.md](docs/framework-view.md) — View, TrustedHtml, InlineJson, HtmlAllowlistSanitizer |
| `Catalyst\Framework\Security` | `app/Framework/Security/`                        | SignedSerializedPayload — signed serialized envelopes for local cache / route-cache rehydration |
| `Catalyst\Framework\Traits` | `app/Framework/Traits/`                          | [framework-traits.md](docs/framework-traits.md) |
| `Catalyst\Framework\Enums` | `app/Framework/Enums/`                           | [framework-enums.md](docs/framework-enums.md) |
| `Catalyst\Framework\Notification` | `app/Framework/Notification/`                    | [framework-notification.md](docs/framework-notification.md) |
| `Catalyst\Framework\Event` | `app/Framework/Event/`                           | [framework-event.md](docs/framework-event.md) |
| `Catalyst\Framework\Queue` | `app/Framework/Queue/`                           | [framework-queue.md](docs/framework-queue.md) |
| `Catalyst\Framework\Schedule` | `app/Framework/Schedule/`                        | [framework-schedule.md](docs/framework-schedule.md) |
| `Catalyst\Framework\Attachment` | `app/Framework/Attachment/`                      | AttachmentManager, AttachmentRepository — canonical PA-06 attachment/evidence contract over `resource_key` + `record_id`, media and document artifacts |
| `Catalyst\Framework\Retention` | `app/Framework/Retention/`                       | RetentionManager, RunRetentionPoliciesJob — canonical PA-05 retention / archive / purge policy runtime over media, artifacts, attachments and audit |
| `Catalyst\Framework\Reporting` | `app/Framework/Reporting/`                       | ReportingManager, RunReportJob — canonical PA-10 queued reporting/export pipeline with persisted CSV/XLS outputs and attachment delivery |
| `Catalyst\Framework\Argument` | `app/Framework/Argument/`                        | [framework-argument.md](docs/framework-argument.md) |
| `Catalyst\Framework\Cli` | `app/Framework/Cli/`                             | CliKernel, CommandRegistry, CommandInterface, AbstractCommand, ScaffoldManager |
| `Catalyst\Framework\Cli\Commands` | `app/Framework/Cli/Commands/`                    | HelpCommand, VersionCommand, StatusCommand, QualityCheckCommand, AutomationMvcRegressionCommand, ConfigSecretsSyncCommand, ClaimsListCommand, ClaimsReleaseCommand, ConcurrencySmokeCommand, SensitivitySmokeCommand, TemporalSmokeCommand, IdempotencySmokeCommand, AttachmentsListCommand, AttachmentsSmokeCommand, RetentionRunCommand, RetentionSmokeCommand, ReportingRunCommand, ReportingSmokeCommand, RouteCacheCommand, RouteClearCommand, InspectModulesCommand, InspectModuleCommand, InspectLintCommand, InspectHarnessCommand, DocsSyncRuntimeCommand, FixturesAuthCommand, FeatureFlagsListCommand, FeatureFlagsSetCommand, PluginListCommand, PluginToggleCommand, DeployListCommand, DeployRunCommand, TenancyStatusCommand, ApiTokensSmokeCommand, SecurityRegressionCommand, MakeControllerCommand, MakeCrudCommand, MakeModelCommand, MakeMiddlewareCommand, MakeModuleCommand, MakePolicyCommand |
| `Catalyst\Framework\Admin\Form` | `app/Framework/Admin/Form/`                      | FormBuilder — declarative admin form schema builder mounted on old-input and validation-error bridge, with sections, repeaters and autosave |
| `Catalyst\Framework\Admin\Grid` | `app/Framework/Admin/Grid/`                      | DataGrid — declarative admin list engine with search, filters, sorting, pagination, row actions, bulk actions, CSV export and structured cell rendering (`stack`, `code`, `badge`, `badges`, `booleanBadge`) |
| `Catalyst\Framework\Admin\Crud` | `app/Framework/Admin/Crud/`                      | CrudScaffoldService — CRUD scaffold over ModuleScaffoldService + FormBuilder + DataGrid, now with migration generation and audit/soft-delete wiring |
| `Catalyst\Framework\Metadata` | `app/Framework/Metadata/`                        | MetadataResourceRegistry, MetadataFieldRepository, MetadataValueRepository, MetadataManager — reusable dynamic field definitions, typed persistence, validation rules and DataGrid/FormBuilder integration |
| `Catalyst\Framework\Media` | `app/Framework/Media/`                           | MediaManager, MediaRepository — reusable file library over StorageManager, audit-ready metadata sync and admin listing/filtering |
| `Catalyst\Framework\Storage` | `app/Framework/Storage/`                         | StorageManager, LocalStorageAdapter, FtpStorageAdapter — public `local`, private `runtime` and configured remote disks |
| `Catalyst\Framework\Health` | `app/Framework/Health/`                          | HealthReportBuilder — shared health/readiness report for CLI and HTTP surfaces |
| `Catalyst\Framework\Database` | `app/Framework/Database/`                        | [framework-database.md](docs/framework-database.md) — DatabaseManager, Connection, QueryBuilder, Transaction, Migration, MigrationRunner |
| `Catalyst\Framework\Concurrency` | `app/Framework/Concurrency/`                     | RecordClaimRepository, RecordClaimManager — canonical PA-01 record claims with expiry, release and audit integration |
| `Catalyst\Framework\Geo` | `app/Framework/Geo/`                             | Coordinate, BoundingBox, GeoManager — canonical PA-07 geo normalization, distance and radius helpers |
| `Catalyst\Framework\Presence` | `app/Framework/Presence/`                        | PresenceManager — canonical PA-08 claim-derived presence snapshots, heartbeat and WS publish bridge |
| `Catalyst\Framework\Idempotency` | `app/Framework/Idempotency/`                     | IdempotencyManager, IdempotencyRepository, conflict/in-progress exceptions — canonical PA-12 deduplication over reusable execution keys |
| `Catalyst\Framework\Database\Concerns` | `app/Framework/Database/Concerns/`               | Extracted `Model` responsibility slices: attributes, relationships, lifecycle hooks, persistence |
| `Catalyst\Framework\Mail` | `app/Framework/Mail/`                            | [framework-mail.md](docs/framework-mail.md) |
| `Catalyst\Framework\Auth` | `app/Framework/Auth/`                            | [framework-auth.md](docs/framework-auth.md) — AuthManager, UserProvider, RememberMe, OAuthManager, **MfaManager**; user mutations now flow through ORM/audit hooks |
| `Catalyst\Framework\Authorization` | `app/Framework/Authorization/`                   | Gate, Policy, PermissionRegistry, RoleRepository, AbilitySubject, ResourcePolicy — RBAC + resource abilities |
| `Catalyst\Framework\Audit` | `app/Framework/Audit/`                           | AuditLogManager, AuditLogRepository — runtime audit capture, repository queries and event/model integration |
| `Catalyst\Framework\FeatureFlag` | `app/Framework/FeatureFlag/`                     | FeatureFlagManager, FeatureFlagOverrideRepository — real runtime flag catalog, effective evaluation, persisted overrides, audit and module/runtime refresh |
| `Catalyst\Framework\Plugin` | `app/Framework/Plugin/`                          | PluginRegistry, PluginManager — plugin manifests, runtime enablement, validation and registry integration |
| `Catalyst\Framework\Deployment` | `app/Framework/Deployment/`                      | DeploymentManager, DeploymentRunRepository — formal deployment profiles, preflight pipeline, staging/release bookkeeping and operations reporting |
| `Catalyst\Framework\Tenancy` | `app/Framework/Tenancy/`                         | TenancyManager — canonical tenancy decision, resolver baseline and runtime status reporting |
| `Catalyst\Framework\Appearance` | `app/Framework/Appearance/`                      | PlatformAppearanceManager — institutional family catalog, shared branding payload, shell-safe logo variants, head bootstrap payload and PDF watermark settings |
| `Catalyst\Framework\Module` | `app/Framework/Module/`                          | ModuleRegistry, ModuleScaffoldService, ModuleInspector, ModuleLinter, ModuleHarnessInspector, ModuleRuntimeDocsGenerator — declarative module metadata plus scaffold/inspection/lint/harness/docs tooling consumed by runtime, CLI and DevTools |
| `Catalyst\Framework\Navigation` | `app/Framework/Navigation/`                      | NavigationRegistry — admin/public menus and breadcrumbs mounted on the module catalog |
| `Catalyst\Framework\Sensitivity` | `app/Framework/Sensitivity/`                     | DataClassificationRegistry, SensitiveDataPolicy — declarative sensitive-field classification reused by audit, API, forms, logs and exports |
| `Catalyst\Framework\Testing` | `app/Framework/Testing/`                         | AuthFixtureCatalog, AuthFixtureFactory, AuthFixtureManager — reversible auth/RBAC runtime fixtures, payload factories, overlay export support and QA helpers/probes |
| `Catalyst\Framework\Temporal` | `app/Framework/Temporal/`                        | EffectiveWindow — canonical validity-window normalization and state evaluation for reusable time-bound records |
| `Catalyst\Framework\Http` | `app/Framework/Http/`                            | Request, FormRequest, UploadedFile, FileValidator, Response, JsonResponse, RedirectResponse, HtmlResponse, ApiRequest |
| `Catalyst\Framework\Route` | `app/Framework/Route/`                           | Router, Route, RouteCollection, RouteCompiler, RouteDispatcher, RouteGroup, UrlGenerator |
| `Catalyst\Framework\Middleware` | `app/Framework/Middleware/`                      | CoreMiddleware, MiddlewareInterface, MiddlewareStack, AuthMiddleware, GuestMiddleware, CorsMiddleware, CsrfMiddleware, RoleMiddleware, LoginThrottleMiddleware, SetupMiddleware, SetupGuardMiddleware, SetupAccessTrait, SecurityHeadersMiddleware, BasicAuthMiddleware, DebugMiddleware, RequestThrottlingMiddleware, WebSocketBootMiddleware, RouteFeatureMiddleware, FeatureFlagInterface (interface), CallableMiddleware |
| `Catalyst\Framework\WebSocket` | `app/Framework/WebSocket/`                       | [framework-websocket.md](docs/framework-websocket.md) |
| `Catalyst\Entities` | `app/Entities/`                                  | Shared extendable entities now include `AuditLogEntry`, `MetadataFieldDefinition`, `MetadataFieldValue`, `MediaItem`, `FeatureFlagOverride`, `DeploymentRun`, `RecordClaim`, `IdempotencyKey` and app-owned `UserProfile` |
| `App\*` | `Repository/App/Surface/*/`                      | Application modules discovered by per-module `module.php`, route files, services, repositories, views and `front/` work assets |
| `Catalyst\Helpers\Debug` | `app/Helpers/Debug/`                             | [helpers-debug.md](docs/helpers-debug.md) |
| `Catalyst\Helpers\Security` | `app/Helpers/Security/`                          | CspNonce, CsrfProtection, SensitiveValueRedactor — CSP nonce/CSRF helpers and shared secret redaction reused by layouts, middleware, CLI and settings surfaces |
| `Catalyst\Helpers\Error` | `app/Helpers/Error/`                             | [helpers-error.md](docs/helpers-error.md) |
| `Catalyst\Helpers\Exceptions` | `app/Helpers/Exceptions/`                        | [helpers-exceptions.md](docs/helpers-exceptions.md) |
| `Catalyst\Helpers\Validation` | `app/Helpers/Validation/`                        | [helpers-validation.md](docs/helpers-validation.md) |
| `Catalyst\Helpers\Config` | `app/Helpers/Config/`                            | [helpers-config.md](docs/helpers-config.md) |
| `Catalyst\Helpers\Log` | `app/Helpers/Log/`                               | [helpers-log.md](docs/helpers-log.md) |
| `Catalyst\Helpers\I18n` | `app/Helpers/I18n/`                              | [helpers-i18n.md](docs/helpers-i18n.md) |
| `Catalyst\Helpers\ToolBox` | `app/Helpers/ToolBox/`                           | [helpers-toolbox.md](docs/helpers-toolbox.md) |
| `Catalyst\Repository\DevTools\Controllers` | `Repository/Framework/DevTools/Controllers/`     | [repository-devtools.md](docs/repository-devtools.md) |
| `Catalyst\Repository\Auth\Controllers` | `Repository/Framework/Auth/Controllers/`         | [repository-auth.md](docs/repository-auth.md) — LoginController, LogoutController, RegisterController, PasswordResetController, EmailVerificationController, SocialAuthController, **MfaController** |
| `Catalyst\Repository\Notification\Controllers` | `Repository/Framework/Notification/Controllers/` | [repository-notification.md](docs/repository-notification.md) |
| `Catalyst\Repository\Roles\Controllers` | `Repository/Framework/Roles/Controllers/`        | Roles/Permissions CRUD + UserRoles — RBAC admin UI now mounted on resource abilities (`authorizeResource`) |
| `Catalyst\Repository\Audit\Controllers` | `Repository/Framework/Audit/Controllers/`        | Audit log admin UI (`/audit-log`, `/audit-log/{id}`) over DataGrid + repository filters/export |
| `Catalyst\Repository\Operations\Controllers` | `Repository/Framework/Operations/Controllers/`   | Operations control plane (`/operations`) + feature flags/plugins/deployments/tenancy surfaces over FormBuilder + DataGrid |
| `Catalyst\Repository\Media\Controllers` | `Repository/Framework/Media/Controllers/`        | Media library admin UI (`/media-library`) + metadata field definitions (`/media-fields`) over MediaManager + MetadataManager |
| `Catalyst\Repository\Documents\Controllers` | `Repository/Framework/Documents/Controllers/`    | Document templates admin UI (`/document-templates`) + preview/export + workflow transitions + version restore |
| `Catalyst\Repository\Automation\Controllers` | `Repository/Framework/Automation/Controllers/`   | Automation rules admin UI (`/automation-rules`) + manual run + execution logs + workflow/version integration |
| `Catalyst\Repository\ApiPlatform\Controllers` | `Repository/Framework/ApiPlatform/Controllers/`  | API platform admin UI (`/api-platform`) + bearer token management + `/api/v1` catalog/workflow/version endpoints |
| `Catalyst\Repository\Settings\Controllers` | `Repository/Framework/Settings/Controllers/`     | Config Panel + Health: ConfigController, HealthController, App/Db/Mail/Session/Cache/Logging/Security/WebSocket/DevTools config save controllers, FtpConfigController, CorsConfigSaveController, DkimController, SetupCompletionController (admin provisioning + finalization) |
| `Catalyst\Repository\Settings\Requests` | `Repository/Framework/Settings/Requests/`        | Section-scoped setup `FormRequest` classes for app, db, mail, session, cache, logging, security, websocket and devtools payloads |
| `Catalyst\Repository\Settings\Support` | `Repository/Framework/Settings/Support/`         | Small support services for setup/config surfaces, including AdminReadinessProbe, FtpConnectionProbe, DbConnectivityProbe and per-section config writers |
| `Catalyst\Framework\Mail\DkimGenerator` | `app/Framework/Mail/DkimGenerator.php`           | RSA 2048-bit DKIM key generation (Etapa 7) |

---

## Architecture Overview

### MVC Pattern
- **Models**: Current runtime persistence classes still live co-located per module (`Repository/Framework/{Module}/Models/`), but shared/extendable entities are now expected to converge toward `app/Entities/` as active migration debt
- **Views**: HTML templates and presentation scope (`boot-core/template/` for layouts/pages/components/errors/debug + `scope/`; `Repository/Framework/{Module}/Views/` and `Repository/App/Surface/{Module}/Views/` with `pages/`, `partials/`, `components/`, `scope/`)
- **Controllers**: Request handling (base: `app/Framework/Controllers/Controller.php`; per module: `Repository/Framework/{Module}/Controllers/`)

→ See [docs/architecture.md](docs/architecture.md)

### Dual-Space Architecture
1. **Framework Core** (`app/Framework/`, `app/Helpers/`) — Core components, read-only
2. **Repository/Framework** (`Repository/Framework/`) — Framework modules with screens (Auth, Roles, Settings, DevTools, Notification, Audit, Media, Documents, Automation, ApiPlatform)
3. **Repository/App** (`Repository/App/`) — Developer modules, safe to modify

→ See [docs/architecture.md](docs/architecture.md)

### Runtime Model
- **Web** — `public/index.php` bootstraps `Kernel::getInstance()->bootstrap()->run()` once per request
- **CLI** — `public/cli.php` registers commands and executes `CliKernel` per invocation
- **Singletons** — accepted in the current request-response / short-lived CLI model; see `app/Framework/Traits/SingletonTrait.php`
- **Long-running boundary** — the main framework stack is not documented as worker-safe for persistent HTTP processes; optional Ratchet WebSocket runtime is a separate CLI process
- **Async runtime boundary** — the first framework queue worker and scheduler are also explicit CLI process boundaries; they persist state through framework-owned DB tables and lock files instead of introducing an external worker runtime

→ See [docs/architecture.md](docs/architecture.md), [TERMINAL.md](TERMINAL.md)

### Route Loading Order
1. `boot-core/routes/global-routes.php` — Global middleware + redirects
2. `boot-core/routes/api.php` — Global API routes
3. `Repository/Framework/{Module}/routes.php` — Framework modules (glob)
4. `Repository/App/Surface/{Module}/routes.php` — App modules (glob)

→ See [docs/architecture.md](docs/architecture.md)

### Active Structural Decisions
- bootstrap-owned runtime directories now live under `boot-core/bin/`, `boot-core/cache/` and `boot-core/database/`
- module-local `Repository/*/Models/` remain live runtime, but new shared/extendable entities should target `app/Entities/`

---

## Dependencies Allowed

**Only FOUR external libraries permitted:**

1. **PHPMailer** (`phpmailer/phpmailer ^6.9`) — SMTP, DKIM email support
2. **league/oauth2-client** (`league/oauth2-client ^2.9`) — OAuth2 social login
3. **cboden/ratchet** (`cboden/ratchet ^0.4`) — WebSocket server
4. **react/http** (`react/http ^1.9`) — Async HTTP (required by Ratchet)

→ See [docs/architecture.md](docs/architecture.md)

---

## Quick Component Reference

### Framework Core
- **Kernel** — App bootstrap, effective runtime config application and request routing → [kernel.md](docs/kernel.md)
- **Controller** — Base controller class with helpers → [framework-controllers.md](docs/framework-controllers.md)
- **View** — Template rendering system → [framework-view.md](docs/framework-view.md)
- **SessionManager** — PHP session bootstrap from effective config, plus FlashMessage integration → [framework-session.md](docs/framework-session.md)
- **DatabaseSessionHandler** — Named-connection database session persistence with auto-created session table for `session_driver=database` → `app/Framework/Session/DatabaseSessionHandler.php`
- **FlashBag** — Session-backed storage for regular/persistent flashes, display history and dismiss tracking → `app/Framework/Session/FlashBag.php`
- **FlashMessage** — Public facade over FlashBag for controllers/views → [framework-session.md](docs/framework-session.md)
- **ToastQueue** — Session-based toast buffer; push()/all()/clear() — SRP complement to FlashMessage → `app/Framework/Session/ToastQueue.php`
- **Traits** → SingletonTrait, HandlesFormEventsTrait, FrontResourceTrait, LoadsFeatureConfigTrait, HasTimestampsTrait, HasSoftDeletesTrait, HasAuditLogTrait → [framework-traits.md](docs/framework-traits.md)

### Templates & Layouts *(boot-core/template/layouts/)*
- **base.php** — Universal chrome: public, pre-auth, dev pages (Bootstrap + FontAwesome + status bar + all partials) → `boot-core/template/layouts/base.phtml`
- **admin.php** — Full-page admin chrome: compact topbar + sidebar domain switcher for inactive areas + grouped navigation + Operations/User children + breadcrumbs + shared Catalyst status bar (no footer render). The active context is shown by the context card/title and is not duplicated as another sidebar link. The shell consumes `NavigationRegistry` and module `group/group_label/group_order` metadata instead of relying only on hardcoded arrays or path heuristics → `boot-core/template/layouts/admin.phtml`
- **auth.php** *(Etapa 13)* — Centered card wrapper (min-vh-100 flex-center bg-body-secondary) for auth pages; now includes the same institutional logo stack + brand identity consumed by public/admin surfaces, with all universal partials included → `boot-core/template/layouts/auth.phtml`
- **blank.php** *(Etapa 13)* — Chrome-free HTML shell; head/body assets only (no status bar, no flash, no toaster); for print, iframes, embedded pages → `boot-core/template/layouts/blank.phtml`

Every `Controller::view()` render now auto-publishes module-local `front/style.css` + `front/script.js` through `FrontResourceTrait` to `public/assets/*/work/{slug}/`.

All layouts share `_head-assets.phtml` (CSRF meta, Bootstrap, FontAwesome, Tabler, FOUC prevention, resolved favicon mime type) and `_body-scripts.phtml` (framework JS) to prevent chrome divergence.

### HTTP Layer
- **Request** *(17-Upload)* — HTTP request wrapper; includes `file(string $key)` and `files()` for normalized uploads → `app/Framework/Http/Request.php`
- **FormRequest** *(8-Validation formal)* — Request wrapper for reusable validation + authorization; auto-resolved by `RouteDispatcher` and backed by `Validator` → `app/Framework/Http/FormRequest.php`
- **UploadedFile** *(17-Upload)* — Uploaded file DTO with MIME detection, extension helpers and UUID storage under `public/uploads/` → `app/Framework/Http/UploadedFile.php`
- **FileValidator** *(17-Upload)* — Real MIME + extension + size validation for uploaded files → `app/Framework/Http/FileValidator.php`
- **Response** — Base HTTP response → `app/Framework/Http/Response.php`
- **JsonResponse** *(18-JS)* — JSON API responses with notification support; client actions via `withRedirect()`, `withRefresh()` y `withHtml()` (`in` + `html`), where partial HTML now requires `TrustedHtml` and emits a `trusted-html` policy marker → `app/Framework/Http/JsonResponse.php`
- **RedirectResponse** — HTTP redirect responses → `app/Framework/Http/RedirectResponse.php`
- **HtmlResponse** — HTML content response; sets Content-Type: text/html → `app/Framework/Http/HtmlResponse.php`
- **ApiRequest** — Internal/legacy helper for API request detection/parsing; no confirmed framework call sites in the current runtime audit → `app/Framework/Http/ApiRequest.php`

### Routing
- **Router** — Route registration and dispatch → `app/Framework/Route/Router.php`
- **Route** — Single route definition with middleware support; preserves configured middleware instances at runtime and serializes route-cache middleware through signed payload envelopes before rehydration → `app/Framework/Route/Route.php`
- **RouteCollection** — Route registry and matching → `app/Framework/Route/RouteCollection.php`
- **RouteCompiler** — Residual route-regex utility; the current `Route` runtime compiles patterns inline and no confirmed consumers call this helper → `app/Framework/Route/RouteCompiler.php`
- **RouteDispatcher** — Runs global middleware before route matching, then applies route middleware to the matched route; now resolves controller constructors through the lightweight container and auto-validates typed `FormRequest` parameters → `app/Framework/Route/RouteDispatcher.php`
- **RouteGroup** — Route grouping with shared prefix/middleware → `app/Framework/Route/RouteGroup.php`
- **UrlGenerator** — Generates URLs from named routes and paths; `generate()`, `to()`, `asset()` → `app/Framework/Route/UrlGenerator.php`

### Database & ORM
- **DatabaseManager** — Multiple named connections → [framework-database.md](docs/framework-database.md)
- **Connection** — PDO wrapper with transactions → [framework-database.md](docs/framework-database.md)
- **QueryBuilder** — Fluent SQL builder with validated identifiers/operators/order clauses through `SqlReference` → [framework-database.md](docs/framework-database.md)
- **SqlReference** — Shared SQL identifier/reference validator used by `Connection` and `QueryBuilder` to harden table, column, operator, join and sort fragments → `app/Framework/Database/SqlReference.php`
- **Model** *(8-ORM)* — Abstract ORM base composed from focused concerns for attributes, relationships, lifecycle hooks and persistence; keeps CRUD, dirty tracking, casts, `$relations` cache and lazy relation access → `app/Framework/Database/Model.php`
- **HasModelAttributes** *(Model concern)* — Attribute hydration, casts, dirty tracking, serialization and magic attribute access helpers → `app/Framework/Database/Concerns/HasModelAttributes.php`
- **HasModelRelationships** *(Model concern)* — Relationship factories, eager/lazy relation cache helpers and relation resolution → `app/Framework/Database/Concerns/HasModelRelationships.php`
- **HasModelLifecycleHooks** *(Model concern)* — Boot state, hook registration, event callbacks and timestamp-style lifecycle dispatch → `app/Framework/Database/Concerns/HasModelLifecycleHooks.php`
- **PersistsModelState** *(Model concern)* — Create/update/delete persistence paths and connection resolution for ORM models → `app/Framework/Database/Concerns/PersistsModelState.php`
- **ModelQueryBuilder** *(8-ORM)* — Extends QueryBuilder; get()→Collection, first()→?Model, paginate()→Pagination, soft-delete scopes, `with(...$relations)` eager loading → `app/Framework/Database/ModelQueryBuilder.php`
- **Relation** *(14-ORM-Rel)* — Abstract base for all relation types; holds parent/related/FK/localKey; `getResults()` + `matchEager()` → `app/Framework/Database/Relations/Relation.php`
- **HasOne** *(14-ORM-Rel)* — 1:1, FK on related table, lazy returns `?Model`, eager: first-match-wins → `app/Framework/Database/Relations/HasOne.php`
- **HasMany** *(14-ORM-Rel)* — 1:N, FK on related table, lazy/eager returns `Collection<Model>` grouped by FK → `app/Framework/Database/Relations/HasMany.php`
- **BelongsTo** *(14-ORM-Rel)* — N:1, FK on parent table, lazy/eager returns `?Model` indexed by owner key → `app/Framework/Database/Relations/BelongsTo.php`
- **BelongsToMany** *(14-ORM-Rel)* — N:N via pivot table, two-step query (no JOIN), lazy/eager returns `Collection<Model>` → `app/Framework/Database/Relations/BelongsToMany.php`
- **Collection** *(8-ORM)* — Typed iterable (Countable + IteratorAggregate): map/filter/pluck/keyBy/chunk/where → `app/Framework/Database/Collection.php`
- **Pagination** *(8-ORM)* — Immutable DTO: items/total/perPage/currentPage/lastPage/nextPage/prevPage → `app/Framework/Database/Pagination.php`
- **HasTimestampsTrait** *(8-ORM, Trait)* — Auto created_at/updated_at via boot hooks → `app/Framework/Traits/HasTimestampsTrait.php`
- **HasSoftDeletesTrait** *(8-ORM, Trait)* — Soft delete: delete()/restore()/forceDelete()/withTrashed()/onlyTrashed() → `app/Framework/Traits/HasSoftDeletesTrait.php`
- **HasAuditLogTrait** *(8-ORM, Trait — HIPAA §164.312b)* — Auto created_by/updated_by/deleted_by from session plus created/updated/deleted/restored audit mutations through `AuditLogManager` → `app/Framework/Traits/HasAuditLogTrait.php`
- **AuditLogEntry** *(Entity compartida)* — ORM model for `audit_logs`; stores actor, request, before/after snapshots, metadata and event channels → `app/Entities/AuditLogEntry.php`
- **MetadataFieldDefinition** *(RM-29)* — Declarative contract per resource/field for dynamic metadata, grid exposure and validation tuning → `app/Entities/MetadataFieldDefinition.php`
- **MetadataFieldValue** *(RM-29)* — Typed value store for dynamic metadata by `resource_key` + `record_id` + definition → `app/Entities/MetadataFieldValue.php`
- **MediaItem** *(RM-30)* — Reusable media library record with storage disk/path/public URL, mime, size and audit hooks → `app/Entities/MediaItem.php`
- **ResourceAttachment** *(PA-06)* — Uniform attachment/evidence link keyed by `resource_key` + `record_id`, bridging media or document artifacts with purpose/type and detach lifecycle → `app/Entities/ResourceAttachment.php`
- **WorkflowInstance** *(RM-32)* — Persisted workflow runtime state for one resource record (`definition_key`, `resource_key`, `record_id`, `current_state`) → `app/Entities/WorkflowInstance.php`
- **WorkflowTransition** *(RM-32)* — Persisted transition audit trail with transition key, actor and metadata payload → `app/Entities/WorkflowTransition.php`
- **DocumentTemplate** *(RM-31)* — Reusable document template with subject, body, variable contract and workflow/version hooks → `app/Entities/DocumentTemplate.php`
- **DocumentArtifact** *(RM-31)* — Persisted rendered document/export artifact linked to a template execution → `app/Entities/DocumentArtifact.php`
- **ReportRun** *(PA-10)* — Queued reporting/export execution with criteria snapshot, persisted output media linkage, retry state and optional attachment delivery target → `app/Entities/ReportRun.php`
- **AutomationRule** *(RM-33)* — Internal automation rule with trigger type, conditions, action type/payload, lifecycle state and reusable `valid_from` / `valid_to` window → `app/Entities/AutomationRule.php`
- **AutomationExecutionLog** *(RM-33)* — Execution history row for automation outcomes, messages and payload snapshots → `app/Entities/AutomationExecutionLog.php`
- **ContentVersion** *(RM-34)* — Shared snapshot/diff history row used by reusable version restore flows → `app/Entities/ContentVersion.php`
- **ApiToken** *(RM-35)* — Bearer token record with scopes, expiry, revocation and actor linkage → `app/Entities/ApiToken.php`
- **IdempotencyKey** *(PA-12)* — Reusable execution-key ledger with request fingerprint, resource scope, completion snapshot and replay/conflict state → `app/Entities/IdempotencyKey.php`
- **ModelNotFoundException** *(8-ORM)* — Thrown by findOrFail()/firstOrFail() → `app/Helpers/Exceptions/ModelNotFoundException.php`
- **Migration** *(15-Migrations)* — Abstract migration base; provides `up()`, `down()`, `getVersion()`, DB helpers and FK helpers → `app/Framework/Database/Migration.php`
- **MigrationRunner** *(15-Migrations)* — Discovers `boot-core/database/migrations/*.php`, tracks versions/batches in `migrations`, runs pending and rolls back the last batch → `app/Framework/Database/MigrationRunner.php`

### Validation & Forms
- **Validator** — Multi-rule validation system → [helpers-validation.md](docs/helpers-validation.md)
- **FormRequest** *(8-Validation formal)* — Abstract reusable request contract: `authorize()`, `rules()`, `validated()`, `prepareForValidation()`, input/file proxies → `app/Framework/Http/FormRequest.php`
- **HandlesFormEventsTrait** — Event-driven form routing → [framework-traits.md](docs/framework-traits.md)

### Frontend Runtime
- **CatalystNotificationSystem** *(18-JS)* — Global JS facade; exports shared HTTP, modal, form and loading helpers, including `showWaitModal()` / `closeWaitModal()` → `public/assets/js/catalyst/catalyst.js`
- **HttpClient** *(18-JS)* — Shared AJAX client; injects XHR/CSRF, refreshes tokens, processes notifications and applies partial DOM injection from JSON responses → `public/assets/js/catalyst/modules/http.js`
- **ModalManager** *(18-JS)* — Bootstrap modal service; supports alert/confirm/load/show plus non-dismissible wait modal → `public/assets/js/catalyst/modules/modal.js`
- **FormHandler** *(18-JS)* — Delegated AJAX forms with shared button loading state, field errors and redirect/refresh handling → `public/assets/js/catalyst/modules/form-handler.js`
- **loading.js** *(18-JS)* — Reusable button loading helpers `setButtonLoading()` / `clearButtonLoading()` → `public/assets/js/catalyst/modules/loading.js`
- **response-actions.js** *(18-JS)* — Applies JSON-driven partial DOM replacement and emits `catalyst:dom:updated` → `public/assets/js/catalyst/modules/response-actions.js`
- **StatusBarManager** *(18-JS)* — Shared authenticated status bar, REST unread fallback, WS auth/subscriptions and presence bridge; kept on browser-safe syntax for the embedded runtime → `public/assets/js/catalyst/modules/status-bar.js`
- **record-presence.js** *(PA-08)* — Claim-derived owner heartbeat + banner refresh runtime over canonical claim context → `public/assets/js/catalyst/modules/record-presence.js`

### Mail & Notifications
- **MailManager** — SMTP email dispatch; live attachments flow through `MailMessage::attach()` / `attachInline()` → [framework-mail.md](docs/framework-mail.md)
- **MailAttachment** — Residual compatibility DTO; not consumed by the current `MailMessage`/`MailManager` pipeline → `app/Framework/Mail/MailAttachment.php`
- **NotificationBag** — Toasters, modals, alerts → [framework-notification.md](docs/framework-notification.md)
- **NotificationRepository** — DB access for notifications → [framework-notification.md](docs/framework-notification.md)
- **NotificationManager** — Notification dispatch, event bridge and queue bridge over the existing persisted notification runtime → [framework-notification.md](docs/framework-notification.md)
- **EventBus** — Framework event dispatcher with sync listeners and queued-listener bridge → [framework-event.md](docs/framework-event.md)
- **EventEnvelope** — Shared event payload entity for runtime, queue and scheduler consumers → [framework-event.md](docs/framework-event.md)
- **QueueManager / QueueRepository / QueueWorker** — Persistent framework queue dispatch, storage, retries and failed jobs → [framework-queue.md](docs/framework-queue.md)
- **ScheduleRegistry / ScheduleRunner / CronExpression** — Declarative scheduler registry, due matcher, locking and queue-backed task execution → [framework-schedule.md](docs/framework-schedule.md)
- **NotificationPosition** — Residual compatibility enum for toaster placement vocabulary; no confirmed active PHP consumers → `app/Framework/Notification/NotificationPosition.php`
- **FlashMessage** — Regular & persistent messages via FlashBag facade → [framework-session.md](docs/framework-session.md)

### Workflow, Documents, Automation & Versioning
- **WorkflowDefinition** *(RM-32)* — Immutable declarative workflow contract (states, transitions, guards, before/after hooks) → `app/Framework/Workflow/WorkflowDefinition.php`
- **WorkflowDefinitionRegistry** *(RM-32)* — Singleton registry for workflow definitions reused by runtime, admin surfaces and API → `app/Framework/Workflow/WorkflowDefinitionRegistry.php`
- **FrameworkWorkflowCatalog** *(RM-32)* — Canonical framework workflow definitions (`document-templates.lifecycle`, `automation-rules.lifecycle`) → `app/Framework/Workflow/FrameworkWorkflowCatalog.php`
- **WorkflowRepository** *(RM-32)* — Persistence/query layer for workflow instances, transitions and listings → `app/Framework/Workflow/WorkflowRepository.php`
- **WorkflowManager** *(RM-32)* — Runtime orchestration for workflow instance bootstrap, guarded transitions, audit/event emission and event-driven transitions → `app/Framework/Workflow/WorkflowManager.php`
- **TemplateStringRenderer** *(RM-31)* — Lightweight variable renderer for preview/export payload resolution without adding a second templating stack → `app/Framework/Document/TemplateStringRenderer.php`
- **DocumentTemplateRepository** *(RM-31)* — Persistence/query layer for templates, artifacts and joined workflow state → `app/Framework/Document/DocumentTemplateRepository.php`
- **DocumentTemplateManager** *(RM-31)* — Reusable document-template service: create/update, preview, export, artifact persistence, workflow sync and version capture → `app/Framework/Document/DocumentTemplateManager.php`
- **AutomationRuleRepository** *(RM-33)* — Persistence/query layer for automation rules with joined workflow state, temporal-state filters and sensitivity-aware execution logs → `app/Framework/Automation/AutomationRuleRepository.php`
- **AutomationManager** *(RM-33)* — Internal rule engine over `EventBus`, `Queue`, `ScheduleRegistry`, `WorkflowManager`, `NotificationManager` and `DocumentTemplateManager`, now aware of canonical validity windows → `app/Framework/Automation/AutomationManager.php`
- **RunScheduledAutomationRulesJob** *(RM-33)* — Queueable scheduler bridge that evaluates due rules and dispatches real actions → `app/Framework/Automation/Jobs/RunScheduledAutomationRulesJob.php`
- **ProcessAutomationEventListener** *(RM-33)* — Wildcard event listener that converts runtime events into automation rule executions without introducing another bus → `app/Framework/Event/Listeners/ProcessAutomationEventListener.php`
- **VersionRepository** *(RM-34)* — Shared persistence/query layer for snapshot/diff history by `resource_key` + `record_id` → `app/Framework/Versioning/VersionRepository.php`
- **VersionManager** *(RM-34)* — Shared version capture, diff generation and restore orchestration reused by documents and automation rules → `app/Framework/Versioning/VersionManager.php`

### API Platform
- **ApiCatalog** *(RM-35)* — Canonical catalog of versioned `/api/v1` routes, permissions and descriptions; prevents drift from ad-hoc endpoint lists → `app/Framework/Api/ApiCatalog.php`
- **ApiTokenRepository** *(RM-35)* — Persistence/query layer for bearer tokens and revocation state, now with direct revoke-by-id support for lifecycle enforcement and smoke coverage → `app/Framework/Api/ApiTokenRepository.php`
- **ApiTokenManager** *(RM-35)* — Token minting, hashing, scope persistence and active-token resolution for API runtime; now rejects invalid/inactive owners and revokes tokens that resolve to broken ownership → `app/Framework/Api/ApiTokenManager.php`
- **ApiTokenMiddleware** *(RM-35)* — Bearer-token auth guard that resolves scoped users through `AuthManager` without creating a parallel auth subsystem → `app/Framework/Middleware/ApiTokenMiddleware.php`

### Authentication
- **AuthManager** — Login/logout facade; MFA pending state (`setPendingMfa`, `completeMfaLogin`, `hasMfaPending`, `getMfaPendingUserId/Redirect/Remember`, `clearPendingMfa`, `loginFromUser`) → [framework-auth.md](docs/framework-auth.md)
- **UserProvider** — User lookups & mutations; MFA (`getMfaData`, `enableMfa`, `disableMfa`, `updateMfaBackupCodes`) now run through the ORM model so audit hooks cover user/profile/MFA changes → [framework-auth.md](docs/framework-auth.md)
- **OAuthManager** — Google & GitHub OAuth2 → [framework-auth.md](docs/framework-auth.md)
- **MfaManager** *(Etapa 12 — HIPAA §164.312(d))* — Pure-PHP TOTP (RFC 6238): `generateSecret()`, `generateQrUri(secret,email,issuer)`, `verifyCode(secret,code,window=1)`, `generateBackupCodes(count=8)`, `verifyBackupCode(code,&codes)` → `app/Framework/Auth/MfaManager.php`

### Middleware
- **CoreMiddleware** — Abstract base with `passToNext()` helper → `app/Framework/Middleware/CoreMiddleware.php`
- **MiddlewareInterface** — Contract: `process(Request, Closure): Response` → `app/Framework/Middleware/MiddlewareInterface.php`
- **MiddlewareStack** — Ordered pipeline execution → `app/Framework/Middleware/MiddlewareStack.php`
- **CallableMiddleware** — Wraps a Closure as middleware → `app/Framework/Middleware/CallableMiddleware.php`
- **SecurityHeadersMiddleware** — Sets CSP, HSTS, X-Frame-Options etc. on every response, including the `trusted-renderer` relaxations used by controlled renderer/embed pages → `app/Framework/Middleware/SecurityHeadersMiddleware.php`
- **DevToolsGuardMiddleware** — Hard gate for DevTools routes: `403` outside development, auth required in development, access limited to admin or `access-devtools` permission → `app/Framework/Middleware/DevToolsGuardMiddleware.php`
- **CsrfMiddleware** — CSRF token validation for POST/PUT/PATCH/DELETE → `app/Framework/Middleware/CsrfMiddleware.php`
- **AuthMiddleware** — Redirects unauthenticated requests to /login → `app/Framework/Middleware/AuthMiddleware.php`
- **GuestMiddleware** — Redirects authenticated users away from guest-only routes to `/` → `app/Framework/Middleware/GuestMiddleware.php`
- **RoleMiddleware** — Gate-based role/permission guard → `app/Framework/Middleware/RoleMiddleware.php`
- **LoginThrottleMiddleware** — 5 attempts / 10 min per IP, file-based SHA-256; bypassed when `IS_DEVELOPMENT=true` → `app/Framework/Middleware/LoginThrottleMiddleware.php`
- **BasicAuthMiddleware** — Internal/legacy HTTP Basic Auth guard with file-based failed-attempt throttling; no active route consumers confirmed → `app/Framework/Middleware/BasicAuthMiddleware.php`
- **DebugMiddleware** — Internal/legacy request-response logger; no active route consumers confirmed → `app/Framework/Middleware/DebugMiddleware.php`
- **RequestThrottlingMiddleware** — Generic limiter for mutating requests, scoped by actor + method + path, file-backed under `boot-core/storage/throttle`, bypassed in development and excluding `/login` + `/register` → `app/Framework/Middleware/RequestThrottlingMiddleware.php`
- **ThrottleProfileCatalog** — Route/context throttle profile resolver for generic mutating-request throttling (`setup_mutation`, `admin_mutation`, `auth_recovery`, `mfa_challenge`, fallback `default_mutation`) → `app/Framework/Middleware/ThrottleProfileCatalog.php`
- **SetupMiddleware** — Redirects to `/configuration/environment-setup` when app not configured; bypasses setup/auth/assets/framework flash endpoints via shared SetupAccessTrait → `app/Framework/Middleware/SetupMiddleware.php`
- **SetupGuardMiddleware** — Protects `/configuration/environment-setup` once app is configured; reuses SetupAccessTrait for setup state + JSON errors → `app/Framework/Middleware/SetupGuardMiddleware.php`
- **WebSocketBootMiddleware** — Feature-gated by effective websocket config; ensures Ratchet WS server is running when enabled → `app/Framework/Middleware/WebSocketBootMiddleware.php`
- **RouteFeatureMiddleware** — Runtime route gate backed by `FeatureFlagManager`; returns `404`/redirect coherently for disabled route capabilities and remains route-cache safe → `app/Framework/Middleware/RouteFeatureMiddleware.php`
- **CorsMiddleware** *(8-CORS)* — Effective CORS headers + real preflight OPTIONS handling, feature-gated via `cors.json` → `app/Framework/Middleware/CorsMiddleware.php`
- **FeatureFlagInterface** — Contract for enable/disable middleware (`isEnabled(): bool`) → `app/Framework/Middleware/FeatureFlagInterface.php`

### WebSocket
- **WebSocketToken** — Short-lived auth tokens for WS connections, now tenant-aware on verification → [framework-websocket.md](docs/framework-websocket.md)
- **WebSocketServer** — Ratchet-based WS server (CLI) → [framework-websocket.md](docs/framework-websocket.md)
- **WebSocketPublisher** — Push notifications and resource-scoped presence payloads to WS clients → [framework-websocket.md](docs/framework-websocket.md)

### Debugging & Logging
- **Dumper** — Variable inspection with 15 themes → [helpers-debug.md](docs/helpers-debug.md)
- **ConfigSecretCatalog / ConfigSecretStore** — Managed-secret registry and companion `secrets.json` storage for runtime config sections (`app`, `db`, `mail`, `ftp`) → [helpers-config.md](docs/helpers-config.md)
- **HealthReportBuilder** — Shared framework health/readiness report reused by `status` and `/configuration/application-health*` → `app/Framework/Health/HealthReportBuilder.php`
- **ModuleRegistry** — Declarative/discoverable module catalog with namespace, routes, views, assets, settings, permissions, health checks, seeds, feature flags, owned-route hydration and sidebar grouping metadata → `app/Framework/Module/ModuleRegistry.php`
- **ModuleLinter** — Structural lint for module metadata, route drift, guards, permission bridge, navigation routes, navigation child routes and duplicate sidebar hrefs → `app/Framework/Module/ModuleLinter.php`
- **FeatureFlagManager** *(RM-36)* — Effective feature flag evaluation across environment catalog, persisted overrides and runtime actor context, with audit integration and module/plugin refresh → `app/Framework/FeatureFlag/FeatureFlagManager.php`
- **DataClassificationRegistry / SensitiveDataPolicy** *(PA-03)* — Declarative sensitive-field registry plus channel-aware sanitization reused by audit payloads, API payloads, old input, logs and CSV export → `app/Framework/Sensitivity/`
- **EffectiveWindow** *(PA-04)* — Shared normalization and active/expired/upcoming window evaluation for reusable time-bound resources → `app/Framework/Temporal/EffectiveWindow.php`
- **IdempotencyManager / IdempotencyRepository** *(PA-12)* — Canonical deduplication flow for manual/API executions with replay, in-progress and conflict handling → `app/Framework/Idempotency/`
- **PluginRegistry / PluginManager** *(RM-37)* — Formal plugin manifests, runtime enable/disable state, validation and bridge to modules/navigation/permissions → `app/Framework/Plugin/PluginRegistry.php`, `app/Framework/Plugin/PluginManager.php`
- **DeploymentManager / DeploymentRunRepository** *(RM-38)* — Formal deployment pipeline, preflight stages, staging/export bookkeeping and release history → `app/Framework/Deployment/DeploymentManager.php`, `app/Framework/Deployment/DeploymentRunRepository.php`
- **TenancyManager** *(RM-39)* — Canonical tenancy decision, resolver baseline and strategy reporting without opening a parallel runtime tenancy stack → `app/Framework/Tenancy/TenancyManager.php`
- **PlatformAppearanceManager** — Shared institutional appearance runtime: fixed family catalog, compact shell-safe logo variants, branding view model for public/auth/admin and PDF watermark settings → `app/Framework/Appearance/PlatformAppearanceManager.php`
- **NavigationRegistry** — Admin shell contexts, public menu and breadcrumb resolution mounted on the module registry plus permission/config visibility rules; canonical navigation taxonomy is documented in `docs/navigation-route-refactor-plan.md` → `app/Framework/Navigation/NavigationRegistry.php`
- **SensitiveValueRedactor** — Shared secret/sensitive-key redaction policy reused by CLI config output and logger context sanitization → `app/Helpers/Security/SensitiveValueRedactor.php`
- **Logger** — Facade de logging; delega configuración, sanitización, formateo, clasificación de request, persistencia y render inline a colaboradores dedicados → [helpers-log.md](docs/helpers-log.md)
- **LoggerSettings / LoggerLevelMap** — Immutable config snapshot + canonical level/channel registry for the logger pipeline → `app/Helpers/Log/LoggerSettings.php`, `app/Helpers/Log/LoggerLevelMap.php`
- **LoggerConfigurator / LoggerContextSanitizer / LoggerRequestClassifier** — Runtime config hydration, sensitive-context scrubbing and HTTP/CLI request classification → `app/Helpers/Log/LoggerConfigurator.php`, `app/Helpers/Log/LoggerContextSanitizer.php`, `app/Helpers/Log/LoggerRequestClassifier.php`
- **LoggerEntryFormatter / LoggerWriter / LoggerInlineDisplay** — Log line assembly, channel/file output and optional inline CLI/browser rendering helpers → `app/Helpers/Log/LoggerEntryFormatter.php`, `app/Helpers/Log/LoggerWriter.php`, `app/Helpers/Log/LoggerInlineDisplay.php`
- **DrawBox** — Facade for diagnostic boxed output across CLI, HTML and file-output flows → [helpers-toolbox.md](docs/helpers-toolbox.md)
- **DrawBoxCliRenderer / DrawBoxHtmlRenderer** — Dedicated renderers for ANSI terminal and HTML box output → `app/Helpers/ToolBox/DrawBoxCliRenderer.php`, `app/Helpers/ToolBox/DrawBoxHtmlRenderer.php`
- **DrawBoxTextHelper / DrawBoxStylePalette / DrawBoxFileOutputDecorator** — Text wrapping, palette resolution and output post-processing helpers for `DrawBox` → `app/Helpers/ToolBox/DrawBoxTextHelper.php`, `app/Helpers/ToolBox/DrawBoxStylePalette.php`, `app/Helpers/ToolBox/DrawBoxFileOutputDecorator.php`
- **ErrorHandler** → Exception handlers, custom exceptions → [helpers-error.md](docs/helpers-error.md), [helpers-exceptions.md](docs/helpers-exceptions.md)

### CLI Commands *(Etapa 11 + 16)*
- **CommandInterface** — Contract: `getName()`, `getDescription()`, `getOptions()`, `getParameters()`, `execute(ArgumentBag): int` → `app/Framework/Cli/CommandInterface.php`
- **AbstractCommand** — Base class: ANSI-aware output helpers (`line/success/error/info/warn`), interactive `ask()`/`confirm()` → `app/Framework/Cli/AbstractCommand.php`
- **CommandRegistry** — Singleton registry: `register()`, `get()`, `all()`, `has()` → `app/Framework/Cli/CommandRegistry.php`
- **CliKernel** — `run(array $argv): int`; resolves & dispatches commands; per-command `--help`; auto-discovers `Repository/App/Surface/*/Commands/*.php` → `app/Framework/Cli/CliKernel.php`
- **TerminalStyle** — ANSI capability detection and color wrappers used by `CliKernel`/`AbstractCommand` in TTY and non-TTY flows → `app/Framework/Cli/TerminalStyle.php`
- **CliRouteLoader** — Shared route bootstrap loader for route-oriented CLI commands, with optional discovery-manifest artifact sync → `app/Framework/Cli/CliRouteLoader.php`
- **RouteContractInspector** — Shared CLI diagnostics for canonical entry routes, approved aliases, lowercase casing rules, public-entry JSON companions and `work/{slug}` asset publication; now distinguishes public App modules from guarded App surfaces generated by module tooling → `app/Framework/Cli/Support/RouteContractInspector.php`
- **AuthFixtureManager / AuthFixtureCatalog / AuthFixtureFactory** *(RM-22)* — Official reversible auth/RBAC fixture catalog, snapshot slots, payload factories, user-role/email/MFA mutation helpers, runtime probes (`field`, `password-check`, `token-counts`), token issuance and development overlay snapshot rendering → `app/Framework/Testing/AuthFixtureManager.php`, `app/Framework/Testing/AuthFixtureCatalog.php`, `app/Framework/Testing/AuthFixtureFactory.php`
- **ModuleHarnessInspector** *(RM-21)* — Per-module harness matrix derived from `ModuleInspector` route ownership, guards, assets, navigation, static readable routes and stateful auth-flow expectations (`pending_mfa`, `pending_setup`) → `app/Framework/Module/ModuleHarnessInspector.php`
- **ModuleRuntimeDocsGenerator** *(RM-23)* — Living markdown generator fed by registries, inspector, harness and lint → `app/Framework/Module/ModuleRuntimeDocsGenerator.php`
- **FormBuilder** *(RM-25)* — Declarative admin-form schema builder with `old()`/validation-error binding, uploads, field dependencies, sections, repeaters and autosave → `app/Framework/Admin/Form/FormBuilder.php`
- **DataGrid** *(RM-26)* — Reusable admin listing engine with declarative columns, search, filters, sorting, pagination, row actions, bulk actions and CSV export → `app/Framework/Admin/Grid/DataGrid.php`
- **CrudScaffoldService** *(RM-24)* — CLI-facing CRUD generator that emits guarded admin modules, `FormRequest`, entity in `app/Entities/`, migration, routes, `work/{slug}` assets and audit/soft-delete wiring over the existing module scaffold contract → `app/Framework/Admin/Crud/CrudScaffoldService.php`
- **MetadataManager** *(RM-29)* — Dynamic field contract service: supported types, typed rules, form sections/fields, grid columns/filters, select/media options and definition normalization → `app/Framework/Metadata/MetadataManager.php`
- **MetadataFieldRepository / MetadataValueRepository** *(RM-29)* — Persistence/query layer for field definitions and typed metadata values → `app/Framework/Metadata/MetadataFieldRepository.php`, `app/Framework/Metadata/MetadataValueRepository.php`
- **MediaManager / MediaRepository** *(RM-30)* — File registration, storage deletion/replacement, metadata sync and admin search/filter/list over `media_library` → `app/Framework/Media/MediaManager.php`, `app/Framework/Media/MediaRepository.php`
- **TimelineManager / TimelineRepository** *(PA-09)* — Reusable timeline primitive for start/stop/milestone capture, elapsed-time summary and workflow-driven milestone logging → `app/Framework/Timeline/TimelineManager.php`, `app/Framework/Timeline/TimelineRepository.php`
- **CatalogManager / CatalogRepository** *(PA-11)* — Governed catalog runtime with lifecycle workflow, temporal item availability, version snapshots and metadata option consumption → `app/Framework/Catalog/CatalogManager.php`, `app/Framework/Catalog/CatalogRepository.php`
- **FeatureFlagsListCommand / FeatureFlagsSetCommand** *(RM-36)* — CLI inspection and mutable default-state management for runtime feature flags → `app/Framework/Cli/Commands/FeatureFlagsListCommand.php`, `app/Framework/Cli/Commands/FeatureFlagsSetCommand.php`
- **PluginListCommand / PluginToggleCommand** *(RM-37)* — CLI inspection and runtime enable/disable for plugin manifests → `app/Framework/Cli/Commands/PluginListCommand.php`, `app/Framework/Cli/Commands/PluginToggleCommand.php`
- **DeployListCommand / DeployRunCommand** *(RM-38)* — CLI inspection and formal deployment pipeline execution over configured profiles → `app/Framework/Cli/Commands/DeployListCommand.php`, `app/Framework/Cli/Commands/DeployRunCommand.php`
- **TenancyStatusCommand** *(RM-39)* — CLI runtime snapshot of the official tenancy baseline and resolver output → `app/Framework/Cli/Commands/TenancyStatusCommand.php`
- **ScaffoldManager** *(16-CLI-Scaffold)* — Shared scaffold helper: class/module normalization, table derivation, stub rendering and file writes → `app/Framework/Cli/ScaffoldManager.php`
- **CacheBuildCommand** (`cache:build`) — Builds the configured cache artifacts (config, discovery, routes) without changing activation flags → `app/Framework/Cli/Commands/CacheBuildCommand.php`
- **CacheClearCommand** (`cache:clear`) — Clears route, bootstrap and application cache artifacts → `app/Framework/Cli/Commands/CacheClearCommand.php`
- **ConfigShowCommand** (`config:show`) — Dumps effective JSON-backed config with secret redaction and optional defaults view → `app/Framework/Cli/Commands/ConfigShowCommand.php`
- **DevToolsDisableCommand** (`devtools:disable`) — Disables debug-facing DevTools runtime flags in `app/logging`; includes `--dry-run` → `app/Framework/Cli/Commands/DevToolsDisableCommand.php`
- **HelpCommand** (`help`) — Lists all registered commands → `app/Framework/Cli/Commands/HelpCommand.php`
- **KeyGenerateCommand** (`key:generate`) — Generates a new `APP_KEY`; `--show` prints without writing → `app/Framework/Cli/Commands/KeyGenerateCommand.php`
- **MakeCommandCommand** (`make:command`) — Scaffolds auto-discovered commands under `Repository/App/Surface/{Module}/Commands/` → `app/Framework/Cli/Commands/MakeCommandCommand.php`
- **VersionCommand** (`version`) — Framework & PHP version info → `app/Framework/Cli/Commands/VersionCommand.php`
- **MakeMigrationCommand** (`make:migration`) — Scaffolds anonymous migrations under `boot-core/database/migrations/` → `app/Framework/Cli/Commands/MakeMigrationCommand.php`
- **MakeRequestCommand** (`make:request`) — Scaffolds `FormRequest` subclasses under `Repository/App/Surface/{Module}/Requests/` → `app/Framework/Cli/Commands/MakeRequestCommand.php`
- **MakeCrudCommand** (`make:crud`) — Scaffolds an administrative CRUD module on top of `FormBuilder` + `DataGrid`, with entity in `app/Entities/`, guarded routes, request class, migration, bulk/soft-delete wiring and audit-ready option → `app/Framework/Cli/Commands/MakeCrudCommand.php`

### Repository Runtime Requests
- **RolePayloadRequest** — Reusable RBAC request for role create/update with route-aware unique checks → `Repository/Framework/Roles/Requests/RolePayloadRequest.php`
- **PermissionPayloadRequest** — Reusable RBAC request for permission create/update with route-aware unique checks → `Repository/Framework/Roles/Requests/PermissionPayloadRequest.php`
- **StatusCommand** (`status`) — Unified runtime health snapshot: base platform checks + session/cache/storage/secrets/throttling + route-contract summary; supports `--json` → `app/Framework/Cli/Commands/StatusCommand.php`
- **QualityCheckCommand** (`quality:check`) — Local quality gate that runs Composer validation/audit plus route, structural, security and runtime status checks; treats `status` as warning-only for local WSDD host DNS caveats → `app/Framework/Cli/Commands/QualityCheckCommand.php`
- **SecurityCheckCommand** (`security:check`) — CSP/frontend hotspot scan for inline handlers, `javascript:` URIs, inline scripts without nonce and remaining inline-style warnings → `app/Framework/Cli/Commands/SecurityCheckCommand.php`
- **SecurityRegressionCommand** (`security:regression`) — Focused security regressions for inline JSON escaping, reset/remember invalidation and signed local cache / route-cache payloads → `app/Framework/Cli/Commands/SecurityRegressionCommand.php`
- **AutomationMvcRegressionCommand** (`automation:mvc-regression`) — Verifies Automation web/API separation, extracted UI factories, execution service, session state and centralized Requests → `app/Framework/Cli/Commands/AutomationMvcRegressionCommand.php`
- **ApiTokensSmokeCommand** (`api-tokens:smoke`) — Live-schema ownership smoke over API token creation, inactive-user revocation, FK enforcement and orphan detection → `app/Framework/Cli/Commands/ApiTokensSmokeCommand.php`
- **RouteCacheCommand** (`route:cache`) — Loads all routes then calls `Router::cacheRoutes()` → `app/Framework/Cli/Commands/RouteCacheCommand.php`
- **RouteClearCommand** (`route:clear`) — Deletes the route cache file via `Router::clearRouteCache()` → `app/Framework/Cli/Commands/RouteClearCommand.php`
- **RouteLintCommand** (`route:lint`) — Validates route casing, approved aliases and `work/{slug}` publication for active modules; supports `--json` → `app/Framework/Cli/Commands/RouteLintCommand.php`
- **RouteListCommand** (`route:list`) — Lists resolved routes with methods, URI, handler and middleware; supports JSON output → `app/Framework/Cli/Commands/RouteListCommand.php`
- **InspectHarnessCommand** (`inspect:harness`) — Emits the real per-module harness matrix over static HTML/JSON routes, assets, surfaces, guard expectations and auth-flow state profiles; supports `--json`, `--module`, `--surface` → `app/Framework/Cli/Commands/InspectHarnessCommand.php`
- **DocsSyncRuntimeCommand** (`docs:sync-runtime`) — Generates `docs/runtime-module-catalog.md` from registries + inspector + harness + lint; supports `--stdout` and `--path` → `app/Framework/Cli/Commands/DocsSyncRuntimeCommand.php`
- **FixturesAuthCommand** (`fixtures:auth`) — Catalog, apply, capture/restore slots, role/email/MFA mutation, runtime auth probes (`--field`, `--password-check`, `--token-counts`) and token issuance for official auth fixtures; supports `--json` → `app/Framework/Cli/Commands/FixturesAuthCommand.php`
- **MakeControllerCommand** (`make:controller`) — Scaffolds `Repository/App/Surface/{Module}/Controllers/{ClassName}.php` from CLI stubs; accepts `Catalog` or `App/Catalog` → `app/Framework/Cli/Commands/MakeControllerCommand.php`
- **MakeModelCommand** (`make:model`) — Scaffolds `Repository/App/Models/{ClassName}.php` with inferred or explicit `$table` → `app/Framework/Cli/Commands/MakeModelCommand.php`
- **MakeMiddlewareCommand** (`make:middleware`) — Scaffolds `Repository/App/Middleware/{ClassName}Middleware.php` implementing `MiddlewareInterface` → `app/Framework/Cli/Commands/MakeMiddlewareCommand.php`
- **MakeModuleCommand** (`make:module`) — Scaffolds a full `Repository/{App|Framework}/{Module}/` module with `Controllers/`, structured `Views/` (`pages` + `scope/pages` baseline), `front/`, `lang/`, `routes.php` and `module.php`; guard-aware surfaces publish initial `work/{slug}` assets immediately and stay coherent with module/permission/navigation registries → `app/Framework/Cli/Commands/MakeModuleCommand.php`
- **MakePolicyCommand** (`make:policy`) — Scaffolds `Repository/App/Surface/{Module}/Policies/{ClassName}.php` extending `Policy` → `app/Framework/Cli/Commands/MakePolicyCommand.php`
- **InspectModulesCommand** (`inspect:modules`) — Aggregated module inventory over `ModuleRegistry`, routes, views, assets and manifest coverage → `app/Framework/Cli/Commands/InspectModulesCommand.php`
- **InspectModuleCommand** (`inspect:module`) — Detailed per-module inspection by key, slug or name → `app/Framework/Cli/Commands/InspectModuleCommand.php`
- **InspectLintCommand** (`inspect:lint`) — Structural lint for module registration, route drift, guards, permission bridge, navigation and route contract → `app/Framework/Cli/Commands/InspectLintCommand.php`
- **MigrateCommand** (`migrate`) — Runs pending database migrations and records the batch → `app/Framework/Cli/Commands/MigrateCommand.php`
- **MigrateRollbackCommand** (`migrate:rollback`) — Reverts the latest recorded migration batch in reverse order → `app/Framework/Cli/Commands/MigrateRollbackCommand.php`
- **MigrateStatusCommand** (`migrate:status`) — Lists discovered migrations and whether they are pending/applied; emits an explicit WSDD/Docker hint when host-only DNS cannot resolve the DB target → `app/Framework/Cli/Commands/MigrateStatusCommand.php`
- **StorageCleanCommand** (`storage:clean`) — Cleans route cache plus runtime artifacts under `boot-core/storage`; supports `--dry-run` → `app/Framework/Cli/Commands/StorageCleanCommand.php`
- **QueueWorkCommand** (`queue:work`) — Processes queued jobs from the framework queue backend; supports `--queue` and `--max-jobs` → `app/Framework/Cli/Commands/QueueWorkCommand.php`
- **QueueFailedCommand** (`queue:failed`) — Lists failed jobs persisted by the framework queue; supports `--json` → `app/Framework/Cli/Commands/QueueFailedCommand.php`
- **QueueRetryCommand** (`queue:retry`) — Requeues one failed job or all failed jobs → `app/Framework/Cli/Commands/QueueRetryCommand.php`
- **ScheduleListCommand** (`schedule:list`) — Lists registered framework schedule tasks; supports `--json` → `app/Framework/Cli/Commands/ScheduleListCommand.php`
- **ScheduleRunCommand** (`schedule:run`) — Evaluates due schedule tasks and queues them; supports `--task` and `--force` → `app/Framework/Cli/Commands/ScheduleRunCommand.php`
- **AttachmentsListCommand** (`attachments:list`) — Lists canonical attachment/evidence links for one `resource_key` + `record_id`; supports `--include-detached` and `--json` → `app/Framework/Cli/Commands/AttachmentsListCommand.php`
- **AttachmentsSmokeCommand** (`attachments:smoke`) — Exercises PA-06 media/artifact linking, generated replacement and detach/delete semantics end to end → `app/Framework/Cli/Commands/AttachmentsSmokeCommand.php`
- **RetentionRunCommand** (`retention:run`) — Lists canonical retention policies or executes dry-run/nominal archive/purge passes over PA-05 surfaces; supports `--resource`, `--dry-run`, `--list-policies` and `--json` → `app/Framework/Cli/Commands/RetentionRunCommand.php`
- **RetentionSmokeCommand** (`retention:smoke`) — Exercises PA-05 dry-run plus archive-then-purge flow over media, document artifacts, detached attachments and audit rows → `app/Framework/Cli/Commands/RetentionSmokeCommand.php`
- **ReportingRunCommand** (`reporting:run`) — Queues a PA-10 report run against the unified reporting pipeline with optional attachment delivery target → `app/Framework/Cli/Commands/ReportingRunCommand.php`
- **ReportingSmokeCommand** (`reporting:smoke`) — Exercises PA-10 queued export, failed-job retry and persisted output attachment delivery end to end → `app/Framework/Cli/Commands/ReportingSmokeCommand.php`
- **TimelineSmokeCommand** (`timeline:smoke`) — Exercises PA-09 timeline semantics plus workflow-event milestone capture end to end → `app/Framework/Cli/Commands/TimelineSmokeCommand.php`
- **CatalogsSmokeCommand** (`catalogs:smoke`) — Exercises PA-11 catalog CRUD/lifecycle plus metadata-driven form/grid consumption end to end → `app/Framework/Cli/Commands/CatalogsSmokeCommand.php`
- **ExportDevelopmentOverlayCommand** (`dev:export-overlay`) — Explicitly registered framework CLI command that exports the live auth/RBAC/social-account development snapshot into `boot-core/database/create-catalyst-db.development.sql`, with Docker web-container fallback when host CLI cannot resolve the WSDD DB target → `app/Framework/Cli/Commands/ExportDevelopmentOverlayCommand.php`
- **CLI Stubs** *(16-CLI-Scaffold)* — Template files for controllers, commands, requests, migrations, models, middleware and modules → `app/Framework/Cli/Stubs/`

### Utilities
- **Translator** — i18n/translation system → [helpers-i18n.md](docs/helpers-i18n.md)
- **ConfigManager** — JSON config read/write plus named-entry resolution; synchronizes compiled config artifacts when cache is enabled from setup → [helpers-config.md](docs/helpers-config.md)
- **CacheSettings / BootstrapCacheManager** — Early cache-policy resolver and bootstrap artifact manager for config/discovery caches, owned exclusively by `cache.json` → `app/Framework/Cache/CacheSettings.php`, `app/Framework/Cache/BootstrapCacheManager.php`
- **CacheManager / FileCacheStore / ArrayCacheStore / NullCacheStore** — Reusable application-cache facade plus runtime stores; local file cache payloads now persist through signed serialized envelopes instead of broad `allowed_classes => true` deserialization → `app/Framework/Cache/CacheManager.php`, `app/Framework/Cache/FileCacheStore.php`, `app/Framework/Cache/ArrayCacheStore.php`, `app/Framework/Cache/NullCacheStore.php`
- **SignedSerializedPayload** — Shared HMAC-signed serialization helper used by file-cache and route-cache middleware snapshots to fail closed on tamper or class drift → `app/Framework/Security/SignedSerializedPayload.php`
- **InlineJson / TrustedHtml** — View-layer trust primitives for safe inline JSON and explicit trusted HTML fragments reused by layout, partial responses and DOM insertion contracts → `app/Framework/View/InlineJson.php`, `app/Framework/View/TrustedHtml.php`
- **AppEntryCatalog** — Canonical app-entry catalog shared by root dispatch, environment setup validation, and settings UI (`Setup`, `User-Access`, public aliases, dev-only entries) → `app/Helpers/Config/AppEntryCatalog.php`
- **PermissionRegistry** — Declarative module permission catalog that bridges `Gate`, `RoleRepository`, role fallbacks and resource abilities without opening a second RBAC system; now boots `ResourcePolicy` for `AbilitySubject` → `app/Framework/Authorization/PermissionRegistry.php`
- **AbilitySubject** — Lightweight authorization subject carrying `resource`, `record` and extra context for resource-level policies and generated CRUD requests/controllers → `app/Framework/Authorization/AbilitySubject.php`
- **ResourcePolicy** — Generic policy surface mapping `view-any/view/create/update/delete/restore/export/bulk/assign/sync` to `PermissionRegistry::userHasResourceAbility()` → `app/Framework/Authorization/ResourcePolicy.php`
- **AuditLogManager** — Central audit recorder for ORM mutations, repository/manual operations and framework events with actor/request context capture and CLI-safe fallbacks → `app/Framework/Audit/AuditLogManager.php`
- **AuditLogRepository** — Search/filter/export repository for `audit_logs`, used by the admin panel and reusable runtime queries → `app/Framework/Audit/AuditLogRepository.php`
- **HasOptimisticLockingTrait** *(PA-01 canonical)* — Opt-in `lock_version` contract for model-level compare-and-swap updates; stale saves raise `OptimisticLockException` and now protect shared admin entities such as documents, automations and media records → `app/Framework/Traits/HasOptimisticLockingTrait.php`
- **InteractsWithRecordClaimsTrait** *(PA-01 canonical)* — Controller helper layer for claim acquire/check/release, hidden concurrency fields and conflict hydration across admin surfaces → `app/Framework/Traits/InteractsWithRecordClaimsTrait.php`
- **RecordClaimRepository / RecordClaimManager** *(PA-01 canonical)* — Canonical expirable claim store over `record_claims`, with semantic audit events for claim/reclaim/release/conflict and owner/token enforcement for live framework modules → `app/Framework/Concurrency/RecordClaimRepository.php`, `app/Framework/Concurrency/RecordClaimManager.php`
- **Argument** — CLI argument parsing → [framework-argument.md](docs/framework-argument.md)
- **DrawBox** — Terminal formatting → [helpers-toolbox.md](docs/helpers-toolbox.md)

---

## DevTools Test Routes

### Test Features Page (`/test-features`)

Main testing dashboard at `Repository/Framework/DevTools/Controllers/TestFeaturesController::index()`. Renders `test-features.php` view (admin layout) with multiple collapsible section cards.

### App Entry Modules (`/`, `/home`, `/landing`, `/dashboard`, `/store`)

Documented application entry points now live as real app modules under `Repository/App/Surface/*` instead of piggybacking on DevTools or hardcoded framework declarations.

#### Route Contract
- Canonical root entry is `/` and is resolved by `App\Surface\Home\Controllers\HomeController::root()` + `App\Services\ApplicationEntryService`
- Lowercase public/application routes remain `/home`, `/landing` and `/store`
- Internal application workspace entry is `/dashboard`
- Las peticiones con casing conocido (`/Home`, `/Landing`, `/Dashboard`, `/Store`) se normalizan por `CanonicalPathRedirectMiddleware` + `CanonicalPathRedirector`; no deben modelarse como rutas canónicas del sistema.
- `GET /Setup` ya no forma parte del runtime como alias vivo.

#### Module Structure
- **Repository/App/Surface/Home** — public root/home entry (`/`, `/home`) plus `HomeController::api()` at JSON companion route `/api/public/home`
- **Repository/App/Surface/Landing** — narrative landing entry (`/landing`) plus `LandingController::api()` at JSON companion route `/api/public/landing`
- **Repository/App/Surface/Dashboard** — account dashboard entry (`/dashboard`) plus authenticated `DashboardController::api()` at JSON companion route `/api/public/dashboard`
- **Repository/Framework/DemoUi** — authenticated framework-owned frozen demo baseline for `/demo-ui`, with real routes for `Forms`, the full `Base UI` catalog, the full `Charts` catalog (`Apex Charts` + `Echarts`) and the full `Tables` catalog (`Static Tables`, `Custom Tables`, `DataTables`), generated theme previews, localized vendor chart/data/image/table assets under `public/assets/vendor/inspinia/`, `front/` work assets backed by runtime modules in `public/assets/js/catalyst/modules/` including `bootstrap-primitives.js`, `bootstrap-components.js`, `simplebar.js`, `demoui-charts.js`, and `demoui-tables.js`, and a local freeze contract in `Repository/Framework/DemoUi/AGENTS.md`
- **Repository/App/Surface/Store** — public catalog/storefront entry (`/store`) plus `StoreController::api()` at JSON companion route `/api/public/store`
- Each module owns `module.php`, `routes.php`, `Controllers/`, structured `Views/`, and `front/` assets
- CSS/JS publish through `FrontResourceTrait` to `public/assets/css/work/{slug}/style.css` and `public/assets/js/work/{slug}/script.js`
- Shared app domain examples now live beside these modules: `app/Entities/UserProfile.php`, `Repository/App/Repositories/UserProfileRepository.php` and `Repository/App/Services/UserProfileService.php`
- `RouteTestController` now only keeps `/index` and `/index.php` compatibility redirects to `/`
- **test-features.php** — Bootstrap-based card layout (no embedded JS; all JS moved to `test-features.js`)
- **test-features.js** — Standalone ES module; implements all interactive card behaviors, including Etapa 18 wait modal/loading/partial refresh demo
- **Flash Messages Card** — `#catalyst-flash-banners` pre-placed; managed by `flash-client.js`

#### Routes by Section

**Flash Messages Testing**
- GET `/test-features/flash` → TestFeaturesController::flash() — Render flash test card

**Toaster Testing**
- GET `/test-features/toast/info` → ToasterTestController::info()
- GET `/test-features/toast/success` → ToasterTestController::success()
- GET `/test-features/toast/warning` → ToasterTestController::warning()
- GET `/test-features/toast/error` → ToasterTestController::error()

**Modal Testing**
- GET `/test-features/modal/sample-content` → ModalTestController::modalSampleContent()
- GET `/test-features/modal/form-content` → ModalTestController::modalFormContent()
- POST `/test-features/modal/form-submit` → ModalTestController::modalFormSubmit()

**Form Events Testing**
- POST `/test-features/form-demo` → FormEventTestController::formDemoStore() — Handles `save`, `validate`, `refresh` and `redirect` via `HandlesFormEventsTrait`

**Infrastructure Testing**
- GET `/test-layout` → InfraTestController::testLayout() — Standalone layout smoke page
- GET `/test-features/layout-test` → InfraTestController::testLayout() — Layout smoke alias linked from DevTools
- GET `/test-features/module-designer` → Operations `ModuleDesignerController::legacyIndex()` — Legacy alias that redirects to `/operations/module-designer`
- POST `/test-features/module-designer/preview` → Operations `ModuleDesignerController::preview()` — Registry-aware scaffold preview via legacy compatibility endpoint
- POST `/test-features/module-designer/generate` → Operations `ModuleDesignerController::generate()` — Real module generation with immediate work-asset publish via legacy compatibility endpoint
- GET `/test-features/e-helper` → InfraTestController::testEscapeHelper() — Output escaping smoke test
- GET `/test-features/json` → InfraTestController::testJson()
- GET `/test-features/json-success` → InfraTestController::testJsonSuccess()
- GET `/test-features/json-error` → InfraTestController::testJsonError()
- GET `/test-features/validation-error` → InfraTestController::testValidationError()
- GET `/test-features/api-response` → InfraTestController::testApiResponse()
- GET `/test-features/logger-email` → InfraTestController::testLoggerEmail()
- GET `/test-features/route-cache` → InfraTestController::testRouteCache()
- GET `/test-features/cors-headers` → InfraTestController::testCorsHeaders()

**Database Testing**
- GET `/test-features/db/users` → DatabaseTestController::users() — List users
- GET `/test-features/db/count` → DatabaseTestController::count() — User count
- GET `/test-features/db/raw` → DatabaseTestController::raw() — Raw query demo

**i18n Testing**
- GET `/test-features/i18n/messages` → I18nTestController::messages() — Translation demo
- GET `/test-features/i18n/dates` → I18nTestController::dates() — Date formatting

**Validator Testing**
- GET `/test-features/validator/demo` → ValidatorTestController::demo() — Validation form
- POST `/test-features/validator/submit` → ValidatorTestController::submit() — Validate input

**File Upload Testing** *(Etapa 17)*
- POST `/test-features/upload` → UploadTestController::upload() — AJAX upload with `UploadedFile`, `FileValidator`, UUID storage and JSON response

**JS Enhancements Testing** *(Etapa 18)*
- GET `/test-features/api/js-enhancements/partial-refresh` → ToasterTestController::apiJsEnhancementPartialRefresh() — delayed JSON response with `withHtml()` partial DOM replacement

**Mail Testing**
- GET `/test-features/mail/form` → MailTestController::form() — Email form
- POST `/test-features/mail/send` → MailTestController::send() — Send test email

**RBAC Testing**
- GET `/test-features/rbac/check` → RbacTestController::check() — Role/permission demo
- GET `/test-features/rbac/list` → RbacTestController::list() — All gates

**ORM Testing** *(new section)*
- GET `/test-features/orm/status` → OrmTestController::ormStatus() — ORM system status
- GET `/test-features/orm/find-or-fail` → OrmTestController::ormFindOrFail() — findOrFail() demo
- GET `/test-features/orm/user-demo` → OrmTestController::ormUserDemo() — User model with relationships
- POST `/test-features/orm/create` → OrmTestController::ormCreate() — Create model instance
- POST `/test-features/orm/update` → OrmTestController::ormUpdate() — Update model instance
- POST `/test-features/orm/delete-latest` → OrmTestController::ormDeleteLatest() — Delete latest record

### UML Diagram Routes (`/uml`)

- GET `/uml` → UmlController::index() — Mermaid ERD visualization
- GET `/uml/schema` → UmlController::schema() — Database schema diagram

### Route Listing (`/test-features/routes`)

- GET `/test-features/routes` → RouteTestController::list() — Display all registered routes with HTTP methods and middleware

---

## Bootstrap Flow

```
Entry Point (public/index.php or public/cli.php)
  ↓
Fallback load error-catcher.php if not already loaded
  ↓
error-catcher.php:
  - sys-constant.php         (defines IS_DEVELOPMENT, etc.)
  - spl-autoload.php         (SPL autoloader for pre-Composer classes)
  - env-constant.php         (reads .env, may throw EnvironmentException)
  - ErrorCatcher             (registers handlers)
  ↓
Load Composer autoloader (vendor/autoload.php)
  ↓
dump-function.php (loaded by Composer)
  ↓
Kernel::getInstance()->bootstrap()->run()
```

→ See [docs/entry-points.md](docs/entry-points.md)

---

**End of Structure Index**
