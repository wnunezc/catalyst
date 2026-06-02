# Task 6D.2 - Canonical JavaScript Contracts

Date: 2026-06-02

Status: implemented and verified / included in the Task 6D.2 checkpoint.

## Scope Completed

Added concise contract headers to the 10 non-DevTools module scripts that
previously lacked useful initialization, selector, event/payload and CSP notes:

- `Repository/Framework/ApiPlatform/front/script.js`
- `Repository/Framework/Audit/front/script.js`
- `Repository/Framework/Auth/front/script.js`
- `Repository/Framework/Automation/front/script.js`
- `Repository/Framework/Catalogs/front/script.js`
- `Repository/Framework/DemoUi/front/script.js`
- `Repository/Framework/Documents/front/script.js`
- `Repository/Framework/Media/front/script.js`
- `Repository/Framework/Operations/front/script.js`
- `Repository/Framework/Roles/front/script.js`

## Behavioral Contract

- No JavaScript behavior changed.
- No selectors, listeners, imports, external URLs or payload shapes changed.
- `docs/runtime-inventory.md` was refreshed after script metadata changed.
- DevTools remains explicitly deferred.

## Verification Plan

```powershell
php public/cli.php docs:inventory --json
php public/cli.php quality:check
git diff --check
```

## Residual Scope

- `6D.3` uses `docs/runtime-inventory.md` to plan executable template
  migration.
- `6D.4` classifies inline assets and CSP transport payloads.
