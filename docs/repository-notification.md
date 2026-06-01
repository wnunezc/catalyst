# Catalyst\Repository\Notification

Module location: `Repository/Framework/Notification/`

## What This Module Really Owns

This module owns the authenticated REST API for persisted user notifications:

- list notifications
- expose unread count
- mark one notification as read
- mark all notifications as read
- refresh the authenticated WebSocket token used by the shared status bar

The module does not ship Repository-scoped views, but it is not "headless" in
the user-facing sense. The runtime includes a real notification UI through:

- `boot-core/template/components/_status-bar.phtml`
- `public/assets/js/catalyst/modules/status-bar.js`
- `public/assets/css/catalyst/status-bar.css`

That shared status bar renders:

- the bell button
- the unread badge
- the slide-up notification panel
- the initial WS bootstrap payload in `window.__catalystWs`

## Runtime Routes

All routes are registered in `Repository/Framework/Notification/routes.php` and
guarded by `AuthMiddleware`.

| Method | Path | Controller | Current runtime consumer |
|---|---|---|---|
| GET | `/api/ws-token` | `NotificationController::wsToken` | `status-bar.js` refresh before WS reconnect |
| GET | `/api/notifications` | `NotificationController::index` | `status-bar.js` panel load |
| GET | `/api/notifications/unread-count` | `NotificationController::unreadCount` | `status-bar.js` REST fallback polling when browser WS is unavailable |
| POST | `/api/notifications/read-all` | `NotificationController::markAllRead` | `status-bar.js` mark-all action |
| POST | `/api/notifications/{id}/read` | `NotificationController::markRead` | `status-bar.js` item click |

## Controller Contract

File: `Repository/Framework/Notification/Controllers/NotificationController.php`

Key behavior:

- `wsToken()` returns a fresh short-lived token for the authenticated user
- `index()` returns paginated notifications plus current unread count
- `unreadCount()` returns only the unread count
- `markRead()` validates `{id}` and marks one unread record as read
- `markAllRead()` marks every unread record for the current user

The route parameter for `markRead()` is resolved by the router/dispatcher via
method signature, not through a `Request::routeParam()` helper.

## Data Contract

File: `app/Framework/Notification/NotificationRepository.php`

Persisted notifications use this contract:

- unread = `read_at IS NULL`
- read = `read_at` contains a timestamp
- no soft-delete flow exists here

The current table shape used by the runtime is:

| Column | Meaning |
|---|---|
| `id` | notification id |
| `user_id` | target user |
| `type` | semantic type (`info`, `success`, `warning`, `error`, `system`) |
| `title` | short title |
| `body` | optional body |
| `read_at` | null until read |
| `created_at` | creation timestamp |

Historic references to `is_read` are stale for the current runtime.

## UI and Transport Split

The notification runtime is split across two concerns:

1. Persisted user notifications
   - stored in `notifications`
   - loaded and mutated through this Repository module
   - surfaced in the status-bar panel and unread badge

2. Real-time delivery transport
   - authenticated browser connection to `/ws`
   - token refresh through `/api/ws-token`
   - optional server-side publish path through `WebSocketPublisher`

## Producer-Side Status

Files:

- `app/Framework/Notification/NotificationManager.php`
- `app/Framework/WebSocket/WebSocketPublisher.php`

Current audit conclusion:

- keep both as supported producer-side framework APIs
- do not describe them as evidence of live in-repo business producers
- the transport path is structurally valid, but no confirmed producer usage was
  found in the repo beyond `NotificationManager` calling `WebSocketPublisher`

That means the notification panel, badge, REST API, WS token bootstrap, and WS
transport are real runtime surfaces; producer adoption in business modules is
still unproven by current audit evidence.

## Practical Flow

1. `_status-bar.phtml` boots the unread badge and first WS token for authenticated users.
2. Si `wsAvailable=true`, `status-bar.js` opens `/ws` and authenticates with that token.
3. If browser WS is unavailable, `status-bar.js` starts REST polling against `GET /api/notifications/unread-count`.
4. If the WS token becomes invalid, `status-bar.js` calls `GET /api/ws-token`.
5. Opening the panel calls `GET /api/notifications?limit=30`.
6. Clicking an unread item calls `POST /api/notifications/{id}/read`.
7. Clicking "Mark all read" calls `POST /api/notifications/read-all`.
8. Real-time payloads, when published, increment the badge and prepend into the open panel.

## Summary

- this module has real user-facing runtime impact even without Repository views
- `/api/ws-token` is a live entry point
- `/api/notifications/unread-count` has a real runtime consumer in REST fallback mode
- `read_at`, not `is_read`, is the live read/unread contract
- the status bar is the current notification UI
- producer-side classes remain available, but their business adoption is not yet
  proven by current in-repo callers
