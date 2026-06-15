# Catalyst Framework - Terminal Commands Reference

> Purpose: concise reference for the real CLI surface registered by `public/cli.php`
> Last Updated: 2026-06-15 (v0.2.0-rc.1 documentation consolidation)

## Entry Point

Run commands from the project root with:

```bash
php public/cli.php <command> [options]
```

The CLI is a separate runtime from the web entry point. `public/cli.php`
registers the built-in commands and dispatches them through `CliKernel`.

## Baseline Verification

```bash
composer dump-autoload
php public/cli.php help
php public/cli.php status
php public/cli.php security:check
php public/cli.php claims:list --active
```

## Commands Available

### Information

- `help` - list all available commands
- `version` - show framework and PHP version information
- `status` - basic environment health check

### Runtime Inspection

- `inspect:modules` - inspect registered modules, metadata, routes, assets and registry coverage
- `inspect:module <key|slug|name>` - inspect one module in detail
- `inspect:lint` - run structural framework lint on modules, registries, guards and work assets
- `inspect:harness` - inspect the real per-module harness matrix over static readable routes plus auth-flow state profiles; supports `--json`, `--module`, `--surface`
- `docs:sync-runtime` - generate or print the living runtime module catalog; supports `--stdout`, `--path`
- `route:list` - list resolved routes; supports `--json`
- `config:show <section>` - show effective configuration; supports `--json`
- `config:sync` - create missing local config files and merge new template keys without replacing local values; supports `--json`
- `config:contract-smoke` - verify local config examples, ignore rules and derived-update preservation; supports `--json`
- `security:check` - scan for CSP/frontend security hotspots
- `migrate:status` - list discovered migrations and applied state
- `feature-flags:list` - inspect the runtime feature flag catalog and effective states; supports `--json`
- `claims:list` - inspect reusable record claims and their current status; supports `--json`
- `plugin:list` - inspect plugin manifests and runtime enablement; supports `--json`
- `deploy:list` - inspect configured deployment profiles and recent runs; supports `--json`
- `tenancy:status` - inspect the official tenancy baseline and resolver output; supports `--json`

### Runtime Maintenance

- `key:generate` - generate an `APP_KEY`; use `--show` to print without writing
- `storage:clean` - clean runtime artifacts; supports `--dry-run`
- `devtools:disable` - disable debug-facing DevTools flags in config; supports `--dry-run`
- `fixtures:auth` - inspect/apply/revert official auth-RBAC fixtures, mutate QA users (roles, email verification, MFA) and probe runtime auth state (`--field`, `--password-check`, `--token-counts`) with reversible slots
- `dev:export-overlay` - export the live development auth/RBAC overlay back to `boot-core/database/create-catalyst-db.development.sql`
- `feature-flags:set` - change the default state of a mutable feature flag
- `claims:release` - release one reusable record claim; supports `--force` and `--json`
- `plugin:toggle` - enable or disable a plugin manifest at runtime
- `deploy:run` - execute the formal deployment pipeline for a configured profile; supports `--profile`, `--dry-run`
- `route:cache` - write route cache
- `route:clear` - clear route cache
- `queue:work` - process queued jobs; supports `--queue` and `--max-jobs`
- `queue:failed` - inspect failed jobs; supports `--json`
- `queue:retry` - requeue one failed job or all failed jobs
- `schedule:list` - list registered framework schedule tasks
- `schedule:run` - evaluate due tasks and queue them; supports `--task` and `--force`
- `concurrency:smoke` - canonical PA-01 DB-backed smoke for optimistic locking plus claim reclaim; supports `--json`
- `timeline:smoke` - canonical PA-09 DB-backed smoke for timeline start/stop/milestone semantics plus workflow capture; supports `--json`
- `catalogs:smoke` - canonical PA-11 DB-backed smoke for governed catalogs plus metadata form/grid consumption; supports `--json`
- `admin-navigation:smoke` - verify admin sidebar projection, canonical surfaces and derived app extension tolerance; supports `--json`

### Database

- `migrate` - run pending migrations
- `migrate:rollback` - rollback the latest migration batch

### Scaffolding

- `make:command`
- `make:controller`
- `make:crud`
- `make:migration`
- `make:middleware`
- `make:model`
- `make:module`
- `make:policy`
- `make:request`

## Common Usage

```bash
php public/cli.php help
php public/cli.php inspect:modules
php public/cli.php inspect:module framework.devtools
php public/cli.php inspect:module framework.configuration
php public/cli.php inspect:module framework.users
php public/cli.php inspect:module framework.account
php public/cli.php inspect:module framework.workspaces
php public/cli.php inspect:module framework.operations
php public/cli.php inspect:module framework.api
php public/cli.php inspect:lint
php public/cli.php inspect:harness --module framework.users --json
php public/cli.php inspect:harness --module framework.workspaces --json
php public/cli.php inspect:harness --module framework.operations --json
php public/cli.php inspect:harness --module framework.api --json
php public/cli.php docs:sync-runtime
php public/cli.php claims:list --active --json
php public/cli.php claims:release --resource=framework.demo --record-id=42 --force --json
php public/cli.php concurrency:smoke --json
php public/cli.php timeline:smoke --json
php public/cli.php catalogs:smoke --json
php public/cli.php feature-flags:list --json
php public/cli.php feature-flags:set --flag=module.framework.operations --enabled=1
php public/cli.php plugin:list --json
php public/cli.php plugin:toggle --plugin=framework.devtools --enabled=1
php public/cli.php deploy:list --json
php public/cli.php deploy:run --profile=local-preview --dry-run
php public/cli.php tenancy:status --json
php public/cli.php fixtures:auth --json
php public/cli.php fixtures:auth --user qa-auth --field email_verified --json
php public/cli.php fixtures:auth --user qa-auth --password-check "$env:CATALYST_E2E_PASSWORD" --json
php public/cli.php fixtures:auth --user qa-auth --token-counts --json
php public/cli.php fixtures:auth --user qa-admin --set-mfa-enabled 0 --json
php public/cli.php route:list --json
php public/cli.php config:show app --json
php public/cli.php config:sync --json
php public/cli.php config:contract-smoke --json
php public/cli.php admin-navigation:smoke --json
php public/cli.php key:generate --show
php public/cli.php version
php public/cli.php update:check
php public/cli.php storage:clean --dry-run
php public/cli.php devtools:disable --dry-run
php public/cli.php make:crud Catalog CatalogItem --fields="name:text!,slug:text!,description:textarea" --soft-deletes=1 --auditable=1
php public/cli.php make:policy CatalogItemPolicy --module=Catalog
php public/cli.php queue:work --queue=default --max-jobs=5
php public/cli.php queue:failed --json
php public/cli.php schedule:run --task=framework.queue.prune-history --force
```

## Operational Notes

- Prefer `help` and per-command `--help` as the authoritative source for options.
- `config:sync` is safe for derived projects: it writes backups before adding
  missing keys and does not replace app URL, DB credentials or session names.
- `admin-navigation:smoke` validates that Catalyst canonical sidebar surfaces
  remain present while allowing valid app-owned entries in canonical groups.
- `migrate:status` may fail from the Windows host when the DB hostname only exists inside WSDD/Docker. That is an environment boundary, not a CLI parser bug.
- DB-backed `fixtures:auth` actions can hit the same host boundary when the Windows host cannot resolve `WSDD-MySql-Server`; in that case run the same CLI through the WSDD PHP container instead of treating it as a command bug.
- For Catalyst mounted in WSDD, the local DB-backed CLI pattern is:
  `docker exec -w /var/www/html/catalyst.dock WSDD-Web-Server-PHP8.4 php public/cli.php reporting:smoke --json`.
  This keeps the verification outside committed project configuration.
- Auth-focused runtime smoke should prefer `fixtures:auth` probes over ad-hoc SQL for email verification, password-reset and token-count assertions.
- `inspect:harness` and `docs:sync-runtime` are the canonical human-facing surfaces for the RM-21/RM-23 runtime matrix; they should be preferred over ad-hoc spreadsheets or duplicated docs.
- `make:crud` is the canonical admin CRUD scaffold over the current framework stack; it now emits the guarded module, entity, request, migration, bulk/soft-delete flow and audit-ready wiring from `public/cli.php`.
- `make:crud` now also supports `--optimistic-locking=1`, which wires `HasOptimisticLockingTrait`, a `lock_version` column and hidden form state into generated admin modules.
- The admin authorization baseline is now resource-driven: generated CRUD requests/controllers and framework admin modules should prefer `authorizeResource(...)` / `AbilitySubject` over ad-hoc permission string checks.
- The audit baseline is now operational, not just metadata fields: inspect `/operations/audit-log` for runtime traces of ORM/repository changes and framework events after DB-backed actions run.
- `claims:list`, `claims:release` and `concurrency:smoke` are the canonical PA-01 operational probes; do not invent ad-hoc claim tables or per-module lock metadata outside this surface.
- `timeline:smoke` and `catalogs:smoke` are the canonical PA-09/PA-11 verification probes; if the host cannot resolve `WSDD-MySql-Server`, run them inside `WSDD-Web-Server-PHP8.4`.
- `PA-01` is now adopted in the live framework admin runtime: Documents, Automation, Media and Roles/Permissions must extend the canonical claim/token + `lock_version` flow instead of introducing local concurrency semantics.
- Media fields, media library, catalogs, documents, module designer and locale
  tools are Workspaces capabilities. CLI verification should go through
  `inspect:module framework.workspaces`, `inspect:harness --module
  framework.workspaces`, `inspect:lint` and `docs:sync-runtime`.
- RM-31/RM-35 tampoco agregan un comando CLI exclusivo por subsistema: las superficies canonicas son `/workspaces/document-templates`, `/operations/automation-rules`, `/operations/api-management` y `/api/v1/*`, mientras que la verificacion CLI debe apoyarse en `inspect:module`, `inspect:harness`, `inspect:lint`, `docs:sync-runtime`, `queue:*` y `schedule:*`.
- RM-36/RM-39 centralizan su gobierno operativo en `/operations`: feature flags, plugins, deployments y tenancy no deben abrirse como paneles paralelos en `/setup` ni en DevTools.
- `status` ahora expone un bloque `Platform` con feature flags, plugins, perfiles de deploy y baseline de tenancy; usarlo como snapshot rapido antes de ejecutar cambios operativos.
- `feature-flags:set` y `plugin:toggle` son mutaciones reales y auditables; los flags/runtime read-only deben rechazarse en CLI y UI en lugar de inventar bypasses.
- `deploy:run` debe preferirse sobre scripts sueltos cuando el objetivo sea validar el pipeline formal del framework; si la verificacion requiere DB y el host no resuelve `WSDD-MySql-Server`, ejecutar el mismo comando dentro de `WSDD-Web-Server-PHP8.4`.
- `tenancy:status` no habilita multi-tenancy real por si solo: documenta la decision oficial del framework (`single` hoy, `shared-db-tenant-id` como objetivo) para evitar implementaciones paralelas.
- El catalogo runtime de `inspect:harness` ahora distingue correctamente rutas JSON protegidas por `ApiTokenMiddleware`: para sesion web `guest/user/admin` deben degradar a `401`, y el perfil operativo `api_token=200` queda documentado en `docs/runtime-module-catalog.md`.
- Dynamic metadata and media flows are DB-backed; if the Windows host cannot resolve `WSDD-MySql-Server`, run the same verification commands inside `WSDD-Web-Server-PHP8.4` instead of treating it as a framework regression.
- `attachments:smoke`, `catalogs:smoke`, `reporting:smoke` and `retention:smoke`
  write auxiliary TXT probes through the private `runtime` disk under
  `boot-core/storage/runtime/smoke/` and remove them during cleanup.
- `retention:smoke` scopes retention mutations to IDs created by its own probe;
  it must not apply retention policies to unrelated historical records.
- `npm run qa:catalyst:buckets` now fans out Playwright buckets through an isolated runner/output strategy, so parallel bucket traces no longer collide under `test-results/`.
- This document is secondary to runtime truth. If `public/cli.php` or the registered command classes disagree with this file, the code wins.
