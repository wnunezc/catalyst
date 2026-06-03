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

- `.htaccess`
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

1. Choose the target site root and host name.
2. Copy the reviewed base contents into the target site root. For XAMPP this can
   be `htdocs` itself; do not require a wrapper folder named `catalyst`.
3. Initialize the application repository and keep Catalyst as upstream:

```powershell
git remote rename origin upstream
git remote add origin <your-application-repository-url>
git fetch upstream --tags
```

If the application repository was already initialized, keep `origin` pointing to
the application repository and add Catalyst separately:

```powershell
git remote add upstream https://github.com/wnunezc/catalyst.git
git fetch upstream --tags
```

4. Run:

```powershell
composer install
composer dump-autoload
```

5. Create local `.env` and config from templates.
6. Set project-specific:
   - app name/URL;
   - DB database/user/password;
   - mail credentials;
   - OAuth credentials if used;
   - storage credentials if used.
7. Configure the web server:
   - preferred: point the VirtualHost document root to `public/`;
   - fallback: allow the root `.htaccess` to forward project-root requests to
     `public/` transparently.
8. Run the setup wizard and create the initial admin.

## Update Workflow

Check local version metadata:

```powershell
php public/cli.php version
php public/cli.php update:check
```

When a new Catalyst release is available, update manually through Git:

```powershell
git fetch upstream --tags
git merge v0.1.1
php public/cli.php quality:check
```

Use the actual target tag instead of `v0.1.1`. `update:check` does not modify
files, branches or remotes; it only reports version information and suggested
commands.

Before and after merging an upstream release, run:

```powershell
php public/cli.php inspect:lint
```

The `app_boundary` check protects derived projects from placing app-owned code
or source assets outside `Repository/App`. See `docs/app-boundary.md` for the
full ownership contract and update checklist.

Keep application-specific implementation inside `Repository/App/` whenever
possible. Framework updates are expected to touch `app/`, `Repository/Framework/`,
`boot-core/`, shared `public/assets/*/catalyst/` files and current documentation.
If a release needs to change the application boundary, the release notes must
call that out explicitly.

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

Also verify the configured browser URL does not require `/catalyst` or another
project-folder segment. Catalyst must be able to run from any physical directory
name because runtime paths are resolved from the project structure, not from a
literal folder name.

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
- configured web URL and whether Apache serves `public/` directly or via the
  root `.htaccess` fallback.
- configured `origin` and `upstream` remotes.
- current `php public/cli.php version` output.
