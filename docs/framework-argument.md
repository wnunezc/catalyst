# Catalyst\Framework\Argument

## Purpose

Document CLI argument and option parsing primitives.

## Runtime Owners

| Concern | Owner |
|---|---|
| Maintains parser, validator, parsed bag, and optional option schema for command-line consumers. | `Catalyst\Framework\Argument\Argument` |
| Provides lookup, existence checks, counts, and array conversion for parsed command-line input. | `Catalyst\Framework\Argument\ArgumentBag` |
| Recognizes long options, short options, combined short flags, option values, and positional parameters. | `Catalyst\Framework\Argument\ArgumentParser` |
| Stores option names, value/default state, required metadata, description, and value acceptance rules. | `Catalyst\Framework\Argument\Option` |
| Stores parameter position, current/default value, required metadata, name, and description. | `Catalyst\Framework\Argument\Parameter` |
| Tracks validation errors, checks required inputs, validates scalar types, and casts option values. | `Catalyst\Framework\Argument\Validator` |

## Current Behavior

This file is regenerated from current PHP docblocks and the runtime inventory scope for `Catalyst\Framework\Argument`. It intentionally replaces stale historical API notes with the classes and methods that exist in code now.

## API From Docblocks

### `Catalyst\Framework\Argument\Argument`

- File: `app/Framework/Argument/Argument.php`
- Kind: `class`
- Summary: Provides the shared entry point for parsing and querying CLI arguments.
- Responsibility: Maintains parser, validator, parsed bag, and optional option schema for command-line consumers.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `__construct()` | `protected` | Initializes parsing and validation services, then parses the current CLI input. | Initializes parsing and validation services, then parses the current CLI input. |
| `parse()` | `public` | Parses raw command-line input into the internal argument bag. | Parses raw command-line input into the internal argument bag. |
| `getArguments()` | `public` | Returns parsed options and positional parameters as a flat associative array. | Returns parsed options and positional parameters as a flat associative array. |
| `has()` | `public` | Checks whether a parsed option is present by short or long name. | Checks whether a parsed option is present by short or long name. |
| `get()` | `public` | Returns a parsed option value or the supplied default when the option is absent. | Returns a parsed option value or the supplied default when the option is absent. |
| `getParameter()` | `public` | Returns a positional parameter value or the supplied default when absent. | Returns a positional parameter value or the supplied default when absent. |
| `getAllOptions()` | `public` | Returns every parsed option object indexed by primary name. | Returns every parsed option object indexed by primary name. |
| `getAllParameters()` | `public` | Returns every parsed positional parameter indexed by position. | Returns every parsed positional parameter indexed by position. |
| `defineOption()` | `public` | Registers a single option definition for schema-aware parsing and validation. | Registers a single option definition for schema-aware parsing and validation. |
| `defineOptions()` | `public` | Registers multiple option definitions for schema-aware parsing and validation. | Registers multiple option definitions for schema-aware parsing and validation. |
| `validate()` | `public` | Validates the current parsed bag against all required defined options. | Validates the current parsed bag against all required defined options. |
| `getValidationErrors()` | `public` | Returns validation error messages collected by the validator. | Returns validation error messages collected by the validator. |
| `getValidator()` | `public` | Returns the validator used by this argument facade. | Returns the validator used by this argument facade. |
| `getParser()` | `public` | Returns the parser used by this argument facade. | Returns the parser used by this argument facade. |
| `getBag()` | `public` | Returns the current parsed argument bag, if parsing has produced one. | Returns the current parsed argument bag, if parsing has produced one. |
| `getRaw()` | `public` | Returns the original argv array stored in the parsed bag. | Returns the original argv array stored in the parsed bag. |
| `isCli()` | `public` | Determines whether the current PHP process is running in CLI mode. | n/a |
| `getScriptName()` | `public` | Returns the executable script basename from the current argv array. | Returns the executable script basename from the current argv array. |

### `Catalyst\Framework\Argument\ArgumentBag`

- File: `app/Framework/Argument/ArgumentBag.php`
- Kind: `class`
- Summary: Stores parsed CLI options, positional parameters, and the original argv array.
- Responsibility: Provides lookup, existence checks, counts, and array conversion for parsed command-line input.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `__construct()` | `public` | Captures the original argv array before options and parameters are added. | Captures the original argv array before options and parameters are added. |
| `addOption()` | `public` | Adds an option to the bag using its primary short or long name. | Adds an option to the bag using its primary short or long name. |
| `addParameter()` | `public` | Adds a positional parameter to the bag by its declared position. | Adds a positional parameter to the bag by its declared position. |
| `getOption()` | `public` | Finds a parsed option by direct key, short name, or long name. | Finds a parsed option by direct key, short name, or long name. |
| `getParameter()` | `public` | Returns the positional parameter stored at the requested index. | Returns the positional parameter stored at the requested index. |
| `hasOption()` | `public` | Checks whether a parsed option exists by short or long name. | Checks whether a parsed option exists by short or long name. |
| `hasParameter()` | `public` | Checks whether a positional parameter exists at the requested index. | Checks whether a positional parameter exists at the requested index. |
| `getOptionValue()` | `public` | Returns a parsed option value or the supplied default when absent. | Returns a parsed option value or the supplied default when absent. |
| `getParameterValue()` | `public` | Returns a positional parameter value or the supplied default when absent. | Returns a positional parameter value or the supplied default when absent. |
| `getAllOptions()` | `public` | Returns all parsed options indexed by primary name. | Returns all parsed options indexed by primary name. |
| `getAllParameters()` | `public` | Returns all parsed positional parameters indexed by position. | Returns all parsed positional parameters indexed by position. |
| `toArray()` | `public` | Converts parsed options and parameters into the flat array format used by legacy CLI consumers. | Converts parsed options and parameters into the flat array format used by legacy CLI consumers. |
| `getRaw()` | `public` | Returns the original argv array captured by the bag. | Returns the original argv array captured by the bag. |
| `countOptions()` | `public` | Counts parsed options currently stored in the bag. | Counts parsed options currently stored in the bag. |
| `countParameters()` | `public` | Counts parsed positional parameters currently stored in the bag. | Counts parsed positional parameters currently stored in the bag. |

### `Catalyst\Framework\Argument\ArgumentParser`

- File: `app/Framework/Argument/ArgumentParser.php`
- Kind: `class`
- Summary: Parses raw CLI argv input into structured option and parameter objects.
- Responsibility: Recognizes long options, short options, combined short flags, option values, and positional parameters.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `parse()` | `public` | Parses an argv array into an argument bag, skipping the executable script name. | Parses an argv array into an argument bag, skipping the executable script name. |
| `parseLongOption()` | `private` | Parses a long option token and stores it in the provided bag. | Parses a long option token and stores it in the provided bag. |
| `parseShortOption()` | `private` | Parses a short option token or combined short flags and stores them in the bag. | Parses a short option token or combined short flags and stores them in the bag. |
| `parseWithSchema()` | `public` | Parses argv and overlays predefined option objects onto matching parsed options. | Parses argv and overlays predefined option objects onto matching parsed options. |

### `Catalyst\Framework\Argument\Option`

- File: `app/Framework/Argument/Option.php`
- Kind: `class`
- Summary: Represents a parsed or predefined command-line option.
- Responsibility: Stores option names, value/default state, required metadata, description, and value acceptance rules.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `__construct()` | `public` | Creates an option definition or parsed option with its default value applied. | Creates an option definition or parsed option with its default value applied. |
| `getShortName()` | `public` | Returns the configured short option name. | Returns the configured short option name. |
| `getLongName()` | `public` | Returns the configured long option name. | Returns the configured long option name. |
| `setValue()` | `public` | Updates the current parsed value for the option. | Updates the current parsed value for the option. |
| `getValue()` | `public` | Returns the current parsed or default value. | Returns the current parsed or default value. |
| `isRequired()` | `public` | Reports whether validation requires this option. | Reports whether validation requires this option. |
| `getDefault()` | `public` | Returns the fallback value assigned to the option. | Returns the fallback value assigned to the option. |
| `getDescription()` | `public` | Returns the human-readable option description. | Returns the human-readable option description. |
| `acceptsValue()` | `public` | Reports whether this option accepts an explicit value. | Reports whether this option accepts an explicit value. |
| `matches()` | `public` | Checks whether a supplied name matches the short or long option name. | Checks whether a supplied name matches the short or long option name. |
| `getPrimaryName()` | `public` | Returns the long option name when available, otherwise the short name. | Returns the long option name when available, otherwise the short name. |

### `Catalyst\Framework\Argument\Parameter`

- File: `app/Framework/Argument/Parameter.php`
- Kind: `class`
- Summary: Represents a positional command-line parameter.
- Responsibility: Stores parameter position, current/default value, required metadata, name, and description.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `__construct()` | `public` | Creates a positional parameter with parsed value, validation metadata, and fallback value. | Creates a positional parameter with parsed value, validation metadata, and fallback value. |
| `getPosition()` | `public` | Returns the positional index assigned to this parameter. | Returns the positional index assigned to this parameter. |
| `setValue()` | `public` | Updates the current value stored for this parameter. | Updates the current value stored for this parameter. |
| `getValue()` | `public` | Returns the current parsed or default parameter value. | Returns the current parsed or default parameter value. |
| `isRequired()` | `public` | Reports whether validation requires this parameter. | Reports whether validation requires this parameter. |
| `getDefault()` | `public` | Returns the fallback value assigned to this parameter. | Returns the fallback value assigned to this parameter. |
| `getName()` | `public` | Returns the parameter name used for identification and validation messages. | Returns the parameter name used for identification and validation messages. |
| `getDescription()` | `public` | Returns the human-readable parameter description. | Returns the human-readable parameter description. |
| `hasValue()` | `public` | Reports whether the parameter currently stores any value. | Reports whether the parameter currently stores any value. |

### `Catalyst\Framework\Argument\Validator`

- File: `app/Framework/Argument/Validator.php`
- Kind: `class`
- Summary: Validates parsed CLI options and positional parameters.
- Responsibility: Tracks validation errors, checks required inputs, validates scalar types, and casts option values.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `validateOption()` | `public` | Validates required and value-bearing constraints for a single option. | Validates required and value-bearing constraints for a single option. |

## Operational Notes

When PHP symbols or method contracts in this namespace change, refresh this document from docblocks and run `php public/cli.php docs:inventory --json`.

## Related Documentation

- `docs/runtime-inventory.md`
- `docs/runtime-module-catalog.md`
- `docs/harness-context-map.md`
