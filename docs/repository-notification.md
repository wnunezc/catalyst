# Catalyst\Repository\Notification

## Purpose

Document the session-authenticated notification and presence runtime transport
controllers. These controllers serve `/runtime/*`; they are not public APIs.

## Runtime Owners

| Concern | Owner |
|---|---|
| Coordinates notification runtime responses for the current user. | `Catalyst\Repository\Notification\Controllers\NotificationController` |
| Refreshes presence state and reports claim conflicts to clients. | `Catalyst\Repository\Notification\Controllers\PresenceController` |

## Current Behavior

This file is regenerated from current PHP docblocks and the runtime inventory scope for `Catalyst\Repository\Notification`. It intentionally replaces stale historical API notes with the classes and methods that exist in code now.

## API From Docblocks

### `Catalyst\Repository\Notification\Controllers\NotificationController`

- File: `Repository/Framework/Notification/Controllers/NotificationController.php`
- Kind: `class`
- Summary: Exposes authenticated notification queries and read-state mutations.
- Responsibility: Coordinates notification runtime responses for the current user.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `userId()` | `private` | Returns the authenticated user identifier used by notification queries. | Returns the authenticated user identifier used by notification queries. |
| `wsToken()` | `public` | Issues a fresh WebSocket authentication token for the current user. | Issues a fresh WebSocket authentication token for the current user. |
| `index()` | `public` | Returns the current user's paginated notifications and unread count. | Returns the current user's paginated notifications and unread count. |
| `unreadCount()` | `public` | Returns the current user's unread notification count. | Returns the current user's unread notification count. |
| `markRead()` | `public` | Marks one notification as read for the current user. | Marks one notification as read for the current user. |
| `markAllRead()` | `public` | Marks all notifications as read for the current user. | Marks all notifications as read for the current user. |

### `Catalyst\Repository\Notification\Controllers\PresenceController`

- File: `Repository/Framework/Notification/Controllers/PresenceController.php`
- Kind: `class`
- Summary: Exposes authenticated presence heartbeat updates for editable records.
- Responsibility: Refreshes presence state and reports claim conflicts to clients.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `userId()` | `private` | Returns the authenticated actor identifier used for presence tracking. | Returns the authenticated actor identifier used for presence tracking. |
| `actorLabel()` | `private` | Builds the display label published with the current actor's presence. | Builds the display label published with the current actor's presence. |
| `heartbeat()` | `public` | Refreshes record presence and returns the resulting presence snapshot. | Refreshes record presence and returns the resulting presence snapshot. |

## Operational Notes

When PHP symbols or method contracts in this namespace change, refresh this document from docblocks and run `php public/cli.php docs:inventory --json`.

## Related Documentation

- `docs/runtime-inventory.md`
- `docs/runtime-module-catalog.md`
- `docs/harness-context-map.md`
