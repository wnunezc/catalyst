# Public surface contract

## Purpose

Catalyst separates application/demo surfaces from framework privileged. The
public surface contract is used by pages that a project implementer is expected
to customize for their own product, ERP, store or landing experience.

Canonical public/demo surfaces:

- `/`
- `/home`
- `/landing`
- `/store`
- `/dashboard`

These pages must not expose framework privileged navigation such as
Configuration, Workspaces, Operations, Users or DevTools. Privileged
features remain protected by their routes, middleware and permissions and use
the same canonical shell.

## Entry point configuration

The privileged role controls the initial behavior from Framework
Configuration:

- **Primary Entry Point** decides what `/` does.
- **Secondary Entry Point** is used when the primary entry point requires a
  post-login destination.

Current intent:

- `Home`, `Landing`, `Store` and `Dashboard` point to application/demo surfaces.
- `User-Access` makes `/` require authentication, then sends the user to the
  configured secondary entry point.
- `Setup` keeps framework setup/privileged behavior for development and operations.

The entry point resolver lives in:

```text
Repository/App/Services/ApplicationEntryService.php
app/Helpers/Config/AppEntryCatalog.php
```

## Document and shell rule

Public/demo pages use the same canonical document as every other complete
surface:

```text
boot-core/template/document.phtml
└── boot-core/template/shell.phtml
```

The public controller supplies explicit shell capabilities:

- `is_public_surface = true`;
- topbar enabled so `_topbar.phtml` renders public navigation;
- sidebar disabled;
- status bar and theme customizer enabled;
- public body, shell and content CSS classes.

These values do not select a Public layout or renderer. They only control
components inside the common shell. Public surfaces therefore retain theme
variables, module assets and the global runtime without exposing the
privileged sidebar.

New public pages should use `PublicPageController::renderPublicPage()` instead
of creating another document wrapper.

## Status bar rule

The status bar can appear on public surfaces, but its account menu must represent
real session state:

- guest users see login, register, email verification and password recovery;
- authenticated users see dashboard, MFA/settings entry and logout.

Do not render placeholder identities such as `Catalyst User` for guests.

## Dashboard rule

`/dashboard` is an application/demo dashboard entry point. Authenticated users
see the account dashboard inside the standard internal shell. Anonymous users
may see a centered guest gateway, but that gateway must still render through
`document.phtml` and `shell.phtml` using the shared `wrapper` / `content-page`
classes with topbar, sidebar and status bar disabled. It must not create an
`account-guest-shell`, `account-guest-content` or any other surface-specific
shell geometry.

Framework privileged remains under protected configuration/operation routes.

## CSP rule

Public surfaces must remain CSP-compatible:

- no inline `style="..."` attributes;
- no inline event handlers such as `onclick="..."`;
- no `href="javascript:..."` links;
- CSS belongs in external files;
- JavaScript belongs in external modules or existing nonce-aware bootstrap
  partials.
