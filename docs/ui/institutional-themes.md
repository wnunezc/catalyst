# Themes and neutral branding

## Alcance

Catalyst usa los temas Inspinia y cuatro temas institucionales dentro del mismo
documento, shell y runtime. Un tema solo modifica variables y presentación; no
selecciona perfiles, layouts, shells o gobernadores alternativos.

El branding documental y los logos se gobiernan aparte desde
`/configuration/platform-appearance` en el tab de Neutral Branding Runtime.

## Modelo actual

La apariencia se divide en dos capas:

1. **UI skin**: controla colores principales, botones, estados activos,
   topbar/sidenav y contraste de la interfaz.
2. **Neutral branding**: controla nombre visible, logos, favicon y watermark PDF.

Esto evita que una marca documental reescriba el layout o que una combinación de
colores del customizer deje textos invisibles.

## Temas disponibles

Los siete temas originales de Inspinia preservados son:

- `default`
- `minimal`
- `modern`
- `material`
- `pixel`
- `luxe`
- `flat`

Los cuatro temas institucionales preservados son:

- `red-cross`
- `civil-protection`
- `firefighters`
- `grempa`

Los once temas están declarados en el runtime de apariencia y apoyados por:

- `public/assets/css/catalyst/response-skins.css`
- `public/assets/css/catalyst/inspinia-runtime-compat.css`
- `public/assets/js/catalyst/appearance-bootstrap.js`
- `public/assets/js/catalyst/shell/theme-customizer.js`

## Presets institucionales cerrados

Estos skins son presets cerrados. No se deben mezclar libremente con cualquier
modo claro/oscuro, topbar o sidenav porque eso puede romper contraste.

| Skin | Theme | Topbar | Sidenav |
|---|---|---|---|
| `red-cross` | `light` | `light` | `light` |
| `civil-protection` | `light` | `dark` | `light` |
| `firefighters` | `light` | `dark` | `dark` |
| `grempa` | `dark` | `dark` | `dark` |

`PlatformAppearanceManager::sanitizeThemeConfig()` es la fuente de verdad para
forzar estas combinaciones. La configuración usa `customizer_enabled`; no
existe un customizer exclusivo de Privileged.

## Reglas de implementación

- No usar `data-bs-theme="red-cross"` ni otros valores personalizados para
  Bootstrap si no se define un modo completo de Bootstrap.
- Mantener `data-bs-theme` como `light`, `dark` o `system` según corresponda.
- Aplicar el preset visual mediante el skin configurado por Catalyst/Inspinia.
- Mantener `response-skins.css` como fuente activa de identidad/color de los
  cuatro presets institucionales; no usarlo para geometria o layouts.
- No usar logos o emblemas como parte del skin visual.
- Mantener los botones de acción como botones reales; evitar `btn-link` para
  acciones privilegiadas.
- Preservar selección y persistencia mediante el runtime central y
  `shell/theme-customizer.js`.
- No crear CSS, shell o JavaScript gobernador por tema.

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

Los uploads PNG/WebP se normalizan para reducir transparencia sobrante y se
almacenan bajo `public/uploads/branding`. El logo primario/alternativo se
selecciona por contraste del fondo, el favicon actua como marca compacta y la
misma identidad configurada se consume en documentos PDF.
