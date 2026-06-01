# `Catalyst\Helpers\Error`

## Overview

The error stack is display-and-log oriented. It does **not** convert PHP errors into thrown exceptions. File writing, channel routing and rotation are delegated to the central `Logger`, `LoggerWriter` and `LogRotator` pipeline.

The live flow is:

1. `ErrorCatcher` initializes the handlers and output buffering.
2. PHP errors go to `ErrorHandler`.
3. Uncaught exceptions go to `ExceptionHandler`.
4. Fatal shutdown errors go to `ShutdownHandler`.
5. All formatted outputs pass through `ErrorOutput`, which logs first via `ErrorLogger` and then renders CLI or web output.

## Class: ErrorCatcher

**File**: `app/Helpers/Error/ErrorCatcher.php`

### Purpose

Bootstraps the error system once per request/process.

### Traits used

- `SingletonTrait`

### Public API

- `initialize(): void`

### Runtime behavior

- configures `display_errors` / `display_startup_errors` from `IS_DEVELOPMENT`
- registers:
  - `register_shutdown_function([ShutdownHandler::getInstance(), 'handle'])`
  - `set_exception_handler([ExceptionHandler::getInstance(), 'handle'])`
  - `set_error_handler([ErrorHandler::getInstance(), 'handle'])`
- starts output buffering when no buffer exists
- auto-initializes at file load time after defining `INITIALIZED_BUG_CATCHER`

## Class: ErrorHandler

**File**: `app/Helpers/Error/ErrorHandler.php`

### Traits used

- `SingletonTrait`
- `OutputCleanerTrait`
- `ErrorTypeTrait`

### Public API

- `handle(int $errorLevel, string $errorDesc, string $errorFile, int $errorLine): bool`

### Important correction

This handler does **not** throw or convert errors into exceptions. It:

- respects `error_reporting()`
- cleans buffered output
- formats error metadata
- delegates rendering to `ErrorOutput`
- returns `true` to suppress PHP's default handler when it handled the error

If the error is masked by `error_reporting()`, it returns `false`.

## Class: ExceptionHandler

**File**: `app/Helpers/Error/ExceptionHandler.php`

### Traits used

- `SingletonTrait`
- `OutputCleanerTrait`

### Public API

- `handle(Throwable $exception): void`

### Runtime behavior

- `ForbiddenException`
  - AJAX / JSON request: emits `403` JSON
  - browser request: flashes an error and redirects to `/`
- `ValidationException`
  - emits `422` JSON with `errors`
- all other exceptions
  - set HTTP `500` in web mode
  - delegate formatted rendering to `ErrorOutput`

## Class: ShutdownHandler

**File**: `app/Helpers/Error/ShutdownHandler.php`

### Traits used

- `SingletonTrait`
- `OutputCleanerTrait`
- `ErrorTypeTrait`

### Public API

- `handle(): void`

### Runtime behavior

- inspects `error_get_last()`
- only reacts to fatal types:
  - `E_ERROR`
  - `E_PARSE`
  - `E_CORE_ERROR`
  - `E_COMPILE_ERROR`
  - `E_USER_ERROR`
  - `E_RECOVERABLE_ERROR`
- avoids re-rendering warnings/notices already handled by `ErrorHandler`

## Class: ErrorLogger

**File**: `app/Helpers/Error/ErrorLogger.php`

### Public API

- `logError(array $errorData): void`

### Runtime behavior

- delegates persistence to `Logger::log(...)`
- derives the log level from the PHP error type / label
- stores context such as `ticket`, `error_type`, `file`, `line`, and formatted trace

### Important correction

`ErrorLogger` does not manage log files directly. File layout, channels, rotation and retention belong to the central `Logger`, `LoggerWriter` and `LogRotator` pipeline.

## Class: ErrorOutput

**File**: `app/Helpers/Error/ErrorOutput.php`

### Traits used

- `SingletonTrait`

### Public API

- `display(array $errorData): void`
- `formatBacktrace(array $errorData): string`

### Runtime behavior

- assigns `micro_time`
- logs through `ErrorLogger` before rendering
- CLI mode:
  - formats the error with `DrawBox`
- web mode:
  - renders `boot-core/template/errors/handler_error.phtml` in development
  - renders `boot-core/template/errors/handler_error_no.phtml` outside development
  - includes a source-code snippet only in development

### Internal helpers

- `displayCLI(array $errorData): void`
- `formatCliOutput(array $errorData): string`
- `displayWeb(array $errorData): void`
- `getCodeSnippet(string $file, int $line, int $contextLines = 5): string`
- `formatArguments(array $args): string`
- `getRouteDescription(array $track): string`

## Related traits

- `D:/OpsZone/DevWorkspace/Projects/Web/catalyst/docs/framework-traits.md`
