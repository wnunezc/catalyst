# Catalyst\Helpers\Exceptions

## Purpose

Document framework exception types.

## Runtime Owners

| Concern | Owner |
|---|---|
| Represents failures while establishing database connections. | `Catalyst\Helpers\Exceptions\ConnectionException` |
| Provides bootstrap exceptions for missing, unreadable or invalid environment files. | `Catalyst\Helpers\Exceptions\EnvironmentException` |
| Provides exceptions for common file read, write and existence failures. | `Catalyst\Helpers\Exceptions\FileSystemException` |
| Carries denial context for role, permission and policy authorization failures. | `Catalyst\Helpers\Exceptions\ForbiddenException` |
| Provides typed error codes and factories for mail delivery failures. | `Catalyst\Helpers\Exceptions\MailException` |
| Carries the HTTP methods accepted by a route after a 405 match failure. | `Catalyst\Helpers\Exceptions\MethodNotAllowedException` |
| Carries model identity when an expected database record is absent. | `Catalyst\Helpers\Exceptions\ModelNotFoundException` |
| Carries model identity and expected versus stored lock versions. | `Catalyst\Helpers\Exceptions\OptimisticLockException` |
| Represents failures while executing database queries. | `Catalyst\Helpers\Exceptions\QueryException` |
| Represents URI or named-route lookup failures with HTTP 404 semantics. | `Catalyst\Helpers\Exceptions\RouteNotFoundException` |
| Carries field errors, safe old input and response metadata for failed validation. | `Catalyst\Helpers\Exceptions\ValidationException` |
| Provides exceptions for missing layouts, missing templates and executable token templates. | `Catalyst\Helpers\Exceptions\ViewException` |

## Current Behavior

This file is regenerated from current PHP docblocks and the runtime inventory scope for `Catalyst\Helpers\Exceptions`. It intentionally replaces stale historical API notes with the classes and methods that exist in code now.

## API From Docblocks

### `Catalyst\Helpers\Exceptions\ConnectionException`

- File: `app/Helpers/Exceptions/ConnectionException.php`
- Kind: `class`
- Summary: Database connection exception
- Responsibility: Represents failures while establishing database connections.

### `Catalyst\Helpers\Exceptions\EnvironmentException`

- File: `app/Helpers/Exceptions/EnvironmentException.php`
- Kind: `class`
- Summary: Exception thrown when the environment configuration cannot be loaded.
- Responsibility: Provides bootstrap exceptions for missing, unreadable or invalid environment files.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `fileMissing()` | `public` | n/a | n/a |
| `copyFailed()` | `public` | n/a | n/a |
| `unreadable()` | `public` | n/a | n/a |

### `Catalyst\Helpers\Exceptions\FileSystemException`

- File: `app/Helpers/Exceptions/FileSystemException.php`
- Kind: `class`
- Summary: Exception class for file system-related errors.
- Responsibility: Provides exceptions for common file read, write and existence failures.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `unableToWriteFile()` | `public` | n/a | n/a |
| `unableToReadFile()` | `public` | n/a | n/a |
| `fileMissing()` | `public` | n/a | n/a |

### `Catalyst\Helpers\Exceptions\ForbiddenException`

- File: `app/Helpers/Exceptions/ForbiddenException.php`
- Kind: `class`
- Summary: ForbiddenException — HTTP 403 Forbidden
- Responsibility: Carries denial context for role, permission and policy authorization failures.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `__construct()` | `private` | Initializes the Forbidden Exception instance. | Initializes the Forbidden Exception instance. |
| `role()` | `public` | User does not have the required role. | n/a |

### `Catalyst\Helpers\Exceptions\MailException`

- File: `app/Helpers/Exceptions/MailException.php`
- Kind: `class`
- Summary: Mail exception
- Responsibility: Provides typed error codes and factories for mail delivery failures.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `configurationError()` | `public` | Create a new configuration error instance | n/a |
| `invalidAddress()` | `public` | Create a new invalid address error instance | n/a |
| `sendingError()` | `public` | Create a new sending error instance | n/a |
| `attachmentError()` | `public` | Create a new attachment error instance | n/a |
| `templateError()` | `public` | Create a new template error instance | n/a |
| `dkimError()` | `public` | Create a new DKIM error instance | n/a |

### `Catalyst\Helpers\Exceptions\MethodNotAllowedException`

- File: `app/Helpers/Exceptions/MethodNotAllowedException.php`
- Kind: `class`
- Summary: Exception thrown when an HTTP method is not allowed for a route
- Responsibility: Carries the HTTP methods accepted by a route after a 405 match failure.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `__construct()` | `public` | Create a new method not allowed exception. | Create a new method not allowed exception. |
| `getAllowedMethods()` | `public` | Get the HTTP methods that are allowed for the route. | Exposes the route methods accepted by the failed request target. |

### `Catalyst\Helpers\Exceptions\ModelNotFoundException`

- File: `app/Helpers/Exceptions/ModelNotFoundException.php`
- Kind: `class`
- Summary: Thrown when a Model query expected exactly one result but found none.
- Responsibility: Carries model identity when an expected database record is absent.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `forModel()` | `public` | Model::findOrFail($id) — expected a specific record. | n/a |

### `Catalyst\Helpers\Exceptions\OptimisticLockException`

- File: `app/Helpers/Exceptions/OptimisticLockException.php`
- Kind: `class`
- Summary: Represents an optimistic-lock conflict while persisting a model.
- Responsibility: Carries model identity and expected versus stored lock versions.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `__construct()` | `public` | Initializes the Optimistic Lock Exception instance. | Initializes the Optimistic Lock Exception instance. |
| `forModel()` | `public` | Creates a conflict exception for a model persistence attempt. | n/a |
| `modelClass()` | `public` | Returns the conflicted model class. | Returns the conflicted model class. |
| `recordId()` | `public` | Returns the conflicted record identifier. | Returns the conflicted record identifier. |
| `column()` | `public` | Returns the lock-version column name. | Returns the lock-version column name. |
| `expectedVersion()` | `public` | Returns the version expected by the writer. | Returns the version expected by the writer. |
| `currentVersion()` | `public` | Returns the current stored version when known. | Returns the current stored version when known. |

### `Catalyst\Helpers\Exceptions\QueryException`

- File: `app/Helpers/Exceptions/QueryException.php`
- Kind: `class`
- Summary: Database query exception
- Responsibility: Represents failures while executing database queries.

### `Catalyst\Helpers\Exceptions\RouteNotFoundException`

- File: `app/Helpers/Exceptions/RouteNotFoundException.php`
- Kind: `class`
- Summary: Exception thrown when a route cannot be found
- Responsibility: Represents URI or named-route lookup failures with HTTP 404 semantics.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `__construct()` | `public` | Create a new route not found exception. | Create a new route not found exception. |

### `Catalyst\Helpers\Exceptions\ValidationException`

- File: `app/Helpers/Exceptions/ValidationException.php`
- Kind: `class`
- Summary: ValidationException — thrown when validation fails.
- Responsibility: Carries field errors, safe old input and response metadata for failed validation.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `__construct()` | `private` | Private constructor — use factory methods. | Private constructor — use factory methods. |
| `withErrors()` | `public` | Create a ValidationException from a field-errors array. | n/a |
| `getErrors()` | `public` | Get field-level validation errors. | Get field-level validation errors. |
| `getStatusCode()` | `public` | Get the HTTP status code. | Exposes the response status associated with the validation failure. |
| `getOldInput()` | `public` | Returns sanitized input for HTML form repopulation. | Returns sanitized input for HTML form repopulation. |
| `getErrorBag()` | `public` | Returns the validation error bag name. | Returns the validation error bag name. |

### `Catalyst\Helpers\Exceptions\ViewException`

- File: `app/Helpers/Exceptions/ViewException.php`
- Kind: `class`
- Summary: ViewException - Thrown for view rendering failures
- Responsibility: Provides exceptions for missing layouts, missing templates and executable token templates.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `templateNotFound()` | `public` | Template file not found in any registered path | n/a |

## Operational Notes

When PHP symbols or method contracts in this namespace change, refresh this document from docblocks and run `php public/cli.php docs:inventory --json`.

## Related Documentation

- `docs/runtime-inventory.md`
- `docs/runtime-module-catalog.md`
- `docs/harness-context-map.md`
