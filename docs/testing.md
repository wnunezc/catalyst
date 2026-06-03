# Testing Guide

## Purpose

List the current verification commands that represent Catalyst runtime readiness.

## Runtime Owners

| Concern | Owner |
|---|---|
| Quality gate | `Catalyst\Framework\Cli\Commands\QualityCheckCommand` |
| Structural lint | `Catalyst\Framework\Cli\Commands\InspectLintCommand` |
| Route lint | `Catalyst\Framework\Cli\Commands\RouteLintCommand` |
| Runtime docs inventory | `Catalyst\Framework\Cli\Commands\DocsInventoryCommand` |
| Runtime module sync | `Catalyst\Framework\Cli\Commands\DocsSyncRuntimeCommand` |

## Current Behavior

The standard documentation/runtime verification set is:

```powershell
php public/cli.php docs:inventory --json
php public/cli.php docs:sync-runtime --stdout
php public/cli.php route:list --json
php public/cli.php inspect:lint
php public/cli.php route:lint
php public/cli.php quality:check
git diff --check
```

## Operational Notes

`quality:check` includes `status`, which may emit environment-bound DNS warnings from host Windows for Docker-only service names. Treat those as acceptable only when the gate reports ready and the failing check is known to be host-environment specific.

## Related Documentation

- `docs/quality-gate.md`
- `docs/documentation-contract.md`
- `docs/runtime-module-catalog.md`