# Phase 6A.1 - Bootstrap, Routing And Middleware Audit

Date: 2026-06-01

Status: initial audit generated / runtime remediation pending user decision.

## Executive Summary

The web and CLI bootstrap review found a critical production-only defect:
when route cache is consumed, Catalyst restores the route collection and
returns before registering the global middleware pipeline. Security headers
remain active because they are wrapped separately by `Kernel`, but CSRF,
throttling, setup, tenancy, WebSocket boot, canonical-path redirect and CORS
middleware are omitted.

The same review confirmed three secondary inconsistencies: the executed route
composition order differs from the documented order, gate definitions are
registered twice, and DevTools UML documentation is stale.

No runtime code was modified during this audit.

## Reviewed Scope

- Web entry point and CLI entry point.
- `Kernel` bootstrap sequence.
- Route discovery, cold loading and cached loading.
- Route dispatch and middleware stack execution.
- Cache build commands and cache activation rules.
- Global route file and global gate registration.
- DevTools UML companions that document the runtime flow.

## Findings

### Critical - ARQ-08: Route cache consumption omits global middleware

**Files and lines**

- `app/Kernel.php:304`
- `boot-core/routes/global-routes.php:45`
- `app/Framework/Route/Router.php:372`
- `app/Framework/Route/Router.php:394`
- `app/Framework/Route/RouteDispatcher.php:83`
- `app/Framework/Cache/CacheSettings.php:91`
- `app/Framework/Cli/Commands/RouteCacheCommand.php:75`
- `app/Framework/Cli/Commands/CacheBuildCommand.php:61`

**Evidence**

On a route-cache hit, `Kernel::loadRoutes()` returns immediately after
`Router::loadCachedRoutes()`. Global middleware is currently registered as a
side effect of requiring `boot-core/routes/global-routes.php`, so that
registration is skipped. The route cache artifact serializes only the
`RouteCollection`; it does not serialize or rebuild the middleware stack.

The omitted pipeline contains:

- `CorsMiddleware`
- `CanonicalPathRedirectMiddleware`
- `WebSocketBootMiddleware`
- `TenancyContextMiddleware`
- `SetupMiddleware`
- `RequestThrottlingMiddleware`
- `CsrfMiddleware`

`SecurityHeadersMiddleware` remains active because `Kernel` invokes it outside
the router middleware stack.

**Impact**

Production can run with a materially weaker middleware pipeline than
development when master cache and route cache are enabled.

**Recommendation**

Correct now. Register global middleware unconditionally before the cache-hit
branch. Keep global endpoint definitions in the global routes file, but remove
transversal bootstrap side effects from that file. Add an executable
regression for the production-like cache-hit path.

### High - ARQ-09: Executed route composition order differs from contract

**Files and lines**

- `app/Kernel.php:287`
- `app/Framework/Cli/CliRouteLoader.php:24`
- `app/Framework/Cli/CliRouteLoader.php:62`
- `app/Framework/Cli/Commands/RouteCacheCommand.php:40`

**Evidence**

Comments describe this order: global routes, optional API routes, Framework
modules, then App surfaces. `CliRouteLoader` initially collects those sources
but applies one alphabetical sort to absolute paths. The current effective
order is global routes, App surfaces and then Framework modules.

There are currently no exact method-path collisions, but the loader does not
enforce its documented precedence contract.

**Recommendation**

Correct together with ARQ-08. Compose and sort each source group explicitly,
preserving the intended phase order.

### Medium - ARQ-10: Gate definitions are registered twice

**Files and lines**

- `app/Kernel.php:119`
- `boot-core/routes/global-routes.php:59`
- `app/Framework/Authorization/PermissionRegistry.php:60`
- `app/Framework/Authorization/Gate.php:85`

**Evidence**

`Kernel` registers gate definitions before route loading. The global routes
file repeats the same registration. `Gate::define()` and `Gate::policy()`
overwrite keyed entries, so the current result is idempotent but duplicated.

**Recommendation**

Correct together with ARQ-08. Keep registration in `Kernel` and remove it from
the global routes file.

### Medium - ARQ-11: DevTools UML companions are stale

**Files**

- `Repository/Framework/DevTools/lang/en/uml.json`
- `Repository/Framework/DevTools/lang/es/uml.json`

**Evidence**

The diagrams do not represent the current middleware chain consistently and
retain obsolete setup route references.

**Recommendation**

Update after the runtime bootstrap correction so documentation reflects the
verified final flow.

## Reviewed Files

- `public/index.php`
- `public/cli.php`
- `app/Kernel.php`
- `app/Framework/Cli/CliRouteLoader.php`
- `app/Framework/Cli/Commands/RouteCacheCommand.php`
- `app/Framework/Cli/Commands/CacheBuildCommand.php`
- `app/Framework/Route/Router.php`
- `app/Framework/Route/RouteDispatcher.php`
- `app/Framework/Route/MiddlewareStack.php`
- `app/Framework/Cache/CacheSettings.php`
- `app/Framework/Authorization/Gate.php`
- `app/Framework/Authorization/PermissionRegistry.php`
- `boot-core/routes/global-routes.php`
- `Repository/Framework/DevTools/lang/en/uml.json`
- `Repository/Framework/DevTools/lang/es/uml.json`

## User Decision Required

Approve or reject immediate execution of roadmap task `6B.0`.

Recommended decision: approve `6B.0` before continuing broad runtime
normalization. The preferred implementation is a small core registrar for the
global middleware pipeline, invoked unconditionally by `Kernel`, plus explicit
route source ordering and a cache-hit regression.

## Next Step

After the decision, either implement and verify `6B.0`, or continue read-only
auditing while recording the critical defect as unresolved.
