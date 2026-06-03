# Quality Gate

## Purpose

Define the local pre-commit/pre-push quality check for Catalyst.

## Runtime Owners

| Concern | Owner |
|---|---|
| Quality gate orchestration | `Catalyst\Framework\Cli\Commands\QualityCheckCommand` |
| Route contract | `Catalyst\Framework\Cli\Commands\RouteLintCommand` |
| Structural contract | `Catalyst\Framework\Cli\Commands\InspectLintCommand` |
| Security scan | `Catalyst\Framework\Cli\Commands\SecurityCheckCommand` |
| Status report | `Catalyst\Framework\Cli\Commands\StatusCommand` |
| Docblock responsibility review | Maintainer release review |

## Current Behavior

Run:

```powershell
php public/cli.php quality:check
```

The gate runs Composer validation/audit, route lint, structural lint, security check and status. Route, structural and security failures block. Local `status` warnings can be environment-bound when host Windows cannot resolve Docker-only service names.

For release work, PHP classes and methods changed by the RC must have docblocks
with a real `Responsibility:` line. The responsibility must not be a copy of the
summary; it must state the operational boundary owned by the class or method,
including whether it validates, coordinates, mutates, serializes, renders or
keeps side effects out of the contract.

## Operational Notes

For documentation reconciliation also run `docs:inventory`, `docs:sync-runtime`, `route:list`, `inspect:lint`, `route:lint` and `git diff --check` explicitly.

## Related Documentation

- `docs/testing.md`
- `docs/documentation-contract.md`
- `docs/deployment.md`
