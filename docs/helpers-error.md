# Catalyst\Helpers\Error

## Purpose

Document runtime error capture, logging and output helpers.

## Runtime Owners

| Concern | Owner |
|---|---|
| Registers shutdown, exception and PHP error handlers once per request. | `Catalyst\Helpers\Error\ErrorCatcher` |
| Converts reportable PHP errors into Catalyst diagnostic output. | `Catalyst\Helpers\Error\ErrorHandler` |
| Maps captured errors to logger levels and writes structured error context. | `Catalyst\Helpers\Error\ErrorLogger` |
| Formats caught errors for CLI boxes or web error templates. | `Catalyst\Helpers\Error\ErrorOutput` |
| Converts framework exceptions into HTTP, JSON or diagnostic error responses. | `Catalyst\Helpers\Error\ExceptionHandler` |
| Captures fatal shutdown errors and renders them through the shared error output path. | `Catalyst\Helpers\Error\ShutdownHandler` |

## Current Behavior

This file is regenerated from current PHP docblocks and the runtime inventory scope for `Catalyst\Helpers\Error`. It intentionally replaces stale historical API notes with the classes and methods that exist in code now.

## API From Docblocks

### `Catalyst\Helpers\Error\ErrorCatcher`

- File: `app/Helpers/Error/ErrorCatcher.php`
- Kind: `class`
- Summary: Class that handles capturing and displaying errors in the application.
- Responsibility: Registers shutdown, exception and PHP error handlers once per request.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `initialize()` | `public` | Initialize the error handling system. | Initialize the error handling system. |
| `configureErrorDisplay()` | `private` | Configure PHP error display settings based on the environment. | Configure PHP error display settings based on the environment. |

### `Catalyst\Helpers\Error\ErrorHandler`

- File: `app/Helpers/Error/ErrorHandler.php`
- Kind: `class`
- Summary: Class that handles registered Errors.
- Responsibility: Converts reportable PHP errors into Catalyst diagnostic output.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `handle()` | `public` | Error handler. Captures and handles errors generated in the application. | Error handler. Captures and handles errors generated in the application. |

### `Catalyst\Helpers\Error\ErrorLogger`

- File: `app/Helpers/Error/ErrorLogger.php`
- Kind: `class`
- Summary: Class to handle logging of errors caught by BugCatcher
- Responsibility: Maps captured errors to logger levels and writes structured error context.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `logError()` | `public` | Log an error caught by BugCatcher | n/a |
| `determineLogLevel()` | `private` | Determine the appropriate log level based on a PHP error type | n/a |

### `Catalyst\Helpers\Error\ErrorOutput`

- File: `app/Helpers/Error/ErrorOutput.php`
- Kind: `class`
- Summary: Class to handle output of errors caught by BugCatcher
- Responsibility: Formats caught errors for CLI boxes or web error templates.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `display()` | `public` | Output the error information based on environment (CLI or Web). | Output the error information based on environment (CLI or Web). |
| `displayCLI()` | `private` | Display error information in CLI mode. | Display error information in CLI mode. |
| `formatCliOutput()` | `private` | Format error data for CLI output. | Format error data for CLI output. |

### `Catalyst\Helpers\Error\ExceptionHandler`

- File: `app/Helpers/Error/ExceptionHandler.php`
- Kind: `class`
- Summary: Class that handles registered Exceptions.
- Responsibility: Converts framework exceptions into HTTP, JSON or diagnostic error responses.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `handle()` | `public` | Exception handler. Captures and handles exceptions thrown in the application. | Exception handler. Captures and handles exceptions thrown in the application. |

### `Catalyst\Helpers\Error\ShutdownHandler`

- File: `app/Helpers/Error/ShutdownHandler.php`
- Kind: `class`
- Summary: Class that handles registered Shutdowns.
- Responsibility: Captures fatal shutdown errors and renders them through the shared error output path.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `handle()` | `public` | Shutdown handler. Captures fatal errors that would otherwise not be caught. | Shutdown handler. Captures fatal errors that would otherwise not be caught. |

## Operational Notes

When PHP symbols or method contracts in this namespace change, refresh this document from docblocks and run `php public/cli.php docs:inventory --json`.

## Related Documentation

- `docs/runtime-inventory.md`
- `docs/runtime-module-catalog.md`
- `docs/harness-context-map.md`
