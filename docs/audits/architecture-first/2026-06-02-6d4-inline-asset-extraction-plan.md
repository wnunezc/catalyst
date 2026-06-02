# Task 6D.4 - Inline Asset Extraction Plan

Date: 2026-06-02

Status: implemented and verified / included in the Task 6D.4 checkpoint.

## Scope

This task is planning-only. It classifies inline scripts/styles and defines a
safe extraction order while preserving CSP, nonce-backed transport payloads and
debug-only behavior.

## Inventory Source

Scanned `boot-core/template` and `Repository` templates/controllers for:

- `<script`
- `<style`
- inline `style=` attributes

Current snapshot:

- `<script` tags: 41
- `<style` tags: 6
- inline `style=` attributes in templates/views: 0

Main owners:

| Owner | Matches | Classification |
|---|---:|---|
| `boot-core/template/components/_body-scripts.phtml` | 12 | External asset includes; retain as declarative script registry. |
| `boot-core/template/layouts/account.phtml` | 7 | Layout script composition; convert only after account shell smoke. |
| `boot-core/template/components/_catalyst-init.phtml` | 2 | Framework boot logic and module work-asset include. |
| `boot-core/template/components/_status-bar.phtml` | 2 | Transport payload plus status-bar boot. |
| `boot-core/template/components/_head-assets.phtml` | 1 | Head bootstrap payload/FOUC guard. |
| `boot-core/template/components/_flash-messages.phtml` | 1 | JSON transport payload. |
| `boot-core/template/errors/*.phtml` | 8 | Error-page inline CSS/JS with nonce. |
| `boot-core/template/debug/*.tpl.phtml` | 2 | Debug-only dumper assets. |
| `Repository/Framework/DevTools/Controllers/InfraTestController.php` | 2 | DevTools deferred. |

## Classification

### Retain As Transport Payload

Keep these inline blocks until a documented alternative preserves exact payload
semantics:

- `script[type="application/json"]` flash payloads.
- `InlineJson` platform appearance and Catalyst init payloads.
- WebSocket/status-bar config payloads.

Rule: JSON transport blocks are not behavior. They remain acceptable when
encoded with `InlineJson`, wrapped with `TrustedHtml` where needed and consumed
by externalized JS.

### Extract Framework Boot Logic

Candidates for versioned assets:

- FOUC/theme bootstrap behavior from `_head-assets.phtml`.
- Catalyst notification/init behavior from `_catalyst-init.phtml`.
- Status-bar boot behavior from `_status-bar.phtml`.
- Account shell inline boot from `layouts/account.phtml`.

Extraction rule:

- Move behavior to `public/assets/js/catalyst/modules/*` or the owning
  `Repository/**/front/script.js`.
- Keep nonce-backed JSON payloads in templates.
- Gate with `quality:check`, `security:check`, `route:bootstrap-regression`
  and layout smoke pages.

### Extract Error Page Assets

Candidates:

- `boot-core/template/errors/404.phtml`
- `boot-core/template/errors/405.phtml`
- `boot-core/template/errors/handler_error.phtml`
- `boot-core/template/errors/handler_error_no.phtml`
- `boot-core/template/pages/route-test.phtml`

Extraction rule:

- Move shared error CSS to a versioned error stylesheet.
- Move small error interactions to an error module only if behavior is reused.
- Preserve nonce fallback until error pages are covered by route/error smokes.

### Keep Debug-Only Deferred

Candidates:

- `boot-core/template/debug/dumper-scripts.tpl.phtml`
- `boot-core/template/debug/dumper-styles.tpl.phtml`
- `Repository/Framework/DevTools/Controllers/InfraTestController.php`

Rule: debug and DevTools internals remain deferred. Do not mix with framework
shell extraction.

## Extraction Order

1. JSON transport audit: confirm every retained JSON block uses `InlineJson` or
   equivalent escaping and is consumed by external JS.
2. Shared shell boot: extract `_head-assets`, `_catalyst-init` and
   `_status-bar` behavior to versioned modules.
3. Account/layout scripts: extract account shell inline behavior after account
   layout smoke coverage.
4. Error pages: extract shared style and optional behavior after error smoke
   coverage exists.
5. Debug/DevTools: handle only in a separately approved DevTools batch.

## Acceptance Criteria For Runtime Extraction

- No template-owned behavior remains inline except approved JSON transport or
  explicitly deferred debug output.
- `security:check` reports no hard failures.
- `docs/runtime-inventory.md` is refreshed if scripts are added or removed.
- CSP nonces remain available for retained transport blocks.
- User explicitly approves any DevTools extraction batch.
