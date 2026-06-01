# Catalyst\Framework\Event

Directory: `app/Framework/Event/`

## Purpose

This subsystem provides the framework event bus introduced for `RM-10`.

It is intentionally small:

- synchronous dispatch for in-process listeners
- queued listener support through the framework queue
- typed event envelope shared across runtime, queue and scheduler

## Core Classes

### `EventBus`

File: `app/Framework/Event/EventBus.php`

- singleton event dispatcher
- accepts event names or full `EventEnvelope` instances
- resolves synchronous listeners immediately
- converts queued listeners into real queue jobs through `InvokeQueuedListenerJob`

### `EventEnvelope`

File: `app/Entities/EventEnvelope.php`

- immutable shared event payload
- carries:
  - `id`
  - `name`
  - `payload`
  - `meta`
  - `occurred_at`

### `EventListenerInterface`

File: `app/Framework/Event/EventListenerInterface.php`

- canonical listener contract for class-based listeners
- method: `handle(EventEnvelope $event): void`

## Default Framework Registrations

`FrameworkEventCatalog` currently registers the first real framework producers/consumers:

- `framework.notification.dispatch`
  - synchronous notification delivery
- `framework.notification.dispatch.async`
  - same delivery path, but queued on the `notifications` queue

The default listener is:

- `DeliverNotificationListener`

This keeps notifications on top of the existing `NotificationManager` runtime,
instead of introducing a parallel notification stack.

## Relationship With Queue

Queued listeners are persisted as real queue jobs through:

- `app/Framework/Queue/Jobs/InvokeQueuedListenerJob.php`

This means async event delivery is not a fake deferred callback. It is stored in
the framework queue backend and processed by `queue:work`.

## Relationship With Notifications

The first framework-level consumer is the existing notification runtime:

- `NotificationManager`
- `NotificationRepository`
- `WebSocketPublisher`

The event bus does not replace them. It only becomes the new dispatch surface
that can feed them synchronously or asynchronously.
