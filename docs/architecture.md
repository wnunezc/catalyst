# Catalyst Framework - Architecture

## MVC Pattern

Catalyst follows the **Model-View-Controller (MVC)** architectural pattern:

**Models** - Data and business logic layer
- Location: inside each module (`Repository/Framework/{Module}/Models/` or `Repository/App/Surface/{Module}/Models/`)
- Purpose: Database interactions, business entities, data validation
- Technical debt active:
  - extendable domain entities should converge toward `app/Entities/`
  - current module-local `Repository/*/Models/` placement remains live runtime, but is now considered migration debt to evaluate before expanding it further

**Views** - Presentation layer
- Location (Framework core templates): `boot-core/template/` (layouts, components, error pages)
- Location (Modules): co-located per module in `Repository/Framework/{Module}/Views/` and `Repository/App/Surface/{Module}/Views/`, with `pages/`, `partials/`, `components/` and mirrored `scope/` companions
- Purpose: HTML templates, rendering data (NO business logic)

**Controllers** - Request handling layer
- Location (Framework base): `app/Framework/Controllers/Controller.php` (abstract base)
- Location (Modules): `Repository/Framework/{Module}/Controllers/` and `Repository/App/Surface/{Module}/Controllers/`
- Purpose: Handle HTTP requests, orchestrate Models and Views

## Dual-Space Architecture

The framework maintains strict separation between framework and application code:

1. **Framework Core** (`app/Framework/`, `app/Helpers/`)
   - Core framework components — **DO NOT MODIFY**
   - Updates applied through framework versions
   - Reserved extension point for shared entities: `app/Entities/`

2. **Repository/Framework** (`Repository/Framework/`)
   - Framework modules with screens (Auth, Roles, Settings, DevTools, Notification)
   - Each module is self-contained: Controllers/, Views/ structured subtree, front/, lang/, routes.php
   - Namespace: `Catalyst\Repository\{Module}\`
   - Routes loaded automatically by Kernel glob

3. **Repository/App** (`Repository/App/`)
   - Developer modules — **SAFE TO MODIFY**
   - Same structure as Repository/Framework/ modules
   - Namespace: `App\Surface\{Module}\`

The async foundations introduced later still respect this split:

- event bus, queue and scheduler live in `app/Framework/`
- shared envelopes / task definitions live in `app/Entities/`
- business producers remain free to emit events or queue jobs from app modules

### Active structural decisions

- Bootstrap-owned runtime directories now live under `boot-core/`:
  - `boot-core/bin/`
  - `boot-core/cache/`
  - `boot-core/database/`
- New shared or extendable entities should target `app/Entities/`.
- Module-local `Repository/*/Models/` remain valid runtime, but should not keep expanding for shared domain entities without evaluating migration to `app/Entities/`.

### Module structure (Repository/Framework/ and Repository/App/)
```
{Module}/
├── Controllers/    ← PHP controllers (extend Controller base)
├── Views/
│   ├── pages/      ← route-owned page templates
│   ├── partials/   ← reusable presentation fragments
│   ├── components/ ← shared module presentation blocks
│   └── scope/      ← presentation-only scope companions
├── front/          ← script.js + style.css (private source)
├── lang/           ← i18n JSON files
└── routes.php      ← loaded automatically by Kernel::loadRoutes()
```

### Route loading order (Kernel::loadRoutes)
1. `boot-core/routes/global-routes.php` — canonical redirects + core actions
2. `boot-core/routes/api.php` — global API routes (if present)
3. `Repository/Framework/{Module}/routes.php` — framework modules (glob)
4. `Repository/App/Surface/{Module}/routes.php` — app modules (glob)

Before route cache lookup, `Kernel::loadRoutes()` always registers the global
middleware pipeline through `GlobalMiddlewareRegistrar` and module view
namespaces through `ModuleViewPathRegistrar`. Cold and cached bootstraps share
the same transversal setup.

## Third-Party Dependencies

**CRITICAL**: Only FOUR external libraries are allowed:

- **PHPMailer** (`phpmailer/phpmailer ^6.9`) — SMTP, DKIM support, email sending
- **league/oauth2-client** (`league/oauth2-client ^2.9`) — OAuth2 Authorization Code Flow; custom GoogleProvider and GitHubProvider extend `AbstractProvider`. Implementing OAuth2 securely from scratch is error-prone.
- **cboden/ratchet** (`cboden/ratchet ^0.4`) — WebSocket server (Etapa 5)
- **react/http** (`react/http ^1.9`) — Async HTTP, required transitively by Ratchet

**Do NOT add other libraries** without explicit user approval.

## Runtime Model

Catalyst uses the traditional PHP execution model.

### Web runtime

- `public/index.php` is the web entry point.
- It loads `boot-core/requirement-loader/error-catcher.php` when needed, then Composer autoload, then executes `Kernel::getInstance()->bootstrap()->run()`.
- `Kernel::bootstrap()` applies runtime configuration, initializes session and translator state, and loads routes.
- `Kernel::run()` wraps request dispatch inside `SecurityHeadersMiddleware` and sends a single response for the current request.

### CLI runtime

- `public/cli.php` is a separate CLI entry point.
- It bootstraps the same early error-catcher/autoload stack, registers built-in commands in `CommandRegistry`, and executes `CliKernel`.
- CLI commands are process-per-invocation, not part of the web request lifecycle.
- Queue workers and scheduler runs also live on this CLI boundary:
  - `queue:work`
  - `queue:failed`
  - `queue:retry`
  - `schedule:list`
  - `schedule:run`

### Singletons in this project

Singletons are an accepted design choice in Catalyst's current runtime model.

- `Kernel`, `Request`, `Logger`, `ConfigManager`, `SessionManager`, `Translator` and `CommandRegistry` all rely on singleton-style access patterns.
- In classic PHP request-response execution this is acceptable because the process state is expected to start fresh for each HTTP request, and CLI state starts fresh for each command invocation.
- Treat singleton removal as an architectural change, not as a default bugfix.

### Long-running process boundary

Catalyst is not designed yet as a general long-running HTTP runtime.

- Do not assume the main framework stack is safe for persistent workers such as Swoole, RoadRunner or ReactPHP-based HTTP servers.
- Mutable singleton state, boot order and request globals are all shaped around short-lived PHP execution.
- The optional WebSocket server is a dedicated Ratchet CLI service and should be treated as a separate process boundary, not as proof that the main HTTP runtime is worker-safe.
- The queue worker and scheduler are also explicit process boundaries. They reuse framework services and persistence, but do not turn the main HTTP runtime into a long-running application server.
