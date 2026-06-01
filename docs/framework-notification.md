# Catalyst\Framework\Notification

Directory: `app/Framework/Notification/`

## Purpose

The framework currently exposes two different notification concerns under one
namespace. Keeping them separate mentally avoids most of the historical drift.

1. Response/UI notifications
   - toasters
   - modal instructions
   - inline alerts
   - carried in JSON responses through `NotificationBag`

2. Persisted user notifications
   - rows stored in `notifications`
   - unread/read lifecycle via `read_at`
   - optional realtime delivery over WebSocket
   - surfaced in the shared status-bar panel

## Response/UI Notification Primitives

These classes support JSON/AJAX UX flows and are actively used by controller and
frontend response handling.

### `NotificationType`

File: `app/Framework/Notification/NotificationType.php`

String-backed enum for semantic notification types and UI mappings.

Representative cases:

- `success`
- `error`
- `warning`
- `info`
- `danger`
- `primary`
- `secondary`

### `Notification`

File: `app/Framework/Notification/Notification.php`

Immutable DTO used inside `NotificationBag` for toasters and alerts.

### `NotificationBag`

File: `app/Framework/Notification/NotificationBag.php`

Collection object used by controllers and `JsonResponse` to attach:

- `toasters`
- `modals`
- `alerts`

Current confirmed repo usage includes:

- controller helpers in `app/Framework/Controllers/Controller.php`
- JSON settings save flows such as the dedicated `/setup/*` save controllers in `Repository/Framework/Settings/Controllers/`
- frontend processing through `public/assets/js/catalyst/modules/notification.js`

### `NotificationPosition`

File: `app/Framework/Notification/NotificationPosition.php`

Enum describing toaster placement vocabulary such as `top-right` and
`bottom-left`.

Current audit conclusion:

- keep it as a supported position vocabulary for the notification UI layer
- do not describe it as actively used by confirmed PHP callers in the current repo

## JSON Response Integration

`app/Framework/Http/JsonResponse.php` supports notification payloads through:

- `withNotification(NotificationBag $bag)`
- `withRedirect(string $url, int $delay = 300)`
- `withRefresh(int $delay = 300)`

Frontend handling lives in:

- `public/assets/js/catalyst/modules/http.js`
- `public/assets/js/catalyst/modules/notification.js`
- `public/assets/js/catalyst/modules/form-handler.js`
- `public/assets/js/catalyst/modules/modal.js`
- `public/assets/js/catalyst/modules/toaster.js`

## Persisted User Notification Runtime

These classes are about per-user notification records and realtime delivery.

### `NotificationRepository`

File: `app/Framework/Notification/NotificationRepository.php`

DB access layer for `notifications`.

Live contract:

- unread = `read_at IS NULL`
- read = `read_at` timestamp exists
- no `is_read` flag is used by the runtime

Primary operations:

- create a notification row
- list notifications
- count unread notifications
- mark one read
- mark all read

### `NotificationManager`

File: `app/Framework/Notification/NotificationManager.php`

Producer-side facade that persists a notification and then asks
`WebSocketPublisher` to push it in realtime.

Current audit conclusion:

- keep it as a supported producer-side API
- do not describe it as central evidence of active business producers
- no in-repo business callers were confirmed during the audit beyond the
  framework self-wiring inside this class

This means the class is structurally valid, but its adoption level in business
modules is currently unproven.

## Repository Module and Shared UI

Persisted user notifications are exposed to authenticated users through:

- `Repository/Framework/Notification/routes.php`
- `Repository/Framework/Notification/Controllers/NotificationController.php`
- `boot-core/template/components/_status-bar.phtml`
- `public/assets/js/catalyst/modules/status-bar.js`

Live user-facing capabilities:

- unread badge in the status bar
- notification panel
- REST listing
- mark-one-read
- mark-all-read
- WS token refresh through `/api/ws-token`
- realtime badge/panel updates when notification payloads are received

The Repository module itself has no dedicated views, but the runtime absolutely
does have a notification UI through the shared status bar.

## Relationship With WebSocket

The transport side lives under `app/Framework/WebSocket/`.

Important distinction:

- `framework-notification` owns the persisted user notification model and the
  producer-side facade
- `framework-websocket` owns tokening, server transport, and the internal HTTP
  publish adapter

Neither side should be documented as stronger than the current evidence:

- the status bar and token refresh flow are confirmed live
- the transport path is confirmed live
- producer-side business usage is not yet confirmed beyond framework plumbing

## Summary

- response notifications and persisted user notifications are different layers
- `NotificationBag` is active for JSON/UI workflows
- `NotificationRepository` is active for persisted per-user notifications
- `NotificationManager` remains available but should be documented honestly as a
  producer-side API without confirmed in-repo business adoption
- `NotificationPosition` remains available, but not as a confirmed actively used
  PHP contract
