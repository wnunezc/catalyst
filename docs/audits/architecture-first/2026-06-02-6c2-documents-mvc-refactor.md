# Task 6C.2 - Documents MVC Refactor

Date: 2026-06-02

Status: implemented and verified / pending user review and commit.

## Scope Completed

- Split tokenized API endpoints into `DocumentTemplateApiController`.
- Reduced `DocumentTemplateController` to web orchestration.
- Extracted module-specific DataGrid and FormBuilder factories.
- Extracted show-page data preparation and transient preview session state.
- Extracted preview and export services shared by HTML and API flows.
- Moved transition and API index normalization into dedicated Requests.
- Preserved route paths, middleware, throttles and observable response contracts.
- Fixed the pre-existing invalid Request inheritance used by export payloads.
- Added `documents:mvc-regression` with a red-green verification cycle.

## Verification

- `php public/cli.php documents:mvc-regression` -> PASS
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

Review and commit the Task 6C.2 diff. Then continue with residual Phase 6
planning without changing completed Automation or Documents behavior.
