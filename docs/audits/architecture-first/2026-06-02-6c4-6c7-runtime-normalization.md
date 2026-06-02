# Tasks 6C.4-6C.7 - Residual Runtime Normalization

Date: 2026-06-02

Status: closed after user review / included in the Phase 6C closure commit.

## Scope Completed

### Task 6C.4 - Roles

- Added `roles:mvc-regression` with an observed RED/GREEN cycle.
- Moved enrollment payload validation and replayable input into a dedicated
  Request.
- Extracted enrollment form construction from `UserManagementController`.
- Centralized role bulk-delete, permission bulk-delete and permission-sync ID
  normalization in focused Requests.
- Preserved RBAC persistence, routes and HTML/JSON response handling.

### Task 6C.5 - Media

- Added `media:mvc-regression` with an observed RED/GREEN cycle.
- Moved media bulk-delete ID normalization into a focused Request.
- Extracted media-library dynamic form construction into a factory.
- Extracted metadata-field FormBuilder assembly into a factory.
- Preserved uploads, storage selection, dynamic metadata and record claims.

### Task 6C.6 - Operations

- Added `operations:requests-regression` with an observed RED/GREEN cycle.
- Centralized feature-flag default, locale settings/create/sync,
  module-designer and appearance payload normalization in focused Requests.
- Shared checkbox normalization through one Request concern.
- Kept plugin toggle unchanged because it does not consume a request payload.
- Preserved routes, middleware, throttles and existing manager behavior.

### Task 6C.7 - Manifest Localization

- Added `modules:localization-regression` with an observed RED/GREEN cycle.
- Replaced positional Settings, Roles and Operations decorator patches with a
  recursive visible-field translation-key contract.
- Kept the existing DevTools localization exception deferred.
- Normalized visible manifest values in active Framework surfaces without
  changing keys, routes, permissions or navigation order.

## Verification

- `php public/cli.php roles:mvc-regression` -> PASS
- `php public/cli.php media:mvc-regression` -> PASS
- `php public/cli.php operations:requests-regression` -> PASS
- `php public/cli.php modules:localization-regression` -> PASS
- `php public/cli.php quality:check` -> PASS
- `php public/cli.php route:bootstrap-regression` -> PASS
- `php public/cli.php route:list --json` -> PASS
- `php public/cli.php inspect:lint` -> PASS
- `php public/cli.php route:lint` -> PASS
- touched PHP lint sweep -> PASS
- touched-controller DI resolution smoke -> PASS
- `git diff --check` -> PASS
- HTTPS smoke `https://catalyst.dock/` -> `200`
- container `php public/cli.php security:regression` -> PASS
- container `php public/cli.php status` -> Ready

Host-only DB DNS warnings remain expected because `WSDD-MySql-Server` resolves
inside the Docker network. The same checks pass from the web container.

## Deliberate Residual Scope

- Roles and Media DataGrid factories remain a later improvement; this pass
  extracted the mutation and form boundaries with the highest behavioral risk.
- Appearance contextual validation remains in its controller because it
  depends on manager catalog state and asset persistence. Input normalization
  moved into its Request.
- DevTools visual/layout internals remain deferred.
- Documentation contract tasks continue as `6D.1` through `6D.4`.
