# Catalyst\Framework\Event

## Purpose

Document event envelopes, listener definitions and dispatch behavior.

## Runtime Owners

| Concern | Owner |
|---|---|
| Registers listeners, dispatches event envelopes, queues eligible listeners, and invokes synchronous handlers. | `Catalyst\Framework\Event\EventBus` |
| Carries listener target, queue eligibility, and queue name for event dispatch. | `Catalyst\Framework\Event\EventListenerDefinition` |
| Defines the handler method required for class-based event listeners. | `Catalyst\Framework\Event\EventListenerInterface` |
| Registers default audit, timeline, notification, and automation event listeners on the event bus. | `Catalyst\Framework\Event\FrameworkEventCatalog` |
| Writes audit log entries from structured event envelope payloads. | `Catalyst\Framework\Event\Listeners\CaptureAuditEventListener` |
| Records configured timeline events from event envelope payloads. | `Catalyst\Framework\Event\Listeners\CaptureTimelineMilestoneListener` |
| Converts notification event payloads into stored or broadcast user notifications. | `Catalyst\Framework\Event\Listeners\DeliverNotificationListener` |
| Invokes automation rule execution when framework events are dispatched. | `Catalyst\Framework\Event\Listeners\ProcessAutomationEventListener` |

## Current Behavior

This file is regenerated from current PHP docblocks and the runtime inventory scope for `Catalyst\Framework\Event`. It intentionally replaces stale historical API notes with the classes and methods that exist in code now.

## API From Docblocks

### `Catalyst\Framework\Event\EventBus`

- File: `app/Framework/Event/EventBus.php`
- Kind: `class`
- Summary: Runtime event dispatcher for framework and module events.
- Responsibility: Registers listeners, dispatches event envelopes, queues eligible listeners, and invokes synchronous handlers.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `__construct()` | `protected` | Initializes logging and registers built-in framework listeners. | Initializes logging and registers built-in framework listeners. |
| `listen()` | `public` | Registers a listener for a named event or wildcard stream. | Registers a listener for a named event or wildcard stream. |
| `dispatch()` | `public` | Dispatches an event envelope or event name with payload and metadata. | Dispatches an event envelope or event name with payload and metadata. |
| `listeners()` | `public` | Returns registered listeners grouped by event name. | Returns registered listeners grouped by event name. |
| `shouldSkipQueuedWildcardListener()` | `private` | Prevents wildcard automation listeners from re-queuing queue lifecycle events. | Prevents wildcard automation listeners from re-queuing queue lifecycle events. |
| `invoke()` | `private` | Invokes a callable, invokable object, or listener class for an event. | Invokes a callable, invokable object, or listener class for an event. |

### `Catalyst\Framework\Event\EventListenerDefinition`

- File: `app/Framework/Event/EventListenerDefinition.php`
- Kind: `class`
- Summary: Value object describing an event listener registration.
- Responsibility: Carries listener target, queue eligibility, and queue name for event dispatch.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `__construct()` | `public` | Initializes the Event Listener Definition instance. | Initializes the Event Listener Definition instance. |

### `Catalyst\Framework\Event\EventListenerInterface`

- File: `app/Framework/Event/EventListenerInterface.php`
- Kind: `interface`
- Summary: Contract for event listener classes.
- Responsibility: Defines the handler method required for class-based event listeners.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `handle()` | `public` | Handles an event envelope. | Handles an event envelope. |

### `Catalyst\Framework\Event\FrameworkEventCatalog`

- File: `app/Framework/Event/FrameworkEventCatalog.php`
- Kind: `class`
- Summary: Catalog of built-in framework event listener registrations.
- Responsibility: Registers default audit, timeline, notification, and automation event listeners on the event bus.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `registerDefaults()` | `public` | Registers the requested definition. | n/a |

### `Catalyst\Framework\Event\Listeners\CaptureAuditEventListener`

- File: `app/Framework/Event/Listeners/CaptureAuditEventListener.php`
- Kind: `class`
- Summary: Listener for capturing audit event envelopes.
- Responsibility: Writes audit log entries from structured event envelope payloads.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `handle()` | `public` | Handles an event envelope. | Handles an event envelope. |

### `Catalyst\Framework\Event\Listeners\CaptureTimelineMilestoneListener`

- File: `app/Framework/Event/Listeners/CaptureTimelineMilestoneListener.php`
- Kind: `class`
- Summary: Listener for capturing timeline milestone events.
- Responsibility: Records configured timeline events from event envelope payloads.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `handle()` | `public` | Handles an event envelope. | Handles an event envelope. |

### `Catalyst\Framework\Event\Listeners\DeliverNotificationListener`

- File: `app/Framework/Event/Listeners/DeliverNotificationListener.php`
- Kind: `class`
- Summary: Listener for delivering notification dispatch events.
- Responsibility: Converts notification event payloads into stored or broadcast user notifications.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `handle()` | `public` | Handles an event envelope. | Handles an event envelope. |

### `Catalyst\Framework\Event\Listeners\ProcessAutomationEventListener`

- File: `app/Framework/Event/Listeners/ProcessAutomationEventListener.php`
- Kind: `class`
- Summary: Listener for forwarding events into automation processing.
- Responsibility: Invokes automation rule execution when framework events are dispatched.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `handle()` | `public` | Handles an event envelope. | Handles an event envelope. |

## Operational Notes

When PHP symbols or method contracts in this namespace change, refresh this document from docblocks and run `php public/cli.php docs:inventory --json`.

## Related Documentation

- `docs/runtime-inventory.md`
- `docs/runtime-module-catalog.md`
- `docs/harness-context-map.md`
