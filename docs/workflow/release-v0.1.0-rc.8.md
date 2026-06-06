# Catalyst v0.1.0-rc.8

## Purpose

RC8 formalizes test ownership for Catalyst and projects derived from Catalyst.
It prevents application E2E coverage from being mixed with framework contracts.

## Test Ownership

```text
test/framework/UnitTest/    Catalyst framework PHP contracts
test/framework/Playwright/  Catalyst framework E2E contracts
test/app/UnitTest/          Derived application PHP behavior
test/app/Playwright/        Derived application E2E behavior
```

A derived project may run the inherited framework suite, but it must place its
own routes, APIs, workflows and UI coverage under `test/app`.

## Playwright Execution

The local workspace engine owns Node/Playwright dependencies, secrets, auth
state and generated results. Specs remain inside the project.

Framework suite:

```powershell
Push-Location D:\OpsZone\DevWorkspace\Engines\Playwright
node .\scripts\run-project-tests.js D:\OpsZone\DevWorkspace\Projects\Web\catalyst --suite framework
Pop-Location
```

Derived application suite:

```powershell
Push-Location D:\OpsZone\DevWorkspace\Engines\Playwright
node .\scripts\run-project-tests.js D:\path\to\derived-project --suite app
Pop-Location
```

The compatible engine accepts only `framework` and `app`, owns the Playwright
`--config` argument and rejects legacy engine test paths.

## Upgrade Notes

- Keep inherited Catalyst tests under `test/framework`.
- Create `test/app/Playwright/playwright.config.cjs` and
  `test/app/Playwright/specs/` for product-specific E2E coverage.
- Maintain a separate `test/app/Playwright/SURFACES.md`.
- Do not copy Node dependencies, credentials, MFA secrets, traces, screenshots
  or storage state into the project repository.
- Migrate legacy tests progressively after confirming each current route and
  visible surface.

## Verification

```powershell
composer validate --strict
composer audit
php public/cli.php docs:inventory --json
php public/cli.php docs:sync-runtime --stdout
php public/cli.php route:list --json
php public/cli.php inspect:lint
php public/cli.php route:lint
php public/cli.php security:check
php public/cli.php quality:check
git diff --check
```

Playwright discovery is verified with `--list`; it does not open a browser or
execute E2E interactions.
