# Catalyst\Framework\Notification

## Purpose

Document notification value objects, manager and repository behavior.

## Runtime Owners

| Concern | Owner |
|---|---|
| Carries immutable notification content and creates standard notification variants. | `Catalyst\Framework\Notification\Notification` |
| Collects toaster, modal, and inline alert payloads for JSON responses. | `Catalyst\Framework\Notification\NotificationBag` |
| Persists notification dispatches, emits events, queues delivery, and publishes WebSocket updates. | `Catalyst\Framework\Notification\NotificationManager` |
| Maps toaster positions to CSS placement and stacking direction. | `Catalyst\Framework\Notification\NotificationPosition` |
| Persists user notifications and updates their read state without physical deletion. | `Catalyst\Framework\Notification\NotificationRepository` |
| Maps notification semantic types to Bootstrap classes, icons, and contrast styles. | `Catalyst\Framework\Notification\NotificationType` |

## Current Behavior

This file is regenerated from current PHP docblocks and the runtime inventory scope for `Catalyst\Framework\Notification`. It intentionally replaces stale historical API notes with the classes and methods that exist in code now.

## API From Docblocks

### `Catalyst\Framework\Notification\Notification`

- File: `app/Framework/Notification/Notification.php`
- Kind: `class`
- Summary: Notification - DTO for individual notifications
- Responsibility: Carries immutable notification content and creates standard notification variants.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `__construct()` | `public` | Create a new Notification instance The ID is always set at construction time. If not provided, a unique ID is auto-generated once, ensuring getId() and toArray() return a stable value. | Create a new Notification instance The ID is always set at construction time. If not provided, a unique ID is auto-generated once, ensuring getId() and toArray() return a stable value. |
| `generateId()` | `private` | Generate a unique notification ID | n/a |
| `getId()` | `public` | Get the notification ID Always returns the stable ID set at construction time. | Exposes the stable notification identifier assigned at construction time. |
| `getIcon()` | `public` | Get the icon class (uses type default if not set). | Resolves the explicit icon class or falls back to the notification type default. |
| `toArray()` | `public` | Convert notification to array for JSON serialization. | Convert notification to array for JSON serialization. |
| `fromArray()` | `public` | Create a Notification instance from an array | n/a |
| `success()` | `public` | Create a success notification | n/a |
| `error()` | `public` | Create an error notification | n/a |
| `warning()` | `public` | Create a warning notification | n/a |
| `info()` | `public` | Create an info notification | n/a |

### `Catalyst\Framework\Notification\NotificationBag`

- File: `app/Framework/Notification/NotificationBag.php`
- Kind: `class`
- Summary: NotificationBag - Collection of notifications for JSON responses
- Responsibility: Collects toaster, modal, and inline alert payloads for JSON responses.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `addToaster()` | `public` | Add a toaster notification. | Add a toaster notification. |
| `toaster()` | `public` | Add a toaster notification with quick syntax. | Add a toaster notification with quick syntax. |
| `success()` | `public` | Add a success toaster. | Add a success toaster. |
| `error()` | `public` | Add an error toaster. | Add an error toaster. |
| `warning()` | `public` | Add a warning toaster. | Add a warning toaster. |
| `info()` | `public` | Add an info toaster. | Add an info toaster. |
| `addModal()` | `public` | Add a modal to be displayed. | Add a modal to be displayed. |
| `modal()` | `public` | Shorthand for adding a modal. | Shorthand for adding a modal. |
| `addAlert()` | `public` | Add an inline alert notification. | Add an inline alert notification. |
| `alert()` | `public` | Add an inline alert with quick syntax. | Add an inline alert with quick syntax. |
| `getToasters()` | `public` | Get all toaster notifications. | Get all toaster notifications. |
| `getModals()` | `public` | Get all modal configurations. | Get all modal configurations. |
| `getAlerts()` | `public` | Get all inline alerts. | Get all inline alerts. |
| `isEmpty()` | `public` | Check if the bag is empty. | Check if the bag is empty. |
| `hasToasters()` | `public` | Check if the bag has any toasters. | Check if the bag has any toasters. |
| `hasModals()` | `public` | Check if the bag has any modals. | Check if the bag has any modals. |
| `hasAlerts()` | `public` | Check if the bag has any alerts. | Check if the bag has any alerts. |
| `count()` | `public` | Get total count of all notifications. | Get total count of all notifications. |
| `toArray()` | `public` | Convert the notification bag to array for JSON serialization. | Convert the notification bag to array for JSON serialization. |
| `fromArray()` | `public` | Create a NotificationBag from an array | n/a |
| `merge()` | `public` | Merge another NotificationBag into this one. | Merge another NotificationBag into this one. |
| `clear()` | `public` | Clear all notifications. | Clear all notifications. |

### `Catalyst\Framework\Notification\NotificationManager`

- File: `app/Framework/Notification/NotificationManager.php`
- Kind: `class`
- Summary: Producer-side facade for persisted user notifications plus optional WS push.
- Responsibility: Persists notification dispatches, emits events, queues delivery, and publishes WebSocket updates.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `send()` | `public` | Persists a dispatch, broadcasts it, and emits its delivery event. | Persists a dispatch, broadcasts it, and emits its delivery event. |
| `emit()` | `public` | Emits a synchronous or asynchronous notification dispatch event. | Emits a synchronous or asynchronous notification dispatch event. |
| `queue()` | `public` | Queues a notification dispatch job with an optional delay. | Queues a notification dispatch job with an optional delay. |
| `notify()` | `public` | Persist a notification and broadcast it to the user's WS connection. | Persist a notification and broadcast it to the user's WS connection. |
| `info()` | `public` | Sends an informational notification to the user. | Sends an informational notification to the user. |
| `success()` | `public` | Sends a success notification to the user. | Sends a success notification to the user. |
| `warning()` | `public` | Sends a warning notification to the user. | Sends a warning notification to the user. |
| `error()` | `public` | Sends an error notification to the user. | Sends an error notification to the user. |
| `system()` | `public` | Sends a system notification to the user. | Sends a system notification to the user. |

### `Catalyst\Framework\Notification\NotificationPosition`

- File: `app/Framework/Notification/NotificationPosition.php`
- Kind: `enum`
- Summary: NotificationPosition - Enum for toaster positions
- Responsibility: Maps toaster positions to CSS placement and stacking direction.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `getCssStyles()` | `public` | Get CSS positioning styles for this position. | Get CSS positioning styles for this position. |
| `isTop()` | `public` | Check if this position is at the top of the screen. | Check if this position is at the top of the screen. |
| `isBottom()` | `public` | Check if this position is at the bottom of the screen. | Check if this position is at the bottom of the screen. |
| `getStackDirection()` | `public` | Get the stacking direction for toasts. | Exposes the toast stacking direction associated with the screen position. |

### `Catalyst\Framework\Notification\NotificationRepository`

- File: `app/Framework/Notification/NotificationRepository.php`
- Kind: `class`
- Summary: Database access layer for the notifications table.
- Responsibility: Persists user notifications and updates their read state without physical deletion.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `db()` | `private` | Returns the PDO connection used for notification persistence. | Returns the PDO connection used for notification persistence. |
| `create()` | `public` | Insert a new notification and return its ID. | Insert a new notification and return its ID. |
| `getUnread()` | `public` | Get unread notifications for a user (newest first). | Get unread notifications for a user (newest first). |
| `getAll()` | `public` | Get all notifications for a user (newest first), read and unread. | Get all notifications for a user (newest first), read and unread. |
| `countUnread()` | `public` | Count unread notifications for a user. | Count unread notifications for a user. |
| `markRead()` | `public` | Mark a single notification as read. | Mark a single notification as read. |
| `markAllRead()` | `public` | Mark all unread notifications for a user as read. | Mark all unread notifications for a user as read. |

### `Catalyst\Framework\Notification\NotificationType`

- File: `app/Framework/Notification/NotificationType.php`
- Kind: `enum`
- Summary: NotificationType - Enum for notification types
- Responsibility: Maps notification semantic types to Bootstrap classes, icons, and contrast styles.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `toBootstrapClass()` | `public` | Get the Bootstrap alert class for this notification type. | Maps the notification type to its Bootstrap alert presentation class. |
| `getDefaultIcon()` | `public` | Get the default FontAwesome icon for this notification type. | Maps the notification type to its default FontAwesome icon class. |
| `toToastClass()` | `public` | Get the toast background class for this notification type. | Maps the notification type to its Bootstrap toast background class. |
| `getTextClass()` | `public` | Get text color class for contrast. | Get text color class for contrast. |

## Operational Notes

When PHP symbols or method contracts in this namespace change, refresh this document from docblocks and run `php public/cli.php docs:inventory --json`.

## Related Documentation

- `docs/runtime-inventory.md`
- `docs/runtime-module-catalog.md`
- `docs/harness-context-map.md`
