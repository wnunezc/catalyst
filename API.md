# Catalyst Framework API Reference

## Purpose

Index the current framework API documentation. Method-level detail in the split docs is generated from PHP docblocks and should be treated as the current source unless runtime inventory says otherwise.

## Runtime Owners

| Concern | Owner |
|---|---|
| Framework class inventory | `docs/runtime-inventory.md` |
| Module and route catalog | `docs/runtime-module-catalog.md` |
| Architecture and docs index | `docs/architecture.md` |
| Technical structure dictionary | `STRUCTURE.md` |

## Current Behavior

Catalyst API documentation is split by namespace and subsystem:

| Area | Documents |
|---|---|
| Core lifecycle | `docs/architecture.md`, `docs/runtime-model.md`, `docs/entry-points.md`, `docs/kernel.md` |
| Routing and guards | `docs/routing.md`, `docs/middleware.md` |
| Views and security | `docs/views.md`, `docs/framework-view.md`, `docs/security-conventions.md` |
| Framework namespaces | `docs/framework-*.md` |
| Helper namespaces | `docs/helpers-*.md` |
| Repository modules | `docs/repository-*.md`, `docs/runtime-module-catalog.md` |
| Generated inventories | `docs/runtime-inventory.md`, `docs/runtime-module-catalog.md` |
| Operations | `docs/testing.md`, `docs/quality-gate.md`, `docs/deployment.md` |

## Public Versioned APIs

The ROADMAP-3 public API contract contains exactly these 13 bearer-token routes:

| Owner | Method | Path |
|---|---|---|
| Workspaces | GET | `/api/v1/document-templates` |
| Workspaces | GET | `/api/v1/document-templates/{id}` |
| Workspaces | POST | `/api/v1/document-templates/{id}/preview` |
| Workspaces | POST | `/api/v1/document-templates/{id}/export` |
| API | GET | `/api/v1/catalog` |
| API | GET | `/api/v1/workflows` |
| API | POST | `/api/v1/workflows/{id}/transition` |
| API | GET | `/api/v1/calendar/events` |
| API | GET | `/api/v1/versions/{resourceKey}/{recordId}` |
| API | POST | `/api/v1/versions/{id}/restore` |
| Operations | GET | `/api/v1/automation-rules` |
| Operations | GET | `/api/v1/automation-rules/{id}` |
| Operations | POST | `/api/v1/automation-rules/{id}/run` |

These routes preserve `ApiTokenMiddleware`, abilities, throttling and payload/error contracts.

## Internal Runtime Transports

Session-authenticated shell transports are not public APIs. Their canonical
family is `/runtime/*`:

- `/runtime/notifications`
- `/runtime/notifications/unread-count`
- `/runtime/notifications/read-all`
- `/runtime/notifications/{id}/read`
- `/runtime/presence/{resourceKey}/{recordId}/heartbeat`
- `/runtime/websocket/token`
- `/runtime/flash/dismiss`

The former `/api/notifications*`, `/api/presence*`, `/api/ws-token` and
`/api/public/*` routes are removed. No compatibility aliases or app companion
replacements are active.

## Operational Notes

Run `php public/cli.php docs:inventory --json` after class/template/script changes and `php public/cli.php docs:sync-runtime --stdout` after module, route, asset, permission or settings changes.

## Related Documentation

- `docs/architecture.md`
- `docs/repository-workspaces.md`
- `docs/repository-operations.md`
- `docs/runtime-inventory.md`
- `docs/runtime-module-catalog.md`
- `STRUCTURE.md`
