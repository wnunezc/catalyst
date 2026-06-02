# Phase 6B - Architecture Normalization

Date: 2026-06-01

Status: implemented and verified / pending user review and commit.

## Scope Completed

- Made route-cache bootstrap preserve global middleware and module view paths.
- Added `route:bootstrap-regression` for middleware, view namespaces and route order.
- Normalized route discovery order: global, optional API, Framework, App.
- Extracted canonical redirects from DevTools into core.
- Migrated Auth and Notification metadata from internal declarations to `module.php`.
- Added missing throttles in Operations and Notification, including presence heartbeat.
- Moved `dev:export-overlay` into explicitly registered framework CLI commands.
- Moved shared public support outside `Repository/App/Surface/`.
- Removed three confirmed unused App residues.
- Updated DevTools UML content without changing its layout or visual surface.
- Reconciled hot setup, cache and entry-point documentation.

## Deleted Confirmed Residues

- `Repository/App/Surface/Demo/Controllers/AppDemoController.php`
- `Repository/App/Surface/PublicSupport/Support/PublicNavigationBuilder.php`
- `Repository/App/Surface/PublicSupport/Support/PublicRuntimeSnapshot.php`

## Verification

- `composer validate --strict` -> PASS
- `composer audit` -> PASS
- `php public/cli.php quality:check` -> PASS
- `php public/cli.php route:bootstrap-regression` -> PASS
- `php public/cli.php route:list --json` -> PASS
- `php public/cli.php dev:export-overlay --help` -> PASS
- `git diff --check` -> PASS
- stale-reference scan for moved/deleted classes -> PASS

Expected local WSDD warnings remain limited to host DNS resolution for
database-backed queue and scheduler checks.

## Next Step

Review and commit the Phase 6B diff. Then start `6C.1`: reduce the Automation
controller responsibility surface without changing its routes or behavior.
