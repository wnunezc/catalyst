# Catalyst\Framework\Schedule

Directory: `app/Framework/Schedule/`

## Purpose

This subsystem provides the first framework-level scheduler introduced for
`RM-13`.

Current scope:

- declarative schedule registry
- cron-style due matching
- per-task locking
- queue-backed task execution
- scheduler run history

## Runtime Model

The scheduler does not execute ad hoc shell scripts.

It evaluates registered `ScheduledTask` definitions and queues the associated
framework jobs. The current history table is auto-provisioned on first use:

- `scheduler_runs`

## Core Classes

### `ScheduleRegistry`

File: `app/Framework/Schedule/ScheduleRegistry.php`

- holds the declarative task map
- loads framework defaults through `FrameworkScheduleCatalog`

### `ScheduledTask`

File: `app/Entities/ScheduledTask.php`

- immutable shared task definition
- stores:
  - task name
  - cron expression
  - target job class
  - serialized job payload
  - target queue
  - description

### `ScheduleRunner`

File: `app/Framework/Schedule/ScheduleRunner.php`

- evaluates due tasks
- claims a run slot in `scheduler_runs`
- applies per-task locking
- dispatches the underlying job to the queue

### `ScheduleLockManager`

File: `app/Framework/Schedule/ScheduleLockManager.php`

- uses lock files under `boot-core/storage/locks/scheduler/`
- prevents concurrent double execution of the same task

### `CronExpression`

File: `app/Framework/Schedule/CronExpression.php`

- lightweight five-field cron matcher
- supports:
  - `*`
  - `*/n`
  - comma lists
  - numeric ranges

## Default Framework Task

Current built-in task:

- `framework.queue.prune-history`
  - cron: `15 3 * * *`
  - queue: `maintenance`
  - job: `PruneQueueHistoryJob`

## CLI Surface

- `php public/cli.php schedule:list`
- `php public/cli.php schedule:run`

## Notes

- scheduler tasks are queue-backed in this first cut
- this keeps scheduler execution aligned with the queue and event bus, instead
  of creating a second async runtime
