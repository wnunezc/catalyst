# Catalyst\Framework\Mail

## Purpose

Document mail message, template, DKIM and attachment primitives.

## Runtime Owners

| Concern | Owner |
|---|---|
| Generate DKIM key material and DNS records for mail signing. | `Catalyst\Framework\Mail\DkimGenerator` |
| Preserve typed attachment metadata for compatibility callers. | `Catalyst\Framework\Mail\MailAttachment` |
| Configure PHPMailer and deliver framework mail messages. | `Catalyst\Framework\Mail\MailManager` |
| Hold and validate per-message mail state for PHPMailer delivery. | `Catalyst\Framework\Mail\MailMessage` |
| Render mail templates from the configured email template root. | `Catalyst\Framework\Mail\MailTemplate` |
| Resolve JSON email templates with locale fallback and placeholder validation. | `Catalyst\Framework\Mail\EmailTemplateManager` |
| Render and deliver outbound template emails with safe delivery logging. | `Catalyst\Framework\Mail\OutboundEmailService` |

## Current Behavior

This file is regenerated from current PHP docblocks and the runtime inventory scope for `Catalyst\Framework\Mail`. It intentionally replaces stale historical API notes with the classes and methods that exist in code now.

## Framework Mail Template Tool

The privileged manager is available at `/workspaces/mail-templates` to users
with `manage-workspaces-mail-templates`. It lists framework defaults, creates
versionable managed templates, edits localized copy and message structure,
previews HTML and text, manages images and sends test messages.

Safe preview uses the same foreground form contract as the rest of the
framework UI. The POST action renders the template, stores a one-time preview
state in the authenticated session, and returns JSON with a redirect target so
the activity overlay and toaster lifecycle remain consistent. It does not
return a full HTML page directly to a `data-catalyst="form"` request.

Framework outbound email should go through
`OutboundEmailService::sendTemplate()`. Applications identify templates by
logical key and never resolve physical paths:

```php
$result = $outboundEmail->sendTemplate(
    'users.enrollment_onboarding',
    $recipientEmail,
    $recipientName,
    [
        'user_name' => $recipientName,
        'action_url' => $setupUrl,
    ],
    $recipientLocale
);
```

### Source Layout

All template resources remain framework-owned:

```text
Repository/Framework/Mail/
+-- system/
|   +-- templates/{domain}/{template}/
|   +-- lang/{locale}/{catalog}.json
|   +-- assets/
+-- managed/
    +-- templates/{domain}/{template}/
    +-- lang/{locale}/{catalog}.json
    +-- assets/
```

`system` contains immutable defaults shipped by Catalyst. `managed` contains
web-managed overrides and custom templates that remain visible to Git. Editing
a system template creates a managed override; deleting that override restores
the system behavior. `Repository/App/Mail` is not used.

Each template directory contains:

- `template.json`: key, display name, translation catalog and namespace,
  structure filenames, required placeholders and sample payload.
- `layout.html`: HTML structure.
- `text.txt`: plain-text structure.

### Localized Content

Mail uses the standard Catalyst `Translator`. A manifest declares a catalog
such as `mail_users_enrollment_onboarding` and a namespace such as
`users.enrollment_onboarding`. The corresponding catalogs are regular
`lang/{locale}/{catalog}.json` files and therefore participate in Locale Tools
coverage, locale initialization, synchronization and configured fallback.

Example catalog:

```json
{
  "users": {
    "enrollment_onboarding": {
      "subject": "Welcome to :brand_name",
      "heading": "Hello, :user_name",
      "action_label": "Configure my password"
    }
  }
}
```

HTML and text structures use `{{ t:users.enrollment_onboarding.heading }}` for
localized copy and `{{ action_url }}` for escaped runtime values. Translator
replacements keep the existing `:name` syntax.

### Layouts, Branding and Images

Use `{{ brand_name }}` and `{{ brand_logo_url }}` to consume Platform
Appearance branding. Additional images are uploaded through the tool and stored
under `Repository/Framework/Mail/managed/assets`. Their generated public copies
live under `public/assets/work/framework-mail/managed`.

Template source must reference registered assets using `{{ asset:name }}`.
Images are validated by detected MIME and are limited to PNG, JPEG, WebP or GIF
files up to 2 MB. An image cannot be removed while a template references it.

### Creating and Personalizing Templates

1. Open Workspaces, then Mail Templates after Locale Tools.
2. Create a managed template or open a system template and choose Customize.
3. Declare its stable key, translation catalog, namespace, required variables
   and sample payload.
4. Edit HTML and text structure.
5. Select each supported locale and edit its catalog copy.
6. Validate and preview both renderings.
7. Optionally upload images and send a test message.
8. Save the managed source for version control.

To return a customized framework template to its distributed default, remove
the managed override from its detail screen. The system source is never
modified.

### Existing Consumers

Registration verification and privileged user enrollment use stable system
keys. Their copy, layout and images can be customized in Workspaces without
changing their controllers. Delivery failures return an administrative status
without exposing setup tokens, temporary credentials or transport secrets.

The complete implementation rules live in `TOOL-Mail.md`.

## API From Docblocks

### `Catalyst\Framework\Mail\DkimGenerator`

- File: `app/Framework/Mail/DkimGenerator.php`
- Kind: `class`
- Summary: RSA DKIM key-pair generator for mail authentication.
- Responsibility: Generate DKIM key material and DNS records for mail signing.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `generateKeys()` | `public` | Generate an RSA key pair, persist it and return DKIM configuration data. selector: string, domain: string, privateKeyPath: string, publicKeyPath: string, dnsRecord: string, storageDir: string }. | Generate an RSA key pair, persist it and return DKIM configuration data. selector: string, domain: string, privateKeyPath: string, publicKeyPath: string, dnsRecord: string, storageDir: string }. |
| `resolveStorageDir()` | `private` | Resolve the DKIM storage directory for a domain and connection. | Resolve the DKIM storage directory for a domain and connection. |
| `ensureDirectory()` | `private` | Ensure the DKIM storage directory exists. | Ensure the DKIM storage directory exists. |

### `Catalyst\Framework\Mail\MailAttachment`

- File: `app/Framework/Mail/MailAttachment.php`
- Kind: `class`
- Summary: Compatibility DTO for mail attachment metadata.
- Responsibility: Preserve typed attachment metadata for compatibility callers.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `__construct()` | `public` | Initializes the object with the collaborators or state required for its responsibility. | Initializes the object with the collaborators or state required for its responsibility. |

### `Catalyst\Framework\Mail\MailManager`

- File: `app/Framework/Mail/MailManager.php`
- Kind: `class`
- Summary: Singleton SMTP gateway for framework mail delivery.
- Responsibility: Configure PHPMailer and deliver framework mail messages.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `init()` | `public` | Load and validate SMTP configuration once for the manager. | Load and validate SMTP configuration once for the manager. |
| `createMessage()` | `public` | Create a new message builder bound to this manager. | Create a new message builder bound to this manager. |
| `send()` | `public` | Send a prepared message through a configured PHPMailer instance. | Send a prepared message through a configured PHPMailer instance. |
| `testConnection()` | `public` | Send a probe message and report whether SMTP delivery succeeds. | Send a probe message and report whether SMTP delivery succeeds. |
| `getConfig()` | `public` | Return resolved mail configuration with sensitive values redacted. | Return resolved mail configuration with sensitive values redacted. |
| `htmlToText()` | `protected` | Convert HTML mail content into a plain-text alternative. | Convert HTML mail content into a plain-text alternative. |
| `createMailer()` | `protected` | Create a PHPMailer instance with exceptions enabled. | Create a PHPMailer instance with exceptions enabled. |
| `configureMailer()` | `protected` | Apply resolved SMTP, transport and sender settings to PHPMailer. | Apply resolved SMTP, transport and sender settings to PHPMailer. |
| `configureDkim()` | `protected` | Apply DKIM signing settings when enabled and the key is readable. | Apply DKIM signing settings when enabled and the key is readable. |
| `ensureInitialized()` | `protected` | Initialize the manager on first use. | Initialize the manager on first use. |

### `Catalyst\Framework\Mail\MailMessage`

- File: `app/Framework/Mail/MailMessage.php`
- Kind: `class`
- Summary: Fluent builder for one outgoing email message.
- Responsibility: Hold and validate per-message mail state for PHPMailer delivery.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `__construct()` | `public` | Initializes the object with the collaborators or state required for its responsibility. | Initializes the object with the collaborators or state required for its responsibility. |
| `from()` | `public` | Override the sender identity for this message. | Override the sender identity for this message. |
| `to()` | `public` | Add primary recipients to the message. | Add primary recipients to the message. |
| `cc()` | `public` | Add carbon-copy recipients to the message. | Add carbon-copy recipients to the message. |
| `bcc()` | `public` | Add blind-carbon-copy recipients to the message. | Add blind-carbon-copy recipients to the message. |
| `replyTo()` | `public` | Set the reply-to identity for the message. | Stores the reply-to address and display name used by the outgoing message. |
| `subject()` | `public` | Set the message subject line. | Stores the subject line used when the mail message is sent. |
| `html()` | `public` | Set the HTML body directly. | Stores trusted HTML body content supplied directly to the message builder. |
| `text()` | `public` | Set the plain-text body directly. | Stores plain-text body content supplied directly to the message builder. |
| `body()` | `public` | Set body content and derive text fallback when HTML is detected. | Set body content and derive text fallback when HTML is detected. |
| `template()` | `public` | Populate the message body from a named template or explicit template path. | Populate the message body from a named template or explicit template path. |
| `attach()` | `public` | Attach a regular file to the message. | Attach a regular file to the message. |
| `attachInline()` | `public` | Attach an inline file with a content ID. | Attach an inline file with a content ID. |
| `bulk()` | `public` | Mark the message for bulk-mail headers during delivery. | Mark the message for bulk-mail headers during delivery. |
| `header()` | `public` | Add a custom mail header to the message. | Add a custom mail header to the message. |
| `send()` | `public` | Validate and dispatch the message through the bound manager. | Validate and dispatch the message through the bound manager. |
| `getFrom()` | `public` | Return the message sender override. | Return the message sender override. |
| `getTo()` | `public` | Return primary recipients. | Return primary recipients. |
| `getCc()` | `public` | Return carbon-copy recipients. | Return carbon-copy recipients. |
| `getBcc()` | `public` | Return blind-carbon-copy recipients. | Return blind-carbon-copy recipients. |
| `getReplyTo()` | `public` | Return the reply-to identity. | Return the reply-to identity. |
| `getSubject()` | `public` | Return the subject line. | Return the subject line. |
| `getHtmlBody()` | `public` | Return the HTML body. | Return the HTML body. |
| `getTextBody()` | `public` | Return the plain-text body. | Return the plain-text body. |
| `getAttachments()` | `public` | Return regular and inline attachments. | Return regular and inline attachments. |
| `getHeaders()` | `public` | Return custom message headers. | Return custom message headers. |
| `isHtml()` | `public` | Determine whether the message has an HTML body. | Determine whether the message has an HTML body. |
| `isBulk()` | `public` | Determine whether the message should use bulk-mail headers. | Determine whether the message should use bulk-mail headers. |
| `validate()` | `protected` | Validate required recipients, subject and body before sending. | Validate required recipients, subject and body before sending. |
| `validateEmailFormat()` | `protected` | Validate address syntax and block known non-deliverable domains. | Validate address syntax and block known non-deliverable domains. |
| `addRecipient()` | `protected` | Append a validated recipient entry to one recipient list. | Append a validated recipient entry to one recipient list. |

### `Catalyst\Framework\Mail\MailTemplate`

- File: `app/Framework/Mail/MailTemplate.php`
- Kind: `class`
- Summary: Email template renderer for framework mail bodies.
- Responsibility: Render mail templates from the configured email template root.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `__construct()` | `public` | Initializes the object with the collaborators or state required for its responsibility. | Initializes the object with the collaborators or state required for its responsibility. |
| `render()` | `public` | Render a named template into HTML and/or text mail bodies. | Render a named template into HTML and/or text mail bodies. |
| `renderFromPath()` | `public` | Render a template file from an explicit filesystem path. | Render a template file from an explicit filesystem path. |
| `setBasePath()` | `public` | Replace the base directory used to resolve named templates. | Replace the base directory used to resolve named templates. |
| `processTemplate()` | `protected` | Include a template file with extracted variables and capture its output. | Include a template file with extracted variables and capture its output. |
| `getTemplatePath()` | `protected` | Resolve the preferred filesystem path for a named template variant. | Resolve the preferred filesystem path for a named template variant. |

## Operational Notes

When PHP symbols or method contracts in this namespace change, refresh this document from docblocks and run `php public/cli.php docs:inventory --json`.

## Related Documentation

- `docs/runtime-inventory.md`
- `docs/runtime-module-catalog.md`
- `docs/harness-context-map.md`
