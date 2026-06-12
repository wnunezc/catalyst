# Testing Guide

## Purpose

Define the standard Catalyst testing environment. Tests must be short,
independent, resident inside the Catalyst project and executed through the
workspace harness without ad hoc scripts.

## Runtime Owners

| Concern | Owner |
|---|---|
| Quality gate | `Catalyst\Framework\Cli\Commands\QualityCheckCommand` |
| Structural lint | `Catalyst\Framework\Cli\Commands\InspectLintCommand` |
| Route lint | `Catalyst\Framework\Cli\Commands\RouteLintCommand` |
| Runtime docs inventory | `Catalyst\Framework\Cli\Commands\DocsInventoryCommand` |
| Runtime module sync | `Catalyst\Framework\Cli\Commands\DocsSyncRuntimeCommand` |
| Framework PHP unit harness | `test/framework/UnitTest` |
| Application PHP unit harness | `test/app/UnitTest` |
| Catalyst framework Playwright specs | `test/framework/Playwright` |
| Derived application Playwright specs | `test/app/Playwright` |
| Playwright runtime | `D:/OpsZone/DevWorkspace/Engines/Playwright` |

## Current Behavior

The standard documentation/runtime verification set remains:

```powershell
php public/cli.php docs:inventory --json
php public/cli.php docs:sync-runtime --stdout
php public/cli.php route:list --json
php public/cli.php configuration:requests-regression
php public/cli.php configuration:localization-smoke
php public/cli.php shell-navigation:smoke --json
php public/cli.php inspect:lint
php public/cli.php route:lint
php public/cli.php security:check
php public/cli.php quality:check
git diff --check
```

## PHP Unit Tests

Unit tests live in:

```text
test/framework/UnitTest  Framework contracts
test/app/UnitTest        Application contracts
```

Run the applicable owner suites with:

```powershell
php test\framework\UnitTest\run.php
php test\app\UnitTest\run.php
```

Catalyst currently does not require PHPUnit or Pest. The local runner is
intentionally small and dependency-free. Unit tests cover pure PHP logic:
helpers, services, config normalization, validators, internal contracts and
regressions that do not need a browser.

## Playwright Tests

Test ownership follows the same framework/application boundary as
`Repository/Framework` and `Repository/App`:

```text
test/framework/Playwright/  Catalyst framework contracts
test/app/Playwright/        Derived application behavior
```

The Catalyst repository owns only the framework suite. A project based on
Catalyst may retain that suite to verify the inherited framework, but all
product-specific routes, workflows, APIs and UI belong in its own `test/app`
suite. Never add derived application specs to Catalyst or mix them into
`test/framework`.

The Node/Playwright runtime lives in the workspace engine:

```text
D:/OpsZone/DevWorkspace/Engines/Playwright
```

Run all Catalyst Playwright specs from PowerShell:

```powershell
$env:CATALYST_PLAYWRIGHT_ENGINE = 'D:\OpsZone\DevWorkspace\Engines\Playwright'
Push-Location $env:CATALYST_PLAYWRIGHT_ENGINE
node .\scripts\run-project-tests.js D:\OpsZone\DevWorkspace\Projects\Web\catalyst --suite framework
Pop-Location
```

Run the modal suite:

```powershell
Push-Location D:\OpsZone\DevWorkspace\Engines\Playwright
node .\scripts\run-project-tests.js D:\OpsZone\DevWorkspace\Projects\Web\catalyst --suite framework --grep "@modals"
Pop-Location
```

Run a derived application's own suite:

```powershell
Push-Location D:\OpsZone\DevWorkspace\Engines\Playwright
node .\scripts\run-project-tests.js D:\path\to\derived-project --suite app
Pop-Location
```

The selected suite must provide its own `Playwright/playwright.config.cjs`,
`specs/`, versionable helpers/fixtures and `SURFACES.md`. The engine accepts only
`framework` or `app`, controls `--config`, and does not execute legacy files
from its own `tests/` directory.

Specs must not live under the Playwright engine. The engine may provide runtime,
local auth state, traces, screenshots and other machine-local artifacts.
By default, Playwright output is written outside the Catalyst repo under the
workspace engine: `D:/OpsZone/DevWorkspace/Engines/Playwright/test-results/catalyst`.
Catalyst runs authenticated specs with one worker because the local test account
and MFA service are shared. Do not enable concurrent authenticated workers
without isolated accounts and isolated MFA service identities.

## Browser E2E Protocol

Before interacting with a page, every E2E test must:

1. Navigate to the route.
2. Confirm the real URL.
3. Confirm whether the session landed in login or MFA.
4. Confirm a title, heading or unique surface signal.
5. Inspect visible relevant triggers.
6. Choose the interaction from the visible DOM.
7. Execute the interaction.
8. Validate the result.
9. Close or clean up using visible real UI.
10. Validate that no UI residue remains.

Do not use the in-app Browser for Catalyst E2E unless explicitly instructed.

## Environment Interruptions

Some tests require local applications such as WSDD, Docker, MFA-Forge or the
workspace Playwright engine. If a required local dependency is missing, the test
must report an environment interruption with the missing application/path and a
replacement/configuration hint. That is not a Catalyst functional failure.

Secrets, account identifiers and browser storage state belong to the local
engine environment, not to the Catalyst repository. Authenticated Playwright
runs read their local account values from
`D:/OpsZone/DevWorkspace/Engines/Playwright/.secrets/catalyst.e2e.json`.
Environment variables may override those engine-local values.

Local machine data must not be committed:

- Playwright `.auth`
- traces, videos, screenshots and reports
- generated upload artifacts
- account identifiers, credentials, MFA secrets or storage state
- WSDD-specific secrets

## Required Coverage Rules

For UI/API bugs, add or update a Playwright spec that covers the affected
surface, a happy path and the relevant sad path or regression guard.

For PHP bugs, add or update a unit test when the behavior is pure PHP. If the
bug crosses config, CLI, database or browser boundaries, keep the unit test
focused on the pure contract and cover the integration path with CLI smoke or
Playwright.

Name Playwright specs by surface, for example `settings-modals.spec.cjs`,
`devtools-modals.spec.cjs`, `auth-mfa.spec.cjs` or `demo-ui.spec.cjs`. Name PHP
tests after the class or contract under test.

Maintain the progressive coverage registry owned by the selected suite:
`test/framework/Playwright/SURFACES.md` for framework behavior and
`test/app/Playwright/SURFACES.md` for application behavior. Generated files,
inactive templates and legacy engine specs are not surfaces. Confirm the
current runtime route and visible surface before adding E2E coverage.

For modal-owning surfaces, an inventory contract is mandatory. It must list the
active triggers expected on the real route and fail when a trigger is added or
removed without updating coverage. Every active trigger or distinct chained
transition must have an independent E2E interaction case. Rendered capabilities
without a visible runtime consumer must be documented and must not be opened by
bypassing the UI.

## Operational Notes

`quality:check` includes `status`, which may emit environment-bound DNS warnings from host Windows for Docker-only service names. Treat those as acceptable only when the gate reports ready and the failing check is known to be host-environment specific.

Release verification also includes a docblock responsibility review for changed
PHP classes and methods. Each class/method docblock must include a meaningful
`Responsibility:` line that is distinct from the summary and explains the
contract boundary, not just a restatement.

## Docker/WSDD DB-Backed Happy Path

When a DB-backed smoke fails from host Windows because `WSDD-MySql-Server`
does not resolve, run the same CLI command inside the WSDD PHP container. This
keeps the workaround outside Catalyst configuration and avoids committing
workspace-specific scripts or `.env` changes.

```powershell
docker exec -w /var/www/html/catalyst.dock WSDD-Web-Server-PHP8.4 php public/cli.php reporting:smoke --json
```

Verified Happy Path on 2026-06-03:

- `first-run-fails-and-persists`: ok.
- `retry-completes-report`: ok.
- `xls-output-matches-request`: ok.
- `unsupported-format-rejected`: ok.

Use the same pattern for other DB-backed CLI smokes only when the project is
mounted at `/var/www/html/catalyst.dock` inside `WSDD-Web-Server-PHP8.4`. This
is a local verification route, not a framework runtime requirement.

## RED And Sad Path Registry

During the `0.1.0-rc.1` adequations, the expected RED checks were command
absence or negative contract failures before implementation. The documented Sad
Paths remain part of the release evidence:

- `deletion:smoke --json`: RED was unknown command; Sad Path covers blockers,
  invalid confirmation token, unsupported action and rejecting handlers.
- `references:smoke --json`: RED was unknown command; Sad Path covers invalid
  resource keys, empty/unsafe record ids and unregistered reference types.
- `sequences:smoke --json`: RED was unknown command; Sad Path covers invalid
  keys, negative `startAt` and `step` lower than one.
- `workflow:smoke --json`: RED was unknown command; Sad Path covers invalid
  transitions, missing approvals and blocked guards.
- `attachments:policy-smoke --json`: RED was unknown command; Sad Path covers
  public storage rejection, size limit rejection, dangerous MIME/extension,
  disallowed purpose, tampered token and revoked token.
- `calendar:smoke --json`: RED was unknown command; Sad Path covers invalid
  ranges, out-of-range event exclusion and permission-hidden restricted events.
- `reports:contract-smoke --json`: RED was unknown command; Sad Path covers
  unknown providers, duplicate providers, unsupported formats and missing
  required criteria.
- `scaffold:app-smoke --json`: RED was unknown command; Sad Path covers unknown
  capabilities and permissions declared on public surfaces.
- `reporting:smoke --json`: host Windows DNS failure for `WSDD-MySql-Server`
  is an environment Sad Path. The Happy Path is to rerun the same command inside
  `WSDD-Web-Server-PHP8.4`.
- `configuration:localization-smoke`: semantic i18n guard for
  `/configuration/environment-setup`. It verifies EN/ES card and group titles
  are not raw keys, repeated values or copies of the global Settings title.
  This complements key-count coverage because valid JSON can still contain
  wrong visible copy.
- Docblock responsibility review: RED is a class or method whose
  `Responsibility:` line is missing, empty or copied from the summary. Sad Path
  is publishing code where the docs do not explain ownership, side effects or
  validation boundaries.

## Related Documentation

- `docs/quality-gate.md`
- `docs/documentation-contract.md`
- `docs/runtime-module-catalog.md`
- `docs/framework-modals.md`
