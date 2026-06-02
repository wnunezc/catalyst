# Task 6C.1 - Automation MVC Refactor

Date: 2026-06-02

Status: implemented and verified / pending user review and commit.

## Scope Completed

- Split tokenized API endpoints into `AutomationRuleApiController`.
- Reduced `AutomationRuleController` to web orchestration.
- Extracted module-specific DataGrid and FormBuilder factories.
- Extracted show-page data preparation.
- Extracted idempotent manual/API execution into a service layer.
- Extracted transient manual-run session state.
- Moved transition and API index normalization into dedicated Requests.
- Preserved route paths, middleware and observable response contracts.
- Added `automation:mvc-regression` with a red-green verification cycle.

## Verification

- `php public/cli.php automation:mvc-regression` -> PASS
- `php public/cli.php quality:check` -> PASS
- `php public/cli.php route:bootstrap-regression` -> PASS
- `php public/cli.php route:list --json` -> PASS
- `php public/cli.php inspect:lint` -> PASS
- `php public/cli.php security:check` -> PASS
- `composer validate --strict` -> PASS
- `composer audit` -> PASS
- `git diff --check` -> PASS

Expected local WSDD warnings remain limited to host DNS resolution for
database-backed queue and scheduler checks.

## Next Step

Review and commit the Task 6C.1 diff. Then start `6C.2`: refactor Documents
using the same MVC boundaries without changing its public behavior.
