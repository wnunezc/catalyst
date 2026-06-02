# Catalyst\Helpers\Config

## Class: ConfigManager
**File**: `app/Helpers/Config/ConfigManager.php`  
**Namespace**: `Catalyst\Helpers\Config`  
**Type**: Class  
**Pattern**: Singleton (`SingletonTrait`)

## Purpose
Resolved runtime configuration for JSON-backed framework features.

- `.env` is still the bootstrap source for concerns that must exist before autoload + config classes are available, especially environment detection.
- After `error-catcher.php` finishes bootstrapping, `ConfigManager::getInstance()` is created early and mirrored into `$GLOBALS['APP_CONFIGURATION']`.
- Runtime consumers should prefer `ConfigManager` or `$GLOBALS['APP_CONFIGURATION']` over reading `.env` directly when the section is managed by `/configuration/environment-setup`.

## Runtime Priority
1. JSON file in `boot-core/config/{environment}/*.json`
2. Companion secret store in `boot-core/config/{environment}/secrets.json` for managed secret keys (`project_key`, `db_password`, `mail_password`, `ftp_password`)
3. `.env`-derived defaults compiled by `readDefaults()`
4. Hard-coded fallback inside the consumer when the section is absent

## Template Configs

Safe starter JSON files live in `boot-core/config/templates/*.json`.

- Templates are not read by the runtime.
- Runtime still reads `boot-core/config/{environment}/*.json`.
- `boot-core/config/development/*.json` remains the local WSDD baseline for this repository.
- `boot-core/config/{environment}/secrets.json`, `boot-core/config/env/.env` and DKIM keys remain local-only and must not be committed.
- New developers can copy templates into a concrete environment directory or use the setup wizard to write runtime JSON.

## Public API
- `get(string $key, mixed $default = null): mixed`  
  Dot-notation access, for example `app.project.project_name`.
- `has(string $key): bool`
- `section(string $section): ?array`  
  Returns the raw section, for example `mail` or `session`.
- `all(): array`
- `defaults(string $section): array`  
  Returns the `.env`-derived default payload for one section.
- `entry(string $section, string $entry, ?array $defaults = null): array`  
  Returns one named entry merged over defaults. Typical pairs:
  - `app / project`
  - `db / db1`
  - `mail / mail1`
  - `session / session`
  - `cache / cache`
  - `logging / logging`
  - `security / security`
  - `websocket / websocket`
  - `cors / cors`
- `writeSection(string $section, array $data): void`
- `isConfigured(): bool`
- `getEnvironment(): string`
- `secretStore(): ConfigSecretStore`

## Notes
- `ConfigManager` is authoritative for JSON-backed runtime configuration, not for every bootstrap concern in the whole framework.
- First boot is still valid: if a JSON section does not exist yet, consumers fall back to `.env` defaults through `entry()`/`defaults()`.
- Managed secret keys are stripped from public section files on write and persisted into `secrets.json`; runtime consumers still read them transparently through `ConfigManager`.
- Secret migration / normalization is available through `php public/cli.php config:secrets:sync`.
