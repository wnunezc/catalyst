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

## Current Behavior

Run:

```powershell
php public/cli.php quality:check
```

The gate runs Composer validation/audit, route lint, structural lint, security check and status. Route, structural and security failures block. Local `status` warnings can be environment-bound when host Windows cannot resolve Docker-only service names.

## Operational Notes

For documentation reconciliation also run `docs:inventory`, `docs:sync-runtime`, `route:list`, `inspect:lint`, `route:lint` and `git diff --check` explicitly.

## Related Documentation

- `docs/testing.md`
- `docs/documentation-contract.md`
- `docs/deployment.md`