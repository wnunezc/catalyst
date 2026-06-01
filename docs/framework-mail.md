# Catalyst\Framework\Mail

**Directory**: `app/Framework/Mail/`  
**Purpose**: Email composition and SMTP dispatch through PHPMailer.

## MailManager
**File**: `app/Framework/Mail/MailManager.php`

### Runtime Config Source
Effective SMTP config is resolved through `ConfigManager::entry('mail', 'mail1')`,
which merges sources in this order:
1. `/setup` JSON config in `boot-core/config/{environment}/mail.json` (`mail1`)
2. `.env`-derived defaults exposed by `ConfigManager::readDefaults()`

This means `/setup/mail` is the live runtime source of truth when the JSON entry
exists, while first boot still falls back to `.env` values without requiring a
manual merge inside `MailManager`.

DKIM- and humanitarian-related settings remain read directly from `.env`/runtime
constants because `/setup` does not manage those surfaces today.

### Sender Identity
- `from_address` comes from `mail_from_address`
- `from_name` comes from `mail_from_name`
- `MailManager` does **not** fall back from `mail_from_name` to the effective
  project name when calling `PHPMailer::setFrom()`
- the effective project name is only reused for the `Organization` header when
  available through `ConfigManager`

### Main Methods
- `init(array $override = []): self`
- `createMessage(): MailMessage`
- `send(MailMessage $message): bool`
- `testConnection(string $testRecipient = ''): array{success:bool,message:string}`
- `getConfig(): array`

## MailMessage
Fluent builder for sender, recipients, subject, HTML/text body, attachments and headers.

Live attachment support currently goes through:
- `attach(string $path, ?string $name = null, string $mimeType = '')`
- `attachInline(string $path, string $cid, ?string $name = null, string $mimeType = '')`

`MailMessage` stores attachment metadata as internal arrays that `MailManager`
passes directly to PHPMailer during `send()`.

## MailAttachment
**File**: `app/Framework/Mail/MailAttachment.php`

Residual compatibility DTO.

The current runtime does not hydrate or consume this class from either
`MailMessage` or `MailManager`. It remains in the tree as a legacy surface, but
it should not be described as part of the live mail-sending pipeline.

## Notes
- If `mail.json` is absent, `ConfigManager::entry()` falls back to `.env`-derived
  defaults for the `mail` section.
- `mail_from_name` falls back only to `MAIL_FROM_NAME` through `ConfigManager`
  defaults, not to the app/project name.
