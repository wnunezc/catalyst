# Catalyst PHP Framework

Catalyst is a PHP 8.4 MVC framework with a dual-space repository layout:
framework-owned runtime lives under `app/` and `Repository/Framework/`, while
application-owned modules live under `Repository/App/`.

Local runtime URL: `https://catalyst.dock/`

## Current Scope

- Traditional PHP request-response runtime for web requests.
- Dedicated CLI entry point at `php public/cli.php`.
- Framework modules currently present: `ApiPlatform`, `Audit`, `Auth`, `Automation`, `DevTools`, `Documents`, `Media`, `Notification`, `Operations`, `Roles`, `Settings`.
- ORM, QueryBuilder, CLI scaffolding, MFA, OAuth, CSP hardening and DevTools protection are already part of the validated runtime.
- Historical roadmap is closed and the old `ROADMAP.md` no longer belongs to the active repo surface.
- Runtime truth lives in the code plus the living operational docs: `AGENTS.md`, `docs/harness-context-map.md`, `08-AI-Context/catalyst.md` and `catalyst-reentry.md`.
- Development traceability is kept out of the repo hot path; use the workspace vault summaries only when history is explicitly needed.

## Implemented Platform Capabilities

- Runtime foundations: route contract, validation bridge, throttling profiles, config/bootstrap cache, sessions, cache stores, storage abstraction, secrets split and unified health/status.
- Async/runtime services: event bus, queue, scheduler and notification delivery over the same canonical stack.
- Framework governance: module, navigation and permission registries; scaffold/inspect/lint/harness/docs runtime tooling; reversible auth fixtures.
- Reusable admin/product layers: CRUD scaffold, `FormBuilder`, `DataGrid`, resource abilities, audit log, dynamic metadata, media library, document templates, workflows, automations, versioning and API platform.
- Operations control plane: `/operations` centralizes feature flags, plugins, deployment profiles and tenancy baseline without parallel subsystems.

## Security Overview

Catalyst does not claim absolute security, but the current validated runtime
already includes a concrete security baseline implemented in code and verified
with technical regressions plus real E2E flows.

- CSP + nonce enforcement through `SecurityHeadersMiddleware`, with
  `unsafe-inline` blocked for scripts and inline event handlers forbidden.
- Trusted server-to-browser rendering contract:
  `InlineJson` for safe inline JSON and `TrustedHtml` / `trusted-html` for
  framework-owned HTML fragments.
- CSRF protection on mutating web flows plus a canonical AJAX/form envelope.
- Auth hardening with password reset, remember-me invalidation, MFA/TOTP,
  email verification and login throttling profiles.
- RBAC + resource authorization through `PermissionRegistry`,
  `RoleMiddleware`, policies and ability subjects.
- Tenant-aware API token lifecycle with ownership enforcement, revocation and
  FK-backed integrity.
- Signed local persistence for file-cache and route-cache middleware payloads,
  reducing deserialization risk on disk-backed artifacts.
- Security-oriented CLI probes:
  `php public/cli.php security:check`,
  `php public/cli.php security:regression`,
  `php public/cli.php api-tokens:smoke`.
- Operational guardrails for sensitive surfaces such as `/setup`,
  `/operations` and `/test-features`.

Current closure reference for the validated remediation batch:

- `SecurityTest/VULNERABILITY_REGISTER.md`
- `SecurityTest/SECURITY_AUDIT_SUMMARY.md`
- `SecurityTest/SECURITY_REMEDIATION_EXECUTION_PLAN.md`

## Runtime Model

Catalyst is designed around the classic PHP execution model:

- `public/index.php` bootstraps the framework and runs `Kernel::getInstance()->bootstrap()->run()` for each web request.
- `public/cli.php` is a separate CLI entry point that registers commands and executes `CliKernel`.
- Singleton usage is accepted in this context because the HTTP runtime expects a fresh PHP lifecycle per request and a fresh CLI process per invocation.
- Catalyst is not designed yet as a general long-running application runtime for Swoole, RoadRunner, ReactPHP workers or similar persistent-process HTTP models.
- The optional WebSocket server is a separate CLI service built on Ratchet; it does not make the main HTTP stack long-running safe.

## Approved Composer Dependencies

Current runtime dependencies from `composer.json`:

- `phpmailer/phpmailer`
- `league/oauth2-client`
- `cboden/ratchet`
- `react/http`

Do not add more Composer dependencies without explicit approval.

## Quick Start

```bash
composer install
composer dump-autoload

php public/cli.php help
php public/cli.php status
php public/cli.php route:list --json
php public/cli.php config:show app --json
php public/cli.php security:check
```

## Repository Layout

```text
catalyst/
├── app/                      # Core runtime: Kernel, HTTP, routing, middleware, ORM, CLI
├── Repository/
│   ├── Framework/            # Framework-owned modules with routes, views, front assets and lang files
│   └── App/                  # Application-owned modules
├── boot-core/
│   ├── bin/                  # Bootstrap-owned scripts and support entry helpers
│   ├── cache/                # Bootstrap/runtime cache artifacts
│   ├── config/               # JSON/env configuration and local runtime files
│   ├── database/             # Migrations and SQL artifacts owned by bootstrap/runtime
│   ├── routes/               # Global route files (`global-routes.php`, optional `api.php`)
│   ├── storage/              # Logs, throttle state and runtime artifacts
│   └── template/             # Shared layouts, components, debug and error templates
├── public/                   # Web root and CLI entry points
│   ├── index.php             # Web entry point
│   ├── cli.php               # CLI entry point
│   └── assets/               # Published JS/CSS assets
├── docs/                     # Split technical documentation
├── STRUCTURE.md              # Technical inventory and quick component map
├── TERMINAL.md               # CLI reference
└── composer.json             # Autoload and dependency contract
```

## Route Loading

`Kernel::loadRoutes()` loads routes in this order:

1. `boot-core/routes/global-routes.php`
2. `boot-core/routes/api.php` if present
3. `Repository/Framework/*/routes.php`
4. `Repository/App/Surface/*/routes.php`

There is no manual central registration for module route files.

## Documentation Map

- [AGENTS.md](AGENTS.md) - project contract for agents
- [docs/harness-context-map.md](docs/harness-context-map.md) - canonical loading order for docs
- [STRUCTURE.md](STRUCTURE.md) - runtime inventory by namespace/component
- [TERMINAL.md](TERMINAL.md) - CLI command reference
- [docs/runtime-module-catalog.md](docs/runtime-module-catalog.md) - generated runtime catalog for modules, guards, assets, permissions and representative surfaces
- [docs/architecture.md](docs/architecture.md) - architecture and runtime model
- [docs/security-conventions.md](docs/security-conventions.md) - CSP, nonce, JS/view rules
- [docs/deployment.md](docs/deployment.md) - deployment and packaging safety
- [API.md](API.md) - subsystem index

## Notes

- Runtime truth is the code first, then the living docs reconciled to runtime.
- Historical process traceability belongs in the workspace vault, not in the framework hot path.
- If a document contradicts runtime behavior, fix the document rather than assuming the text is correct.
