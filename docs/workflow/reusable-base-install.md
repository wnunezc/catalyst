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
- active local development config:
  - `boot-core/config/development/app.json`
  - `boot-core/config/development/db.json`
  - `boot-core/config/development/session.json`
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

5. Create local `.env` and config from templates, then run:

```powershell
php public/cli.php config:sync
```

   `config:sync` creates missing active development config from
   `app.example.json`, `db.example.json` and `session.example.json`, then adds
   any new template keys without replacing project-specific values.
6. Set project-specific:
   - app name/URL;
   - DB database/user/password;
   - mail credentials;
   - OAuth credentials if used;
   - storage credentials if used.
7. If the database is totally empty and a developer runs migrations before the
   setup wizard, bootstrap the setup SQL first:

```powershell
php -r "require 'boot-core/requirement-loader/error-catcher.php'; require 'vendor/autoload.php'; Catalyst\Repository\Settings\Services\SetupDatabaseService::make()->open();"
php public/cli.php migrate
```

   `migrate` and `migrate:status` also print this guidance when the migration
   path cannot proceed against a clean derived install.
8. Configure the web server:
   - preferred: point the VirtualHost document root to `public/`;
   - fallback: allow the root `.htaccess` to forward project-root requests to
     `public/` transparently.
9. Run the setup wizard and create the initial admin.

## Update Workflow

Check local version metadata:

```powershell
php public/cli.php version
php public/cli.php update:check
```

When a new Catalyst release is available, update manually through Git:

```powershell
git fetch upstream --tags
git merge v0.1.0-rc.6
composer install
php public/cli.php config:sync
php public/cli.php config:contract-smoke --json
php public/cli.php migrate:status
php public/cli.php migrate
php public/cli.php admin-navigation:smoke --json
php public/cli.php quality:check
```

Use the actual target tag instead of `v0.1.0-rc.6`. `update:check` does not modify
files, branches or remotes; it only reports version information and suggested
commands.

For `v0.1.0-rc.6`, expect framework-owned changes in:

- reusable-base first-run JSON templates under `boot-core/config/development/*.example.json`;
- local config protection rules in `.gitignore`;
- local config sync and contract smoke commands under `app/Framework/Cli/Commands/`;
- the local config merge service under `app/Framework/Config/`;
- migration contracts and new organization hierarchy tables under `boot-core/database/migrations/`;
- RBAC/role administration surfaces under `Repository/Framework/Roles/`;
- shared admin form multi-select rendering under `app/Framework/Admin/Form/` and `boot-core/template/components/admin-form-builder/`;
- organization hierarchy primitives under `app/Framework/Organization/`;
- admin navigation projection under `app/Framework/Navigation/`;
- structural lint guard under `app/Framework/Module/ModuleLinter.php`;
- admin shell bridge under `boot-core/template/scope/layouts/_demo-product-shell.php`;
- runtime documentation generated by `docs:sync-runtime` and `docs:inventory`.

These changes should not require deleting local application work. Keep derived application changes in `Repository/App/` and resolve merge conflicts by preserving project-specific code there while accepting framework updates in `app/`, `boot-core/` and `Repository/Framework/`.

After merging `v0.1.0-rc.5` or later, keep project-specific active config local.
If Git reports deletions for `boot-core/config/development/app.json`,
`db.json` or `session.json`, accept the upstream removal from the index but keep
the files on disk. They are intentionally ignored and owned by the derived
project. Run `php public/cli.php config:sync` after the merge to receive new
default keys without resetting app URL, database credentials or session name.

After merging `v0.1.0-rc.2` or later, administrators must populate organization hierarchy metadata manually from:

```text
/users/organization-hierarchy
```

No default organization, scope, level or unit rows are seeded. Create an organization first, then scopes and levels, then classify roles from `/users/roles/create` or role edit screens.

Before and after merging an upstream release, run:

```powershell
php public/cli.php inspect:lint
php public/cli.php config:contract-smoke --json
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
