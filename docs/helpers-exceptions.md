# Catalyst\Helpers\Exceptions

All custom exceptions extend PHP's base `Exception` (or `RuntimeException`) and are located in `app/Helpers/Exceptions/`.

## Class: EnvironmentException
**File**: app/Helpers/Exceptions/EnvironmentException.php
**Namespace**: Catalyst\Helpers\Exceptions
**Type**: Class
**Extends**: RuntimeException
**Purpose**: Thrown during bootstrap when the .env file cannot be loaded. Used by `readEnvironmentVariable()` in env-constant.php.

### Static Factory Methods
- `fileMissing(string $path): self` - **public static** - .env and .env.example both absent
- `copyFailed(string $source, string $dest): self` - **public static** - Failed to copy .env.example to .env
- `unreadable(string $path): self` - **public static** - file() returned false
- `empty(string $path): self` - **public static** - .env exists but contains no parseable lines

### Usage Notes
- Must be resolvable during bootstrap BEFORE Composer is loaded
- SPL autoloader (`spl-autoload.php`) maps `Catalyst\Helpers\Exceptions\` → `app/Helpers/Exceptions`
- `readEnvironmentVariable()` throws; single top-level catch in env-constant.php bootstrap scope

---

## Class: ViewException
**File**: app/Helpers/Exceptions/ViewException.php
**Namespace**: Catalyst\Helpers\Exceptions
**Type**: Class
**Extends**: RuntimeException
**Purpose**: Thrown by `View::render()` when a template or layout file cannot be found.

### Static Factory Methods
- `templateNotFound(string $template): self` - Template name (dot notation) not resolved in any registered path
- `layoutNotFound(string $layout): self` - Layout name not found in `boot-core/template/layouts/`

---

## Class: ConnectionException
**File**: app/Helpers/Exceptions/ConnectionException.php
**Purpose**: Thrown for database or network connection failures

---

## Class: FileSystemException
**File**: app/Helpers/Exceptions/FileSystemException.php
**Purpose**: Thrown for file system operation failures

---

## Class: MailException
**File**: app/Helpers/Exceptions/MailException.php
**Namespace**: Catalyst\Helpers\Exceptions
**Extends**: Exception
**Purpose**: Thrown for all email-related failures. Used by MailManager, MailMessage, and MailTemplate.

### Error Codes
- `ERROR_CONFIGURATION = 100` — Missing or invalid SMTP configuration
- `ERROR_INVALID_ADDRESS = 101` — Invalid email address format
- `ERROR_SENDING = 102` — SMTP send failure
- `ERROR_ATTACHMENT = 103` — File attachment failure
- `ERROR_TEMPLATE = 104` — Template load/render failure
- `ERROR_DKIM = 105` — DKIM signing failure

### Static Factory Methods
- `configurationError(string $message): self`
- `invalidAddress(string $address): self`
- `sendingError(string $message): self`
- `attachmentError(string $filePath, string $message): self`
- `templateError(string $template, string $message): self`
- `dkimError(string $message): self`

---

## Class: MethodNotAllowedException
**File**: app/Helpers/Exceptions/MethodNotAllowedException.php
**Purpose**: Thrown for HTTP method not allowed (405)

---

## Class: QueryException
**File**: app/Helpers/Exceptions/QueryException.php
**Purpose**: Thrown for database query failures

---

## Class: OptimisticLockException
**File**: app/Helpers/Exceptions/OptimisticLockException.php
**Purpose**: Thrown when a model using `HasOptimisticLockingTrait` attempts to save with a stale `lock_version`

### Methods
- `forModel(string $modelClass, int|string|null $recordId, string $column, int $expectedVersion, ?int $currentVersion = null): self`
- `modelClass(): string`
- `recordId(): int|string|null`
- `column(): string`
- `expectedVersion(): int`
- `currentVersion(): ?int`

---

## Class: RouteNotFoundException
**File**: app/Helpers/Exceptions/RouteNotFoundException.php
**Purpose**: Thrown when route is not found (404)

---

## Class: ValidationException
**File**: app/Helpers/Exceptions/ValidationException.php
**Namespace**: Catalyst\Helpers\Exceptions
**Type**: Class
**Extends**: RuntimeException
**Purpose**: Thrown by `Controller::validateOrFail()` when validation fails. `ExceptionHandler` converts it to a 422 JSON response.

### Static Factory Methods
- `withErrors(array $errors, string $message = 'Validation failed'): self` - Create from field-level errors array

### Methods
- `getErrors(): array<string, string[]>` - Returns field-level errors `['field' => ['msg1', ...]]`
- `getStatusCode(): int` - Returns 422
