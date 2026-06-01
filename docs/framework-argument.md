# Catalyst\Framework\Argument

## Class: Argument
**File**: app/Framework/Argument/Argument.php
**Namespace**: Catalyst\Framework\Argument
**Type**: Class
**Purpose**: Main CLI argument handling facade with Singleton pattern

### Traits Used
- `Catalyst\Framework\Traits\SingletonTrait`

### Properties
- `$bag: ?ArgumentBag` - **private** - Parsed argument bag
- `$parser: ArgumentParser` - **private** - Argument parser instance
- `$validator: Validator` - **private** - Argument validator instance
- `$definedOptions: array` - **private** - Predefined options schema

### Public Methods
- `getArguments(): array` - **public** - Returns all arguments as associative array (compatible with FileOutput.php)
- `has(string $name): bool` - **public** - Checks if an option exists
- `get(string $name, mixed $default = null): mixed` - **public** - Gets option value with default fallback
- `getParameter(int $position, mixed $default = null): mixed` - **public** - Gets parameter value by position
- `getAllOptions(): array` - **public** - Returns all Option objects
- `getAllParameters(): array` - **public** - Returns all Parameter objects
- `defineOption(Option $option): self` - **public** - Defines an option schema
- `defineOptions(array $options): self` - **public** - Defines multiple option schemas
- `validate(): bool` - **public** - Validates arguments against schema
- `getValidationErrors(): array` - **public** - Gets validation errors
- `getValidator(): Validator` - **public** - Gets validator instance
- `getParser(): ArgumentParser` - **public** - Gets parser instance
- `getBag(): ?ArgumentBag` - **public** - Gets argument bag
- `getRaw(): array` - **public** - Gets raw argv array
- `isCli(): bool` - **public static** - Checks if running in CLI mode
- `getScriptName(): string` - **public** - Gets script name

### Protected Methods
- `__construct(): void` - **protected** - Constructor with auto-parse
- `parse(?array $argv = null): self` - **public** - Parses command line arguments

### Used By
- `Catalyst\Helpers\IO\FileOutput` - Uses `getArguments()` for file output detection

---

## Class: ArgumentParser
**File**: app/Framework/Argument/ArgumentParser.php
**Namespace**: Catalyst\Framework\Argument
**Type**: Class
**Purpose**: Parses raw $argv into structured Option and Parameter objects

### Public Methods
- `parse(array $argv): ArgumentBag` - **public** - Parses argv array into ArgumentBag
- `parseWithSchema(array $argv, array $definedOptions): ArgumentBag` - **public** - Parses with predefined options schema

### Private Methods
- `parseLongOption(array $args, int $index, ArgumentBag $bag): int` - **private** - Parses long options (--option)
- `parseShortOption(array $args, int $index, ArgumentBag $bag): int` - **private** - Parses short options (-o) and combined flags (-abc)

### Supported Formats
- Short options: `-f value`, `-f=value`
- Long options: `--file value`, `--file=value`
- Boolean flags: `--verbose`, `-v`
- Combined short: `-abc` expands to `-a -b -c`
- Positional parameters: Arguments without flags

---

## Class: ArgumentBag
**File**: app/Framework/Argument/ArgumentBag.php
**Namespace**: Catalyst\Framework\Argument
**Type**: Class
**Purpose**: Container for parsed CLI arguments

### Properties
- `$options: array` - **private** - Options indexed by name
- `$parameters: array` - **private** - Parameters indexed by position
- `$raw: array` - **private** - Raw argv array

### Public Methods
- `__construct(array $raw = []): void` - **public** - Constructor
- `addOption(Option $option): self` - **public** - Adds an option
- `addParameter(Parameter $parameter): self` - **public** - Adds a parameter
- `getOption(string $name): ?Option` - **public** - Gets option by name
- `getParameter(int $position): ?Parameter` - **public** - Gets parameter by position
- `hasOption(string $name): bool` - **public** - Checks if option exists
- `hasParameter(int $position): bool` - **public** - Checks if parameter exists
- `getOptionValue(string $name, mixed $default = null): mixed` - **public** - Gets option value
- `getParameterValue(int $position, mixed $default = null): mixed` - **public** - Gets parameter value
- `getAllOptions(): array` - **public** - Gets all options
- `getAllParameters(): array` - **public** - Gets all parameters
- `toArray(): array` - **public** - Converts to associative array (for FileOutput compatibility)
- `getRaw(): array` - **public** - Gets raw argv
- `countOptions(): int` - **public** - Counts options
- `countParameters(): int` - **public** - Counts parameters

---

## Class: Option
**File**: app/Framework/Argument/Option.php
**Namespace**: Catalyst\Framework\Argument
**Type**: Class
**Purpose**: Represents a CLI option/flag (-f, --file)

### Properties
- `$shortName: ?string` - **private** - Short name (single character)
- `$longName: ?string` - **private** - Long name
- `$value: mixed` - **private** - Option value
- `$required: bool` - **private** - Whether required
- `$default: mixed` - **private** - Default value
- `$description: string` - **private** - Description
- `$acceptsValue: bool` - **private** - Whether accepts a value

### Public Methods
- `__construct(?string $shortName, ?string $longName, mixed $default, bool $required, string $description, bool $acceptsValue): void` - **public** - Constructor
- `getShortName(): ?string` - **public** - Gets short name
- `getLongName(): ?string` - **public** - Gets long name
- `setValue(mixed $value): self` - **public** - Sets value
- `getValue(): mixed` - **public** - Gets value
- `isRequired(): bool` - **public** - Checks if required
- `getDefault(): mixed` - **public** - Gets default value
- `getDescription(): string` - **public** - Gets description
- `acceptsValue(): bool` - **public** - Checks if accepts value
- `isSet(): bool` - **public** - Checks if option has been set
- `matches(string $name): bool` - **public** - Checks if name matches (short or long)
- `getPrimaryName(): ?string` - **public** - Gets primary name (long preferred)

---

## Class: Parameter
**File**: app/Framework/Argument/Parameter.php
**Namespace**: Catalyst\Framework\Argument
**Type**: Class
**Purpose**: Represents a positional CLI parameter

### Properties
- `$position: int` - **private** - Position index
- `$value: mixed` - **private** - Parameter value
- `$required: bool` - **private** - Whether required
- `$default: mixed` - **private** - Default value
- `$name: string` - **private** - Name/identifier
- `$description: string` - **private** - Description

### Public Methods
- `__construct(int $position, mixed $value, bool $required, mixed $default, string $name, string $description): void` - **public** - Constructor
- `getPosition(): int` - **public** - Gets position
- `setValue(mixed $value): self` - **public** - Sets value
- `getValue(): mixed` - **public** - Gets value
- `isRequired(): bool` - **public** - Checks if required
- `getDefault(): mixed` - **public** - Gets default value
- `getName(): string` - **public** - Gets name
- `getDescription(): string` - **public** - Gets description
- `isSet(): bool` - **public** - Checks if parameter has been set
- `hasValue(): bool` - **public** - Checks if parameter has a value

---

## Class: Validator
**File**: app/Framework/Argument/Validator.php
**Namespace**: Catalyst\Framework\Argument
**Type**: Class
**Purpose**: Validates CLI arguments against requirements and types

### Properties
- `$errors: array` - **private** - Validation errors

### Public Methods
- `validateOption(Option $option): bool` - **public** - Validates a single option
- `validateParameter(Parameter $parameter): bool` - **public** - Validates a single parameter
- `validateBag(ArgumentBag $bag, array $requiredOptions = []): bool` - **public** - Validates entire argument bag
- `getErrors(): array` - **public** - Gets validation errors
- `hasErrors(): bool` - **public** - Checks if there are errors
- `getErrorsAsString(string $separator = "\n"): string` - **public** - Gets errors as formatted string
- `clearErrors(): void` - **public** - Clears validation errors
- `validateType(mixed $value, string $expectedType): bool` - **public** - Validates value type (string, int, float, bool, array)
- `castValue(mixed $value, string $type): mixed` - **public** - Casts value to specified type
