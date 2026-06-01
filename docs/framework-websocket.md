# Catalyst\Framework\WebSocket

Directory: `app/Framework/WebSocket/`

## Purpose

This subsystem provides the realtime transport used by the shared status bar:

- short-lived browser auth tokens
- authenticated browser connection to `/ws`
- optional internal HTTP publish path into the WS server
- resource-scoped presence fanout over the same authenticated socket

It is transport infrastructure, not the notification UI itself.

## Runtime Config Source

Effective WebSocket config is resolved in this order:

1. `/setup` JSON config via `ConfigManager` (`websocket.json`, entry `websocket`)
2. `.env` defaults

The same effective config is consumed by:

- `WebSocketBootMiddleware`
- `WebSocketPublisher`
- `WebSocketToken`
- `boot-core/bin/websocket-server.php`

## Browser-Facing Runtime

Shared UI integration lives outside this directory:

- `boot-core/template/components/_status-bar.phtml`
- `public/assets/js/catalyst/modules/status-bar.js`

Runtime flow:

1. `_status-bar.phtml` injects `window.__catalystWs` for authenticated users.
2. If `wsAvailable=true`, the browser connects to `/ws`.
3. `StatusBarManager` sends `{ action: "auth", token }`.
4. After auth, the same client may subscribe/unsubscribe to `{ tenant_id, resource_key, record_id }` presence scopes.
5. If the token expires or is rejected, the client refreshes it through `GET /api/ws-token`.
6. If `wsAvailable=false` or the host is not browser-usable, the shared status bar degrades to REST unread polling via `/api/notifications/unread-count`.

For claim-derived presence, the browser fallback remains:

- initial server-side claim snapshot
- owner heartbeat through `POST /api/presence/{resourceKey}/{recordId}/heartbeat`

No second realtime transport is introduced when WS is unavailable.

`/api/ws-token` is therefore a live app entry point tied to the WS transport.

## Important Runtime Flags

- `enabled`
  - `false`: middleware skips boot and `boot-core/bin/websocket-server.php` exits without starting the server
  - `true`: runtime may auto-boot or publish as usual
- `ws_host`
- `ws_port`
- `ws_internal_port`
- `ws_publisher_url`

## Core Classes

### `WebSocketToken`

- issues and verifies short-lived auth tokens
- uses the effective application key from `app.project.project_key` when available
- carries tenant context and verifies it before joining authenticated scopes

### `WebSocketServer`

- Ratchet server handling `/ws`
- authenticates clients through `WebSocketToken`
- broadcasts notification payloads to connected users
- handles `subscribe` / `unsubscribe` actions for resource-scoped presence rooms
- emits `type: "presence"` envelopes keyed by `tenant_id + resource_key + record_id`

### `WebSocketPublisher`

- internal HTTP adapter that POSTs to the WS server's publisher endpoint
- reads the effective `ws_publisher_url`
- returns `false` silently when the WS server is unavailable
- can publish both notification payloads and resource-scoped presence payloads

Current audit conclusion:

- keep `WebSocketPublisher` as a supported low-level adapter
- do not present it as proof of active business producers in the repo
- current evidence only confirms the structural chain `NotificationManager -> WebSocketPublisher`

The publisher endpoint is internal transport plumbing, not a public browser route.

## Operational Entry Point

Standalone server process:

```powershell
php boot-core/bin/websocket-server.php
```

## What This Doc Does Not Claim

- it does not claim that business modules actively emit notifications today
- it does not claim that REST notification reads are handled here; those belong to `Repository/Framework/Notification/`
- it does not claim that REST fallback replaces the WS transport; unread-count polling is only the browser-side degradation path when WS is unavailable

## Summary

- WS transport is alive
- `/api/ws-token` is part of the live runtime contract
- resource-scoped presence reuse lives on the same authenticated WS transport
- unread-count REST polling exists as browser fallback outside this subsystem's core transport classes
- browser UI lives in the shared status bar, not in this framework directory
- `WebSocketPublisher` remains available, but producer-side adoption is still unproven beyond framework self-wiring
