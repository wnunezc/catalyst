# First Run Workflow

## Purpose

Bring a fresh Catalyst checkout to a verifiable local development state without
using historical zips or local-only artifacts.

## Prerequisites

- PHP 8.4 available in the WSDD/Docker stack.
- Composer available in the host shell or container.
- WSDD route for `https://catalyst.dock/`.
- MariaDB available as `WSDD-MySql-Server` from inside the Docker network.
- Local secrets prepared outside Git.

## Setup Steps

1. Clone or copy the repository into the workspace.
2. Install dependencies:

```powershell
composer install
composer dump-autoload
```

3. Prepare local environment files:
   - copy from safe templates under `boot-core/config/templates/`;
   - create `boot-core/config/env/.env` locally;
   - do not commit `.env`, DKIM private keys or runtime storage.
4. Confirm the CLI is available:

```powershell
php public/cli.php help
php public/cli.php status
```

5. Open the setup wizard when the project is not configured:

```text
https://catalyst.dock/configuration/environment-setup
```

6. Follow the setup contract in
   `docs/checklists/setup-completion-e2e.md`.
7. Run the quality gate:

```powershell
php public/cli.php quality:check
```

## Expected Local Warnings

From a Windows host shell, `status` may warn when queue/scheduler checks try to
resolve Docker-only DB names such as `WSDD-MySql-Server`.

This is acceptable only when:

- `Overall: Ready` is still reported;
- blocker checks in `quality:check` pass;
- the same DB-dependent smoke is run from the PHP container when the task
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
