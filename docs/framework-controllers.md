# Catalyst\Framework\Controllers

## Purpose

Document controller and flash response primitives used by routed modules.

## Runtime Owners

| Concern | Owner |
|---|---|
| Redirects alternate route aliases back to canonical framework URLs. | `Catalyst\Framework\Controllers\CanonicalRedirectController` |
| Provides shared response, view, validation, authorization, notification, and redirect helpers. | `Catalyst\Framework\Controllers\Controller` |
| Handles client requests that dismiss flash messages from the active session. | `Catalyst\Framework\Controllers\FlashController` |

## Current Behavior

This file is regenerated from current PHP docblocks and the runtime inventory scope for `Catalyst\Framework\Controllers`. It intentionally replaces stale historical API notes with the classes and methods that exist in code now.

## API From Docblocks

### `Catalyst\Framework\Controllers\CanonicalRedirectController`

- File: `app/Framework/Controllers/CanonicalRedirectController.php`
- Kind: `class`
- Summary: Controller for canonical path redirects.
- Responsibility: Redirects alternate route aliases back to canonical framework URLs.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `root()` | `public` | Redirects the request to the canonical application root. | Redirects the request to the canonical application root. |

### `Catalyst\Framework\Controllers\Controller`

- File: `app/Framework/Controllers/Controller.php`
- Kind: `class`
- Summary: Base controller for HTTP request handlers.
- Responsibility: Provides shared response, view, validation, authorization, notification, and redirect helpers.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `__construct()` | `public` | Initializes request, logger, and view engine collaborators. | Initializes request, logger, and view engine collaborators. |
| `view()` | `protected` | Render a view template, optionally wrapped in a layout. | Render a view template, optionally wrapped in a layout. |
| `json()` | `protected` | Return a JSON response. | Return a JSON response. |
| `trustedHtmlResponse()` | `protected` | Builds an HTML response explicitly marked as trusted fragment content. | Builds an HTML response explicitly marked as trusted fragment content. |
| `jsonSuccess()` | `protected` | Return a successful JSON API response. | Return a successful JSON API response. |
| `jsonError()` | `protected` | Return an error JSON API response. | Return an error JSON API response. |
| `redirect()` | `protected` | Return a redirect response. | Return a redirect response. |
| `isAjax()` | `protected` | Check if the request is an AJAX request. | Check if the request is an AJAX request. |
| `expectsJson()` | `protected` | Check if the request expects a JSON response. | Check if the request expects a JSON response. |
| `logInfo()` | `protected` | Log an info message. | Log an info message. |
| `logError()` | `protected` | Log an error message. | Log an error message. |
| `logDebug()` | `protected` | Log a debug message. | Log a debug message. |
| `logWarning()` | `protected` | Log a warning message. | Log a warning message. |
| `jsonValidationError()` | `protected` | Return a JSON response for validation errors. | Return a JSON response for validation errors. |
| `validate()` | `protected` | Run validation and return the Validator instance. The caller is responsible for checking $v->fails() and handling errors. | Run validation and return the Validator instance. The caller is responsible for checking $v->fails() and handling errors. |
| `validateOrFail()` | `protected` | Run validation and throw ValidationException if it fails. The ExceptionHandler converts ValidationException to a 422 JSON response. | Run validation and throw ValidationException if it fails. The ExceptionHandler converts ValidationException to a 422 JSON response. |
| `authorize()` | `protected` | Assert the current user passes the given ability via Gate or Policy. Throws ForbiddenException (→ 403) if the check fails. | Assert the current user passes the given ability via Gate or Policy. Throws ForbiddenException (→ 403) if the check fails. |
| `can()` | `protected` | Check if the current user passes the given ability (non-throwing). | Check if the current user passes the given ability (non-throwing). |
| `authorizeResource()` | `protected` | Authorizes an ability against a resource-specific subject wrapper. | Authorizes an ability against a resource-specific subject wrapper. |
| `canResource()` | `protected` | Checks a resource-specific ability without throwing an authorization exception. | Checks a resource-specific ability without throwing an authorization exception. |
| `apiResponse()` | `protected` | Return a standardized API response. | Return a standardized API response. |
| `resourceJsonSuccess()` | `protected` | Returns a JSON success response with sensitive fields sanitized for a resource key. | Returns a JSON success response with sensitive fields sanitized for a resource key. |
| `sanitizeResourcePayload()` | `protected` | Sanitizes arrays or model payloads according to the resource sensitivity policy. | Sanitizes arrays or model payloads according to the resource sensitivity policy. |
| `sanitizeVersionPayloads()` | `protected` | Sanitizes version snapshots and diffs before exposing them through API responses. | Sanitizes version snapshots and diffs before exposing them through API responses. |
| `redirectToRoute()` | `protected` | Redirect to a named route. | Redirect to a named route. |
| `flash()` | `protected` | Get the flash message instance. | Provides the flash message bag used by controllers to enqueue one-shot feedback. |
| `toast()` | `protected` | Queue an ephemeral toast notification for the next page load. Use for confirmations that don't require the user to re-render a form (logout, save, copy). For validation errors or blocking issues that must stay until re-render, use flash()->error(...) instead. | Queue an ephemeral toast notification for the next page load. Use for confirmations that don't require the user to re-render a form (logout, save, copy). For validation errors or blocking issues that must stay until re-render, use flash()->error(...) instead. |
| `withSuccess()` | `protected` | Add a success flash message and optionally redirect. | Add a success flash message and optionally redirect. |
| `withError()` | `protected` | Add an error flash message and optionally redirect. | Add an error flash message and optionally redirect. |
| `rememberValidationState()` | `protected` | Stores non-sensitive old input and validation errors in the session. | Stores non-sensitive old input and validation errors in the session. |
| `input()` | `protected` | Get a request input value. | Get a request input value. |
| `all()` | `protected` | Get all request input. | Get all request input. |
| `only()` | `protected` | Get only specific input keys. | Get only specific input keys. |
| `except()` | `protected` | Get all input except specific keys. | Get all input except specific keys. |
| `notify()` | `protected` | Create a new notification bag. | Create a new notification bag. |
| `toaster()` | `protected` | Create a notification bag with a toaster. | Create a notification bag with a toaster. |
| `modal()` | `protected` | Create a notification bag with a modal. | Create a notification bag with a modal. |
| `jsonSuccessWithToast()` | `protected` | Return a JSON success response with a success toaster. | Return a JSON success response with a success toaster. |
| `jsonErrorWithToast()` | `protected` | Return a JSON error response with an error toaster. | Return a JSON error response with an error toaster. |
| `postActionSuccessRedirect()` | `protected` | Builds a success response for post-action redirects across HTML and JSON flows. | Builds a success response for post-action redirects across HTML and JSON flows. |
| `postActionErrorRedirect()` | `protected` | Builds an error response for post-action redirects across HTML and JSON flows. | Builds an error response for post-action redirects across HTML and JSON flows. |
| `isSequentialArray()` | `private` | Determines whether an array uses sequential numeric keys. | Determines whether an array uses sequential numeric keys. |
| `postActionRedirect()` | `private` | Applies toast or flash state and returns the appropriate redirect response shape. | Applies toast or flash state and returns the appropriate redirect response shape. |

### `Catalyst\Framework\Controllers\FlashController`

- File: `app/Framework/Controllers/FlashController.php`
- Kind: `class`
- Summary: Controller for flash message interactions.
- Responsibility: Handles client requests that dismiss flash messages from the active session.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `dismiss()` | `public` | Dismisses a flash message by id and returns a JSON response. | Dismisses a flash message by id and returns a JSON response. |

## Operational Notes

When PHP symbols or method contracts in this namespace change, refresh this document from docblocks and run `php public/cli.php docs:inventory --json`.

## Related Documentation

- `docs/runtime-inventory.md`
- `docs/runtime-module-catalog.md`
- `docs/harness-context-map.md`
