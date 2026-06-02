# Phase 7A - Future Workflow Without Direct Zips

Date: 2026-06-02

Status: implemented and verified / pending commit.

## Scope Completed

- `7A.1` patch intake flow:
  `docs/workflow/patch-intake.md`.
- `7A.2` first-run workflow:
  `docs/workflow/first-run.md`.
- `7A.3` reusable base install workflow:
  `docs/workflow/reusable-base-install.md`.

## Contracts

- External zips/patches are inspected outside `Projects/Web/catalyst` before
  applying any file to the active tree.
- Direct archive overwrite of the repo root is rejected.
- First-run setup routes through Composer, local secrets, setup wizard contract
  and `quality:check`.
- Reusable base installs exclude runtime storage, secrets, DKIM keys, uploads
  and ad-hoc archives.

## Verification Plan

```powershell
php public/cli.php docs:inventory --json
php public/cli.php docs:sync-runtime --stdout
php public/cli.php quality:check
git diff --check
```

Verification result:

- `php public/cli.php docs:inventory --json` -> PASS; 622 symbols, 229 templates, 54 scripts.
- `php public/cli.php docs:sync-runtime --stdout` -> PASS; 18 modules.
- `php public/cli.php quality:check` -> PASS.
- `git diff --check` -> PASS.

## Residual Scope

- Runtime automation for patch intake is not implemented in this pass.
- Release packaging remains governed by `docs/deployment.md` until a dedicated
  release/export command is approved.
