# Catalyst\Helpers\Validation

**Directory**: app/Helpers/Validation/
**Purpose**: Self-contained validation system. No external dependencies. Consumes the i18n `__()` helper for all messages.

## Form Requests

Reusable request validation now has a live framework base class:

- **FormRequest** — `app/Framework/Http/FormRequest.php`
  - wraps the current `Request`
  - supports `authorize()`, `rules()`, `labels()`, `only()`, `except()`, `prepareForValidation()`
  - exposes `validated()` plus request/file helpers
  - is auto-resolved by `RouteDispatcher` when a controller action type-hints a `FormRequest` subclass
  - throws `ValidationException` on validation failure and `ForbiddenException` when `authorize()` returns `false`

## Class: Validator
**File**: app/Helpers/Validation/Validator.php
**Namespace**: Catalyst\Helpers\Validation
**Type**: Class
**Purpose**: Public API entry point. Instantiate with data + rules, then call `fails()` / `errors()`.

### Constructor
- `__construct(array $data, array $rules, array $labels = [])` - `$rules` is `['field' => 'required|min:3']` or `['field' => ['required','min:3']]`

### Methods
- `fails(): bool` - Run validation (once) and return true if any errors found
- `passes(): bool` - Inverse of `fails()`
- `errors(): array<string, string[]>` - All errors per field
- `firstErrors(): array<string, string>` - First error per field only
- `getErrorsForJson(): array` - Alias for `errors()`, compatible with `jsonValidationError()`

---

## Class: ValidationRunner
**File**: app/Helpers/Validation/ValidationRunner.php
**Namespace**: Catalyst\Helpers\Validation
**Type**: Class
**Purpose**: Internal engine. Iterates field/rule pairs, dispatches to rule classes, collects i18n error messages.

### Methods
- `run(array $data, array $ruleMap, array $labels): array<string, string[]>` - Execute all rules, return errors

### Private Methods
- `applyRule(string $rule, string $field, string $label, mixed $value, array $params, array $data): ?string`
- `dataGet(array $data, string $key): mixed` - Dot-notation field resolution (e.g. `address.city`)
- `hasRule(string $ruleName, array $parsedRules): bool`
- `isEmpty(mixed $value): bool`

---

## Class: RuleParser
**File**: app/Helpers/Validation/RuleParser.php
**Namespace**: Catalyst\Helpers\Validation
**Type**: Class
**Purpose**: Parses rule definitions into normalized `[ruleName, params[]]` tuples.

### Methods
- `parse(string|array $rules): array` - Input: `'required|min:3'` or `['required','min:3']`. Output: `[['required',[]], ['min',['3']]]`

---

## Class: Rules\StringRules
**File**: app/Helpers/Validation/Rules/StringRules.php
**Rules**: `required`, `min`, `max`, `alpha`, `alpha_num`, `regex`

- `required(mixed $value): bool`
- `min(mixed $value, array $params): bool` — params[0] = minLength (mb_strlen)
- `max(mixed $value, array $params): bool` — params[0] = maxLength (mb_strlen)
- `alpha(mixed $value): bool` — only [a-zA-Z]
- `alphaNum(mixed $value): bool` — only [a-zA-Z0-9]
- `regex(mixed $value, array $params): bool` — params[0] = pattern

---

## Class: Rules\NumericRules
**File**: app/Helpers/Validation/Rules/NumericRules.php
**Rules**: `numeric`, `integer`, `min_value`, `max_value`, `between`

- `numeric(mixed $value): bool`
- `integer(mixed $value): bool`
- `minValue(mixed $value, array $params): bool` — params[0] = min numeric value
- `maxValue(mixed $value, array $params): bool` — params[0] = max numeric value
- `between(mixed $value, array $params): bool` — params[0]=min, params[1]=max

---

## Class: Rules\FormatRules
**File**: app/Helpers/Validation/Rules/FormatRules.php
**Rules**: `email`, `url`, `date`, `boolean`

- `email(mixed $value): bool` — FILTER_VALIDATE_EMAIL
- `url(mixed $value): bool` — FILTER_VALIDATE_URL
- `date(mixed $value): bool` — strtotime()
- `boolean(mixed $value): bool` — FILTER_VALIDATE_BOOLEAN

---

## Class: Rules\ComparisonRules
**File**: app/Helpers/Validation/Rules/ComparisonRules.php
**Rules**: `same`, `different`, `confirmed`, `in`, `not_in`

- `same(mixed $value, array $params, array $data): bool` — params[0] = other field name
- `different(mixed $value, array $params, array $data): bool` — params[0] = other field name
- `confirmed(mixed $value, mixed $confirmValue): bool` — compare value vs confirmation value directly
- `in(mixed $value, array $params): bool` — params = allowed values
- `notIn(mixed $value, array $params): bool` — params = disallowed values

---

## Class: Rules\UniqueRule
**File**: app/Helpers/Validation/Rules/UniqueRule.php
**Rules**: `unique`
**Depends on**: `DatabaseManager`, `Logger`

- `passes(mixed $value, array $params): bool`
  — params: `[table, column, ignoreValue?, ignoreColumn?]`
  — Returns `true` (pass) silently if DB unavailable (logs warning)

---

## Class: Rules\FileRules
**File**: app/Helpers/Validation/Rules/FileRules.php
**Rules**: `file`, `mimes`, `max_size`, `max_file_size`, `mime_types`
**Note**: The canonical Etapa 17 flow validates `UploadedFile` objects from `Request::file()`. Legacy aliases still accept `$_FILES[$field]`.

- `file(mixed $value): bool` — uploaded file exists and is valid
- `mimes(mixed $value, array $params): bool` — params = allowed extensions (`jpg,png,pdf`), validated against real MIME via `finfo_file()`
- `maxSize(mixed $value, array $params): bool` — params[0] = max KB
- `maxFileSize(mixed $fieldOrFile, array $params): bool` — legacy alias of `max_size`
- `mimeTypes(mixed $fieldOrFile, array $params): bool` — legacy alias that accepts allowed MIME types
