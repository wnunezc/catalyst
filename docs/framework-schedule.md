# Catalyst\Framework\Schedule

## Purpose

Document schedule registry, cron parsing, locks and runner behavior.

## Runtime Owners

| Concern | Owner |
|---|---|
| Determines whether a scheduled task is due by matching cron segments, ranges, lists, and steps. | `Catalyst\Framework\Schedule\CronExpression` |
| Adds queue-history cleanup, automation evaluation, and retention execution to the scheduler registry once. | `Catalyst\Framework\Schedule\FrameworkScheduleCatalog` |
| Creates task-specific lock files and executes callbacks only while holding an exclusive non-blocking lock. | `Catalyst\Framework\Schedule\ScheduleLockManager` |
| Loads framework defaults and indexes scheduled tasks by their unique name. | `Catalyst\Framework\Schedule\ScheduleRegistry` |
| Prevents duplicate slot dispatches, records queued or skipped runs, summarizes history, and prunes old records. | `Catalyst\Framework\Schedule\ScheduleRepository` |
| Resolves task scope, checks cron slots, prevents duplicate execution, queues due jobs, and reports outcomes. | `Catalyst\Framework\Schedule\ScheduleRunner` |
| Creates the scheduler history table once per request using the queue database connection. | `Catalyst\Framework\Schedule\ScheduleSchemaManager` |
| Provides the scheduler enabled flag and history-table setting. | `Catalyst\Framework\Schedule\ScheduleSettings` |

## Current Behavior

This file is regenerated from current PHP docblocks and the runtime inventory scope for `Catalyst\Framework\Schedule`. It intentionally replaces stale historical API notes with the classes and methods that exist in code now.

## API From Docblocks

### `Catalyst\Framework\Schedule\CronExpression`

- File: `app/Framework/Schedule/CronExpression.php`
- Kind: `class`
- Summary: Evaluates five-part cron expressions against UTC scheduler slots.
- Responsibility: Determines whether a scheduled task is due by matching cron segments, ranges, lists, and steps.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `isDue()` | `public` | Determines whether a cron expression matches the supplied time. | n/a |
| `matchesPart()` | `private` | Determines whether one cron segment accepts the supplied numeric value. | n/a |
| `parseRange()` | `private` | Parses a single cron value or range into normalized bounds. | n/a |
| `normalizeValue()` | `private` | Normalizes weekday seven to Sunday zero when required. | n/a |

### `Catalyst\Framework\Schedule\FrameworkScheduleCatalog`

- File: `app/Framework/Schedule/FrameworkScheduleCatalog.php`
- Kind: `class`
- Summary: Registers the framework-owned recurring maintenance tasks.
- Responsibility: Adds queue-history cleanup, automation evaluation, and retention execution to the scheduler registry once.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `registerDefaults()` | `public` | Registers the default framework schedule entries once. | n/a |

### `Catalyst\Framework\Schedule\ScheduleLockManager`

- File: `app/Framework/Schedule/ScheduleLockManager.php`
- Kind: `class`
- Summary: Serializes scheduled task execution through filesystem locks.
- Responsibility: Creates task-specific lock files and executes callbacks only while holding an exclusive non-blocking lock.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `runWithLock()` | `public` | Runs a task callback while holding its scheduler lock. | Runs a task callback while holding its scheduler lock. |
| `lockPath()` | `private` | Builds the storage path for a sanitized task lock file. | Builds the storage path for a sanitized task lock file. |

### `Catalyst\Framework\Schedule\ScheduleRegistry`

- File: `app/Framework/Schedule/ScheduleRegistry.php`
- Kind: `class`
- Summary: Stores scheduled tasks available to the framework scheduler.
- Responsibility: Loads framework defaults and indexes scheduled tasks by their unique name.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `__construct()` | `protected` | Initializes the Schedule Registry instance. | Initializes the Schedule Registry instance. |
| `register()` | `public` | Registers or replaces a scheduled task by name. | Registers or replaces a scheduled task by name. |
| `all()` | `public` | Returns every registered scheduled task keyed by name. | Returns every registered scheduled task keyed by name. |
| `get()` | `public` | Returns one registered scheduled task by name. | Returns one registered scheduled task by name. |

### `Catalyst\Framework\Schedule\ScheduleRepository`

- File: `app/Framework/Schedule/ScheduleRepository.php`
- Kind: `class`
- Summary: Persists scheduler slot claims and execution history.
- Responsibility: Prevents duplicate slot dispatches, records queued or skipped runs, summarizes history, and prunes old records.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `claimSlot()` | `public` | Claims a scheduler slot by inserting its unique task-and-slot record. | Claims a scheduler slot by inserting its unique task-and-slot record. |

### `Catalyst\Framework\Schedule\ScheduleRunner`

- File: `app/Framework/Schedule/ScheduleRunner.php`
- Kind: `class`
- Summary: Evaluates scheduled tasks and dispatches due work to the queue.
- Responsibility: Resolves task scope, checks cron slots, prevents duplicate execution, queues due jobs, and reports outcomes.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `__construct()` | `public` | Initializes the Schedule Runner instance. | Initializes the Schedule Runner instance. |
| `run()` | `public` | Evaluates due tasks or one requested task and returns dispatch outcomes. | Evaluates due tasks or one requested task and returns dispatch outcomes. |
| `resolveTask()` | `private` | Resolves a registered scheduled task or fails for an unknown name. | Resolves a registered scheduled task or fails for an unknown name. |

### `Catalyst\Framework\Schedule\ScheduleSchemaManager`

- File: `app/Framework/Schedule/ScheduleSchemaManager.php`
- Kind: `class`
- Summary: Ensures scheduler history storage exists before repository operations run.
- Responsibility: Creates the scheduler history table once per request using the queue database connection.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `ensure()` | `public` | Creates the scheduler history table when it has not been initialized yet. | n/a |

### `Catalyst\Framework\Schedule\ScheduleSettings`

- File: `app/Framework/Schedule/ScheduleSettings.php`
- Kind: `class`
- Summary: Resolves scheduler defaults and normalized runtime configuration.
- Responsibility: Provides the scheduler enabled flag and history-table setting.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `defaults()` | `public` | Returns the default scheduler configuration. | n/a |
| `current()` | `public` | Returns the normalized active scheduler configuration. | n/a |

## Operational Notes

When PHP symbols or method contracts in this namespace change, refresh this document from docblocks and run `php public/cli.php docs:inventory --json`.

## Related Documentation

- `docs/runtime-inventory.md`
- `docs/runtime-module-catalog.md`
- `docs/harness-context-map.md`
