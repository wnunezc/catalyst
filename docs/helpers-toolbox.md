# Catalyst\Helpers\ToolBox

## Purpose

Document DrawBox and CLI/HTML/text helper primitives.

## Runtime Owners

| Concern | Owner |
|---|---|
| Selects CLI or HTML rendering and optionally appends file-output status. | `Catalyst\Helpers\ToolBox\DrawBox` |
| Calculates terminal dimensions, wraps content and assembles styled CLI boxes. | `Catalyst\Helpers\ToolBox\DrawBoxCliRenderer` |
| Inserts a colored separator and centered persistence message before the box footer. | `Catalyst\Helpers\ToolBox\DrawBoxFileOutputDecorator` |
| Builds styled HTML output with optional header, body and footer regions. | `Catalyst\Helpers\ToolBox\DrawBoxHtmlRenderer` |
| Supplies terminal escape codes and CSS class names for formatted boxes. | `Catalyst\Helpers\ToolBox\DrawBoxStylePalette` |
| Preserves ANSI decoration while fitting visible text into constrained widths. | `Catalyst\Helpers\ToolBox\DrawBoxTextHelper` |

## Current Behavior

This file is regenerated from current PHP docblocks and the runtime inventory scope for `Catalyst\Helpers\ToolBox`. It intentionally replaces stale historical API notes with the classes and methods that exist in code now.

## API From Docblocks

### `Catalyst\Helpers\ToolBox\DrawBox`

- File: `app/Helpers/ToolBox/DrawBox.php`
- Kind: `class`
- Summary: DrawBox class for creating formatted text boxes in terminal or HTML
- Responsibility: Selects CLI or HTML rendering and optionally appends file-output status.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `__construct()` | `protected` | DrawBox constructor. | Prepares the box renderer configuration used to compose CLI or HTML diagnostic output. |
| `draw()` | `public` | Draw a box around the given content. | Draw a box around the given content. |
| `normalizeOptions()` | `private` | Merges caller options with draw-box defaults. | Merges caller options with draw-box defaults. |
| `normalizeContent()` | `private` | Normalizes scalar or line-array content into renderable lines. | Normalizes scalar or line-array content into renderable lines. |
| `isCli()` | `private` | Check if running in the CLI environment. | Check if running in the CLI environment. |

### `Catalyst\Helpers\ToolBox\DrawBoxCliRenderer`

- File: `app/Helpers/ToolBox/DrawBoxCliRenderer.php`
- Kind: `class`
- Summary: Renders draw-box content for terminal output.
- Responsibility: Calculates terminal dimensions, wraps content and assembles styled CLI boxes.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `__construct()` | `public` | Initializes the object with the collaborators or state required for its responsibility. | Initializes the object with the collaborators or state required for its responsibility. |
| `render()` | `public` | Renders content lines into a terminal box or a width warning. | Renders content lines into a terminal box or a width warning. |
| `buildCliBox()` | `private` | Builds the complete terminal box around prepared content. | Builds the complete terminal box around prepared content. |

### `Catalyst\Helpers\ToolBox\DrawBoxFileOutputDecorator`

- File: `app/Helpers/ToolBox/DrawBoxFileOutputDecorator.php`
- Kind: `class`
- Summary: Appends file-output status to a rendered CLI box.
- Responsibility: Inserts a colored separator and centered persistence message before the box footer.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `__construct()` | `public` | Initializes the object with the collaborators or state required for its responsibility. | Initializes the object with the collaborators or state required for its responsibility. |
| `append()` | `public` | Appends a file-output result line to an existing CLI box. | Appends a file-output result line to an existing CLI box. |

### `Catalyst\Helpers\ToolBox\DrawBoxHtmlRenderer`

- File: `app/Helpers/ToolBox/DrawBoxHtmlRenderer.php`
- Kind: `class`
- Summary: Renders draw-box content as escaped HTML sections.
- Responsibility: Builds styled HTML output with optional header, body and footer regions.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `__construct()` | `public` | Initializes the Draw Box Html Renderer instance. | Initializes the Draw Box Html Renderer instance. |
| `render()` | `public` | Renders escaped content lines into a styled HTML box. | Renders escaped content lines into a styled HTML box. |

### `Catalyst\Helpers\ToolBox\DrawBoxStylePalette`

- File: `app/Helpers/ToolBox/DrawBoxStylePalette.php`
- Kind: `class`
- Summary: Maps draw-box style identifiers to CLI and HTML presentation values.
- Responsibility: Supplies terminal escape codes and CSS class names for formatted boxes.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `getCliScheme()` | `public` | Returns CLI color and reset sequences for a style identifier. | Returns CLI color and reset sequences for a style identifier. |
| `getHtmlStyleClass()` | `public` | Returns the HTML CSS class for a style identifier. | Returns the HTML CSS class for a style identifier. |
| `getEnvironmentBasedStyle()` | `private` | Returns the default style associated with the active environment. | Returns the default style associated with the active environment. |

### `Catalyst\Helpers\ToolBox\DrawBoxTextHelper`

- File: `app/Helpers/ToolBox/DrawBoxTextHelper.php`
- Kind: `class`
- Summary: Measures and splits decorated text for box rendering.
- Responsibility: Preserves ANSI decoration while fitting visible text into constrained widths.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `__construct()` | `public` | Initializes the Draw Box Text Helper instance. | Initializes the Draw Box Text Helper instance. |
| `visibleLength()` | `public` | Returns text length excluding ANSI sequences. | Returns text length excluding ANSI sequences. |
| `splitLineToFit()` | `public` | Splits a line while preserving readable key-value alignment. | Splits a line while preserving readable key-value alignment. |
| `splitTextToChunks()` | `public` | Splits text into visible-width chunks while restoring ANSI decoration. | Splits text into visible-width chunks while restoring ANSI decoration. |
| `extractAnsiCodes()` | `private` | Extracts the first ANSI style and final reset sequence. | Extracts the first ANSI style and final reset sequence. |

## Operational Notes

When PHP symbols or method contracts in this namespace change, refresh this document from docblocks and run `php public/cli.php docs:inventory --json`.

## Related Documentation

- `docs/runtime-inventory.md`
- `docs/runtime-module-catalog.md`
- `docs/harness-context-map.md`
