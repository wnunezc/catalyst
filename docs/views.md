# Views Index

This file is a thin navigation index for Catalyst's view layer.

It exists to satisfy the generic Phase 4 target without collapsing the existing split view/security/runtime docs into one file again.

## Canonical references

- View engine, template resolution, layouts, and exceptions: `docs/framework-view.md`
- Runtime i18n integration for templates: `docs/helpers-i18n.md`
- MVC placement and module co-location model: `docs/architecture.md`
- Output escaping, CSP, nonce, `data-*`, and frontend conventions: `docs/security-conventions.md`
- Layout inventory and template locations: `STRUCTURE.md`

## Current template contract

Catalyst now uses a token-aware `.phtml` contract for declarative HTML views.

Core rules:

- `.phtml` = HTML only, no `<?php` or `<?=`
- module templates live under `Views/pages|partials|components`
- companion scope preparation = `Views/scope/.../*.php` or `boot-core/template/scope/.../*.php`
- renderer = `ViewTokenRenderer`
- trusted raw HTML = `TrustedHtml`

Canonical token forms:

- escaped variable:
  - `{{ page_title }}`
- translation:
  - `{{ t:operations.appearance.hero_eyebrow }}`
- translation with replacements:
  - `{{ t:messages.welcome name=user_name }}`
- trusted raw:
  - `{{{ csrfField }}}`
- conditional:
  - `{{#if has_items}} ... {{else}} ... {{/if}}`
- iterable:
  - `{{#each cards}} ... {{/each}}`
- partial:
  - `{{> "./partials/_card" }}`

Default safety model:

- `{{ ... }}` escapes by default
- `{{ t:... }}` translates and escapes
- `{{{ ... }}}` should only receive `TrustedHtml`

## Scope note

`docs/framework-view.md` is the canonical deep dive for the framework view engine.
This file is only the broad entry point for readers expecting a generic `views.md`.

## Usage note

Use this file when a task starts from the broad label `views`.
