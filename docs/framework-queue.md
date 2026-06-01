# Catalyst\Framework\Queue

Directory: `app/Framework/Queue/`

## Purpose

This subsystem provides the first real queue runtime introduced for `RM-12`.

Current scope:

- persistent job dispatch
- database-backed queue storage
- retry / release flow
- failed jobs persistence
- worker CLI

## Runtime Model

The queue backend is framework-owned and persists into database tables that are
auto-provisioned on first use:

- `queue_jobs`
- `failed_jobs`

The queue uses the configured DB connection from `queue.connection`
(`db1` by default).

## Core Classes

### `QueueManager`

File: `app/Framework/Queue/QueueManager.php`

- high-level dispatch facade
- serializes `QueueableJobInterface` implementations
- persists them through `QueueRepository`
- emits queue lifecycle events through `EventBus`

### `QueueRepository`

File: `app/Framework/Queue/QueueRepository.php`

- database persistence for:
  - queued jobs
  - failed jobs
- reservation logic
- retry / requeue helpers
- lightweight queue summary for health/status

### `QueueWorker`

File: `app/Framework/Queue/QueueWorker.php`

- reserves one job
- deserializes it through `QueueJobSerializer`
- executes it
- either:
  - completes it
  - releases it for retry
  - moves it to `failed_jobs`

### `QueueableJobInterface`

File: `app/Framework/Queue/QueueableJobInterface.php`

Queue jobs must provide:

- `handle()`
- `displayName()`
- `queueName()`
- `maxAttempts()`
- `backoffSeconds()`
- `toPayload()`
- `fromPayload()`

## Built-in Jobs

- `DispatchNotificationJob`
  - queues existing notification delivery
- `InvokeQueuedListenerJob`
  - powers async event listeners
- `PruneQueueHistoryJob`
  - maintenance task used by the scheduler

## CLI Surface

- `php public/cli.php queue:work`
- `php public/cli.php queue:failed`
- `php public/cli.php queue:retry`

## Notes

- this is not a parallel worker framework outside Catalyst
- the queue is integrated with:
  - the event bus
  - notifications
  - the scheduler
