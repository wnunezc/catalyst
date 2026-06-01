# Public surface contract

## Purpose

Catalyst separates application/demo surfaces from framework administration. The
public surface contract is used by pages that a project implementer is expected
to customize for their own product, ERP, store or landing experience.

Canonical public/demo surfaces:

- `/`
- `/home`
- `/landing`
- `/store`
- `/dashboard`

These pages must not expose framework administration navigation such as
Configuration, Workspaces, Operations, Users or DevTools. Administrative features
remain in the Admin Shell and are reached only through protected framework routes.

## Entry point configuration

The administrator controls the initial behavior from Framework Settings:

- **Primary Entry Point** decides what `/` does.
- **Secondary Entry Point** is used when the primary entry point requires a
  post-login destination.

Current intent:

- `Home`, `Landing`, `Store` and `Dashboard` point to application/demo surfaces.
- `User-Access` makes `/` require authentication, then sends the user to the
  configured secondary entry point.
- `Setup` keeps framework setup/admin behavior for development and operations.

The entry point resolver lives in:

```text
Repository/App/Services/ApplicationEntryService.php
app/Helpers/Config/AppEntryCatalog.php
```

## Layout rule

Public/demo pages render through:

```text
boot-core/template/layouts/public.phtml
```

The Public Shell includes:

- a compact public navigation bar;
- the selected Catalyst/Inspinia theme variables;
- module-local frontend assets;
- the global status bar.

The Public Shell excludes:

- the Admin Shell left menu;
- framework administration topbar actions;
- framework administration customizer controls;
- direct links to admin-only configuration routes.

## Status bar rule

The status bar can appear on public surfaces, but its account menu must represent
real session state:

- guest users see login, register, email verification and password recovery;
- authenticated users see dashboard, MFA/settings entry and logout.

Do not render placeholder identities such as `Catalyst User` for guests.

## Dashboard rule

`/dashboard` is an authenticated application/demo dashboard. It is not the
framework administration console. It should demonstrate ERP-style cards,
activity, metrics and workflows that implementers can replace.

Framework administration remains under protected configuration/operation routes.

## CSP rule

Public surfaces must remain CSP-compatible:

- no inline `style="..."` attributes;
- no inline event handlers such as `onclick="..."`;
- no `href="javascript:..."` links;
- CSS belongs in external files;
- JavaScript belongs in external modules or existing nonce-aware bootstrap
  partials.
