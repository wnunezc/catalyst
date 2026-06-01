# Catalyst\Framework\Enums

## Enum: AppEnvironment
**File**: app/Framework/Enums/AppEnvironment.php
**Namespace**: Catalyst\Framework\Enums
**Type**: Enum (string backed)
**Purpose**: Type-safe representation of the application environment for use in framework code (not bootstrap-phase checks — those use `IS_DEVELOPMENT`, `IS_STAGING`, etc. constants)

### Cases
- `DEVELOPMENT` = 'development'
- `STAGING` = 'staging'
- `TESTING` = 'testing'
- `PRODUCTION` = 'production'

### Public Methods
- `values(): array` - **public static** - Returns all valid environment strings
- `isValid(string $env): bool` - **public static** - Checks if a string is a valid environment
- `current(): self` - **public static** - Returns the current environment by reading `IS_*` constants
- `allowsDebug(): bool` - **public** - Returns true for all environments except PRODUCTION
- `isProductionLike(): bool` - **public** - Returns true for PRODUCTION and STAGING

### Usage Notes
- Use in framework code that needs type-safe environment checks
- Bootstrap-phase code (before Composer) must still use `IS_DEVELOPMENT`, `IS_STAGING`, etc.
- `current()` depends on `IS_*` constants being defined (requires env-constant.php loaded)
