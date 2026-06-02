# Reusable Base Install Workflow

## Purpose

Use Catalyst as a base for a new project without inheriting local runtime state,
secrets, historical archives or project-specific residues.

## Source Requirements

Start from a clean Git checkout or a reviewed release export. Do not start from
an ad-hoc zip created from an active development tree.

Before copying:

```powershell
git status --short --branch
php public/cli.php quality:check
```

## Files To Keep

Keep:

- `app/`
- `Repository/Framework/`
- `Repository/App/` starter surfaces that are intentionally part of the base
- `boot-core/routes/`
- `boot-core/template/`
- `boot-core/config/templates/`
- `public/`
- `docs/`
- `composer.json`, `composer.lock`
- `AGENTS.md`, `STRUCTURE.md`, `API.md`, `README.md`

## Files To Exclude Or Recreate

Do not carry over:

- `boot-core/config/env/.env`
- real `boot-core/config/env/.env.*` variants
- `boot-core/config/*/secrets.json`
- `boot-core/config/dkim/`
- `boot-core/storage/logs/`
- `boot-core/storage/throttle/`
- `boot-core/storage/runtime/`
- `boot-core/storage/*.pid`
- `boot-core/storage/*.stamp`
- `public/uploads/devtools/`
- ad-hoc archives such as `*.zip`
- IDE-local files

## Rename And Configure

1. Choose the target project folder and host name.
2. Copy the reviewed base into the target folder.
3. Run:

```powershell
composer install
composer dump-autoload
```

4. Create local `.env` and config from templates.
5. Set project-specific:
   - app name/URL;
   - DB database/user/password;
   - mail credentials;
   - OAuth credentials if used;
   - storage credentials if used.
6. Run the setup wizard and create the initial admin.

## Baseline Verification

```powershell
composer validate --strict
composer audit
php public/cli.php help
php public/cli.php status
php public/cli.php quality:check
php public/cli.php route:bootstrap-regression
php public/cli.php docs:inventory --json
php public/cli.php docs:sync-runtime --stdout
git diff --check
```

## Project-Specific Reset Points

For a reusable base, decide explicitly whether to keep or replace:

- `Repository/App/Surface/*` sample surfaces;
- public demo surfaces;
- development fixtures;
- generated runtime docs timestamps;
- branding and appearance defaults.

Do not remove framework-owned modules as a cleanup shortcut. If a target project
does not need a module, disable or hide it through the approved module/feature
flag mechanisms first.

## Handoff Artifact

Each new project should retain:

- its own `AGENTS.md` if constraints differ from Catalyst;
- local setup notes;
- first quality gate output;
- list of any base modules intentionally disabled.
