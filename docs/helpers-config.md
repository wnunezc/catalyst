# Catalyst\Helpers\Config

## Purpose

Document JSON-backed configuration and managed secret helpers.

## Runtime Owners

| Concern | Owner |
|---|---|
| Supplies selectable entry labels, keys and route paths for configured surfaces. | `Catalyst\Helpers\Config\AppEntryCatalog` |
| Loads, exposes and persists environment configuration while isolating secret values. | `Catalyst\Helpers\Config\ConfigManager` |
| Splits, merges and audits secret values for managed configuration sections. | `Catalyst\Helpers\Config\ConfigSecretCatalog` |
| Loads, writes, merges and audits secrets for one runtime environment. | `Catalyst\Helpers\Config\ConfigSecretStore` |

## Current Behavior

This file is regenerated from current PHP docblocks and the runtime inventory scope for `Catalyst\Helpers\Config`. It intentionally replaces stale historical API notes with the classes and methods that exist in code now.

## API From Docblocks

### `Catalyst\Helpers\Config\AppEntryCatalog`

- File: `app/Helpers/Config/AppEntryCatalog.php`
- Kind: `class`
- Summary: Catalogs the application entry points exposed by setup configuration.
- Responsibility: Supplies selectable entry labels, keys and route paths for configured surfaces.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `primaryLabels()` | `public` | Returns labels accepted as primary application entries. | n/a |
| `secondaryLabels()` | `public` | Returns labels accepted as secondary application entries. | n/a |
| `primaryKeys()` | `public` | Returns keys accepted as primary application entries. | n/a |
| `secondaryKeys()` | `public` | Returns keys accepted as secondary application entries. | n/a |
| `requiresSecondary()` | `public` | Determines whether the primary entry requires a secondary selection. | n/a |
| `resolvePath()` | `public` | Resolves the route path assigned to an entry key. | n/a |
| `labels()` | `private` | Filters catalog labels by visibility and secondary-entry rules. | n/a |

### `Catalyst\Helpers\Config\ConfigManager`

- File: `app/Helpers/Config/ConfigManager.php`
- Kind: `class`
- Summary: Framework configuration manager
- Responsibility: Loads, exposes and persists environment configuration while isolating secret values.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `__construct()` | `protected` | Initializes the object with the collaborators or state required for its responsibility. | Initializes the object with the collaborators or state required for its responsibility. |
| `get()` | `public` | Retrieve a config value by dot-notation key. Examples: get('db.db1.db_host') → 'localhost' get('mail.mail1.mail_port') → 587 get('app.project.project_name', 'Catalyst') → value or default. | Retrieve a config value by dot-notation key. Examples: get('db.db1.db_host') → 'localhost' get('mail.mail1.mail_port') → 587 get('app.project.project_name', 'Catalyst') → value or default. |
| `has()` | `public` | Check whether a dot-notation key exists and is non-null. | Check whether a dot-notation key exists and is non-null. |
| `section()` | `public` | Return all instances within a section (e.g. 'db' → ['db1'=>[...], 'db2'=>[...]]). Returns null when the section does not exist. | Return all instances within a section (e.g. 'db' → ['db1'=>[...], 'db2'=>[...]]). Returns null when the section does not exist. |
| `all()` | `public` | Return the full config array (all sections). | Return the full config array (all sections). |
| `defaults()` | `public` | Return .env-derived defaults for a single section. | Return .env-derived defaults for a single section. |
| `entry()` | `public` | Return one named entry inside a section, merged over .env-derived defaults. Typical mappings: app → project session → session cache → cache logging → logging security → security websocket → websocket cors → cors db → db1 mail → mail1. | Return one named entry inside a section, merged over .env-derived defaults. Typical mappings: app → project session → session cache → cache logging → logging security → security websocket → websocket cors → cors db → db1 mail → mail1. |
| `isConfigured()` | `public` | True when the Setup Wizard has run and app.json is present with project_config = true. False on first boot. | True when the Setup Wizard has run and app.json is present with project_config = true. False on first boot. |
| `getEnvironment()` | `public` | Return the resolved environment name (development \| staging \| testing \| production). | Return the resolved environment name (development \| staging \| testing \| production). |
| `writeSection()` | `public` | Persist a config section to disk and update the in-memory cache. Writes (or overwrites) boot-core/config/{environment}/{section}.json. Creates the config directory if it does not exist. Re-evaluates isConfigured() after the write. | Persist a config section to disk and update the in-memory cache. Writes (or overwrites) boot-core/config/{environment}/{section}.json. Creates the config directory if it does not exist. Re-evaluates isConfigured() after the write. |

### `Catalyst\Helpers\Config\ConfigSecretCatalog`

- File: `app/Helpers/Config/ConfigSecretCatalog.php`
- Kind: `class`
- Summary: Catalogs configuration fields that must be stored outside public JSON files.
- Responsibility: Splits, merges and audits secret values for managed configuration sections.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `managedSections()` | `public` | Returns configuration sections with managed secrets. | n/a |
| `managesSection()` | `public` | Determines whether a configuration section contains managed secrets. | n/a |
| `sensitiveKeys()` | `public` | Returns sensitive keys managed for a configuration section. | n/a |
| `splitSection()` | `public` | Separates public values from secrets before persisting a section. | n/a |
| `mergeSection()` | `public` | Merges persisted secrets into an in-memory public section. | n/a |
| `containsPublicSecrets()` | `public` | Determines whether a public section still contains secret values. | n/a |

### `Catalyst\Helpers\Config\ConfigSecretStore`

- File: `app/Helpers/Config/ConfigSecretStore.php`
- Kind: `class`
- Summary: Persists environment-specific configuration secrets separately from public JSON.
- Responsibility: Loads, writes, merges and audits secrets for one runtime environment.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `__construct()` | `public` | Initializes the Config Secret Store instance. | Initializes the Config Secret Store instance. |
| `path()` | `public` | Returns the secrets file path. | Returns the secrets file path. |
| `exists()` | `public` | Determines whether the secrets file exists. | Determines whether the secrets file exists. |
| `load()` | `public` | Loads the persisted secrets payload. | Loads the persisted secrets payload. |
| `persist()` | `public` | Persists or removes the environment secrets payload. | Persists or removes the environment secrets payload. |
| `mergeIntoConfig()` | `public` | Merges persisted secrets into an in-memory configuration array. | Merges persisted secrets into an in-memory configuration array. |
| `persistSection()` | `public` | Replaces the persisted secrets for one configuration section. | Replaces the persisted secrets for one configuration section. |
| `publicSecretLeaks()` | `public` | Returns public configuration sections that still expose secret values. | Returns public configuration sections that still expose secret values. |

## Operational Notes

When PHP symbols or method contracts in this namespace change, refresh this document from docblocks and run `php public/cli.php docs:inventory --json`.

## Related Documentation

- `docs/runtime-inventory.md`
- `docs/runtime-module-catalog.md`
- `docs/harness-context-map.md`
