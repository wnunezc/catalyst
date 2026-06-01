# Catalyst\Framework\Session

## Overview

La capa de sesion del framework separa tres responsabilidades:

- `SessionManager` administra el almacenamiento PHP session.
- `FlashBag` mantiene la estructura interna de flashes regulares/persistentes, historial y dismiss.
- `FlashMessage` expone la API publica orientada a controladores y templates.
- `ToastQueue` maneja toasts efimeros para el siguiente page load.

## Class: SessionManager
**File**: `app/Framework/Session/SessionManager.php`  
**Namespace**: `Catalyst\Framework\Session`  
**Type**: Singleton

### Runtime Config Source
La configuracion efectiva de sesion se resuelve asi:
1. `/setup` JSON via `ConfigManager` (`session.json`, entry `session`)
2. `.env` defaults

### Effective Keys
- `session_driver`
- `session_connection`
- `session_table`
- `session_name`
- `session_lifetime`
- `session_activity_timeout`
- `session_use_activity_timeout`
- `session_secure`
- `session_http_only`
- `session_same_site`
- `session_domain`

### Notes
- `SessionManager` usa la referencia temprana `$GLOBALS['APP_CONFIGURATION']` cuando ya existe.
- `/setup/session` ya no guarda configuracion fantasma: esas claves son consumidas por el runtime real.
- El runtime soporta `session_driver=file|database`; en `database` registra `DatabaseSessionHandler` y autoprovisiona la tabla configurada.
- El manager ahora tambien transporta estado de validacion HTML entre redirect y render:
  - `flashOldInput(array $input)`
  - `consumeOldInput()`
  - `flashValidationErrors(array $errors, string $bag = 'default')`
  - `consumeValidationErrors()`
  - `clearFormState()`
- Las vistas leen ese bridge via helpers globales:
  - `old('field', $default)`
  - `validation_errors()`
  - `validation_error('field')`

## Class: DatabaseSessionHandler
**File**: `app/Framework/Session/DatabaseSessionHandler.php`
**Namespace**: `Catalyst\Framework\Session`
**Type**: Class

### Purpose
Persistencia de sesiones en base de datos usando una conexion nombrada del framework.

### Runtime Behavior
- Se registra desde `SessionManager` cuando `session_driver=database`.
- Resuelve PDO via `DatabaseManager::connection($session_connection)`.
- Crea automaticamente la tabla configurada (`session_table`) si no existe.
- Guarda `payload`, `last_activity`, `ip_address` y `user_agent`.
- `gc()` limpia filas expiradas usando `session.gc_maxlifetime`.

---

## Class: FlashBag
**File**: `app/Framework/Session/FlashBag.php`  
**Namespace**: `Catalyst\Framework\Session`  
**Type**: Class  
**Purpose**: Bolsa interna de flashes. Encapsula session keys, validacion de estructura, TTL del historial y dismiss de mensajes persistentes.

### Session Keys
- `FLASH_KEY` → `_flash_messages`
- `PERSISTENT_KEY` → `_flash_persistent`
- `HISTORY_KEY` → `_flash_history`
- `DISMISSED_KEY` → `_flash_dismissed`

### Public Methods
- `add(string $type, string $message, ?string $customId = null): void`
- `addPersistent(string $type, string $message, ?string $customId = null): void`
- `dismiss(string $id): void`
- `all(): array`
- `allPersistent(): array`
- `get(string $type): array`
- `has(?string $type = null): bool`
- `hasPersistent(?string $type = null): bool`
- `clear(): void`
- `clearPersistent(): void`
- `clearHistory(): void`
- `clearDismissed(): void`
- `reset(): void`
- `peek(): array`
- `count(): int`

### Notes
- IDs unicos via `xxh3`.
- Historial maximo: `100`.
- TTL de historial: `3600` segundos.
- Los mensajes persistentes se descartan desde frontend via `POST /flash/dismiss`.

---

## Class: FlashMessage
**File**: `app/Framework/Session/FlashMessage.php`  
**Namespace**: `Catalyst\Framework\Session`  
**Type**: Class  
**Purpose**: Fachada publica del sistema de flashes. Delega almacenamiento y bookkeeping a `FlashBag`.

### Traits Used
- `Catalyst\Framework\Traits\SingletonTrait`

### Public Methods
- `add(string $type, string $message, ?string $customId = null): self`
- `addPersistent(string $type, string $message, ?string $customId = null): self`
- `dismiss(string $id): self`
- `success(string $message, ?string $id = null): self`
- `successPersistent(string $message, ?string $id = null): self`
- `error(string $message, ?string $id = null): self`
- `errorPersistent(string $message, ?string $id = null): self`
- `warning(string $message, ?string $id = null): self`
- `warningPersistent(string $message, ?string $id = null): self`
- `info(string $message, ?string $id = null): self`
- `infoPersistent(string $message, ?string $id = null): self`
- `all(): array`
- `allPersistent(): array`
- `get(string $type): array`
- `has(?string $type = null): bool`
- `hasPersistent(?string $type = null): bool`
- `clear(): self`
- `clearPersistent(): self`
- `clearHistory(): self`
- `clearDismissed(): self`
- `reset(): self`
- `peek(): array`
- `count(): int`

### Usage
```php
$flash = FlashMessage::getInstance();

$flash->success('Record saved.');
$flash->warningPersistent('Please review your setup.', 'setup_warning');

$regular = $flash->all();
$persistent = $flash->allPersistent();

$flash->dismiss('setup_warning');
```

---

## Class: ToastQueue
**File**: `app/Framework/Session/ToastQueue.php`  
**Namespace**: `Catalyst\Framework\Session`  
**Type**: Class  
**Purpose**: Cola de toasts efimeros consumidos por el siguiente render.

### Public Methods
- `push(string $type, string $message): self`
- `all(): array`
- `clear(): self`

### Difference vs FlashMessage
- `FlashMessage` sirve banners inline y persistentes, con IDs e historial.
- `ToastQueue` solo bufferiza popups one-shot para el siguiente page load.
