# Catalyst\Helpers\Log

## Class: Logger
**File**: `app/Helpers/Log/Logger.php`  
**Namespace**: `Catalyst\Helpers\Log`  
**Type**: Class  
**Pattern**: Singleton (`SingletonTrait`)

## Purpose
Framework logger with configurable minimum level, optional inline display, request classification and channel-based file output.

## Effective Runtime Config
The kernel configures the logger from effective runtime config:
- `logging.log_channel`
- `logging.log_level`
- `logging.display_logs`
- `logging.log_rotation_enabled`
- `logging.log_max_file_size_mb`
- `logging.log_max_rotated_files`

The environment setup writer and logger configurator both enforce safety caps:
- max file size: `1-50` MB
- max rotated files: `1-10` files per log

The development profile defaults to `daily` channel, `warning` level, rotation enabled, `2` MB per file and `5` rotated files. This avoids high-volume `info`/`debug` writes filling a local or production disk unexpectedly.

Accepted channels:
- `single`
- `daily`
- `stderr`

Accepted log levels are normalized case-insensitively against:
- `DEBUG`
- `INFO`
- `NOTICE`
- `WARNING`
- `ERROR`
- `CRITICAL`
- `ALERT`
- `EMERGENCY`

## Main Methods
- `configure(array $config): self`
- `log(string $level, string $message, array $context = []): void`
- `emergency/alert/critical/error/warning/notice/info/debug(...)`
- `email(string $to, string $subject, array $context = []): bool`
- `mail(string $event, string $message, array $context): void`
- `user(string $event, string $message, array $context = []): void`

## Output Behaviour
- `single` writes category files like `errors/errors.log`
- `daily` writes category files like `errors/YYYY-MM-DD.log`
- `stderr` writes to `php://stderr`

Log categories:
- `errors/` for warning and above
- `events/` for notice
- `info/` for info and debug
- `email/` for `email()`
