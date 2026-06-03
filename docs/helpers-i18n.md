# Catalyst\Helpers\I18n

## Purpose

Document translation loading and translation helper behavior.

## Runtime Owners

| Concern | Owner |
|---|---|
| Loads, flattens, merges and caches locale translation groups. | `Catalyst\Helpers\I18n\TranslationLoader` |
| Resolves localized strings, lists and dates across global and module paths. | `Catalyst\Helpers\I18n\Translator` |

## Current Behavior

This file is regenerated from current PHP docblocks and the runtime inventory scope for `Catalyst\Helpers\I18n`. It intentionally replaces stale historical API notes with the classes and methods that exist in code now.

## API From Docblocks

### `Catalyst\Helpers\I18n\TranslationLoader`

- File: `app/Helpers/I18n/TranslationLoader.php`
- Kind: `class`
- Summary: TranslationLoader — file loading layer for the i18n system.
- Responsibility: Loads, flattens, merges and caches locale translation groups.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `load()` | `public` | Load and merge translations for a group across all registered paths. Files are merged in order: later paths override earlier ones. This allows module lang files to override global ones when needed. | Load and merge translations for a group across all registered paths. Files are merged in order: later paths override earlier ones. This allows module lang files to override global ones when needed. |
| `flatten()` | `public` | Flatten a nested array to dot-notation keys. For each node that is an array, BOTH the array value (at the parent key) AND all its children (recursively) are stored. This allows: - get('form.gender_options.male') → string - getList('form.gender_options') → array. | Flatten a nested array to dot-notation keys. For each node that is an array, BOTH the array value (at the parent key) AND all its children (recursively) are stored. This allows: - get('form.gender_options.male') → string - getList('form.gender_options') → array. |
| `resolve()` | `public` | Resolve a subkey within a flat translations array. | Resolve a subkey within a flat translations array. |
| `clearCache()` | `public` | Invalidate cache entries. | Invalidate cache entries. |

### `Catalyst\Helpers\I18n\Translator`

- File: `app/Helpers/I18n/Translator.php`
- Kind: `class`
- Summary: Translator — i18n / Translation system for Catalyst Framework.
- Responsibility: Resolves localized strings, lists and dates across global and module paths.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `__construct()` | `protected` | Initializes the Translator instance. | Initializes the Translator instance. |
| `init()` | `public` | Initialize the translation system. Must be called once in Kernel::bootstrap(), after SessionManager::init() so that getLocale() can read the session. | Initialize the translation system. Must be called once in Kernel::bootstrap(), after SessionManager::init() so that getLocale() can read the session. |
| `addPath()` | `public` | Register an additional lang/ directory (called from module routes.php). Paths registered later override earlier ones on key collision, so modules can override global translations for their own group files. | Register an additional lang/ directory (called from module routes.php). Paths registered later override earlier ones on key collision, so modules can override global translations for their own group files. |
| `setLocale()` | `public` | Set the user's locale preference (stored in session). Does not require the user to be logged in. The locale persists for the session duration regardless of authentication state. | Stores the session locale preference used to resolve translations for the active visitor. |
| `getLocale()` | `public` | Get the current effective locale. Resolution order: session → default locale from .env. | Resolves the effective locale from the session preference and configured application default. |
| `getDefaultLocale()` | `public` | Get the default locale (from .env APP_LANG). | Exposes the configured fallback locale used when no session preference exists. |
| `get()` | `public` | Get a translated string by dot-notation key. Key format: '{group}.{subkey}' where group = JSON file name. 'validation.required' → loads validation.json → key 'required' 'messages.save_success' → loads messages.json → key 'save_success' 'dates.formats.default' → loads dates.json → nested 'formats.default' Fallback chain: 1. Requested locale 2. Default locale (if different from requested) 3. The key itself (no exception thrown). | Get a translated string by dot-notation key. Key format: '{group}.{subkey}' where group = JSON file name. 'validation.required' → loads validation.json → key 'required' 'messages.save_success' → loads messages.json → key 'save_success' 'dates.formats.default' → loads dates.json → nested 'formats.default' Fallback chain: 1. Requested locale 2. Default locale (if different from requested) 3. The key itself (no exception thrown). |
| `has()` | `public` | Check if a translation key exists for the given (or current) locale. | Check if a translation key exists for the given (or current) locale. |
| `getList()` | `public` | Get an array value for use as select options or enumeration lists. Only returns entries where the value is a string (skips nested arrays), making it safe to pass directly to a <select> builder. JSON example (form.json): "gender_options": { "male": "Male", "female": "Female", "other": "Other" } "gender_default": "male" Usage: $options = Translator::getInstance()->getList('form.gender_options'); // → ['male' => 'Male', 'female' => 'Female', 'other' => 'Other'] $default = __('form.gender_default'); // → 'male'. | Get an array value for use as select options or enumeration lists. Only returns entries where the value is a string (skips nested arrays), making it safe to pass directly to a <select> builder. JSON example (form.json): "gender_options": { "male": "Male", "female": "Female", "other": "Other" } "gender_default": "male" Usage: $options = Translator::getInstance()->getList('form.gender_options'); // → ['male' => 'Male', 'female' => 'Female', 'other' => 'Other'] $default = __('form.gender_default'); // → 'male'. |
| `formatDate()` | `public` | Format a date using a locale-aware format pattern. Format patterns are defined in dates.json under 'formats.*'. Month and day names are translated using the dates.json name tables. Built-in format keys (defined in boot-core/lang/{locale}/dates.json): 'default' → locale default (e.g. 'm/d/Y' for en, 'd/m/Y' for es) 'long' → full month name (e.g. 'January 15, 2026') 'short' → abbreviated (e.g. '01/15/26') 'full' → day name + full date 'time' → time only 'datetime' → date + time 'iso' → Y-m-d (locale-independent) You can also pass a literal PHP date format string as $format (e.g. 'Y-m-d', 'd/m/Y H:i') when no translation key is needed. | Format a date using a locale-aware format pattern. Format patterns are defined in dates.json under 'formats.*'. Month and day names are translated using the dates.json name tables. Built-in format keys (defined in boot-core/lang/{locale}/dates.json): 'default' → locale default (e.g. 'm/d/Y' for en, 'd/m/Y' for es) 'long' → full month name (e.g. 'January 15, 2026') 'short' → abbreviated (e.g. '01/15/26') 'full' → day name + full date 'time' → time only 'datetime' → date + time 'iso' → Y-m-d (locale-independent) You can also pass a literal PHP date format string as $format (e.g. 'Y-m-d', 'd/m/Y H:i') when no translation key is needed. |
| `clearCache()` | `public` | Invalidate loaded translation cache. | Invalidate loaded translation cache. |
| `parseKey()` | `private` | Parse a dot-notation key into [group, subkey]. 'validation.required' → ['validation', 'required'] 'dates.formats.default' → ['dates', 'formats.default'] 'standalone' → ['standalone', '']. | Parse a dot-notation key into [group, subkey]. 'validation.required' → ['validation', 'required'] 'dates.formats.default' → ['dates', 'formats.default'] 'standalone' → ['standalone', '']. |
| `resolveValue()` | `private` | Load the group for a locale and resolve the subkey. | Load the group for a locale and resolve the subkey. |
| `applyReplacements()` | `private` | Replace :placeholder tokens in a translated string. | Replace :placeholder tokens in a translated string. |
| `toDateTime()` | `private` | Normalize input to a DateTimeInterface. | Normalize input to a DateTimeInterface. |

## Operational Notes

When PHP symbols or method contracts in this namespace change, refresh this document from docblocks and run `php public/cli.php docs:inventory --json`.

## Related Documentation

- `docs/runtime-inventory.md`
- `docs/runtime-module-catalog.md`
- `docs/harness-context-map.md`
