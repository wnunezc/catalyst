# Response skins and neutral branding

## Alcance

Este documento reemplaza la documentación antigua de temas institucionales.
Catalyst ya no usa un runtime visual paralelo basado en
`response-skins.css`. La UI administrativa vive sobre Inspinia y los colores
especiales se aplican como **Select Theme / skin presets**.

El branding documental y los logos se gobiernan aparte desde
`/configuration/platform-appearance` en el tab de Neutral Branding Runtime.

## Modelo actual

La apariencia se divide en dos capas:

1. **UI skin**: controla colores principales, botones, estados activos,
   topbar/sidenav y contraste de la interfaz.
2. **Neutral branding**: controla nombre visible, logos, favicon y watermark PDF.

Esto evita que una marca documental reescriba el layout o que una combinación de
colores del customizer deje textos invisibles.

## Skins disponibles

Los skins especiales actuales son:

- `red-cross`
- `civil-protection`
- `firefighters`
- `grempa`

Cada skin está declarado en el runtime de apariencia y apoyado por:

- `public/assets/css/catalyst/response-skins.css`
- `public/assets/css/catalyst/inspinia-runtime-compat.css`
- `public/assets/js/catalyst/modules/shell-theme-customizer.js`
- `public/assets/js/catalyst/modules/theme-toggle.js`

## Presets cerrados

Estos skins son presets cerrados. No se deben mezclar libremente con cualquier
modo claro/oscuro, topbar o sidenav porque eso puede romper contraste.

| Skin | Theme | Topbar | Sidenav |
|---|---|---|---|
| `red-cross` | `light` | `light` | `light` |
| `civil-protection` | `light` | `dark` | `light` |
| `firefighters` | `light` | `dark` | `dark` |
| `grempa` | `dark` | `dark` | `dark` |

`PlatformAppearanceManager::sanitizeThemeConfig()` es la fuente de verdad para
forzar estas combinaciones.

## Reglas de implementación

- No usar `data-bs-theme="red-cross"` ni otros valores personalizados para
  Bootstrap si no se define un modo completo de Bootstrap.
- Mantener `data-bs-theme` como `light`, `dark` o `system` según corresponda.
- Aplicar el preset visual mediante el skin configurado por Catalyst/Inspinia.
- No reactivar `response-skins.css`.
- No usar logos o emblemas como parte del skin visual.
- Mantener los botones de acción como botones reales; evitar `btn-link` para
  acciones administrativas.

## Neutral branding

El tab secundario de `/configuration/platform-appearance` concentra:

- nombre de marca;
- nombre corto;
- tagline;
- logo claro;
- logo oscuro;
- favicon;
- watermark PDF.

Esta capa puede cambiar documentos y chrome de marca sin crear un sistema de
temas visuales paralelo.
