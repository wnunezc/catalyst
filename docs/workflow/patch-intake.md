# Patch Intake Workflow

## Purpose

Review external zips, patches or AI-generated edits outside the active Catalyst
tree before applying anything to the repository.

## Intake Directory

Use a temporary directory outside the repo, for example:

```powershell
$intake = 'D:\OpsZone\DevWorkspace\Backups\catalyst-patch-intake'
```

Do not extract untrusted archives inside `Projects/Web/catalyst`.

## Intake Checklist

1. Create a clean intake folder.
2. Extract the zip or patch into that folder only.
3. List top-level contents and verify there is no nested project root surprise.
4. Scan for forbidden or sensitive files:
   - `.env`, `.env.*`, `secrets.json`
   - DKIM private keys
   - `boot-core/storage/**`
   - logs, PID/stamp files
   - `public/uploads/**`
   - vendor payloads not already approved
5. Inspect intended file changes before copying:
   - changed PHP classes
   - changed templates/views
   - changed JS/CSS assets
   - changed routes/module manifests
   - changed config templates
6. Reject the patch if it modifies `vendor/`, adds Composer dependencies, ships
   secrets, bypasses CSP, weakens auth/RBAC, or changes bootstrap order without
   an explicit approved task.

## Pre-Apply Commands

Run from the active repo before applying a reviewed patch:

```powershell
git status --short --branch
composer validate --strict
php public/cli.php quality:check
git diff --check
```

Expected:

- worktree is clean or unrelated local changes are understood;
- blocker checks pass;
- accepted WSDD host warnings are documented in `docs/quality-gate.md`.

## Apply Rule

Apply reviewed files deliberately. Do not bulk-copy an extracted archive over
the repository root.

Preferred order:

1. Apply source/runtime files.
2. Apply view/assets files.
3. Apply docs/config templates.
4. Refresh generated docs if routes, modules, scripts or templates changed.

## Post-Apply Commands

Use the narrowest relevant checks plus the standard gate:

```powershell
composer dump-autoload
php public/cli.php quality:check
php public/cli.php route:bootstrap-regression
php public/cli.php docs:inventory --json
php public/cli.php docs:sync-runtime --stdout
git diff --check
```

If the patch touches a Phase 6-normalized surface, also run the matching
regression:

```powershell
php public/cli.php automation:mvc-regression
php public/cli.php documents:mvc-regression
php public/cli.php roles:mvc-regression
php public/cli.php media:mvc-regression
php public/cli.php operations:requests-regression
php public/cli.php modules:localization-regression
```

## Evidence Required

Before commit, record:

- source archive/patch name and intake folder;
- rejected files or accepted exclusions;
- files applied to the repo;
- verification commands and outcomes;
- residual risk or deferred work.

Store evidence under a relevant `docs/audits/**` path for non-trivial patches.
