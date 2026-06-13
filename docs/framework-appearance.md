# Catalyst Framework Appearance

## Purpose

Document the current Catalyst appearance runtime for shell theme policy, neutral branding and PDF watermark settings.

## Runtime Owners

| Concern | Owner |
|---|---|
| Appearance normalization and persistence | `Catalyst\Framework\Appearance\PlatformAppearanceManager` |
| Appearance privileged route | `Catalyst\Repository\Configuration\Controllers\AppearanceController` |
| Theme frontend runtime | `public/assets/js/catalyst/modules/theme-toggle.js` |
| Shell customizer runtime | `public/assets/js/catalyst/modules/shell-theme-customizer.js` |
| Response skins CSS | `public/assets/css/catalyst/response-skins.css` |

## Current Behavior

The canonical privileged surface is `/configuration/platform-appearance`. Configuration is stored under the `appearance` section and `platform` entry. The runtime separates privileged customizer policy from neutral branding. Locked themes are sanitized before reaching layouts; unsupported values fall back to safe defaults.

The document loads framework base and generic UI CSS first, module CSS next,
and global theme CSS last. Themes override the shared visual contract and do
not depend on Demo UI scopes or surface-specific wrappers.

## Operational Notes

Appearance belongs to Configuration and is protected by `manage-platform-configuration`. The shared `PlatformAppearanceManager` remains framework infrastructure because the document shell and PDF runtime consume it. When appearance routes, assets or permissions change, refresh `docs/runtime-module-catalog.md` with `php public/cli.php docs:sync-runtime --stdout`.

## Related Documentation

- `docs/helpers-i18n.md`
- `docs/security-conventions.md`
- `docs/runtime-module-catalog.md`
