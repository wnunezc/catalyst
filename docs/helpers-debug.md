# Catalyst\Helpers\Debug

Inventario honesto del subsistema debug bajo `app/Helpers/Debug/`.

Este archivo no pretende ser una API reference exhaustiva de cada helper interno.
La verdad canónica para firmas finas sigue siendo el código real.

## Superficie principal

### `Dumper`

Archivo:
- `app/Helpers/Debug/Dumper.php`

Rol:
- fachada singleton del sistema de dumps

Métodos públicos reales:
- `dump(array $options): void`
  - espera `['data' => array, 'caller' => ?array, 'config' => array]`
- `configure(array $options): void`
- `getAvailableThemes(): array`
- `setTheme(string $theme): void`
- `getTheme(): string`
- `getThemesNameList(): string`

Notas:
- `dump()` formatea cada entrada con `MainFormatter::formatVar(..., 'Output', IS_REQUEST)`
- el renderer recibe ya strings formateados, no variables crudas

## Contrato de temas

### `ThemeProviderInterface`

Archivo:
- `app/Helpers/Debug/ThemeProviderInterface.php`

Contrato real:
- `public static function getPalette(): array`
- `public static function getPaletteList(): array`
- `public static function getTheme(string $themeName, string $fallback = 'default'): array`
- `public static function themeExists(string $themeName): bool`

No documentarlo como una interfaz de instancia con `getColors()`/`getName()`.

### `ThemeName`

Archivo:
- `app/Helpers/Debug/ThemeName.php`

Rol:
- enum string con los nombres de tema soportados

Métodos públicos reales:
- `getNames(): array`
- `exists(string $name): bool`
- `fromString(string $name): ?self`

## Colaboradores internos relevantes

### `DumperConfig`

Archivo:
- `app/Helpers/Debug/DumperConfig.php`

Rol:
- configuración del sistema (`maxStrLength`, `maxChildren`, `maxDepth`, `showFloatingButton`, `initiallyExpanded`, `colorTheme`)

Métodos útiles:
- `applyOptions(array $options): void`
- getters/setters para los límites y el tema
- `getAvailableThemes(): array`

### `DumperColorizer`

Archivo:
- `app/Helpers/Debug/DumperColorizer.php`

Rol:
- resuelve colores desde `DumperPalette`

Métodos públicos relevantes:
- `setTheme(string $theme): self`
- `getTheme(): string`
- `getAvailableThemes(): array`
- `getColor(string $type, bool $isHtml): string`
- `colorize(string $text, string $type, bool $isHtml): string`
- `getTypeColor(string $type, bool $isHtml): string`

### `DumperCollapsible`

Archivo:
- `app/Helpers/Debug/DumperCollapsible.php`

Rol:
- ids y JS delegado para secciones colapsables del dump HTML

Método público confirmado en uso documental:
- `resetCounter(): void`

## Firmas que suelen documentarse mal

### `DumperRenderer`

Archivo:
- `app/Helpers/Debug/DumperRenderer.php`

Firma pública real:

```php
public function render(array $data, ?array $caller, bool $isHtml): string
```

Notas:
- `$data` es un array de strings ya formateados
- `$caller` contiene contexto opcional (`file`, `line`)
- `$isHtml=false` deriva a salida CLI; `$isHtml=true` genera modal/botón HTML

### `MainFormatter`

Archivo:
- `app/Helpers/Debug/MainFormatter.php`

Firma pública real:

```php
public function formatVar(mixed $var, string $label = '', bool $isHtml = true, int $depth = 0): string
```

Notas:
- `label` es opcional
- `depth` existe y afecta indentación/recursión
- arrays y objetos delegan a formatters especializados

## Formatters especializados

Directorio:
- `app/Helpers/Debug/Formatters/`

Piezas reales:
- `PrimitiveTypeFormatter`
- `ArrayFormatter`
- `ObjectFormatter`
- `ResourceFormatter`

Uso:
- son detalles internos del pipeline de `MainFormatter`
- no tratarlos como API pública estable del framework

## Render HTML y CSP

El render HTML del dumper usa templates en:
- `boot-core/template/debug/`

Piezas relevantes:
- `dumper-button.tpl.php`
- `dumper-modal.tpl.php`
- `dumper-scripts.tpl.php`
- `dumper-styles.tpl.php`

Las convenciones CSP y los `data-dumper-*` vigentes se documentan en:
- `docs/security-conventions.md`

## Temas disponibles

Temas reales bajo `app/Helpers/Debug/Themes/`:

- `dark`
- `light`
- `monokai`
- `solarized`
- `github`
- `midnight_breeze`
- `ocean_wave`
- `candy_pop`
- `terminal_classic`
- `arctic_ice`
- `icy_blue`
- `forest_light`
- `mocha_blend`
- `neon_dream`
- `pastel_candy`

El fallback operativo sigue siendo `default` cuando un nombre inválido no existe.
