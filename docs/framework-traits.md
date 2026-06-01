# `Catalyst\Framework\Traits`

This file inventories the traits that actually live under `app/Framework/Traits/` and clarifies their real consumers.

## Trait: SingletonTrait

**File**: `app/Framework/Traits/SingletonTrait.php`

### Purpose

Provides a shared `getInstance()` pattern for framework singletons.

### Public API

- `getInstance(mixed ...$args): static`
- `setInstance(object $instance): void`
- `resetInstance(): void`
- `__wakeup(): void`

### Protected / private internals

- `__construct()`
- `getArguments(): array`
- `__clone()`

### Notes

- `setInstance()` is guarded against production usage.
- This trait is widely used across the framework: `Kernel`, `Router`, `Request`, `View`, `Logger`, `ConfigManager`, `DatabaseManager`, `SessionManager`, `AuthManager`, `NotificationManager`, `Translator`, and the error stack.

## Trait: OutputCleanerTrait

**File**: `app/Framework/Traits/OutputCleanerTrait.php`

### Purpose

Normalizes output buffers before error rendering so partial HTML/JSON does not leak into the final error response.

### API

- `cleanOutput(): void`

### Live consumers

- `ErrorHandler`
- `ExceptionHandler`
- `ShutdownHandler`

## Trait: ErrorTypeTrait

**File**: `app/Framework/Traits/ErrorTypeTrait.php`

### Purpose

Maps PHP error constants to readable labels.

### API

- `getErrorType(int $errorLevel): string`

### Live consumers

- `ErrorHandler`
- `ShutdownHandler`

## Trait: HandlesFormEventsTrait

**File**: `app/Framework/Traits/HandlesFormEventsTrait.php`

### Purpose

Routes a POST `_event` value to a protected controller method named `on{Event}`.

### API

- `dispatchEvent(): Response`
- `onDefault(): Response`
- `eventName(): ?string`

### Runtime behavior

- Unknown events return a `400` JSON response.
- The trait does not auto-wire any route by itself; a controller action must call `dispatchEvent()`.

### Confirmed live consumer

- `Repository/Framework/DevTools/Controllers/FormEventTestController.php`

## Trait: FrontResourceTrait

**File**: `app/Framework/Traits/FrontResourceTrait.php`

### Purpose

Copies module-local `front/script.js` and `front/style.css` files into the public work asset tree and exposes the module slug to the view layer.

### API

- `resolveSlug(): string`
- `deployFrontAssets(): void`

### Runtime behavior

- Source files:
  - `{Module}/front/script.js`
  - `{Module}/front/style.css`
- Published destinations:
  - `public/assets/js/work/{slug}/script.js`
  - `public/assets/css/work/{slug}/style.css`
- Missing source files are skipped silently.
- `Controller::view()` now calls `deployFrontAssets()` automatically before rendering.
- The shared slug is reset on every render so controllers without a `front/`
  directory cannot leak a previous module's work assets into the next response.
- Publishing compares file hashes, not only file size, so same-length edits still deploy.

### Project rule

- Module- or view-specific CSS/JS belongs in the module-local `front/` directory.
- Shared framework shell assets (`boot-core/template` layouts, status bar, auth/admin shell)
  may remain in their canonical global paths.
- Third-party remote vendors required at runtime are allowed only when the module work
  script orchestrates them explicitly and no approved local copy exists.

## Trait: LoadsFeatureConfigTrait

**File**: `app/Framework/Traits/LoadsFeatureConfigTrait.php`

### Purpose

Loads one named config section through `ConfigManager`, merges it with defaults, and degrades gracefully when JSON is missing or malformed.

### API

- `loadFeatureSection(string $section, array $defaults = []): array`
- `warnMissingConfig(string $section): void`

### Runtime behavior

- caches the resolved section per instance
- logs warnings/errors instead of breaking the request
- injects `'enabled' => true` when the key is absent

### Confirmed live consumers

- `CorsMiddleware`
- `WebSocketBootMiddleware`

## Trait: HasTimestampsTrait

**File**: `app/Framework/Traits/HasTimestampsTrait.php`

### Purpose

Registers ORM lifecycle hooks that stamp `created_at` and `updated_at`.

### API

- `bootHasTimestampsTrait(): void`
- `setCreatedAt(): void`
- `setUpdatedAt(): void`
- `freshTimestamp(): string`
- `touch(): bool`

### Runtime behavior

- hooks into `Model::bootIfNeeded()`
- writes string timestamps for storage
- relies on model casts when reading back as `DateTimeImmutable`

### Current status

Supported framework extension point, but no confirmed repository model currently uses it. `User` explicitly relies on MySQL-managed timestamp columns instead.

## Trait: HasAuditLogTrait

**File**: `app/Framework/Traits/HasAuditLogTrait.php`

### Purpose

Stamps `created_by`, `updated_by`, and `deleted_by` from the current session user through ORM hooks.

### API

- `bootHasAuditLogTrait(): void`
- `stampCreatedBy(): void`
- `stampUpdatedBy(): void`
- `stampDeletedBy(): void`
- `createdBy(): ?int`
- `updatedBy(): ?int`
- `deletedBy(): ?int`
- `resolveCurrentUserId(): ?int`

### Runtime behavior

- participates in `inserting`, `updating`, and `deleting` hooks
- cooperates with `HasSoftDeletesTrait` so `deleted_by` can be persisted during soft delete

### Current status

Supported framework extension point, but no confirmed repository model currently uses it in the active runtime.

## Trait: HasSoftDeletesTrait

**File**: `app/Framework/Traits/HasSoftDeletesTrait.php`

### Purpose

Overrides `Model::delete()` to set `deleted_at` instead of hard-deleting, and exposes soft-delete query helpers.

### Constants

- `SOFT_DELETES = true`
- `DELETED_AT = 'deleted_at'`

### API

- `bootHasSoftDeletesTrait(): void`
- `delete(): bool`
- `forceDelete(): bool`
- `restore(): bool`
- `trashed(): bool`
- `withTrashed(): ModelQueryBuilder`
- `onlyTrashed(): ModelQueryBuilder`

### Important corrections

- The hard-delete bypass method is `forceDelete()`, not `permanentDelete()`.
- `ModelQueryBuilder` reads the trait constants directly to decide whether to auto-apply the `deleted_at IS NULL` scope.

### Current status

Supported framework extension point, but no confirmed repository model currently uses it in the active runtime.

## Trait: HasOptimisticLockingTrait

**File**: `app/Framework/Traits/HasOptimisticLockingTrait.php`

### Purpose

Activates model-level optimistic locking through a `lock_version` column.

### Constants

- `OPTIMISTIC_LOCKING = true`
- `LOCK_VERSION = 'lock_version'`

### API

- `bootHasOptimisticLockingTrait(): void`
- `currentLockVersion(): ?int`

### Runtime behavior

- seeds `lock_version=1` on insert when absent
- enables compare-and-swap updates inside `Model::save()`
- stale updates throw `OptimisticLockException`
- intended for shared entities or generated CRUD resources that need conflict detection

### Confirmed live consumers

- `RecordClaim`
- `DocumentTemplate`
- `AutomationRule`
- `MediaItem`
- `MetadataFieldDefinition`

## Trait: InteractsWithRecordClaimsTrait

**File**: `app/Framework/Traits/InteractsWithRecordClaimsTrait.php`

### Purpose

Provides the canonical controller helpers for claim acquire, owner/token enforcement, release and conflict state hydration.

### API

- `acquireRecordClaim(string $resourceKey, int $recordId, array $metadata = []): array`
- `assertRecordClaimAvailable(string $resourceKey, int $recordId, Request $request): ?array`
- `releaseRecordClaim(string $resourceKey, int $recordId, Request $request, ?string $reason = null): void`
- `buildRecordClaimContext(?array $claim): ?array`
- `concurrencyHiddenFields(?array $claim, ?int $lockVersion = null): array`
- `rememberConcurrencyConflict(Request $request, RuntimeException $e, string $bag = 'default'): void`

### Confirmed live consumers

- `DocumentTemplateController`
- `AutomationRuleController`
- `MediaLibraryController`
- `MetadataFieldController`
- `RolesController`
- `PermissionsController`

## Scope note

`SetupAccessTrait` is not part of this inventory because it lives under `app/Framework/Middleware/`, not under `app/Framework/Traits/`.
