# Catalyst PHP Framework

Catalyst is a PHP 8.4 MVC framework distributed as a project base, not as a
Composer package. It is meant to be copied or cloned as the starting point for a
web application, then configured through local environment files and the setup
wizard.

Current distribution target: `0.1.0-rc.1`.

## Runtime Model

- Web entry point: `public/index.php`.
- CLI entry point: `public/cli.php`.
- Project root: the directory that contains `app/`, `boot-core/`, `public/`,
  `Repository/`, `composer.json` and this README.
- Effective web root: `public/`.

The physical folder name is not part of runtime path resolution. Catalyst
calculates its project directory from the relative structure of the entry points
and bootstrap files, so it must not depend on a folder named `catalyst`.

## Installation Contract

Install the contents of this project into the target site root. For a local
XAMPP-style setup, this means `htdocs` can contain the Catalyst project files
directly:

```text
htdocs/
├── app/
├── boot-core/
├── public/
├── Repository/
├── vendor/
├── composer.json
└── README.md
```

The preferred Apache configuration is to point the VirtualHost `DocumentRoot` to
`public/`:

```apacheconf
DocumentRoot "C:/xampp/htdocs/public"
<Directory "C:/xampp/htdocs/public">
    AllowOverride All
    Require all granted
</Directory>
```

If Apache is pointed at the project root instead, the root `.htaccess` forwards
requests internally to `public/` and blocks direct access to sensitive project
directories. That fallback is for portability; production hosts should still use
`public/` as the document root when possible.

Do not publish or rely on a URL shape that exposes the project folder as a path
segment. If a developer chooses a subdirectory deployment anyway, it must be
treated as an environment-specific concern and not as the default Catalyst
contract.

## Supported Local Stacks

Catalyst requires PHP 8.4, Composer and MySQL/MariaDB. The local stack can be
XAMPP, WSDD/Docker, Laragon, MAMP or another equivalent PHP/Apache setup.

`https://catalyst.dock/` is only the maintainer's WSDD local URL. A clean local
install should normally start from `http://localhost/`, a local VirtualHost, or
whatever URL the developer configures in the setup wizard.

## First Run

Install dependencies:

```powershell
composer install
composer dump-autoload
```

Create local environment files:

- copy `boot-core/config/env/.env.example` to `boot-core/config/env/.env`;
- generate and set `APP_KEY`;
- set only the local secrets needed for first boot;
- do not commit `.env`, DKIM keys, `secrets.json`, uploads, logs or runtime
  storage.

Confirm the CLI is available:

```powershell
php public/cli.php help
php public/cli.php status
```

Open the setup wizard at the configured local URL:

```text
http://localhost/configuration/environment-setup
```

Use the wizard to configure app URL, database, mail, session, cache, logging,
security, WebSocket and DKIM values as needed. Database credentials, mail
credentials, OAuth secrets, FTP credentials and DKIM keys belong to the
developer's environment, not to the distributed base.

## Configuration Baseline

Portable starter configuration lives in:

```text
boot-core/config/templates/
```

These templates intentionally avoid maintainer-specific URLs, database hosts,
mail accounts and secrets. Runtime configuration is loaded from:

```text
boot-core/config/{environment}/*.json
boot-core/config/env/.env
```

The setup wizard materializes project-specific configuration. Treat tracked
development configuration as a local baseline only; release exports must exclude
runtime secrets and private artifacts.

## Platform Capabilities

- Route registration, route linting and route cache tooling.
- HTTP middleware, sessions, CSRF, CSP nonce support and trusted rendering
  boundaries.
- Auth with password reset, remember-me invalidation, MFA/TOTP, email
  verification, throttling, RBAC and resource policies.
- ORM, QueryBuilder, migrations and database tooling.
- Framework modules for API Platform, Audit, Auth, Automation, Catalogs,
  DevTools, Documents, Media, Notification, Operations, Roles and Settings.
- Admin building blocks: CRUD scaffold, `FormBuilder`, `DataGrid`, resource
  abilities, audit log, metadata, media library, document templates, workflows,
  automations and API tokens.
- CLI quality gates, inspectors, module catalog generation and runtime
  inventory generation.

## Approved Composer Dependencies

Runtime dependencies are limited to:

- `phpmailer/phpmailer`
- `league/oauth2-client`
- `cboden/ratchet`
- `react/http`

Do not add Composer dependencies without an explicit project decision.

## Quality Gate

Run before committing framework, routing, security, config or asset changes:

```powershell
composer validate --strict
composer audit
php public/cli.php route:lint
php public/cli.php inspect:lint
php public/cli.php security:check
php public/cli.php quality:check
php public/cli.php route:list --json
git diff --check
```

For documentation/runtime reconciliation also run:

```powershell
php public/cli.php docs:inventory --json
php public/cli.php docs:sync-runtime --stdout
```

Environment-specific warnings are acceptable only when blocker checks pass and
`quality:check` reports the runtime as ready.

## Repository Layout

```text
app/                      Core runtime: Kernel, HTTP, routing, middleware, ORM, CLI
Repository/Framework/     Framework-owned modules
Repository/App/           Application-owned surfaces and project code
boot-core/bin/            Bootstrap-owned scripts and support entry helpers
boot-core/cache/          Bootstrap/runtime cache artifacts
boot-core/config/         JSON/env configuration and local runtime files
boot-core/database/       Migrations and SQL artifacts
boot-core/routes/         Global route files
boot-core/storage/        Logs, throttle state and runtime artifacts
boot-core/template/       Shared layouts, components, debug and error templates
public/                   Web entry point, CLI entry point and public assets
docs/                     Current technical documentation
STRUCTURE.md              Technical inventory and quick component map
TERMINAL.md               CLI command reference
API.md                    Subsystem index
```

## Documentation Map

- [docs/workflow/first-run.md](docs/workflow/first-run.md) - fresh install workflow
- [docs/workflow/reusable-base-install.md](docs/workflow/reusable-base-install.md) - reusable project base workflow
- [docs/deployment.md](docs/deployment.md) - deployment and packaging boundaries
- [docs/architecture.md](docs/architecture.md) - architecture and documentation index
- [docs/runtime-module-catalog.md](docs/runtime-module-catalog.md) - live module and route catalog
- [docs/runtime-inventory.md](docs/runtime-inventory.md) - generated class/template/script inventory
- [docs/security-conventions.md](docs/security-conventions.md) - CSP, nonce and trusted HTML rules
- [docs/quality-gate.md](docs/quality-gate.md) - local quality gate contract
- [TERMINAL.md](TERMINAL.md) - CLI command reference

## Distribution Status

`0.1.0-rc.1` is intended as the first distribution candidate for developers who
will use Catalyst as a project base. It is not a public Composer package and is
not intended to be installed into another project's `vendor/` directory.

Before a release artifact is published, generate it from a clean checkout and
exclude local secrets, DKIM keys, runtime storage, uploads, logs, ad-hoc zips and
IDE files.

## Notes

- Runtime truth is the code first, then the living docs reconciled to runtime.
- If a document contradicts runtime behavior, fix the document rather than
  assuming the text is correct.
- Historical process traceability belongs outside the framework hot path.
