# First Run Workflow

## Purpose

Bring a fresh Catalyst checkout to a verifiable local development state without
using historical zips or local-only artifacts.

## Prerequisites

- PHP 8.4 available in the local stack.
- Composer available in the host shell or container.
- Apache or an equivalent web server configured for the site.
- MySQL/MariaDB reachable from PHP.
- Local secrets prepared outside Git.

The stack can be XAMPP, WSDD/Docker, Laragon, MAMP or another equivalent PHP
environment. WSDD is a maintainer-local development stack, not a framework
requirement.

## Setup Steps

1. Clone or copy the repository contents into the target site root. For XAMPP,
   this can be the contents of `htdocs` directly; a wrapper folder named
   `catalyst` is not required.
2. Install dependencies:

```powershell
composer install
composer dump-autoload
```

3. Prepare local environment files:
   - copy from safe templates under `boot-core/config/templates/`;
   - create `boot-core/config/env/.env` locally;
   - do not commit `.env`, DKIM private keys or runtime storage.
4. Configure Apache so the effective document root is `public/`.
   - Preferred: point the VirtualHost `DocumentRoot` directly at `public/`.
   - Fallback: if Apache serves the project root, the root `.htaccess` forwards
     requests internally to `public/` and blocks direct access to sensitive
     project directories.
5. Confirm the CLI is available:

```powershell
php public/cli.php help
php public/cli.php status
```

6. Open the setup wizard when the project is not configured:

```text
http://localhost/configuration/environment-setup
```

   Replace `http://localhost` with the local URL configured for the site.
7. Follow the setup contract in
   `docs/checklists/setup-completion-e2e.md`.
8. Run the quality gate:

```powershell
php public/cli.php quality:check
```

## Expected Local Warnings

From a host shell, `status` may warn when queue/scheduler checks cannot resolve
environment-specific database hosts. This commonly happens when a Docker-only
service name is visible inside the container but not from the host.

This is acceptable only when:

- `Overall: Ready` is still reported;
- blocker checks in `quality:check` pass;
- the same DB-dependent smoke is run from the runtime environment when the task
  requires DB certainty.

## Verification Checklist

```powershell
composer validate --strict
composer audit
php public/cli.php route:lint
php public/cli.php inspect:lint
php public/cli.php security:check
php public/cli.php quality:check
php public/cli.php route:list --json
```

Optional runtime smokes depend on the task:

```powershell
php public/cli.php fixtures:auth --help
php public/cli.php security:regression
php public/cli.php route:bootstrap-regression
```

## Handoff Criteria

A fresh checkout is ready for development when:

- Composer install/autoload succeeds;
- CLI `help` and `status` run;
- setup wizard contract is satisfied or project config is intentionally false
  for setup testing;
- quality gate passes;
- local secrets remain untracked.
- the browser URL does not require a `/catalyst` path segment.
