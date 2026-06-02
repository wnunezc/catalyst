# Kernel

## Namespace: Catalyst

### Class: Kernel
**File**: `app/Kernel.php`  
**Purpose**: Bootstraps the framework, applies effective runtime configuration, loads routes and dispatches every HTTP request.

## Main Responsibilities
- Initialize `Logger` and `Request`
- Apply effective runtime config from `ConfigManager`
  - `app.project_debug`
  - `app.project_timezone`
  - `logging.log_channel`
  - `logging.log_level`
  - `logging.display_logs`
- Initialize `SessionManager`
- Initialize `Translator` using the effective app language
- Load routes, optionally using cached routes when production + setup-owned cache flags are enabled
- Register global middleware and module view namespaces before route cache lookup
- Run every request through `SecurityHeadersMiddleware` and then the router/middleware pipeline

## Public Methods
- `bootstrap(): self`
- `run(): void`

## Important Protected Methods
- `dispatchRequest(Request $request): Response`
- `loadRoutes(): void`
- `buildNotFoundResponse(RouteNotFoundException $e): Response`
- `buildMethodNotAllowedResponse(MethodNotAllowedException $e): Response`
- `buildServerErrorResponse(string $ticket): Response`
- `applyRuntimeConfiguration(): void`

## Notes
- `Kernel::run()` always dispatches the request after bootstrap. It no longer gates dispatch behind environment-specific branches.
- Cache activation is resolved only from `boot-core/config/{env}/cache.json`.
- Runtime cache consumption is allowed only when the real environment is `production` and `cache.cache.cache_enabled = true`.
- When route cache is enabled but the artifact is missing, `Kernel::loadRoutes()` performs a cold route bootstrap and regenerates the route cache file best-effort.
- `GlobalMiddlewareRegistrar` and `ModuleViewPathRegistrar` run before the cache branch so a cache hit cannot omit middleware or view namespaces.
