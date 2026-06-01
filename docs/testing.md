# Testing Guide

This file is a thin navigation index for Catalyst's current verification surface.

It exists to satisfy the generic Phase 4 target without duplicating the split documentation already aligned to the real runtime.

## Scope

Catalyst does not ship a dedicated Phase 6 toolchain in the current approved stream.

- No `PHPUnit` bootstrap is treated as canonical in this stream.
- No `PHPStan` or other new dev dependencies are part of this document set.
- Current verification remains centered on runtime smoke checks, targeted checklists, and subsystem-specific docs.

## Canonical references

- CLI/runtime smoke and command surface: `TERMINAL.md`, `docs/entry-points.md`
- Official reversible auth/RBAC fixtures: `php public/cli.php fixtures:auth --help`
- Per-module harness matrix: `php public/cli.php inspect:harness --json`
- Living runtime catalog: `docs/runtime-module-catalog.md`, `php public/cli.php docs:sync-runtime`
- Canonical full smoke: `npm run qa:catalyst:buckets`
- Setup flow verification: `docs/checklists/setup-completion-e2e.md`
- Security/frontend checks: `docs/security-conventions.md`, `docs/deployment.md`
- Auth behavior under test: `docs/framework-auth.md`, `docs/repository-auth.md`
- DevTools/runtime harness coverage: `docs/repository-devtools.md`
- Notification/status bar runtime coverage: `docs/repository-notification.md`

## Usage note

Use this file when a task starts from the broad label `testing`.
For the actual behavior contract of each subsystem, read the split documents above.
When a test needs auth/RBAC state changes, prefer `fixtures:auth` snapshot slots over ad-hoc SQL or manual baseline rewriting.
When a test needs to assert auth runtime state, prefer `fixtures:auth --field`, `--password-check` and `--token-counts` over direct SQL checks.
When a test needs forced MFA setup or challenge state, prefer `fixtures:auth --set-mfa-enabled` plus the real MFA runtime flow instead of synthetic session hacks.
