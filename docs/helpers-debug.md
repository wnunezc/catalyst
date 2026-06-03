# Catalyst\Helpers\Debug

## Purpose

Document debug dump rendering and formatting helpers.

## Runtime Owners

| Concern | Owner |
|---|---|
| Enumerates the semantic color slots supported by dumper palettes. | `Catalyst\Helpers\Debug\ColorType` |
| Coordinates dumper configuration, formatting, and rendering for each debug inspection. | `Catalyst\Helpers\Debug\Dumper` |
| Builds collapsible debug sections and their CSP-safe browser behavior. | `Catalyst\Helpers\Debug\DumperCollapsible` |
| Resolves theme colors and applies them to dumper output for HTML or CLI rendering. | `Catalyst\Helpers\Debug\DumperColorizer` |
| Stores and validates runtime presentation limits and theme preferences for dumps. | `Catalyst\Helpers\Debug\DumperConfig` |
| Loads, validates, caches, and exposes dumper theme palettes. | `Catalyst\Helpers\Debug\DumperPalette` |
| Renders formatted dump data as terminal text or interactive HTML output. | `Catalyst\Helpers\Debug\DumperRenderer` |
| Formats nested arrays while enforcing dumper depth and child-count limits. | `Catalyst\Helpers\Debug\Formatters\ArrayFormatter` |
| Formats reflected object structure while enforcing depth, size, and recursion limits. | `Catalyst\Helpers\Debug\Formatters\ObjectFormatter` |
| Formats scalar and null values according to dumper limits and output mode. | `Catalyst\Helpers\Debug\Formatters\PrimitiveTypeFormatter` |
| Formats PHP resources with their runtime identifier and resource type. | `Catalyst\Helpers\Debug\Formatters\ResourceFormatter` |
| Selects the specialized formatter that represents each inspected PHP value. | `Catalyst\Helpers\Debug\MainFormatter` |
| Enumerates the supported dumper theme identifiers and validates theme-name input. | `Catalyst\Helpers\Debug\ThemeName` |
| Defines the palette operations required from dumper theme providers. | `Catalyst\Helpers\Debug\ThemeProviderInterface` |

## Current Behavior

This file is regenerated from current PHP docblocks and the runtime inventory scope for `Catalyst\Helpers\Debug`. It intentionally replaces stale historical API notes with the classes and methods that exist in code now.

## API From Docblocks

### `Catalyst\Helpers\Debug\ColorType`

- File: `app/Helpers/Debug/ColorType.php`
- Kind: `enum`
- Summary: ColorType - Enum for available color types
- Responsibility: Enumerates the semantic color slots supported by dumper palettes.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `getTypes()` | `public` | Get all color types as an array of strings | n/a |
| `exists()` | `public` | Check if a color type exists | n/a |
| `fromString()` | `public` | Get a ColorType case from a string | n/a |

### `Catalyst\Helpers\Debug\Dumper`

- File: `app/Helpers/Debug/Dumper.php`
- Kind: `class`
- Summary: Dumper class for debugging variables
- Responsibility: Coordinates dumper configuration, formatting, and rendering for each debug inspection.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `__construct()` | `protected` | Initializes the dumper with formatter and rendering collaborators. | Constructor with dependency injection. |
| `initialize()` | `protected` | Initialize the Dumper instance with dependencies. | Initialize the Dumper instance with dependencies. |
| `dump()` | `public` | Dump variables for inspection | n/a |
| `configure()` | `public` | Configure the dumper | n/a |
| `getAvailableThemes()` | `public` | Get available color themes | n/a |
| `setTheme()` | `public` | Set the current color theme | n/a |
| `getTheme()` | `public` | Get the current color theme | n/a |
| `getThemesNameList()` | `public` | Get a comma-separated list of all available theme names | n/a |

### `Catalyst\Helpers\Debug\DumperCollapsible`

- File: `app/Helpers/Debug/DumperCollapsible.php`
- Kind: `class`
- Summary: DumperCollapsible class for handling collapsible sections in debug output
- Responsibility: Builds collapsible debug sections and their CSP-safe browser behavior.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `__construct()` | `public` | Initializes the object with the collaborators or state required for its responsibility. | Initializes the object with the collaborators or state required for its responsibility. |
| `resetCounter()` | `public` | Reset the collapse counter. | Reset the collapse counter. |
| `create()` | `public` | Create a collapsible section with a chevron toggle. | Create a collapsible section with a chevron toggle. |
| `getJavaScript()` | `public` | Generate JavaScript code for collapsible functionality. CSP-safe: returns a function + a global click delegate on [data-dumper-collapse]. The delegate is attached only once per page even if multiple dumps are rendered (guarded by a window flag). | Generate JavaScript code for collapsible functionality. CSP-safe: returns a function + a global click delegate on [data-dumper-collapse]. The delegate is attached only once per page even if multiple dumps are rendered (guarded by a window flag). |

### `Catalyst\Helpers\Debug\DumperColorizer`

- File: `app/Helpers/Debug/DumperColorizer.php`
- Kind: `class`
- Summary: DumperColorizer class for handling text coloring in debug output
- Responsibility: Resolves theme colors and applies them to dumper output for HTML or CLI rendering.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `__construct()` | `public` | Initializes the object with the collaborators or state required for its responsibility. | Initializes the object with the collaborators or state required for its responsibility. |
| `getThemes()` | `private` | Lazy load themes when needed. | Lazy load themes when needed. |
| `validateTheme()` | `private` | Validate the current theme and set to default if invalid. | Validate the current theme and set to default if invalid. |
| `setTheme()` | `public` | Set the current color theme. | Stores the active dumper theme after validating it against available palettes. |
| `getTheme()` | `public` | Get the current theme name. | Exposes the active dumper theme key used by color resolution. |
| `getAvailableThemes()` | `public` | Get all available theme names. | Get all available theme names. |
| `getColor()` | `public` | Get the color for a specific type in the current theme. | Resolves the configured color value for a dumper type in the active theme. |
| `getBackgroundColor()` | `public` | Get background color for the current theme. | Get background color for the current theme. |
| `getTextColor()` | `public` | Get text color for the current theme. | Get text color for the current theme. |
| `getHtmlColors()` | `public` | Get all HTML colors for the current theme keyed by logical color type. | Get all HTML colors for the current theme keyed by logical color type. |
| `getHeaderColor()` | `public` | Get header background color for the current theme. | Get header background color for the current theme. |
| `colorize()` | `public` | Apply color to text based on type. | Apply color to text based on type. |
| `getTypeColor()` | `public` | Get the type color based on the variable type. | Maps a PHP runtime value to the dumper color configured for its detected type. |

### `Catalyst\Helpers\Debug\DumperConfig`

- File: `app/Helpers/Debug/DumperConfig.php`
- Kind: `class`
- Summary: DumperConfig class for managing dumper configuration
- Responsibility: Stores and validates runtime presentation limits and theme preferences for dumps.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `__construct()` | `public` | Initializes the object with the collaborators or state required for its responsibility. | Initializes the object with the collaborators or state required for its responsibility. |
| `applyOptions()` | `public` | Apply configuration options. | Apply configuration options. |
| `getMaxStrLength()` | `public` | Get maximum string length. | Get maximum string length. |
| `setMaxStrLength()` | `public` | Set maximum string length. | Set maximum string length. |
| `getMaxChildren()` | `public` | Get maximum children. | Get maximum children. |
| `setMaxChildren()` | `public` | Set maximum children. | Set maximum children. |
| `getMaxDepth()` | `public` | Returns the maximum nesting depth allowed during dump rendering. | Exposes the depth guard used by formatters to stop recursive expansion. |
| `setMaxDepth()` | `public` | Updates the maximum nesting depth allowed during dump rendering. | Stores the depth guard while enforcing the minimum supported value. |
| `getShowFloatingButton()` | `public` | Get whether to show a floating button. | Get whether to show a floating button. |
| `setShowFloatingButton()` | `public` | Set whether to show a floating button. | Set whether to show a floating button. |
| `getInitiallyExpanded()` | `public` | Get whether arrays and objects are initially expanded. | Get whether arrays and objects are initially expanded. |
| `setInitiallyExpanded()` | `public` | Set whether arrays and objects are initially expanded. | Set whether arrays and objects are initially expanded. |
| `getColorTheme()` | `public` | Returns the active color theme used by dump renderers. | Exposes the selected palette key for HTML and CLI dumper output. |
| `setColorTheme()` | `public` | Updates the active color theme when the requested palette is available. | Stores only supported palette keys so dump rendering keeps a valid theme. |
| `getAvailableThemes()` | `public` | Get available color themes. | Get available color themes. |

### `Catalyst\Helpers\Debug\DumperPalette`

- File: `app/Helpers/Debug/DumperPalette.php`
- Kind: `class`
- Summary: DumperPalette - Color theme provider for the Dumper component
- Responsibility: Loads, validates, caches, and exposes dumper theme palettes.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `getPalette()` | `public` | Returns all available color palettes for the Dumper component | n/a |
| `getPaletteList()` | `public` | Returns a list of all available palette names | n/a |
| `getTheme()` | `public` | Gets a specific theme by name | n/a |
| `themeExists()` | `public` | Checks if a theme exists | n/a |
| `loadPalettes()` | `private` | Loads all palettes from theme files | n/a |
| `validatePalette()` | `private` | Validates a palette to ensure it has all required color types | n/a |

### `Catalyst\Helpers\Debug\DumperRenderer`

- File: `app/Helpers/Debug/DumperRenderer.php`
- Kind: `class`
- Summary: DumperRenderer class for rendering debug output
- Responsibility: Renders formatted dump data as terminal text or interactive HTML output.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `__construct()` | `public` | Initializes the object with the collaborators or state required for its responsibility. | Initializes the object with the collaborators or state required for its responsibility. |
| `loadTemplate()` | `private` | Load a template file and extract variables into its scope. | Load a template file and extract variables into its scope. |
| `resolveTemplatePath()` | `private` | Resolve the first existing template path for the requested template name. | Resolve the first existing template path for the requested template name. |
| `render()` | `public` | Render debug output. | Render debug output. |
| `renderCli()` | `private` | Render debug output for CLI. | Render debug output for CLI. |
| `renderHtml()` | `private` | Render debug output for HTML. | Render debug output for HTML. |
| `generateCss()` | `private` | Generate CSS for HTML output. | Generate CSS for HTML output. |
| `generateJavaScript()` | `private` | Generate JavaScript for HTML output. | Generate JavaScript for HTML output. |
| `generateModal()` | `private` | Generate modal HTML. | Generate modal HTML. |
| `generateFloatingButton()` | `private` | Generate floating button HTML. | Generate floating button HTML. |
| `adjustBrightness()` | `private` | Adjust brightness of a hex color. | Adjust brightness of a hex color. |

### `Catalyst\Helpers\Debug\Formatters\ArrayFormatter`

- File: `app/Helpers/Debug/Formatters/ArrayFormatter.php`
- Kind: `class`
- Summary: ArrayFormatter class for formatting array variable types
- Responsibility: Formats nested arrays while enforcing dumper depth and child-count limits.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `__construct()` | `public` | Initializes the object with the collaborators or state required for its responsibility. | Initializes the object with the collaborators or state required for its responsibility. |
| `formatArray()` | `public` | Format array for output. | Format array for output. |

### `Catalyst\Helpers\Debug\Formatters\ObjectFormatter`

- File: `app/Helpers/Debug/Formatters/ObjectFormatter.php`
- Kind: `class`
- Summary: ObjectFormatter class for formatting object variable types
- Responsibility: Formats reflected object structure while enforcing depth, size, and recursion limits.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `__construct()` | `public` | Initializes the object with the collaborators or state required for its responsibility. | Initializes the object with the collaborators or state required for its responsibility. |
| `formatObject()` | `public` | Format object for output. | Format object for output. |
| `getClassConstants()` | `private` | Get class constants with their visibility. | Get class constants with their visibility. |
| `formatConstants()` | `private` | Format constants for output. | Format constants for output. |
| `formatProperties()` | `private` | Format properties for output. | Format properties for output. |
| `formatMethods()` | `private` | Format methods for output. | Format methods for output. |

### `Catalyst\Helpers\Debug\Formatters\PrimitiveTypeFormatter`

- File: `app/Helpers/Debug/Formatters/PrimitiveTypeFormatter.php`
- Kind: `class`
- Summary: PrimitiveTypeFormatter class for formatting primitive variable types
- Responsibility: Formats scalar and null values according to dumper limits and output mode.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `__construct()` | `public` | Initializes the object with the collaborators or state required for its responsibility. | Initializes the object with the collaborators or state required for its responsibility. |
| `formatString()` | `public` | Format string for output. | Format string for output. |
| `formatNumber()` | `public` | Format numeric value for output. | Format numeric value for output. |
| `formatBoolean()` | `public` | Format boolean for output. | Format boolean for output. |
| `formatNull()` | `public` | Format null for output. | Format null for output. |

### `Catalyst\Helpers\Debug\Formatters\ResourceFormatter`

- File: `app/Helpers/Debug/Formatters/ResourceFormatter.php`
- Kind: `class`
- Summary: ResourceFormatter class for formatting resource variable types
- Responsibility: Formats PHP resources with their runtime identifier and resource type.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `__construct()` | `public` | Initializes the object with the collaborators or state required for its responsibility. | Initializes the object with the collaborators or state required for its responsibility. |
| `formatResource()` | `public` | Format resource for output. | Format resource for output. |

### `Catalyst\Helpers\Debug\MainFormatter`

- File: `app/Helpers/Debug/MainFormatter.php`
- Kind: `class`
- Summary: MainFormatter class for coordinating the formatting of different variable types
- Responsibility: Selects the specialized formatter that represents each inspected PHP value.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `__construct()` | `public` | Initializes the object with the collaborators or state required for its responsibility. | Initializes the object with the collaborators or state required for its responsibility. |
| `formatVar()` | `public` | Format and output the variable. | Format and output the variable. |

### `Catalyst\Helpers\Debug\ThemeName`

- File: `app/Helpers/Debug/ThemeName.php`
- Kind: `enum`
- Summary: ThemeName - Enum for available theme names
- Responsibility: Enumerates the supported dumper theme identifiers and validates theme-name input.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `getNames()` | `public` | Get all theme names as an array of strings | n/a |
| `exists()` | `public` | Check if a theme name exists | n/a |
| `fromString()` | `public` | Get a ThemeName case from a string | n/a |

### `Catalyst\Helpers\Debug\ThemeProviderInterface`

- File: `app/Helpers/Debug/ThemeProviderInterface.php`
- Kind: `interface`
- Summary: ThemeProviderInterface - Contract for theme providers
- Responsibility: Defines the palette operations required from dumper theme providers.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `getPalette()` | `public` | Returns all available color palettes | n/a |
| `getPaletteList()` | `public` | Returns a list of all available palette names | n/a |
| `getTheme()` | `public` | Gets a specific theme by name | n/a |
| `themeExists()` | `public` | Checks if a theme exists | n/a |

## Operational Notes

When PHP symbols or method contracts in this namespace change, refresh this document from docblocks and run `php public/cli.php docs:inventory --json`.

## Related Documentation

- `docs/runtime-inventory.md`
- `docs/runtime-module-catalog.md`
- `docs/harness-context-map.md`
