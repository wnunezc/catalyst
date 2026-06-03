# Catalyst\Framework\Enums

## Purpose

Document framework enum values available to runtime code.

## Runtime Owners

| Concern | Owner |
|---|---|
| Normalizes environment names and exposes environment capability checks. | `Catalyst\Framework\Enums\AppEnvironment` |

## Current Behavior

This file is regenerated from current PHP docblocks and the runtime inventory scope for `Catalyst\Framework\Enums`. It intentionally replaces stale historical API notes with the classes and methods that exist in code now.

## API From Docblocks

### `Catalyst\Framework\Enums\AppEnvironment`

- File: `app/Framework/Enums/AppEnvironment.php`
- Kind: `enum`
- Summary: Defines the valid application environments accepted by Catalyst.
- Responsibility: Normalizes environment names and exposes environment capability checks.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `values()` | `public` | Returns all valid APP_ENV string values. | n/a |
| `isValid()` | `public` | Checks if the given string is a valid environment value. | n/a |
| `current()` | `public` | Returns the current application environment from the IS_* PHP constants. Requires env-constant.php to have been loaded. | n/a |
| `allowsDebug()` | `public` | Whether this environment enables debug output and error display. | Whether this environment enables debug output and error display. |
| `isProductionLike()` | `public` | Whether this environment runs in production-like mode (no debug tools). | Whether this environment runs in production-like mode (no debug tools). |

## Operational Notes

When PHP symbols or method contracts in this namespace change, refresh this document from docblocks and run `php public/cli.php docs:inventory --json`.

## Related Documentation

- `docs/runtime-inventory.md`
- `docs/runtime-module-catalog.md`
- `docs/harness-context-map.md`
