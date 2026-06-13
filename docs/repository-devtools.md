# Catalyst\Repository\DevTools

## Purpose

Document DevTools controllers and local diagnostic services.

## Runtime Owners

| Concern | Owner |
|---|---|
| Delegates destructive test-database resets to the reset service. | `Catalyst\Repository\DevTools\Controllers\DatabaseResetController` |
| Reports connection health and configuration source for DevTools. | `Catalyst\Repository\DevTools\Controllers\DatabaseTestController` |
| Creates, persists and clears test flash messages. | `Catalyst\Repository\DevTools\Controllers\FlashTestController` |
| Returns deterministic save, validation, refresh and redirect test responses. | `Catalyst\Repository\DevTools\Controllers\FormEventTestController` |
| Reports translation samples and switches the active test locale. | `Catalyst\Repository\DevTools\Controllers\I18nTestController` |
| Exercises response envelopes, escaping, logging, CORS and route caching. | `Catalyst\Repository\DevTools\Controllers\InfraTestController` |
| Validates demo mail fields and reports the mail-manager result. | `Catalyst\Repository\DevTools\Controllers\MailTestController` |
| Supplies modal content and validates the modal form harness. | `Catalyst\Repository\DevTools\Controllers\ModalTestController` |
| Validates CRUD, collection, pagination and exception flows against demo data. | `Catalyst\Repository\DevTools\Controllers\OrmTestController` |
| Reports current RBAC state and assigns the demo privileged role role. | `Catalyst\Repository\DevTools\Controllers\RbacTestController` |
| Maps configured application entries to their canonical paths. | `Catalyst\Repository\DevTools\Controllers\RouteTestController` |
| Supplies authentication and navigation state to the DevTools workspace. | `Catalyst\Repository\DevTools\Controllers\TestFeaturesController` |
| Returns deterministic toaster, modal and partial-refresh responses. | `Catalyst\Repository\DevTools\Controllers\ToasterTestController` |
| Supplies configuration diagnostics to the trusted UML renderer. | `Catalyst\Repository\DevTools\Controllers\UmlController` |
| Validates, stores and reports uploaded DevTools attachments. | `Catalyst\Repository\DevTools\Controllers\UploadTestController` |
| Returns deterministic validation and uniqueness-check responses. | `Catalyst\Repository\DevTools\Controllers\ValidatorTestController` |
| Provides disposable model data for CRUD and collection tests. | `Catalyst\Repository\DevTools\Models\DemoEmail` |
| Orchestrates destructive DevTools database reset operations. | `Catalyst\Repository\DevTools\Services\DatabaseResetService` |

## Current Behavior

This file is regenerated from current PHP docblocks and the runtime inventory scope for `Catalyst\Repository\DevTools`. It intentionally replaces stale historical API notes with the classes and methods that exist in code now.

## API From Docblocks

### `Catalyst\Repository\DevTools\Controllers\DatabaseResetController`

- File: `Repository/Framework/DevTools/Controllers/DatabaseResetController.php`
- Kind: `class`
- Summary: Exposes the development-only database reset endpoint.
- Responsibility: Delegates destructive test-database resets to the reset service.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `reset()` | `public` | Resets the development database and redirects with the operation result. | Resets the development database and redirects with the operation result. |

### `Catalyst\Repository\DevTools\Controllers\DatabaseTestController`

- File: `Repository/Framework/DevTools/Controllers/DatabaseTestController.php`
- Kind: `class`
- Summary: Exposes a development diagnostic for the configured database connection.
- Responsibility: Reports connection health and configuration source for DevTools.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `testDbConnection()` | `public` | Tests the active database connection and returns diagnostic metadata. | Tests the active database connection and returns diagnostic metadata. |

### `Catalyst\Repository\DevTools\Controllers\FlashTestController`

- File: `Repository/Framework/DevTools/Controllers/FlashTestController.php`
- Kind: `class`
- Summary: Exposes development endpoints for exercising flash-message behavior.
- Responsibility: Creates, persists and clears test flash messages.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `triggerFlash()` | `public` | Adds a one-time flash message of the requested supported type. | Adds a one-time flash message of the requested supported type. |
| `triggerFlashPersistent()` | `public` | Adds a persistent flash message of the requested supported type. | Adds a persistent flash message of the requested supported type. |
| `clearFlash()` | `public` | Clears all queued flash messages. | Clears all queued flash messages. |

### `Catalyst\Repository\DevTools\Controllers\FormEventTestController`

- File: `Repository/Framework/DevTools/Controllers/FormEventTestController.php`
- Kind: `class`
- Summary: Exercises the form-event response helpers used by interactive forms.
- Responsibility: Returns deterministic save, validation, refresh and redirect test responses.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `formDemoStore()` | `public` | Dispatches the submitted demo form event to its handler. | Dispatches the submitted demo form event to its handler. |
| `onSave()` | `protected` | Validates demo contact fields and returns a successful save response. | Validates demo contact fields and returns a successful save response. |
| `onValidate()` | `protected` | Returns deterministic field-validation errors for the demo harness. | Returns deterministic field-validation errors for the demo harness. |
| `onRefresh()` | `protected` | Returns a response that schedules a client refresh. | Returns a response that schedules a client refresh. |
| `onRedirect()` | `protected` | Returns a response that schedules a client redirect. | Returns a response that schedules a client redirect. |

### `Catalyst\Repository\DevTools\Controllers\I18nTestController`

- File: `Repository/Framework/DevTools/Controllers/I18nTestController.php`
- Kind: `class`
- Summary: Exposes development diagnostics for translation and locale behavior.
- Responsibility: Reports translation samples and switches the active test locale.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `testI18n()` | `public` | Returns translation, catalog and date-format diagnostics for the active locale. | Returns translation, catalog and date-format diagnostics for the active locale. |
| `setLocale()` | `public` | Validates and activates a supported locale for the current session. | Validates and activates a supported locale for the current session. |

### `Catalyst\Repository\DevTools\Controllers\InfraTestController`

- File: `Repository/Framework/DevTools/Controllers/InfraTestController.php`
- Kind: `class`
- Summary: Exposes development diagnostics for shared HTTP and infrastructure helpers.
- Responsibility: Exercises response envelopes, escaping, logging, CORS and route caching.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `index()` | `public` | Redirects the infrastructure diagnostic entry point to the DevTools harness. | Redirects the infrastructure diagnostic entry point to the DevTools harness. |
| `testEscapeHelper()` | `public` | Returns representative escaped values produced by the HTML helper. | Returns representative escaped values produced by the HTML helper. |
| `testLayout()` | `public` | Renders the layout smoke-test page with escaping tokens. | Renders the layout smoke-test page with escaping tokens. |
| `testJson()` | `public` | Returns a raw JSON response envelope. | Returns a raw JSON response envelope. |
| `testJsonSuccess()` | `public` | Returns a successful JSON response envelope. | Returns a successful JSON response envelope. |
| `testJsonError()` | `public` | Returns an error JSON response envelope. | Returns an error JSON response envelope. |
| `testValidationError()` | `public` | Returns a validation-error JSON response envelope. | Returns a validation-error JSON response envelope. |
| `testApiResponse()` | `public` | Returns a legacy API response envelope with pagination metadata. | Returns a legacy API response envelope with pagination metadata. |
| `testLoggerEmail()` | `public` | Writes an email audit entry and returns its expected log path. | Writes an email audit entry and returns its expected log path. |
| `testCorsHeaders()` | `public` | Returns normalized CORS configuration diagnostics. | Returns normalized CORS configuration diagnostics. |
| `testRouteCache()` | `public` | Builds, loads and clears a route cache to validate the cache lifecycle. | Builds, loads and clears a route cache to validate the cache lifecycle. |

### `Catalyst\Repository\DevTools\Controllers\MailTestController`

- File: `Repository/Framework/DevTools/Controllers/MailTestController.php`
- Kind: `class`
- Summary: Exposes a development endpoint for validating outbound mail delivery.
- Responsibility: Validates demo mail fields and reports the mail-manager result.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `mailTest()` | `public` | Sends a validated test message through the configured mail manager. | Sends a validated test message through the configured mail manager. |

### `Catalyst\Repository\DevTools\Controllers\ModalTestController`

- File: `Repository/Framework/DevTools/Controllers/ModalTestController.php`
- Kind: `class`
- Summary: Exposes partial HTML and submission responses for modal UI diagnostics.
- Responsibility: Supplies modal content and validates the modal form harness.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `modalSampleContent()` | `public` | Returns trusted sample HTML for a dynamically loaded modal. | Returns trusted sample HTML for a dynamically loaded modal. |
| `modalFormContent()` | `public` | Returns trusted HTML for the modal form partial. | Returns trusted HTML for the modal form partial. |
| `modalFormSubmit()` | `public` | Validates modal form fields and returns the submission result. | Validates modal form fields and returns the submission result. |

### `Catalyst\Repository\DevTools\Controllers\OrmTestController`

- File: `Repository/Framework/DevTools/Controllers/OrmTestController.php`
- Kind: `class`
- Summary: Exposes development endpoints that exercise ORM model behavior.
- Responsibility: Validates CRUD, collection, pagination and exception flows against demo data.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `uniqueOrmEmail()` | `private` | Builds a unique demo email address for ORM write operations. | Builds a unique demo email address for ORM write operations. |
| `ormStatus()` | `public` | Returns collection and pagination diagnostics for demo email records. | Returns collection and pagination diagnostics for demo email records. |
| `ormCreate()` | `public` | Creates a demo email record and reports its persisted state. | Creates a demo email record and reports its persisted state. |
| `ormUpdate()` | `public` | Updates the latest demo email record and reports dirty-state behavior. | Updates the latest demo email record and reports dirty-state behavior. |
| `ormDeleteLatest()` | `public` | Deletes the latest matching demo email record. | Deletes the latest matching demo email record. |
| `ormFindOrFail()` | `public` | Verifies model-not-found exception handling with a missing record. | Verifies model-not-found exception handling with a missing record. |
| `ormUserDemo()` | `public` | Returns ORM casting and hidden-field diagnostics for user records. | Returns ORM casting and hidden-field diagnostics for user records. |

### `Catalyst\Repository\DevTools\Controllers\RbacTestController`

- File: `Repository/Framework/DevTools/Controllers/RbacTestController.php`
- Kind: `class`
- Summary: Exposes development diagnostics for role and permission behavior.
- Responsibility: Reports current RBAC state and assigns the demo privileged role role.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `rbacStatus()` | `public` | Returns role, permission and gate diagnostics for the authenticated user. | Returns role, permission and gate diagnostics for the authenticated user. |
| `makePrivileged()` | `public` | Assigns the privileged role role to the authenticated development user. | Assigns the privileged role role to the authenticated development user. |

### `Catalyst\Repository\DevTools\Controllers\RouteTestController`

- File: `Repository/Framework/DevTools/Controllers/RouteTestController.php`
- Kind: `class`
- Summary: Resolves the development route-test entry page and configured redirects.
- Responsibility: Maps configured application entries to their canonical paths.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `index()` | `public` | Redirects configured projects or renders the route-test landing page. | Redirects configured projects or renders the route-test landing page. |
| `redirectToRoot()` | `public` | Redirects the legacy route-test endpoint to the application root. | Redirects the legacy route-test endpoint to the application root. |
| `resolveConfiguredEntryTarget()` | `private` | Resolves the configured primary or authenticated secondary entry path. | Resolves the configured primary or authenticated secondary entry path. |
| `mapEntryToPath()` | `private` | Maps an application entry identifier to its configured path. | Maps an application entry identifier to its configured path. |

### `Catalyst\Repository\DevTools\Controllers\TestFeaturesController`

- File: `Repository/Framework/DevTools/Controllers/TestFeaturesController.php`
- Kind: `class`
- Summary: Presents the development feature-test harness.
- Responsibility: Supplies authentication and navigation state to the DevTools workspace.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `index()` | `public` | Renders the DevTools harness with current authentication state. | Renders the DevTools harness with current authentication state. |

### `Catalyst\Repository\DevTools\Controllers\ToasterTestController`

- File: `Repository/Framework/DevTools/Controllers/ToasterTestController.php`
- Kind: `class`
- Summary: Exposes notification-envelope variants for client toaster diagnostics.
- Responsibility: Returns deterministic toaster, modal and partial-refresh responses.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `apiToasterSuccess()` | `public` | Returns a successful response with a success toaster. | Returns a successful response with a success toaster. |
| `apiToasterError()` | `public` | Returns an error response with an error toaster. | Returns an error response with an error toaster. |
| `apiToasterWarning()` | `public` | Returns a partial-success response with a warning toaster. | Returns a partial-success response with a warning toaster. |
| `apiToasterInfo()` | `public` | Returns a successful response with an informational toaster. | Returns a successful response with an informational toaster. |
| `apiMultipleToasters()` | `public` | Returns a response carrying multiple queued toasters. | Returns a response carrying multiple queued toasters. |
| `apiModalTrigger()` | `public` | Returns a response that instructs the client to load a modal. | Returns a response that instructs the client to load a modal. |
| `apiJsEnhancementPartialRefresh()` | `public` | Returns refreshed partial HTML and its update notification. | Returns refreshed partial HTML and its update notification. |

### `Catalyst\Repository\DevTools\Controllers\UmlController`

- File: `Repository/Framework/DevTools/Controllers/UmlController.php`
- Kind: `class`
- Summary: Presents the development UML and runtime architecture workspace.
- Responsibility: Supplies configuration diagnostics to the trusted UML renderer.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `index()` | `public` | Renders UML diagnostics with loaded configuration metadata. | Renders UML diagnostics with loaded configuration metadata. |

### `Catalyst\Repository\DevTools\Controllers\UploadTestController`

- File: `Repository/Framework/DevTools/Controllers/UploadTestController.php`
- Kind: `class`
- Summary: Exposes a development endpoint for validating file uploads.
- Responsibility: Validates, stores and reports uploaded DevTools attachments.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `upload()` | `public` | Validates and stores an uploaded attachment for the harness. | Validates and stores an uploaded attachment for the harness. |

### `Catalyst\Repository\DevTools\Controllers\ValidatorTestController`

- File: `Repository/Framework/DevTools/Controllers/ValidatorTestController.php`
- Kind: `class`
- Summary: Exposes development endpoints for validator rule diagnostics.
- Responsibility: Returns deterministic validation and uniqueness-check responses.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `validatorTest()` | `public` | Validates representative form fields or a forced invalid payload. | Validates representative form fields or a forced invalid payload. |
| `validatorUniqueTest()` | `public` | Verifies uniqueness validation for a submitted demo email address. | Verifies uniqueness validation for a submitted demo email address. |

### `Catalyst\Repository\DevTools\Models\DemoEmail`

- File: `Repository/Framework/DevTools/Models/DemoEmail.php`
- Kind: `class`
- Summary: Represents isolated email records used by ORM diagnostics.
- Responsibility: Provides disposable model data for CRUD and collection tests.

### `Catalyst\Repository\DevTools\Services\DatabaseResetService`

- File: `Repository/Framework/DevTools/Services/DatabaseResetService.php`
- Kind: `class`
- Summary: Rebuilds the development database from canonical SQL and migrations.
- Responsibility: Orchestrates destructive DevTools database reset operations.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `reset()` | `public` | Drops current tables and replays the canonical development schema. | Drops current tables and replays the canonical development schema. |
| `executeSqlFile()` | `private` | Executes a Catalyst-controlled SQL file against the active connection. | Executes a Catalyst-controlled SQL file against the active connection. |

## Operational Notes

When PHP symbols or method contracts in this namespace change, refresh this document from docblocks and run `php public/cli.php docs:inventory --json`.

## Related Documentation

- `docs/runtime-inventory.md`
- `docs/runtime-module-catalog.md`
- `docs/harness-context-map.md`
