# Catalyst Framework — API Reference

> **Purpose**: Index of the framework's public API documentation.
> **Last Updated**: 2026-05-19

---

## How this file works

Catalyst's API documentation is split across topical files in `docs/`. Each file documents the classes and methods of one subsystem, following the convention **`{space}-{subsystem}.md`**:

- `framework-*.md` — `Catalyst\Framework\*` classes (core)
- `helpers-*.md` — `Catalyst\Helpers\*` classes (system utilities)
- `repository-*.md` — `Catalyst\Repository\*` modules (framework-owned UI modules)

For an exhaustive dictionary of every class, method, and property (including private ones), read `STRUCTURE.md` at the project root.

---

## Framework Core (`Catalyst\Framework\*`)

| Subsystem | File | Covers |
|-----------|------|--------|
| HTTP / Request / Response | *(covered inline in)* `docs/framework-controllers.md` | `Request`, `Response`, `JsonResponse`, `HtmlResponse`, `RedirectResponse` |
| Kernel | `docs/kernel.md` | `Kernel::bootstrap()`, `run()`, route loading, 404/405 handling |
| Controllers | `docs/framework-controllers.md` | `Controller` base (JSON, flash, input, logging, validate, authorize), constructor DI via route container, typed `FormRequest` auto-resolution |
| Views | `docs/framework-view.md` | `View`, template resolution, multi-layout, `ViewException` |
| Session | `docs/framework-session.md` | `SessionManager`, `FlashMessage` (regular + persistent + toast) |
| Notification | `docs/framework-notification.md` | `NotificationType`, `Notification`, `NotificationBag` (`NotificationPosition` remains legacy/residual) |
| Event | `docs/framework-event.md` | `EventBus`, `EventEnvelope`, `EventListenerInterface`, queued listener bridge |
| Queue | `docs/framework-queue.md` | `QueueManager`, `QueueRepository`, `QueueWorker`, queue jobs, failed jobs |
| Schedule | `docs/framework-schedule.md` | `ScheduleRegistry`, `ScheduledTask`, `ScheduleRunner`, `CronExpression` |
| Auth | `docs/framework-auth.md` | `AuthManager`, `UserProvider`, `RememberMe`, OAuth providers |
| Mail | `docs/framework-mail.md` | `MailManager`, `MailMessage`, `MailTemplate` (`MailAttachment` remains residual/legacy) |
| Database | `docs/framework-database.md` | `DatabaseManager`, `Connection`, `QueryBuilder`, `Transaction` |
| Concurrency | `docs/framework-concurrency.md` | `HasOptimisticLockingTrait`, `RecordClaimRepository`, `RecordClaimManager`, `OptimisticLockException` |
| WebSocket | `docs/framework-websocket.md` | `WebSocketServer`, `WebSocketToken` (HMAC), `WebSocketPublisher` |
| Traits | `docs/framework-traits.md` | `SingletonTrait`, `FrontResourceTrait`, `ErrorTypeTrait`, `HandlesFormEventsTrait`, `LoadsFeatureConfigTrait` |
| Enums | `docs/framework-enums.md` | `AppEnvironment` and framework-wide enums |
| CLI Argument parser | `docs/framework-argument.md` | CLI entry point parser + built-in command surface (`help`, `status`, `security:check`, scaffolds, migrations) |

---

## System Helpers (`Catalyst\Helpers\*`)

| Subsystem | File | Covers |
|-----------|------|--------|
| Validation | `docs/helpers-validation.md` | `Validator`, `ValidationRunner`, `RuleParser`, 6 rule families, `ValidationException`, `FormRequest` runtime contract |
| i18n | `docs/helpers-i18n.md` | `Translator`, `TranslationLoader`, global `__()` / `t()` / `format_date()` |
| Config | `docs/helpers-config.md` | `ConfigManager` (per-section JSON, `writeSection()`, `readDefaults()`) |
| Debug | `docs/helpers-debug.md` | `Dumper`, themes, formatters |
| Error | `docs/helpers-error.md` | `ErrorCatcher`, `ErrorHandler`, `ExceptionHandler`, `ShutdownHandler`, `ErrorLogger`, `ErrorOutput` |
| Exceptions | `docs/helpers-exceptions.md` | Custom exception hierarchy |
| Log | `docs/helpers-log.md` | `Logger` (8 PSR-3 levels, file output) |
| ToolBox | `docs/helpers-toolbox.md` | General utilities |

---

## Repository Modules (`Catalyst\Repository\*`)

| Module | File | Covers |
|--------|------|--------|
| Auth | `docs/repository-auth.md` | `/login`, `/register`, `/logout`, password reset, email verification, OAuth callbacks |
| DevTools | `docs/repository-devtools.md` | `/test-features` UI Test Harness, `/route-test` |
| Notification | `docs/repository-notification.md` | Status bar panel UI + REST endpoints (index/unreadCount/markRead/markAllRead) + WS token bootstrap |

## Runtime-driven module documentation

Some framework modules no longer have or need dedicated split `repository-*.md` files. Their canonical contract is runtime-driven and lives across these sources:

- `docs/runtime-module-catalog.md` — module keys, guards, representative HTML/JSON routes, permissions, settings, assets and feature-flag/plugin state
- `STRUCTURE.md` — controllers, managers, repositories, entities and canonical surfaces
- `TERMINAL.md` — CLI inspection, harness and operational commands tied to those modules

Current runtime-driven modules:

- Settings — `/setup`, `/health`, config save surfaces, FTP/SFTP probes
- Roles / Permissions — RBAC administration and user-role assignment
- Audit — `/audit-log`
- Operations — `/operations`, feature flags, plugins, deployments, tenancy
- Media — `/media-library`, `/media-fields`
- Documents — `/document-templates`, preview/export/workflow/version restore
- Automation — `/automation-rules`, manual run, workflow/version integration
- API Platform — `/api-platform`, `/api/v1/*`, token administration, workflow/version endpoints

---

## Cross-cutting references

- **Architecture overview**: `docs/architecture.md`
- **Composer / PSR-4 mapping**: `docs/composer.md`
- **Entry points** (`public/index.php`, `public/cli.php`): `docs/entry-points.md`
- **Runtime module catalog**: `docs/runtime-module-catalog.md`
- **CLI/runtime operations**: `TERMINAL.md`
- **Update log** (framework-side changelog): `docs/update-log.md`
- **Agent contract**: `AGENTS.md`

---

## Conventions

All public API follows these rules across every subsystem:

- **Strict types**: Every file declares `declare(strict_types=1);`
- **PSR-4 autoloading**: Namespace matches directory structure
- **XSS escape on output**: Every template echo uses `e($value)` — see `boot-core/global-function/dump-function.php`
- **Singleton pattern** via `SingletonTrait` for managers (SessionManager, Translator, View, DatabaseManager, MailManager, AuthManager, Gate, etc.)
- **JSON response envelope**: `{success:bool, message:string, data?:mixed, errors?:array, meta?:array}` — constructed via `JsonResponse::api()`, `::success()`, `::error()`, `::validation()`
- **Flash vs Toast**: `flash()->error(...)` / `flash()->success(...)` for inline banners that require re-render (history + dismiss tracking); `$this->toast('success', ...)` for ephemeral popup confirmations drained by `_catalyst-init.php` on the next page load. Backed by `FlashMessage` and `ToastQueue` (SRP-split).

---

## Quick-start examples

### Return a JSON success with a toaster

```php
return $this->jsonSuccess($user, 'User created')
    ->withNotification(
        $this->toaster('success', 'User created successfully!')
    );
```

### Validate request input with a FormRequest

```php
public function store(StoreUserRequest $request): Response
{
    $data = $request->validated();
}
```

### Authorize an action

```php
$this->authorize('manage-users');   // throws ForbiddenException if denied
```

### Translate a string

```php
echo e(__('messages.welcome', ['name' => $user->name]));
```

### Send mail

```php
MailManager::getInstance()
    ->init()
    ->createMessage()
    ->to($user->email)
    ->subject(__('mail.welcome.subject'))
    ->body($view->render('mail.welcome', ['user' => $user]))
    ->send();
```

For full method signatures and behaviour contracts, open the corresponding `docs/*.md` file.
