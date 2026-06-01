# Routing Index

This file is a thin navigation index for Catalyst's routing and dispatch model.

It exists to satisfy the generic Phase 4 target without duplicating the runtime story already split across architecture, kernel, and module docs.

## Canonical references

- Route loading order and runtime model: `docs/architecture.md`
- Bootstrap, dispatch, route cache, and global middleware entry: `docs/kernel.md`
- Web and CLI entry points: `docs/entry-points.md`
- Base controller request/response helpers: `docs/framework-controllers.md`
- Module route surfaces:
  - Auth: `docs/repository-auth.md`
  - DevTools: `docs/repository-devtools.md`
  - Notification: `docs/repository-notification.md`
- Navigation and route ownership taxonomy: `docs/navigation-route-refactor-plan.md`
- Living runtime route truth: `php public/cli.php route:list --json`, `php public/cli.php docs:sync-runtime`, `docs/runtime-module-catalog.md`
- Historical route matrix snapshot: `docs/navigation-route-matrix-222.md`

## Scope note

The project does not keep a separate long-form generic routing manual.
The canonical routing story is intentionally split by concern: architecture, kernel bootstrap, and module route surfaces.

Route visibility is separate from route existence. Canonical sidebar entries, contextual CRUD routes, technical helpers, callbacks and normalization behavior are classified in `docs/navigation-route-refactor-plan.md`; the living router universe should be read from `route:list --json` and `docs/runtime-module-catalog.md`.

## Usage note

Use this file when a task starts from the broad label `routing`.
