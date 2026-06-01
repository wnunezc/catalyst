# Catalyst Framework Appearance

## Scope

This document describes the current appearance runtime used by Catalyst after the
Inspinia shell cutover and the `/configuration/platform-appearance` governance
refactor.

Canonical runtime pieces:

- `app/Framework/Appearance/PlatformAppearanceManager.php`
- `Repository/Framework/Operations/Controllers/AppearanceController.php`
- `Repository/Framework/Operations/Views/pages/appearance.phtml`
- `public/assets/js/catalyst/modules/theme-toggle.js`
- `public/assets/js/catalyst/modules/shell-theme-customizer.js`
- `public/assets/css/catalyst/response-skins.css`
- `public/assets/css/catalyst/inspinia-runtime-compat.css`

## Governance Surface

Canonical UI:

- `/configuration/platform-appearance`

Canonical persistence:

- `boot-core/config/development/appearance.json`
- section `appearance`
- entry `platform`

The surface is intentionally split into two responsibilities:

1. **Admin Customizer policy and global UI preset** — controls whether users may
   customize the shell or whether the platform applies a locked global theme.
2. **Neutral branding runtime** — controls brand text, shell/document logos,
   favicon and PDF watermark settings.

The old institutional-theme runtime is not the canonical UI system anymore.
The project now keeps the interface skinning layer aligned with Inspinia and
stores document/branding behavior separately.

## Appearance Configuration Shape

The live schema is nested under `platform`:

```json
{
  "platform": {
    "ui": {
      "admin_customizer_enabled": true,
      "mode": "user",
      "locked_theme": {
        "skin": "default",
        "theme": "light",
        "topbar-color": "gray",
        "sidenav-color": "dark",
        "sidenav-size": "default",
        "position": "fixed",
        "width": "fluid",
        "dir": "ltr"
      }
    },
    "branding": {
      "theme_family": "inspinia",
      "default_variant": "light",
      "allow_user_variant_override": false,
      "brand_name": "Catalyst Framework",
      "brand_short_name": "Catalyst",
      "brand_tagline": "",
      "logo_primary_path": "",
      "logo_dark_path": "",
      "favicon_path": "",
      "pdf_watermark_enabled": false,
      "pdf_watermark_text": "INTERNAL USE",
      "pdf_watermark_font_size": 46,
      "pdf_watermark_color": "#CBD5E1"
    }
  }
}
```

`PlatformAppearanceManager::normalizeSettings()` still accepts legacy flat keys
so older local configuration files can be read and rewritten into the new shape.

## Admin Customizer Policy

When `admin_customizer_enabled = true`:

- the status-bar customizer controls are visible;
- the current user may use the Inspinia customizer;
- the frontend may use `localStorage.__THEME_CONFIG__` as the user preference.

When `admin_customizer_enabled = false`:

- the status-bar theme toggle is hidden;
- the Admin Customizer launcher is hidden;
- the frontend ignores `localStorage.__THEME_CONFIG__`;
- the shell uses `platform.ui.locked_theme` for every user.

The locked theme is sanitized before it reaches the layout. Unsupported values
fall back to safe defaults.

## Select Theme / Skin Model

Catalyst keeps Bootstrap/Inspinia color mode and Catalyst skins separate.
`data-bs-theme` remains a mode value such as `light` or `dark`. Custom response
skins are applied through the skin preset instead of replacing Bootstrap's color
mode.

Allowed skins currently include:

- `default`
- `minimal`
- `modern`
- `material`
- `pixel`
- `luxe`
- `flat`
- `red-cross`
- `civil-protection`
- `firefighters`
- `grempa`

The following skins are closed presets. Their theme/topbar/sidenav colors are
forced by `PlatformAppearanceManager::closedSkinPresets()` to avoid invalid
contrast combinations:

| Skin | Theme | Topbar | Sidenav |
|---|---|---|---|
| `red-cross` | `light` | `light` | `light` |
| `civil-protection` | `light` | `dark` | `light` |
| `firefighters` | `light` | `dark` | `dark` |
| `grempa` | `dark` | `dark` | `dark` |

## Neutral Branding Runtime

Branding is intentionally separate from UI skinning. It resolves:

- `brand_name`
- `brand_short_name`
- `brand_tagline`
- `logo_light_url`
- `logo_dark_url`
- `favicon_url`
- PDF watermark text/color/size/logo behavior

The canonical catalog currently has the neutral `inspinia` family as the base.
The response skins are UI presets, not branding families with logo ownership.

## Head Bootstrap Payload

`PlatformAppearanceManager::headBootstrapPayload()` exposes the minimal payload
required by the shell bootstrap:

- `themeFamily`
- `defaultVariant`
- `allowUserVariantOverride`
- `brandName`
- `brandShortName`
- `brandTagline`
- `adminCustomizerEnabled`
- `mode`
- `lockedConfig`

Layouts and shared shell components should consume this payload instead of
inventing parallel theme state.

## PDF Watermark

Document exports consume PDF watermark settings through:

- `PlatformAppearanceManager::pdfWatermarkSettings()`
- `DocumentTemplateManager`

Runtime behavior:

- `pdf_watermark_enabled` controls whether watermarking is active;
- `pdf_watermark_text` controls the rendered text;
- `pdf_watermark_font_size` is clamped between 24 and 96;
- `pdf_watermark_color` is normalized as a six-digit hex color;
- logo watermarking only uses local JPEG assets that are safe for the current
  simple PDF writer.

## Verification Baseline

CLI:

- `php public/cli.php help`
- `php public/cli.php status`
- `php public/cli.php route:lint`

Runtime:

- `/configuration/platform-appearance`
- `/demo-ui`
- `/users/enroll`
- `/configuration/environment-setup`
