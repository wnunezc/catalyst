# Catalyst Playwright Harness

## Purpose

Playwright specs for Catalyst framework contracts live here and run with the
workspace engine at `D:\OpsZone\DevWorkspace\Engines\Playwright`. Derived
applications place their own product specs in `test/app/Playwright`; they must
not add application behavior to this directory.

## Standard Command

```powershell
$env:CATALYST_PLAYWRIGHT_ENGINE = 'D:\OpsZone\DevWorkspace\Engines\Playwright'
Push-Location $env:CATALYST_PLAYWRIGHT_ENGINE
node .\scripts\run-project-tests.js D:\OpsZone\DevWorkspace\Projects\Web\catalyst --suite framework
Pop-Location
```

Use `--grep` for a single surface:

```powershell
node .\scripts\run-project-tests.js D:\OpsZone\DevWorkspace\Projects\Web\catalyst --suite framework --grep "@modals"
```

Run only exhaustive read-only coverage:

```powershell
node .\scripts\run-project-tests.js D:\OpsZone\DevWorkspace\Projects\Web\catalyst --suite framework --project surface-parallel --grep "@roadmap7-full"
```

Run only stateful/serial coverage:

```powershell
node .\scripts\run-project-tests.js D:\OpsZone\DevWorkspace\Projects\Web\catalyst --suite framework --project stateful-serial
```

## Environment

- Default URL: `https://catalyst.dock`
- Override with `CATALYST_E2E_BASE_URL`.
- MFA uses `D:\OpsZone\DevWorkspace\Engines\MFA-Forge` by default.
- Override with `CATALYST_MFA_FORGE`.
- Authenticated runs read local secrets from
  `D:\OpsZone\DevWorkspace\Engines\Playwright\.secrets\catalyst.e2e.json`.
- `auth-setup` creates isolated authenticated sessions sequentially; MFA
  challenges never run concurrently.
- `surface-parallel` uses four read-only workers by default. Override with
  `CATALYST_E2E_PARALLEL_WORKERS`.
- `stateful-serial` retains one worker for shared-state interactions.
- `CATALYST_E2E_EMAIL`, `CATALYST_E2E_PASSWORD` and
  `CATALYST_E2E_MFA_SERVICE` may override the engine-local values.
- Do not commit account identifiers or credentials in this repo.

If a local dependency such as WSDD, Docker, MFA-Forge, the Playwright engine or
an OS-level application is missing, the spec must report the run as interrupted
by environment, not as a Catalyst functional failure.

## E2E Protocol

Every browser test must:

1. Navigate to the route.
2. Confirm the real URL.
3. Detect login/MFA and complete it only through approved helpers.
4. Confirm a title, heading or surface signal.
5. Inspect visible triggers.
6. Choose interaction from the visible DOM.
7. Execute the interaction.
8. Validate the result.
9. Close or clean up through visible UI.
10. Validate no UI residue remains.

## Layout

- `helpers/`: reusable runtime helpers.
- `specs/`: short independent specs by surface.
- `fixtures/`: versionable static fixtures only.
- `SURFACES.md`: progressive route/surface coverage registry.

Do not keep `.auth`, traces, screenshots, videos, account identifiers,
credentials, MFA secrets, storage state or test results in this repo. By
default Playwright output goes to the workspace engine under
`D:\OpsZone\DevWorkspace\Engines\Playwright\test-results\catalyst`.
