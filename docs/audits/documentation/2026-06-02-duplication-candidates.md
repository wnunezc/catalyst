# Duplication Candidates Audit

Date: 2026-06-02
Scope: Candidate responsibility, feature and functionality duplication across Catalyst PHP runtime.

## Classification

- Confirmed duplicate: same responsibility and same functional boundary.
- Overlap: related responsibility but different owner or lifecycle.
- Intentional facade: repeated wording exists because one class delegates or exposes a simpler API.
- Naming drift: same concept appears under different names.
- No action: candidate was reviewed and rejected.

## Review Rules

- Do not rename or move code in this audit.
- Do not change runtime logic.
- Record exact files and symbols.
- Every confirmed duplicate needs a proposed owner.

## Candidates

| Status | Category | Primary Owner | Other Symbols | Evidence | Recommendation |
|---|---|---|---|---|---|
| Overlap | Responsibility | `Catalyst\Repository\Automation\Requests\AutomationRuleTransitionRequest` | `Catalyst\Repository\Documents\Requests\DocumentTemplateTransitionRequest` | Same responsibility text found in inline PHP docblocks: "Expose and validate the transition key and optional operator notes." | Keep domain-specific request classes while workflow transitions remain resource-specific; consider a shared request concern only if a third resource repeats the same payload contract. |
| Overlap | Auth and authorization | `Catalyst\Framework\Auth\AuthManager` | `UserProvider`, `TokenRepository`, `MfaManager`, `OAuthManager`, `Gate`, `PermissionRegistry`, `RoleMiddleware` | Auth package owns identity/session primitives; authorization package and route middleware own permission checks and request gating. Runtime routes show auth-flow routes plus permission-protected admin/workspace routes. | Keep split; document that Auth authenticates actors while Authorization and middleware enforce ability boundaries. |
| Overlap | Routes and middleware | `Catalyst\Framework\Route\Router` | `RouteDispatcher`, `RouteCollection`, `RouteCompiler`, `RouteGroup`, `UrlGenerator`, `GlobalMiddlewareRegistrar`, `CoreMiddleware` | Responsibilities share routing concerns but separate registration, compilation, dispatch and cross-cutting middleware. `route:list --json` and `route:lint` are coherent. | Keep split if each class retains one boundary; ensure `docs/routing.md`, `docs/kernel.md` and `docs/middleware.md` do not describe one owner as doing all routing work. |
| Intentional facade | Views and frontend resources | `Catalyst\Framework\View\View` | `ModuleViewPathRegistrar`, `ViewTokenRenderer`, `TrustedHtml`, `InlineJson`, `FrontResourceTrait`, `ModuleAssetPublisher` | View rendering, tokenized templates, trusted HTML, inline JSON transport and work asset publishing are adjacent but separate responsibilities. Runtime inventory reports 230 templates and 54 scripts. | Keep facade/delegate model; canonical docs should route CSP and `data-*` rules to `docs/security-conventions.md` instead of duplicating them in every view doc. |
| Overlap | Modules and manifests | `Catalyst\Framework\Module\ModuleRegistry` | `BuiltInModuleDeclarations`, `ModuleDiscovery`, `ModuleInspector`, `ModuleHarnessInspector`, `ModuleLinter`, `NavigationRegistry`, `PermissionRegistry` | Runtime module catalog is generated from module, navigation, permission, inspector, harness and lint registries. | Keep registry/inspector/linter split; document `runtime-module-catalog.md` as generated truth and avoid static snapshots as live sources. |
| Intentional facade | CLI and scaffolding | `Catalyst\Framework\Cli\CliKernel` | `CommandRegistry`, `AbstractCommand`, `ScaffoldManager`, `CrudScaffoldService`, `ModuleScaffoldService`, `CrudFileFactory`, `ModuleFileFactory` | CLI help lists command discovery and scaffolding commands; factories render files while services orchestrate scaffolds. | Keep CLI entry facade thin; document scaffolding under workflow docs and keep generated file ownership in factory docs. |
| Overlap | Database, ORM and migrations | `Catalyst\Framework\Database\DatabaseManager` | `Connection`, `Model`, `ModelQueryBuilder`, `MigrationRunner`, relation classes, model concerns, repositories under framework modules | Database package owns connection/query/model primitives; repositories own module persistence. | Keep ORM primitive and repository boundaries distinct; canonical database docs should avoid presenting module repositories as ORM internals. |
| Overlap | Logging, errors and debug | `Catalyst\Helpers\Error\ErrorHandler` | `ExceptionHandler`, `ErrorLogger`, `ShutdownHandler`, `Logger`, `LoggerConfigurator`, `LoggerContextSanitizer`, debug dump formatters | Error helpers handle exception/runtime capture; log helpers format, sanitize and write operational logs; debug helpers render developer diagnostics. | Keep separate runtime surfaces; document production output and debug output as different concerns. |
| Overlap | Documents, media and attachments | `Catalyst\Framework\Attachment\AttachmentManager` | `DocumentTemplateManager`, `DocumentTemplateRepository`, `MediaManager`, `MediaRepository`, `MetadataManager`, `ReportingManager` | Documents and media own resource creation; attachments relate generated artifacts or media items to resources; reporting uses persisted output attachments. | Keep attachment layer as cross-resource relationship owner; docs should avoid describing media or document modules as owning generic attachment lifecycle. |
| Overlap | Workflow, automation, queue and schedule | `Catalyst\Framework\Workflow\WorkflowManager` | `AutomationManager`, `QueueManager`, `ScheduleRunner`, `EventBus`, `RunScheduledAutomationRulesJob`, `InvokeQueuedListenerJob` | Automation rules can be triggered manually/API/schedule; queue and schedule own asynchronous execution; events trigger listeners. CLI includes `queue:*`, `schedule:*`, `automation:mvc-regression`. | Keep lifecycle boundaries separate; canonical docs should state which subsystem triggers, queues, executes and records state. |
| Overlap | Notification, presence and timeline | `Catalyst\Framework\Notification\NotificationManager` | `PresenceManager`, `TimelineManager`, `WebSocketPublisher`, notification controllers, presence controller | Notification API routes, websocket token route and presence heartbeat route are separate runtime surfaces; timeline records milestones. | Keep notification delivery, presence heartbeat and timeline history separate; document websocket as notification transport support, not timeline ownership. |
| Overlap | Settings, operations and setup | `Catalyst\Repository\Settings\Controllers\ConfigController` | Operations controllers, setup config writers/probes, `PlatformAppearanceManager`, `FeatureFlagManager`, `PluginManager`, `LocalizationManager` | Settings surface owns first-run setup and health; Operations owns post-setup platform controls such as feature flags, plugins, appearance, deployments and locale tools. | Keep setup vs operations split; docs should route first-run setup to checklist/workflow docs and platform administration to operations/appearance/i18n docs. |
