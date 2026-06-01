# Runtime Model

This file is a thin navigation index for Catalyst's runtime model.

It exists to satisfy the generic Phase 4 target and to keep the canonical runtime statements discoverable through a dedicated broad entry point.

## Canonical statements

- Web requests run through `public/index.php` and execute `Kernel::bootstrap()->run()` once per request.
- CLI commands run through `public/cli.php` once per invocation.
- Singletons are an accepted design choice in Catalyst's current request-response / short-lived CLI model.
- Catalyst is not currently documented as a general long-running HTTP runtime.
- The Ratchet WebSocket server is a separate CLI process boundary and does not make the main HTTP stack worker-safe.

## Canonical references

- Architecture overview and runtime boundary: `docs/architecture.md`
- Entry point split (`index.php` / `cli.php`): `docs/entry-points.md`
- Bootstrap and request dispatch details: `docs/kernel.md`
- CLI surface and operational commands: `TERMINAL.md`

## Usage note

Use this file when a task starts from the broad label `runtime model`.
The detailed runtime wording still lives primarily in `docs/architecture.md`.
