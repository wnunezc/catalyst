# Catalyst\Framework\Traits

## Purpose

Document reusable traits mixed into models, controllers and framework services.

## Runtime Owners

| Concern | Owner |
|---|---|
| Stamps missing tenant identifiers and rejects cross-tenant inserts. | `Catalyst\Framework\Traits\BelongsToTenantTrait` |
| Maps PHP error-level constants to readable labels. | `Catalyst\Framework\Traits\ErrorTypeTrait` |
| Publishes module-scoped frontend assets and exposes their module slug to views. | `Catalyst\Framework\Traits\FrontResourceTrait` |
| Routes submitted form event names to controller handler methods. | `Catalyst\Framework\Traits\HandlesFormEventsTrait` |
| Stamps actor identifiers and records model lifecycle mutations. | `Catalyst\Framework\Traits\HasAuditLogTrait` |
| Rejects stale writes and increments optimistic lock versions. | `Catalyst\Framework\Traits\HasOptimisticLockingTrait` |
| Replaces destructive model deletion with restorable timestamp markers. | `Catalyst\Framework\Traits\HasSoftDeletesTrait` |
| Maintains creation and update timestamps through model lifecycle hooks. | `Catalyst\Framework\Traits\HasTimestampsTrait` |
| Acquires, validates, releases and exposes concurrency claim state. | `Catalyst\Framework\Traits\InteractsWithRecordClaimsTrait` |
| Loads feature configuration once per instance with resilient defaults. | `Catalyst\Framework\Traits\LoadsFeatureConfigTrait` |
| Resets output buffering before framework error rendering. | `Catalyst\Framework\Traits\OutputCleanerTrait` |
| Provides controlled singleton instantiation, replacement and reset behavior. | `Catalyst\Framework\Traits\SingletonTrait` |

## Current Behavior

This file is regenerated from current PHP docblocks and the runtime inventory scope for `Catalyst\Framework\Traits`. It intentionally replaces stale historical API notes with the classes and methods that exist in code now.

## API From Docblocks

### `Catalyst\Framework\Traits\BelongsToTenantTrait`

- File: `app/Framework/Traits/BelongsToTenantTrait.php`
- Kind: `trait`
- Summary: Applies tenant ownership to tenant-scoped models before insertion.
- Responsibility: Stamps missing tenant identifiers and rejects cross-tenant inserts.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `bootBelongsToTenantTrait()` | `protected` | Registers the model hook that enforces tenant ownership on insert. | n/a |

### `Catalyst\Framework\Traits\ErrorTypeTrait`

- File: `app/Framework/Traits/ErrorTypeTrait.php`
- Kind: `trait`
- Summary: Trait ErrorTypeTrait
- Responsibility: Maps PHP error-level constants to readable labels.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `getErrorType()` | `private` | Map PHP error level to text description. | Map PHP error level to text description. |

### `Catalyst\Framework\Traits\FrontResourceTrait`

- File: `app/Framework/Traits/FrontResourceTrait.php`
- Kind: `trait`
- Summary: FrontResourceTrait — on-demand front asset deployment
- Responsibility: Publishes module-scoped frontend assets and exposes their module slug to views.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `resolveSlug()` | `protected` | Derive a lowercase slug from the module segment of the class namespace. Namespace convention: …\{Module}\Controllers\{ClassName} The segment immediately before "Controllers" is used as the slug. Examples: Catalyst\Repository\DevTools\Controllers\Foo → "devtools" App\Invoices\Controllers\Bar → "invoices" Falls back to the lowercased class basename when the convention is not met. | Derive a lowercase slug from the module segment of the class namespace. Namespace convention: …\{Module}\Controllers\{ClassName} The segment immediately before "Controllers" is used as the slug. Examples: Catalyst\Repository\DevTools\Controllers\Foo → "devtools" App\Invoices\Controllers\Bar → "invoices" Falls back to the lowercased class basename when the convention is not met. |
| `deployFrontAssets()` | `protected` | Copy front/script.js and front/style.css to their public destinations if the source filesize differs from the currently published file. Also shares the resolved slug as $moduleSlug with the View layer so that _catalyst-init.phtml can conditionally load the published assets. | Copy front/script.js and front/style.css to their public destinations if the source filesize differs from the currently published file. Also shares the resolved slug as $moduleSlug with the View layer so that _catalyst-init.phtml can conditionally load the published assets. |

### `Catalyst\Framework\Traits\HandlesFormEventsTrait`

- File: `app/Framework/Traits/HandlesFormEventsTrait.php`
- Kind: `trait`
- Summary: HandlesFormEventsTrait — Event-driven form routing for controllers
- Responsibility: Routes submitted form event names to controller handler methods.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `dispatchEvent()` | `protected` | Dispatch the incoming POST event to the appropriate handler method. Reads `_event` from POST input and calls `on{EventName}()` on `$this`. Event name is ucfirst'd: event "saveUser" → method "onSaveUser()". | Dispatch the incoming POST event to the appropriate handler method. Reads `_event` from POST input and calls `on{EventName}()` on `$this`. Event name is ucfirst'd: event "saveUser" → method "onSaveUser()". |

### `Catalyst\Framework\Traits\HasAuditLogTrait`

- File: `app/Framework/Traits/HasAuditLogTrait.php`
- Kind: `trait`
- Summary: HIPAA-compliant audit trail for Model subclasses.
- Responsibility: Stamps actor identifiers and records model lifecycle mutations.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `bootHasAuditLogTrait()` | `protected` | Called once per class by Model::bootIfNeeded(). | n/a |
| `stampCreatedBy()` | `public` | Set created_by if not already set (preserves explicit overrides). | Set created_by if not already set (preserves explicit overrides). |
| `stampUpdatedBy()` | `public` | Refresh updated_by on every update. | Refresh updated_by on every update. |
| `stampDeletedBy()` | `public` | Set deleted_by before the row is soft-deleted or hard-deleted. For hard deletes the column value is never persisted — this is a no-op. | Set deleted_by before the row is soft-deleted or hard-deleted. For hard deletes the column value is never persisted — this is a no-op. |
| `createdBy()` | `public` | Returns the user identifier that created the model. | Returns the user identifier that created the model. |
| `updatedBy()` | `public` | Returns the user identifier that last updated the model. | Returns the user identifier that last updated the model. |
| `deletedBy()` | `public` | Returns the user identifier that deleted the model. | Returns the user identifier that deleted the model. |
| `resolveCurrentUserId()` | `protected` | Resolve the current authenticated user ID from the session. Returns null when: - PHP session is not active - No user is logged in (user_id absent in session) Override this method in your model to use a different resolution strategy (e.g. reading from a request context object, JWT claim, etc.). | Resolve the current authenticated user ID from the session. Returns null when: - PHP session is not active - No user is logged in (user_id absent in session) Override this method in your model to use a different resolution strategy (e.g. reading from a request context object, JWT claim, etc.). |

### `Catalyst\Framework\Traits\HasOptimisticLockingTrait`

- File: `app/Framework/Traits/HasOptimisticLockingTrait.php`
- Kind: `trait`
- Summary: Adds lock-version checks to model updates.
- Responsibility: Rejects stale writes and increments optimistic lock versions.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `bootHasOptimisticLockingTrait()` | `protected` | Registers the update hook that validates and increments lock versions. | n/a |
| `currentLockVersion()` | `public` | Returns the model's current lock version. | Returns the model's current lock version. |

### `Catalyst\Framework\Traits\HasSoftDeletesTrait`

- File: `app/Framework/Traits/HasSoftDeletesTrait.php`
- Kind: `trait`
- Summary: Soft-delete support for Model subclasses.
- Responsibility: Replaces destructive model deletion with restorable timestamp markers.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `bootHasSoftDeletesTrait()` | `protected` | Declares the soft-delete trait boot hook. | n/a |
| `delete()` | `public` | Soft-delete the model by setting the deleted_at column. Fires the deleting / deleted hooks so other traits (e.g. HasAuditLogTrait) can inject fields (deleted_by) before the UPDATE is executed. | Soft-delete the model by setting the deleted_at column. Fires the deleting / deleted hooks so other traits (e.g. HasAuditLogTrait) can inject fields (deleted_by) before the UPDATE is executed. |
| `forceDelete()` | `public` | Permanently remove the row from the database. | Permanently remove the row from the database. |
| `restore()` | `public` | Restore a soft-deleted model by clearing deleted_at (and deleted_by). | Restore a soft-deleted model by clearing deleted_at (and deleted_by). |
| `trashed()` | `public` | Check whether this model has been soft-deleted. | Check whether this model has been soft-deleted. |
| `withTrashed()` | `public` | Start a query that includes soft-deleted rows. | n/a |
| `onlyTrashed()` | `public` | Start a query that returns only soft-deleted rows. | n/a |

### `Catalyst\Framework\Traits\HasTimestampsTrait`

- File: `app/Framework/Traits/HasTimestampsTrait.php`
- Kind: `trait`
- Summary: Automatic timestamp management for Model subclasses.
- Responsibility: Maintains creation and update timestamps through model lifecycle hooks.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `bootHasTimestampsTrait()` | `protected` | Called once per class by Model::bootIfNeeded(). Registers inserting and updating hooks. | n/a |
| `setCreatedAt()` | `public` | Set created_at only if not already present. Allows explicit creation timestamps to be preserved. | Set created_at only if not already present. Allows explicit creation timestamps to be preserved. |
| `setUpdatedAt()` | `public` | Always refresh updated_at on every update. | Always refresh updated_at on every update. |
| `freshTimestamp()` | `public` | Return the current timestamp string in the format expected by the DB. | Return the current timestamp string in the format expected by the DB. |
| `touch()` | `public` | Convenience: update updated_at and save. | Convenience: update updated_at and save. |

### `Catalyst\Framework\Traits\InteractsWithRecordClaimsTrait`

- File: `app/Framework/Traits/InteractsWithRecordClaimsTrait.php`
- Kind: `trait`
- Summary: Adds record-claim coordination helpers to mutation controllers.
- Responsibility: Acquires, validates, releases and exposes concurrency claim state.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `acquireRecordClaim()` | `protected` | Acquires a claim for a resource record. | Acquires a claim for a resource record. |
| `assertRecordClaimAvailable()` | `protected` | Verifies that a record claim permits the requested mutation. | Verifies that a record claim permits the requested mutation. |
| `releaseRecordClaim()` | `protected` | Releases a record claim after a mutation. | Releases a record claim after a mutation. |
| `buildRecordClaimContext()` | `protected` | Normalizes a record claim for view consumption. | Normalizes a record claim for view consumption. |
| `concurrencyHiddenFields()` | `protected` | Builds hidden form fields required for concurrency checks. | Builds hidden form fields required for concurrency checks. |
| `rememberConcurrencyConflict()` | `protected` | Stores a concurrency conflict as a validation error. | Stores a concurrency conflict as a validation error. |

### `Catalyst\Framework\Traits\LoadsFeatureConfigTrait`

- File: `app/Framework/Traits/LoadsFeatureConfigTrait.php`
- Kind: `trait`
- Summary: LoadsFeatureConfigTrait — JSON config loader with graceful degradation.
- Responsibility: Loads feature configuration once per instance with resilient defaults.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `loadFeatureSection()` | `protected` | Load a config section, merging JSON values over $defaults. Cached per instance: subsequent calls return the same array. | Load a config section, merging JSON values over $defaults. Cached per instance: subsequent calls return the same array. |

### `Catalyst\Framework\Traits\OutputCleanerTrait`

- File: `app/Framework/Traits/OutputCleanerTrait.php`
- Kind: `trait`
- Summary: Trait that provides output buffer cleaning functionality
- Responsibility: Resets output buffering before framework error rendering.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `cleanOutput()` | `protected` | Clean any output that might have been sent before an error occurred. | Clean any output that might have been sent before an error occurred. |

### `Catalyst\Framework\Traits\SingletonTrait`

- File: `app/Framework/Traits/SingletonTrait.php`
- Kind: `trait`
- Summary: Trait that handles: Singleton Instance
- Responsibility: Provides controlled singleton instantiation, replacement and reset behavior.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `__construct()` | `protected` | Protected constructor to prevent direct instantiation. | Protected constructor to prevent direct instantiation. |
| `getInstance()` | `public` | Get the singleton instance of the class | n/a |
| `setInstance()` | `public` | Set a specific instance (for mocking/testing only). Not available in production environments. | n/a |
| `getArguments()` | `protected` | Get constructor arguments | n/a |
| `resetInstance()` | `public` | Reset the singleton instance | n/a |
| `__clone()` | `private` | Prevent cloning of the instance. | Prevent cloning of the instance. |
| `__wakeup()` | `public` | Prevent unserialization of the instance. | Prevent unserialization of the instance. |

## Operational Notes

When PHP symbols or method contracts in this namespace change, refresh this document from docblocks and run `php public/cli.php docs:inventory --json`.

## Related Documentation

- `docs/runtime-inventory.md`
- `docs/runtime-module-catalog.md`
- `docs/harness-context-map.md`
