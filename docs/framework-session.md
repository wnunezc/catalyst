# Catalyst\Framework\Session

## Purpose

Document session, flash and toast queue primitives.

## Runtime Owners

| Concern | Owner |
|---|---|
| Implements database-backed session reads, writes, cleanup and table bootstrap. | `Catalyst\Framework\Session\DatabaseSessionHandler` |
| Persists, consumes and deduplicates flash-message state in the session. | `Catalyst\Framework\Session\FlashBag` |
| Exposes the controller-facing API for one-shot and persistent flash messages. | `Catalyst\Framework\Session\FlashMessage` |
| Initializes PHP sessions and provides storage, migration and form-state helpers. | `Catalyst\Framework\Session\SessionManager` |
| Buffers one-shot toast notifications and drains them on the next read. | `Catalyst\Framework\Session\ToastQueue` |

## Current Behavior

This file is regenerated from current PHP docblocks and the runtime inventory scope for `Catalyst\Framework\Session`. It intentionally replaces stale historical API notes with the classes and methods that exist in code now.

## API From Docblocks

### `Catalyst\Framework\Session\DatabaseSessionHandler`

- File: `app/Framework/Session/DatabaseSessionHandler.php`
- Kind: `class`
- Summary: Stores PHP session payloads in the configured database table.
- Responsibility: Implements database-backed session reads, writes, cleanup and table bootstrap.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `__construct()` | `public` | Initializes the Database Session Handler instance. | Initializes the Database Session Handler instance. |
| `open()` | `public` | Prepares the database table when PHP opens the session handler. | Prepares the database table when PHP opens the session handler. |
| `close()` | `public` | Completes a database-backed session handling cycle. | Completes a database-backed session handling cycle. |
| `read()` | `public` | Reads the requested value. | Reads the requested value. |
| `write()` | `public` | Writes the requested value. | Writes the requested value. |
| `destroy()` | `public` | Deletes a persisted session payload. | Deletes a persisted session payload. |
| `gc()` | `public` | Deletes sessions older than the configured lifetime. | Deletes sessions older than the configured lifetime. |
| `ensureTable()` | `private` | Creates the session table on demand and reports whether it is ready. | Creates the session table on demand and reports whether it is ready. |
| `pdo()` | `private` | Returns the configured PDO connection. | Returns the configured PDO connection. |
| `clientIp()` | `private` | Resolves the client IP address stored with a session. | Resolves the client IP address stored with a session. |
| `userAgent()` | `private` | Resolves the bounded user-agent value stored with a session. | Resolves the bounded user-agent value stored with a session. |

### `Catalyst\Framework\Session\FlashBag`

- File: `app/Framework/Session/FlashBag.php`
- Kind: `class`
- Summary: FlashBag — low-level storage for regular and persistent flash messages.
- Responsibility: Persists, consumes and deduplicates flash-message state in the session.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `__construct()` | `public` | Initializes the Flash Bag instance. | Initializes the Flash Bag instance. |
| `add()` | `public` | Adds a one-shot flash message unless it was already displayed. | Adds a one-shot flash message unless it was already displayed. |
| `addPersistent()` | `public` | Adds a persistent flash message unless it was dismissed. | Adds a persistent flash message unless it was dismissed. |
| `dismiss()` | `public` | Dismisses a persistent message and removes it from the active list. | Dismisses a persistent message and removes it from the active list. |
| `all()` | `public` | Consumes unread one-shot messages grouped by type. | Consumes unread one-shot messages grouped by type. |
| `allPersistent()` | `public` | Returns visible persistent messages. | Returns visible persistent messages. |
| `get()` | `public` | Consumes unread messages of a selected type. | Consumes unread messages of a selected type. |
| `has()` | `public` | Determines whether unread one-shot messages remain. | Determines whether unread one-shot messages remain. |
| `hasPersistent()` | `public` | Determines whether visible persistent messages remain. | Determines whether visible persistent messages remain. |
| `clear()` | `public` | Clears one-shot messages. | Clears one-shot messages. |
| `clearPersistent()` | `public` | Clears persistent messages. | Clears persistent messages. |
| `clearHistory()` | `public` | Clears the displayed-message history. | Clears the displayed-message history. |
| `clearDismissed()` | `public` | Clears the dismissed-message identifiers. | Clears the dismissed-message identifiers. |
| `reset()` | `public` | Clears all flash-message storage. | Clears all flash-message storage. |
| `peek()` | `public` | Returns queued one-shot messages without consuming them. | Returns queued one-shot messages without consuming them. |
| `count()` | `public` | Counts unread and visible flash messages. | Counts unread and visible flash messages. |
| `initializeStorage()` | `private` | Initializes and validates flash-message session storage. | Initializes and validates flash-message session storage. |
| `isValidMessage()` | `private` | Determines whether a stored message has the expected shape. | Determines whether a stored message has the expected shape. |
| `generateMessageId()` | `private` | Generates a unique identifier for a flash message. | Generates a unique identifier for a flash message. |
| `isDisplayed()` | `private` | Determines whether a message was already displayed. | Determines whether a message was already displayed. |
| `isDismissed()` | `private` | Determines whether a persistent message was dismissed. | Determines whether a persistent message was dismissed. |
| `markAsDisplayed()` | `private` | Records a message as displayed and bounds history size. | Records a message as displayed and bounds history size. |
| `cleanupHistory()` | `private` | Removes expired displayed-message history entries. | Removes expired displayed-message history entries. |

### `Catalyst\Framework\Session\FlashMessage`

- File: `app/Framework/Session/FlashMessage.php`
- Kind: `class`
- Summary: FlashMessage — high-level facade for inline banner messages.
- Responsibility: Exposes the controller-facing API for one-shot and persistent flash messages.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `__construct()` | `protected` | Initializes the Flash Message instance. | Initializes the Flash Message instance. |
| `add()` | `public` | Adds a one-shot flash message. | Adds a one-shot flash message. |
| `addPersistent()` | `public` | Adds a persistent flash message. | Adds a persistent flash message. |
| `dismiss()` | `public` | Dismisses a persistent flash message by identifier. | Dismisses a persistent flash message by identifier. |
| `success()` | `public` | Adds a one-shot success message. | Adds a one-shot success message. |
| `successPersistent()` | `public` | Adds a persistent success message. | Adds a persistent success message. |
| `error()` | `public` | Adds a one-shot error message. | Adds a one-shot error message. |
| `errorPersistent()` | `public` | Adds a persistent error message. | Adds a persistent error message. |
| `warning()` | `public` | Adds a one-shot warning message. | Adds a one-shot warning message. |
| `warningPersistent()` | `public` | Adds a persistent warning message. | Adds a persistent warning message. |
| `info()` | `public` | Adds a one-shot informational message. | Adds a one-shot informational message. |
| `infoPersistent()` | `public` | Adds a persistent informational message. | Adds a persistent informational message. |
| `all()` | `public` | Consumes grouped one-shot flash messages. | Consumes grouped one-shot flash messages. |
| `allPersistent()` | `public` | Returns visible persistent messages. | Returns visible persistent messages. |
| `get()` | `public` | Consumes unread messages of a selected type. | Consumes unread messages of a selected type. |
| `has()` | `public` | Determines whether unread one-shot messages remain. | Determines whether unread one-shot messages remain. |
| `hasPersistent()` | `public` | Determines whether has Persistent. | Determines whether has Persistent. |
| `clear()` | `public` | Clears one-shot messages. | Clears one-shot messages. |
| `clearPersistent()` | `public` | Clears persistent messages. | Clears persistent messages. |
| `clearHistory()` | `public` | Clears displayed-message history. | Clears displayed-message history. |
| `clearDismissed()` | `public` | Clears dismissed-message identifiers. | Clears dismissed-message identifiers. |
| `reset()` | `public` | Clears all flash-message state. | Clears all flash-message state. |
| `peek()` | `public` | Returns queued one-shot messages without consuming them. | Returns queued one-shot messages without consuming them. |
| `count()` | `public` | Counts unread and visible flash messages. | Counts unread and visible flash messages. |

### `Catalyst\Framework\Session\SessionManager`

- File: `app/Framework/Session/SessionManager.php`
- Kind: `class`
- Summary: SessionManager class for managing application sessions
- Responsibility: Initializes PHP sessions and provides storage, migration and form-state helpers.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `getConfig()` | `public` | Get the session configuration. | Exposes the session configuration used to start and manage session state. |
| `setConfig()` | `public` | Set the session configuration. | Stores session configuration before the session manager starts runtime state. |
| `init()` | `public` | Initialize the session with provided configuration. | Initialize the session with provided configuration. |
| `has()` | `public` | Check if a session variable exists. | Check if a session variable exists. |
| `get()` | `public` | Returns and removes validation-error bags. Get a session variable. | Returns and removes validation-error bags. Get a session variable. |
| `set()` | `public` | Set a session variable. | Set a session variable. |
| `remove()` | `public` | Remove a session variable. | Remove a session variable. |
| `all()` | `public` | Get all session data. | Get all session data. |
| `clear()` | `public` | Clear all session data. | Clear all session data. |
| `destroy()` | `public` | Destroy the current session. | Destroy the current session. |
| `regenerateId()` | `public` | Regenerate the session ID. | Regenerate the session ID. |
| `isInitialized()` | `public` | Check if the session is initialized. | Check if the session is initialized. |
| `seedActiveSession()` | `public` | Seeds the active session payload into a target persistence driver. | Seeds the active session payload into a target persistence driver. |
| `flashOldInput()` | `public` | Stores sanitized old form input for the next request. | Stores sanitized old form input for the next request. |
| `peekOldInput()` | `public` | Returns stored old form input without consuming it. | Returns stored old form input without consuming it. |
| `consumeOldInput()` | `public` | Returns and removes stored old form input. | Returns and removes stored old form input. |
| `flashValidationErrors()` | `public` | Stores normalized validation errors for the next request. | Stores normalized validation errors for the next request. |
| `peekValidationErrors()` | `public` | Returns validation-error bags without consuming them. | Returns validation-error bags without consuming them. |
| `consumeValidationErrors()` | `public` | Returns and removes validation-error bags. | Returns and removes validation-error bags. |
| `clearFormState()` | `public` | Clears old input and validation-error state from the session. | Clears old input and validation-error state from the session. |
| `resolveSecureDefault()` | `protected` | Resolve the secure cookie default based on environment (G11). Development: false (HTTP allowed). Production/Staging: true (HTTPS required). Production warning is logged in init() after full config merge. | Resolve the secure cookie default based on environment (G11). Development: false (HTTP allowed). Production/Staging: true (HTTPS required). Production warning is logged in init() after full config merge. |
| `configureSessionHandler()` | `private` | Configures the requested session persistence driver. | Configures the requested session persistence driver. |
| `ensureInitialized()` | `protected` | Ensure the session is initialized. | Ensure the session is initialized. |
| `sanitizeSessionTable()` | `private` | Sanitizes the provided value. | Sanitizes the provided value. |
| `writeNativeSessionFile()` | `private` | Writes the requested value. | Writes the requested value. |
| `sanitizeOldInput()` | `private` | Removes sensitive fields from recursively stored old input. | Removes sensitive fields from recursively stored old input. |
| `sanitizeOldInputValue()` | `private` | Sanitizes the provided value. | Sanitizes the provided value. |
| `normalizeValidationErrors()` | `private` | Normalizes validation errors into field-to-message arrays. | Normalizes validation errors into field-to-message arrays. |

### `Catalyst\Framework\Session\ToastQueue`

- File: `app/Framework/Session/ToastQueue.php`
- Kind: `class`
- Summary: ToastQueue — ephemeral toast notifications queued for the next page load.
- Responsibility: Buffers one-shot toast notifications and drains them on the next read.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `__construct()` | `protected` | Initializes the object with the collaborators or state required for its responsibility. | Initializes the object with the collaborators or state required for its responsibility. |
| `push()` | `public` | Queue a toast notification for the next page load. | Queue a toast notification for the next page load. |
| `all()` | `public` | Consume all pending toasts (clears the queue). | Consume all pending toasts (clears the queue). |
| `clear()` | `public` | Clear the queue without consuming. | Clear the queue without consuming. |

## Operational Notes

When PHP symbols or method contracts in this namespace change, refresh this document from docblocks and run `php public/cli.php docs:inventory --json`.

## Related Documentation

- `docs/runtime-inventory.md`
- `docs/runtime-module-catalog.md`
- `docs/harness-context-map.md`
