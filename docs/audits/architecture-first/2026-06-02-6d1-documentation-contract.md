# Task 6D.1 - Documentation Contract And Inventory

Date: 2026-06-02

Status: implemented and verified / included in the Task 6D.1 checkpoint.

## Scope Completed

- Added `docs/documentation-contract.md` as the repo-local contract for hot,
  warm and cold documentation classes.
- Added `docs:inventory` to generate the exhaustive symbol/template/script
  inventory from filesystem scans and PHP token parsing.
- Generated `docs/runtime-inventory.md` as the machine-verifiable inventory.
- Kept `STRUCTURE.md` as a curated dictionary and routed exhaustive lookups to
  generated docs.
- Documented risk-boundary comment policy for bootstrap, routing, cache,
  middleware, public repositories and security-sensitive services.

## Inventory Snapshot

Current `docs:inventory --json` counts:

- Symbols: 622
- Templates: 229
- Scripts: 54

## Verification Plan

```powershell
php public/cli.php docs:inventory --json
php public/cli.php docs:sync-runtime --stdout
php public/cli.php quality:check
git diff --check
```

## Residual Scope

- `6D.2` documents canonical JavaScript contracts over the scripts surfaced by
  `docs/runtime-inventory.md`.
- `6D.3` uses the template inventory to plan executable PHP template migration.
- `6D.4` classifies inline assets and CSP transport payloads.
