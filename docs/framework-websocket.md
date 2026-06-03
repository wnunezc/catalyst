# Catalyst\Framework\WebSocket

## Purpose

Document websocket token, server and publisher primitives.

## Runtime Owners

| Concern | Owner |
|---|---|
| Sends notification and resource payloads to the internal WebSocket publisher endpoint. | `Catalyst\Framework\WebSocket\WebSocketPublisher` |
| Authenticates connections, manages subscriptions and broadcasts user or resource payloads. | `Catalyst\Framework\WebSocket\WebSocketServer` |
| Issues and verifies stateless WebSocket authentication tokens with tenant context. | `Catalyst\Framework\WebSocket\WebSocketToken` |

## Current Behavior

This file is regenerated from current PHP docblocks and the runtime inventory scope for `Catalyst\Framework\WebSocket`. It intentionally replaces stale historical API notes with the classes and methods that exist in code now.

## API From Docblocks

### `Catalyst\Framework\WebSocket\WebSocketPublisher`

- File: `app/Framework/WebSocket/WebSocketPublisher.php`
- Kind: `class`
- Summary: Internal HTTP adapter for pushing notification payloads into the WS server.
- Responsibility: Sends notification and resource payloads to the internal WebSocket publisher endpoint.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `publish()` | `public` | POST a notification payload to the WS server's internal publisher. | POST a notification payload to the WS server's internal publisher. |
| `publishToResource()` | `public` | Publishes a presence payload for a tenant resource. | Publishes a presence payload for a tenant resource. |
| `dispatchPayload()` | `private` | Sends a payload to the internal WebSocket publisher endpoint. | Sends a payload to the internal WebSocket publisher endpoint. |
| `publisherUrl()` | `private` | Resolves the configured internal WebSocket publisher URL. | Resolves the configured internal WebSocket publisher URL. |

### `Catalyst\Framework\WebSocket\WebSocketServer`

- File: `app/Framework/WebSocket/WebSocketServer.php`
- Kind: `class`
- Summary: Ratchet WebSocket server — manages connections and per-user broadcasts.
- Responsibility: Authenticates connections, manages subscriptions and broadcasts user or resource payloads.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `__construct()` | `public` | Initializes the Web Socket Server instance. | Initializes the Web Socket Server instance. |
| `onOpen()` | `public` | Registers a newly opened WebSocket connection. | Registers a newly opened WebSocket connection. |
| `onMessage()` | `public` | Processes authentication, subscription and heartbeat client messages. | Processes authentication, subscription and heartbeat client messages. |

### `Catalyst\Framework\WebSocket\WebSocketToken`

- File: `app/Framework/WebSocket/WebSocketToken.php`
- Kind: `class`
- Summary: Generates and verifies HMAC-signed WebSocket authentication tokens.
- Responsibility: Issues and verifies stateless WebSocket authentication tokens with tenant context.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `generate()` | `public` | Generate a signed WS auth token for the given user. | n/a |
| `verify()` | `public` | Verify a WS auth token and return the user ID, or null on failure. | n/a |
| `verifyContext()` | `public` | Verifies a token and returns its user and tenant context. | n/a |
| `key()` | `private` | Read APP_KEY from environment. | n/a |

## Operational Notes

When PHP symbols or method contracts in this namespace change, refresh this document from docblocks and run `php public/cli.php docs:inventory --json`.

## Related Documentation

- `docs/runtime-inventory.md`
- `docs/runtime-module-catalog.md`
- `docs/harness-context-map.md`
