# Catalyst\Helpers\Validation

## Purpose

Document validation runner, parser, validator and rule families.

## Runtime Owners

| Concern | Owner |
|---|---|
| Normalizes string or array validation definitions into rule-and-parameter tuples. | `Catalyst\Helpers\Validation\RuleParser` |
| Validates relationships between fields and membership in allowed value sets. | `Catalyst\Helpers\Validation\Rules\ComparisonRules` |
| Resolves uploaded files and validates file presence, size, extension and MIME type. | `Catalyst\Helpers\Validation\Rules\FileRules` |
| Validates common scalar formats such as email, URL, date and boolean values. | `Catalyst\Helpers\Validation\Rules\FormatRules` |
| Validates numeric types and configured numeric ranges. | `Catalyst\Helpers\Validation\Rules\NumericRules` |
| Validates required values, string lengths, character sets and patterns. | `Catalyst\Helpers\Validation\Rules\StringRules` |
| Checks uniqueness constraints through the database query builder. | `Catalyst\Helpers\Validation\Rules\UniqueRule` |
| Applies parsed validation rules to input fields and collects localized errors. | `Catalyst\Helpers\Validation\ValidationRunner` |
| Exposes lazy validation results and field-level error collections to callers. | `Catalyst\Helpers\Validation\Validator` |

## Current Behavior

This file is regenerated from current PHP docblocks and the runtime inventory scope for `Catalyst\Helpers\Validation`. It intentionally replaces stale historical API notes with the classes and methods that exist in code now.

## API From Docblocks

### `Catalyst\Helpers\Validation\RuleParser`

- File: `app/Helpers/Validation/RuleParser.php`
- Kind: `class`
- Summary: RuleParser — parses rule definitions into a normalized structure.
- Responsibility: Normalizes string or array validation definitions into rule-and-parameter tuples.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `parse()` | `public` | Parse a rule definition into normalized tuples. | Parse a rule definition into normalized tuples. |

### `Catalyst\Helpers\Validation\Rules\ComparisonRules`

- File: `app/Helpers/Validation/Rules/ComparisonRules.php`
- Kind: `class`
- Summary: ComparisonRules — validation rules comparing fields or value sets.
- Responsibility: Validates relationships between fields and membership in allowed value sets.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `same()` | `public` | The field must match another field in the data set. Usage: same:other_field | n/a |
| `different()` | `public` | The field must differ from another field in the data set. Usage: different:other_field | n/a |
| `confirmed()` | `public` | The field must match its confirmation counterpart ({field}_confirmation). Usage: confirmed  (on field 'password' → checks 'password_confirmation') | n/a |
| `in()` | `public` | The field must be one of the listed values. Usage: in:admin,user,moderator | n/a |
| `notIn()` | `public` | The field must NOT be one of the listed values. Usage: not_in:banned,suspended | n/a |

### `Catalyst\Helpers\Validation\Rules\FileRules`

- File: `app/Helpers/Validation/Rules/FileRules.php`
- Kind: `class`
- Summary: FileRules — validation rules for uploaded files ($_FILES).
- Responsibility: Resolves uploaded files and validates file presence, size, extension and MIME type.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `file()` | `public` | Determines whether the value resolves to a valid uploaded file. | n/a |
| `mimes()` | `public` | The uploaded file must have one of the allowed extensions and a matching MIME type detected from the file contents. Usage: mimes:jpg,jpeg,png,pdf | n/a |
| `maxSize()` | `public` | The uploaded file must not exceed $params[0] kilobytes. Usage: max_size:2048 | n/a |
| `maxFileSize()` | `public` | The uploaded file must not exceed $params[0] kilobytes. Usage: max_file_size:2048  (2 MB) | n/a |
| `mimeTypes()` | `public` | The uploaded file must be one of the listed MIME types. Usage: mime_types:image/jpeg,image/png,image/gif | n/a |
| `resolveUploadedFile()` | `private` | Resolves an uploaded-file instance from a value or request field. | n/a |

### `Catalyst\Helpers\Validation\Rules\FormatRules`

- File: `app/Helpers/Validation/Rules/FormatRules.php`
- Kind: `class`
- Summary: FormatRules — validation rules for format-based fields.
- Responsibility: Validates common scalar formats such as email, URL, date and boolean values.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `email()` | `public` | The field must be a valid email address. | n/a |
| `url()` | `public` | The field must be a valid URL. | n/a |
| `date()` | `public` | The field must be a parseable date string. | n/a |
| `boolean()` | `public` | The field must represent a boolean value. | n/a |

### `Catalyst\Helpers\Validation\Rules\NumericRules`

- File: `app/Helpers/Validation/Rules/NumericRules.php`
- Kind: `class`
- Summary: NumericRules — validation rules for numeric fields.
- Responsibility: Validates numeric types and configured numeric ranges.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `numeric()` | `public` | The field must be a numeric value (int or float). | n/a |
| `integer()` | `public` | The field must be a valid integer. | n/a |
| `minValue()` | `public` | The field must be at least $params[0] (numeric comparison). | n/a |
| `maxValue()` | `public` | The field must not exceed $params[0] (numeric comparison). | n/a |
| `between()` | `public` | The field must be between $params[0] and $params[1] inclusive. | n/a |

### `Catalyst\Helpers\Validation\Rules\StringRules`

- File: `app/Helpers/Validation/Rules/StringRules.php`
- Kind: `class`
- Summary: StringRules — validation rules for string fields.
- Responsibility: Validates required values, string lengths, character sets and patterns.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `required()` | `public` | The field must not be empty. | n/a |
| `min()` | `public` | The field must have at least $params[0] characters. | n/a |
| `max()` | `public` | The field may not exceed $params[0] characters. | n/a |
| `alpha()` | `public` | The field may only contain letters (a–z, A–Z). | n/a |
| `alphaNum()` | `public` | The field may only contain letters and numbers. | n/a |
| `regex()` | `public` | The field must match the given regular expression. | n/a |

### `Catalyst\Helpers\Validation\Rules\UniqueRule`

- File: `app/Helpers/Validation/Rules/UniqueRule.php`
- Kind: `class`
- Summary: UniqueRule — validates that a value does not already exist in a DB column.
- Responsibility: Checks uniqueness constraints through the database query builder.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `passes()` | `public` | Check that $value does not exist in the specified table/column. | n/a |

### `Catalyst\Helpers\Validation\ValidationRunner`

- File: `app/Helpers/Validation/ValidationRunner.php`
- Kind: `class`
- Summary: ValidationRunner — internal engine that applies rules to data.
- Responsibility: Applies parsed validation rules to input fields and collects localized errors.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `run()` | `public` | Run all rules against the data and return field-level errors. | Run all rules against the data and return field-level errors. |
| `applyRule()` | `private` | Dispatch a single rule and return the error message, or null on pass. | Dispatch a single rule and return the error message, or null on pass. |
| `dataGet()` | `private` | Retrieve a value from a nested array using dot notation. E.g. 'address.city' → $data['address']['city']. | Retrieve a value from a nested array using dot notation. E.g. 'address.city' → $data['address']['city']. |
| `hasRule()` | `private` | Check whether a specific rule name is present in the parsed rules list. | Check whether a specific rule name is present in the parsed rules list. |
| `isEmpty()` | `private` | Determine whether a value is considered empty. | Determine whether a value is considered empty. |

### `Catalyst\Helpers\Validation\Validator`

- File: `app/Helpers/Validation/Validator.php`
- Kind: `class`
- Summary: Validator — public API for the Catalyst validation system.
- Responsibility: Exposes lazy validation results and field-level error collections to callers.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `__construct()` | `public` | Initializes the object with the collaborators or state required for its responsibility. | Initializes the object with the collaborators or state required for its responsibility. |
| `fails()` | `public` | Determine whether validation fails. Runs validation on first call; subsequent calls use the cached result. | Determine whether validation fails. Runs validation on first call; subsequent calls use the cached result. |
| `passes()` | `public` | Determine whether validation passes. | Determine whether validation passes. |
| `errors()` | `public` | Get all field-level errors. | Get all field-level errors. |
| `firstErrors()` | `public` | Get the first error message per field. | Exposes the first validation message for each field so forms can show concise feedback. |
| `getErrorsForJson()` | `public` | Alias for errors() — compatible with jsonValidationError() format. | Alias for errors() — compatible with jsonValidationError() format. |
| `runOnce()` | `private` | Run validation exactly once; cache the result. | Run validation exactly once; cache the result. |

## Operational Notes

When PHP symbols or method contracts in this namespace change, refresh this document from docblocks and run `php public/cli.php docs:inventory --json`.

## Related Documentation

- `docs/runtime-inventory.md`
- `docs/runtime-module-catalog.md`
- `docs/harness-context-map.md`
