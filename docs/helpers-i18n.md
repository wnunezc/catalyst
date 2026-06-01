# Catalyst i18n Runtime

## Scope

This document describes the live internationalization contract used by Catalyst
after the 2026-05 token-first and Administration i18n closure.

Canonical runtime pieces:

- `app/Helpers/I18n/Translator.php`
- `app/Helpers/I18n/TranslationLoader.php`
- `app/Framework/Localization/LocalizationManager.php`
- `Repository/Framework/Operations/Controllers/LocalizationController.php`
- CLI:
  - `i18n:status`
  - `i18n:init-locale`
  - `i18n:sync`

## Runtime Layers

There are two complementary layers:

1. `Translator`
   - resolves runtime strings during web or CLI execution
   - powers `__()`, `t()` and token expressions `{{ t:... }}`
   - loads language roots registered by modules

2. `LocalizationManager`
   - governs locale inventory, coverage reporting and initialization/sync from base locale
   - persists runtime locale settings in JSON config
   - is the canonical management surface behind `/operations/localization` and the `i18n:*` CLI commands

## Base Locale Rule

- Base locale is fixed to `en`
- Coverage and synchronization are always computed against English catalogs
- `es` is currently closed at `100%` coverage in the framework-owned surface set

## Global Helpers

Defined through the normal framework bootstrap:

| Function | Signature | Purpose |
|---|---|---|
| `__()` | `__(string $key, array $replacements = [], ?string $locale = null): string` | Canonical translation helper |
| `t()` | `t(string $key, array $replacements = [], ?string $locale = null): string` | Alias of `__()` |
| `format_date()` | `format_date(DateTimeInterface\|string\|int $date, string $format = 'default', ?string $locale = null): string` | Locale-aware date formatting using `dates.json` |

## Translator Contract

`Translator` is initialized by framework bootstrap and consumes:

- global language root:
  - `boot-core/lang`
- module language roots registered from runtime:
  - `Repository/Framework/*/lang`
  - `Repository/App/*/lang`
  - `Repository/App/Surface/*/lang`

Lookup key format:

- `{catalog}.{key}`
- examples:
  - `auth.login.submit`
  - `operations.appearance.hero_eyebrow`
  - `ui.shell.administration_tagline`

Placeholder format:

- `:name`
- example:
  - `__('messages.welcome', ['name' => 'Walter'])`

Fallback chain:

1. requested locale
2. default locale from runtime settings
3. raw key string

## LocalizationManager Contract

`LocalizationManager` is the canonical governance API for locale coverage and runtime settings.

Primary responsibilities:

- discover language roots
- report coverage vs `en`
- initialize a new locale by cloning English catalogs
- synchronize missing keys without overwriting existing translations
- persist runtime locale metadata
- sync `app.project.project_lang` with configured default locale

Runtime config:

- `boot-core/config/development/localization.json`
- entry:
  - section `localization`
  - key `runtime`

Important defaults:

- `base_locale = en`
- `fallback_locale = en`
- `default_locale = app.project.project_lang`
- supported locales always include `en`

## CLI Governance

Canonical commands:

- `php public/cli.php i18n:status --locale=es`
  - reports catalog coverage and missing/extra keys
- `php public/cli.php i18n:init-locale --locale=fr --label=Francais`
  - clones base `en` catalog structure into the new locale
- `php public/cli.php i18n:sync --locale=es`
  - backfills missing keys from `en` without overwriting translated values

## Administration Surface

Canonical UI:

- `/operations/localization`

This surface is framework-owned and uses `LocalizationManager` directly for:

- locale inventory
- coverage report
- dry-run initialization
- dry-run synchronization
- runtime default locale management

## Token Templates and i18n

Tokenized `.phtml` templates should prefer renderer-managed translation:

- `{{ t:auth.login.submit }}`
- `{{ t:messages.welcome name=user_name }}`

This keeps translation and escaping inside the view pipeline instead of inline PHP.

## Language Root Model

Current discovery roots:

- `boot-core/lang`
- `Repository/Framework/*/lang`
- `Repository/App/*/lang`
- `Repository/App/Surface/*/lang`

Each locale directory contains JSON catalogs named after the first segment of the key:

- `boot-core/lang/en/ui.json`
- `Repository/Framework/Operations/lang/es/operations.json`

## Locale Persistence

User locale runtime preference still lives in session through `Translator`.

Framework-level locale governance lives in config through `LocalizationManager`:

- supported locales
- labels
- default locale

This split is intentional:

- session = current actor preference
- runtime config = platform-supported locale baseline

## Verification Baseline

Useful verification commands:

- `php public/cli.php help`
- `php public/cli.php status`
- `php public/cli.php i18n:status --locale=es`
- `php public/cli.php route:lint`

Runtime verification patterns:

- switch locale through `/test-features/i18n/set-locale`
- inspect framework-owned copies under `/operations/*`, `/health`, `/uml`, `/test-features`

## Summary

- `Translator` resolves strings at runtime
- `LocalizationManager` governs locale inventory and coverage
- English is the required base locale
- `Administration` owns runtime i18n governance
- token templates should use `{{ t:... }}` instead of inline PHP translation calls
