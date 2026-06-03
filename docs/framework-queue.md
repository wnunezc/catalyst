# Catalyst\Framework\Queue

## Purpose

Document persistent queue, job serialization, worker and built-in queue jobs.

## Runtime Owners

| Concern | Owner |
|---|---|
| Carries notification state across the queue boundary and executes notification delivery. | `Catalyst\Framework\Queue\Jobs\DispatchNotificationJob` |
| Carries an event and listener identity across the queue boundary and dispatches the restored listener. | `Catalyst\Framework\Queue\Jobs\InvokeQueuedListenerJob` |
| Executes periodic cleanup for queue failures and scheduler run records. | `Catalyst\Framework\Queue\Jobs\PruneQueueHistoryJob` |
| Converts queueable jobs between runtime objects and repository payloads while enforcing their contract. | `Catalyst\Framework\Queue\QueueJobSerializer` |
| Resolves queue routing, persists new jobs, and emits dispatch events. | `Catalyst\Framework\Queue\QueueManager` |
| Provides the database operations required to enqueue, reserve, retry, complete, inspect, and prune queued work. | `Catalyst\Framework\Queue\QueueRepository` |
| Creates pending and failed queue tables once per request using the configured database connection. | `Catalyst\Framework\Queue\QueueSchemaManager` |
| Provides validated queue connection, table, default queue, and stale-reservation settings. | `Catalyst\Framework\Queue\QueueSettings` |
| Reserves jobs, executes them, persists their outcome, and emits queue lifecycle events. | `Catalyst\Framework\Queue\QueueWorker` |
| Standardizes execution, queue routing, retry policy, and payload serialization for queued work. | `Catalyst\Framework\Queue\QueueableJobInterface` |

## Current Behavior

This file is regenerated from current PHP docblocks and the runtime inventory scope for `Catalyst\Framework\Queue`. It intentionally replaces stale historical API notes with the classes and methods that exist in code now.

## API From Docblocks

### `Catalyst\Framework\Queue\Jobs\DispatchNotificationJob`

- File: `app/Framework/Queue/Jobs/DispatchNotificationJob.php`
- Kind: `class`
- Summary: Delivers a notification through the notification manager from the queue.
- Responsibility: Carries notification state across the queue boundary and executes notification delivery.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `__construct()` | `public` | Initializes the Dispatch Notification Job instance. | Initializes the Dispatch Notification Job instance. |
| `handle()` | `public` | Sends the queued notification. | Sends the queued notification. |
| `displayName()` | `public` | Returns a diagnostic label containing the notification type. | Returns a diagnostic label containing the notification type. |
| `queueName()` | `public` | Returns the queue selected for notification delivery. | Returns the queue selected for notification delivery. |
| `maxAttempts()` | `public` | Returns the allowed delivery-attempt count. | Returns the allowed delivery-attempt count. |
| `backoffSeconds()` | `public` | Returns the retry delay for failed notification delivery. | Returns the retry delay for failed notification delivery. |
| `toPayload()` | `public` | Exports notification state for queue persistence. | Exports notification state for queue persistence. |
| `fromPayload()` | `public` | Restores a notification-delivery job from persisted state. | n/a |

### `Catalyst\Framework\Queue\Jobs\InvokeQueuedListenerJob`

- File: `app/Framework/Queue/Jobs/InvokeQueuedListenerJob.php`
- Kind: `class`
- Summary: Invokes an event listener asynchronously through the queue.
- Responsibility: Carries an event and listener identity across the queue boundary and dispatches the restored listener.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `__construct()` | `public` | Initializes the Invoke Queued Listener Job instance. | Initializes the Invoke Queued Listener Job instance. |
| `handle()` | `public` | Resolves and invokes the queued listener for the stored event. | Resolves and invokes the queued listener for the stored event. |

### `Catalyst\Framework\Queue\Jobs\PruneQueueHistoryJob`

- File: `app/Framework/Queue/Jobs/PruneQueueHistoryJob.php`
- Kind: `class`
- Summary: Prunes failed queue jobs and old scheduler history from the maintenance queue.
- Responsibility: Executes periodic cleanup for queue failures and scheduler run records.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `__construct()` | `public` | Initializes the Prune Queue History Job instance. | Initializes the Prune Queue History Job instance. |
| `handle()` | `public` | Deletes queue and scheduler history outside their retention windows. | Deletes queue and scheduler history outside their retention windows. |
| `displayName()` | `public` | Returns the maintenance-job label. | Returns the maintenance-job label. |
| `queueName()` | `public` | Returns the queue selected for maintenance work. | Returns the queue selected for maintenance work. |
| `maxAttempts()` | `public` | Returns the allowed maintenance-attempt count. | Returns the allowed maintenance-attempt count. |
| `backoffSeconds()` | `public` | Returns the retry delay for maintenance failures. | Returns the retry delay for maintenance failures. |
| `toPayload()` | `public` | Exports cleanup windows and queue routing for persistence. | Exports cleanup windows and queue routing for persistence. |
| `fromPayload()` | `public` | Restores a cleanup job from persisted state. | n/a |

### `Catalyst\Framework\Queue\QueueJobSerializer`

- File: `app/Framework/Queue/QueueJobSerializer.php`
- Kind: `class`
- Summary: Serializes queue jobs into persistence descriptors and restores them safely.
- Responsibility: Converts queueable jobs between runtime objects and repository payloads while enforcing their contract.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `encode()` | `public` | Encodes a queueable job into the fields stored by the queue repository. | n/a |
| `decode()` | `public` | Restores a queueable job after validating its persisted class. | n/a |

### `Catalyst\Framework\Queue\QueueManager`

- File: `app/Framework/Queue/QueueManager.php`
- Kind: `class`
- Summary: Dispatches queueable jobs for asynchronous processing.
- Responsibility: Resolves queue routing, persists new jobs, and emits dispatch events.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `dispatch()` | `public` | Persists a job in its target queue and returns the generated identifier. | Persists a job in its target queue and returns the generated identifier. |

### `Catalyst\Framework\Queue\QueueRepository`

- File: `app/Framework/Queue/QueueRepository.php`
- Kind: `class`
- Summary: Persists queued jobs, failed jobs, and their processing state.
- Responsibility: Provides the database operations required to enqueue, reserve, retry, complete, inspect, and prune queued work.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `enqueue()` | `public` | Inserts a job that is ready for queue processing at the requested time. | Inserts a job that is ready for queue processing at the requested time. |

### `Catalyst\Framework\Queue\QueueSchemaManager`

- File: `app/Framework/Queue/QueueSchemaManager.php`
- Kind: `class`
- Summary: Ensures the queue storage tables exist before repository operations run.
- Responsibility: Creates pending and failed queue tables once per request using the configured database connection.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `ensure()` | `public` | Creates the queue tables when they have not been initialized yet. | n/a |

### `Catalyst\Framework\Queue\QueueSettings`

- File: `app/Framework/Queue/QueueSettings.php`
- Kind: `class`
- Summary: Resolves queue defaults and normalized runtime configuration.
- Responsibility: Provides validated queue connection, table, default queue, and stale-reservation settings.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `defaults()` | `public` | Returns the default queue configuration. | n/a |
| `current()` | `public` | Returns the normalized active queue configuration. | n/a |

### `Catalyst\Framework\Queue\QueueWorker`

- File: `app/Framework/Queue/QueueWorker.php`
- Kind: `class`
- Summary: Processes the next available queue job and applies its retry policy.
- Responsibility: Reserves jobs, executes them, persists their outcome, and emits queue lifecycle events.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `processNext()` | `public` | Processes one available job and returns its resulting worker status. | Processes one available job and returns its resulting worker status. |

### `Catalyst\Framework\Queue\QueueableJobInterface`

- File: `app/Framework/Queue/QueueableJobInterface.php`
- Kind: `interface`
- Summary: Defines the contract required for jobs executed by the framework queue.
- Responsibility: Standardizes execution, queue routing, retry policy, and payload serialization for queued work.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `handle()` | `public` | Executes the queued work. | Executes the queued work. |
| `displayName()` | `public` | Returns the diagnostic label shown for the queued job. | Returns the diagnostic label shown for the queued job. |
| `queueName()` | `public` | Returns the queue where the job should be dispatched. | Returns the queue where the job should be dispatched. |
| `maxAttempts()` | `public` | Returns the maximum number of processing attempts. | Returns the maximum number of processing attempts. |
| `backoffSeconds()` | `public` | Returns the retry delay in seconds after a failed attempt. | Returns the retry delay in seconds after a failed attempt. |
| `toPayload()` | `public` | Exports the job state required to reconstruct it later. | Exports the job state required to reconstruct it later. |
| `fromPayload()` | `public` | Reconstructs a job from its persisted payload. | n/a |

## Operational Notes

When PHP symbols or method contracts in this namespace change, refresh this document from docblocks and run `php public/cli.php docs:inventory --json`.

## Related Documentation

- `docs/runtime-inventory.md`
- `docs/runtime-module-catalog.md`
- `docs/harness-context-map.md`
