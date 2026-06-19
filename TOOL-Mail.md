# Catalyst Framework Mail Template Tool

## Purpose

Define the implementation and extension contract for the framework-owned email
template manager exposed at `/workspaces/mail-templates`.

This capability is owned entirely by Catalyst Framework. Applications consume
templates by stable keys and payloads; they do not own a parallel mail template
directory, renderer, translation pipeline or delivery runtime.

## Canonical Ownership

All editable and distributed mail resources live under:

```text
Repository/Framework/Mail/
+-- system/
|   +-- templates/{domain}/{template}/
|   |   +-- template.json
|   |   +-- layout.html
|   |   +-- text.txt
|   +-- lang/{locale}/{catalog}.json
|   +-- assets/
+-- managed/
    +-- templates/{domain}/{template}/
    |   +-- template.json
    |   +-- layout.html
    |   +-- text.txt
    +-- lang/{locale}/{catalog}.json
    +-- assets/
```

- `system` contains release-owned defaults and is read-only from the web tool.
- `managed` contains versionable overrides and new templates created by the
  privileged Workspaces tool.
- `Repository/App/Mail` is not a supported extension point.
- No database table stores template source, layouts, translations or assets.
- Public asset copies are generated under
  `public/assets/work/framework-mail/{system|managed}` and are never edited as
  source.

## Resolution Contract

The logical key `users.enrollment_onboarding` maps to the filesystem directory
`users/enrollment-onboarding`.

Template structure resolves in this order:

1. `managed/templates/{domain}/{template}`
2. `system/templates/{domain}/{template}`

Translations use the existing `Translator` and locale policy. The effective
order is:

1. managed catalog for the requested locale;
2. system catalog for the requested locale;
3. managed catalog for the configured default locale;
4. system catalog for the configured default locale.

Editing a system template creates a managed override. Removing that override
restores the system template. The web tool must never mutate or delete system
source.

## Manifest Contract

Each template directory contains `template.json`:

```json
{
  "key": "users.enrollment_onboarding",
  "name": "User enrollment onboarding",
  "translation_catalog": "mail_users_enrollment_onboarding",
  "translation_namespace": "users.enrollment_onboarding",
  "html_template": "layout.html",
  "text_template": "text.txt",
  "required_placeholders": [
    "user_name",
    "action_url",
    "brand_name"
  ],
  "sample_payload": {
    "user_name": "Example User",
    "action_url": "https://example.test/account/setup",
    "brand_name": "Catalyst"
  }
}
```

The translation catalog owns subject and localized copy. HTML and text files
own message structure. A template may use:

- `{{ t:path.to.key }}` for localized catalog values;
- `{{ payload_key }}` for escaped runtime payload values;
- `{{ asset:name }}` for registered framework-mail assets;
- `{{ brand_name }}` and `{{ brand_logo_url }}` for global platform branding.

Application code sends a message through the stable public service:

```php
$outboundEmail->sendTemplate(
    'users.enrollment_onboarding',
    $recipientEmail,
    $recipientName,
    $payload,
    $recipientLocale
);
```

Callers never pass physical template or translation paths.

## Localization Rules

- Mail catalogs must use `lang/{locale}/{catalog}.json`, matching the existing
  Catalyst translation loader.
- Locale Tools must discover, report, initialize and synchronize both system
  and managed mail catalogs.
- English remains the technical base catalog. The runtime default and
  supported locales remain governed by Locale Tools.
- The mail tool does not implement its own locale registry, fallback list or
  placeholder syntax for translated strings.
- Catalog replacements use the existing `:placeholder` Translator contract.

## Security Rules

- Mutations are restricted to the resolved `managed` root.
- Template keys accept lowercase alphanumeric segments separated by `.`, `_`
  or `-`; traversal, absolute paths and path separators are rejected.
- Resolved paths and symlinks must remain inside their allowed root.
- Writes are atomic and rollback all touched files on partial failure.
- HTML rejects scripts, frames, embedded executable objects, `on*` attributes,
  `javascript:` URLs, `file:` URLs and local filesystem references.
- Preview HTML renders in a sandboxed iframe without script execution.
- Safe preview submits through the canonical `data-catalyst="form"` contract.
  The POST action stores a one-time preview state in the authenticated session
  and returns JSON with a redirect target; it must not return a full HTML page
  directly to the Catalyst form runtime.
- Required placeholders and referenced translation keys must validate before
  persistence, preview or delivery.
- Asset uploads accept detected PNG, JPEG, WebP or GIF content up to 2 MB.
  SVG, executable content, unsafe filenames and base64 source blobs are
  rejected.
- Referenced assets cannot be deleted until dependencies are removed.
- Every mutation and test delivery requires authentication, the canonical
  Workspaces permission, CSRF protection and privileged throttling.
- Delivery logs contain template key, locale, outcome and recipient hash only.
  Payload values, tokens, credentials and complete recipient addresses are
  never logged.

## UI and Runtime Rules

- The canonical surface is `/workspaces/mail-templates`.
- Navigation appears immediately after Locale Tools.
- The permission is `manage-workspaces-mail-templates`.
- Bootstrap/Inspinia, DataGrid, forms, modals, activity overlay, toasts and the
  shared Catalyst frontend runtime remain authoritative.
- Do not create a second shell, renderer, translator, notification runtime,
  upload runtime or surface-specific framework substitute.
- Do not modify `/demo-ui` to implement or demonstrate this tool.

## Verification Contract

Changes to this capability require focused unit tests for resolution,
translation, validation, atomic writes, assets and delivery failure, plus
Playwright coverage for the visible Workspaces workflow and at least one real
consumer such as `/users/enroll`.
