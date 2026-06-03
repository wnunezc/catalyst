# Catalyst\Helpers\Log

## Purpose

Document log formatting, sanitization, rotation and writer helpers.

## Runtime Owners

| Concern | Owner |
|---|---|
| Preserves bounded log history while excluding stream destinations. | `Catalyst\Helpers\Log\LogRotator` |
| Filters, sanitizes, formats and writes application log events. | `Catalyst\Helpers\Log\Logger` |
| Creates log directories and validates configurable channel, level and rotation limits. | `Catalyst\Helpers\Log\LoggerConfigurator` |
| Applies resource sensitivity policies to nested and top-level logging context. | `Catalyst\Helpers\Log\LoggerContextSanitizer` |
| Adds request metadata, timestamps, client identity and serialized context to log messages. | `Catalyst\Helpers\Log\LoggerEntryFormatter` |
| Maps logger levels to CLI styles and emits formatted diagnostic output. | `Catalyst\Helpers\Log\LoggerInlineDisplay` |
| Normalizes level names and resolves filtering priorities and directories. | `Catalyst\Helpers\Log\LoggerLevelMap` |
| Distinguishes CLI, API, asset, bot, AJAX and page requests. | `Catalyst\Helpers\Log\LoggerRequestClassifier` |
| Stores destination, threshold, display and rotation options used by logger services. | `Catalyst\Helpers\Log\LoggerSettings` |
| Resolves log paths, creates directories, rotates files and appends entries. | `Catalyst\Helpers\Log\LoggerWriter` |

## Current Behavior

This file is regenerated from current PHP docblocks and the runtime inventory scope for `Catalyst\Helpers\Log`. It intentionally replaces stale historical API notes with the classes and methods that exist in code now.

## API From Docblocks

### `Catalyst\Helpers\Log\LogRotator`

- File: `app/Helpers/Log/LogRotator.php`
- Kind: `class`
- Summary: Rotates filesystem logs when they exceed configured size limits.
- Responsibility: Preserves bounded log history while excluding stream destinations.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `rotateIfNeeded()` | `public` | Rotates a log file when rotation is enabled and its size limit is reached. | Rotates a log file when rotation is enabled and its size limit is reached. |
| `rotate()` | `private` | Shifts rotated files and reopens the active log path. | Shifts rotated files and reopens the active log path. |
| `isStream()` | `private` | Determines whether a path targets a PHP stream. | Determines whether a path targets a PHP stream. |

### `Catalyst\Helpers\Log\Logger`

- File: `app/Helpers/Log/Logger.php`
- Kind: `class`
- Summary: Logger class for recording system events, errors, and user activities
- Responsibility: Filters, sanitizes, formats and writes application log events.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `__construct()` | `protected` | Initializes logger settings, directory readiness and formatting collaborators. | Prepares the singleton logger pipeline used by channel writers and context sanitization. |
| `configure()` | `public` | Configure logger settings - will only run once per request. | Configure logger settings - will only run once per request. |
| `log()` | `public` | Log a message with a specific level. | Log a message with a specific level. |
| `shouldLogWebAssetRequest()` | `private` | Determines whether the current asset request should emit the given level. | Determines whether the current asset request should emit the given level. |
| `getRequestId()` | `private` | Get a unique ID for this request. | Get a unique ID for this request. |
| `mail()` | `public` | Logs a mail-related event through the standard info channel. | Logs a mail-related event through the standard info channel. |
| `displayLog()` | `private` | Display log in the terminal or browser - will only be used if explicitly enabled. | Display log in the terminal or browser - will only be used if explicitly enabled. |
| `emergency()` | `public` | Log an emergency message. | Log an emergency message. |
| `alert()` | `public` | Log an alert message. | Log an alert message. |
| `critical()` | `public` | Log a critical message. | Log a critical message. |
| `error()` | `public` | Log an error message. | Log an error message. |
| `warning()` | `public` | Log a warning message. | Log a warning message. |
| `notice()` | `public` | Log a notice message. | Log a notice message. |
| `info()` | `public` | Log an info message. | Log an info message. |
| `debug()` | `public` | Log a debug message. | Log a debug message. |
| `system()` | `public` | Records a system event through the standard informational log channel. | Adds system event metadata before delegating sanitized context to the shared logger pipeline. |
| `email()` | `public` | Records a mail delivery event in the dedicated email log stream. | Sanitizes email context and writes a formatted delivery entry through the logger writer. |
| `user()` | `public` | Records an application user event through the standard informational log channel. | Adds user event metadata before delegating sanitized context to the shared logger pipeline. |

### `Catalyst\Helpers\Log\LoggerConfigurator`

- File: `app/Helpers/Log/LoggerConfigurator.php`
- Kind: `class`
- Summary: Applies runtime options to mutable logger settings.
- Responsibility: Creates log directories and validates configurable channel, level and rotation limits.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `ensureLogDirectory()` | `public` | Creates the configured log directory when missing. | Creates the configured log directory when missing. |
| `applyRuntimeOptions()` | `public` | Applies logger options that may change during a request. | Applies logger options that may change during a request. |
| `applyInitialOptions()` | `public` | Applies validated logger options that initialize destination behavior. | Applies validated logger options that initialize destination behavior. |

### `Catalyst\Helpers\Log\LoggerContextSanitizer`

- File: `app/Helpers/Log/LoggerContextSanitizer.php`
- Kind: `class`
- Summary: Sanitizes contextual data before it reaches log destinations.
- Responsibility: Applies resource sensitivity policies to nested and top-level logging context.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `__construct()` | `public` | Initializes the Logger Context Sanitizer instance. | Initializes the Logger Context Sanitizer instance. |
| `sanitize()` | `public` | Sanitizes the provided value. | Sanitizes the provided value. |

### `Catalyst\Helpers\Log\LoggerEntryFormatter`

- File: `app/Helpers/Log/LoggerEntryFormatter.php`
- Kind: `class`
- Summary: Formats structured application and email log entries.
- Responsibility: Adds request metadata, timestamps, client identity and serialized context to log messages.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `format()` | `public` | Formats an application log entry with request metadata when available. | Formats an application log entry with request metadata when available. |
| `formatEmail()` | `public` | Formats an email log entry. | Formats an email log entry. |
| `buildEntry()` | `private` | Builds the common serialized log entry representation. | Builds the common serialized log entry representation. |
| `getCurrentUserId()` | `private` | Returns the authenticated user identifier or the guest marker. | Returns the authenticated user identifier or the guest marker. |
| `getClientIp()` | `private` | Returns the client IP address or CLI marker. | Returns the client IP address or CLI marker. |

### `Catalyst\Helpers\Log\LoggerInlineDisplay`

- File: `app/Helpers/Log/LoggerInlineDisplay.php`
- Kind: `class`
- Summary: Renders enabled log output in CLI boxes.
- Responsibility: Maps logger levels to CLI styles and emits formatted diagnostic output.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `render()` | `public` | Renders a formatted log entry when inline CLI display is enabled. | Renders a formatted log entry when inline CLI display is enabled. |

### `Catalyst\Helpers\Log\LoggerLevelMap`

- File: `app/Helpers/Log/LoggerLevelMap.php`
- Kind: `class`
- Summary: Maps logger levels to priorities and output categories.
- Responsibility: Normalizes level names and resolves filtering priorities and directories.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `channels()` | `public` | Returns supported logger channel names. | n/a |
| `normalize()` | `public` | Normalizes a logger level name when supported. | n/a |
| `priority()` | `public` | Returns the numeric priority for a logger level. | n/a |
| `categoryFor()` | `public` | Returns the output directory category for a logger level. | n/a |

### `Catalyst\Helpers\Log\LoggerRequestClassifier`

- File: `app/Helpers/Log/LoggerRequestClassifier.php`
- Kind: `class`
- Summary: Classifies the active request for logging decisions.
- Responsibility: Distinguishes CLI, API, asset, bot, AJAX and page requests.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `classify()` | `public` | Returns the category of the active request. | Returns the category of the active request. |

### `Catalyst\Helpers\Log\LoggerSettings`

- File: `app/Helpers/Log/LoggerSettings.php`
- Kind: `class`
- Summary: Carries mutable runtime logger configuration.
- Responsibility: Stores destination, threshold, display and rotation options used by logger services.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `__construct()` | `public` | Initializes the Logger Settings instance. | Initializes the Logger Settings instance. |

### `Catalyst\Helpers\Log\LoggerWriter`

- File: `app/Helpers/Log/LoggerWriter.php`
- Kind: `class`
- Summary: Writes formatted log entries to configured destinations.
- Responsibility: Resolves log paths, creates directories, rotates files and appends entries.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `__construct()` | `public` | Initializes the Logger Writer instance. | Initializes the Logger Writer instance. |
| `write()` | `public` | Writes a categorized application log entry. | Writes a categorized application log entry. |
| `writeEmail()` | `public` | Writes an entry to the daily email log. | Writes an entry to the daily email log. |
| `resolveLogFile()` | `private` | Resolves the destination path for a log level and channel. | Resolves the destination path for a log level and channel. |
| `ensureDirectory()` | `private` | Creates a log directory when missing. | Creates a log directory when missing. |
| `appendToFile()` | `private` | Rotates and appends one entry to a log destination. | Rotates and appends one entry to a log destination. |

## Operational Notes

When PHP symbols or method contracts in this namespace change, refresh this document from docblocks and run `php public/cli.php docs:inventory --json`.

## Related Documentation

- `docs/runtime-inventory.md`
- `docs/runtime-module-catalog.md`
- `docs/harness-context-map.md`
