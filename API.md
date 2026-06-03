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

## Operational Notes

Run `php public/cli.php docs:inventory --json` after class/template/script changes and `php public/cli.php docs:sync-runtime --stdout` after module, route, asset, permission or settings changes.

## Related Documentation

- `docs/architecture.md`
- `docs/runtime-inventory.md`
- `docs/runtime-module-catalog.md`
- `STRUCTURE.md`