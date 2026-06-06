# Catalyst v0.1.0-rc.7 Upgrade Notes

## Scope

`v0.1.0-rc.7` supersedes `v0.1.0-rc.6` and closes GitHub issues #11 and #12:

- makes local runtime configuration portable across Windows, Linux and WSDD
  without materializing a `NUL` file;
- moves all environment runtime JSON below ignored environment directories and
  keeps portable defaults in `boot-core/config/templates`;
- adds `config:e2e-readiness` and extends configuration contracts and quality
  gates;
- fixes modal layering and cleanup across Catalyst shells;
- normalizes the Catalyst test harness with project-owned PHP unit and
  Playwright test directories;
- adds exhaustive E2E inventory coverage for every active modal trigger and
  transition on Settings, DevTools and Demo UI.

## Local Upgrade

From a derived project that tracks Catalyst as `upstream`:

```powershell
git status --short --branch
git fetch upstream --tags
git merge v0.1.0-rc.7
composer install
php public/cli.php config:sync
php public/cli.php config:e2e-readiness --json
php public/cli.php quality:check
```

Review local changes before merging. Active environment config remains local and
must not be replaced with maintainer values.

## Configuration Contract

Portable defaults live in:

```text
boot-core/config/templates/
```

Active runtime files live below ignored environment directories:

```text
boot-core/config/development/
boot-core/config/testing/
boot-core/config/staging/
boot-core/config/production/
```

`config:sync` creates missing active files and merges missing template keys
without overwriting existing local values. `config:e2e-readiness` verifies that
the current project is configured and that active runtime config is not tracked.

## Testing Harness

Versioned tests live inside Catalyst:

```text
test/framework/UnitTest/
test/framework/Playwright/
```

The workspace Playwright engine is a local external runtime. It owns Node
dependencies, local secrets, MFA integration, traces, screenshots and results;
none of those artifacts are included in Catalyst or the release archive.

Run the Catalyst modal suite from the engine:

```powershell
Push-Location D:\OpsZone\DevWorkspace\Engines\Playwright
node .\scripts\run-project-tests.js D:\OpsZone\DevWorkspace\Projects\Web\catalyst --grep "@modals"
Pop-Location
```

The RC7 modal inventory covers:

- Settings: inventory contract and every active visible settings modal;
- DevTools: confirm, alert, dynamic HTML, dynamic form, API notification and
  wait modal;
- Demo UI: every declared trigger, chained transition and varying-content
  trigger.

Capabilities without a runtime consumer and inactive modals are documented and
are not opened by bypassing visible UI.

## Documentation

- Testing environment and protocol: `docs/testing.md`
- Safe modal implementation: `docs/framework-modals.md`
- Progressive E2E surface registry:
  `test/framework/Playwright/SURFACES.md`
- Derived-project setup: `docs/workflow/reusable-base-install.md`
- Harness routing map: `docs/harness-context-map.md`

## Verification

Verified before release:

```powershell
php public/cli.php config:contract-smoke --json
php public/cli.php config:e2e-readiness --json
php public/cli.php docs:inventory --json
php public/cli.php docs:sync-runtime --stdout
php public/cli.php quality:check
git diff --check
```

Playwright modal result:

```text
41 passed
```

No secrets, local auth state or Playwright results reside in the Catalyst
repository.
