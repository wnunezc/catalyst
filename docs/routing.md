# Routing Index

## Purpose

Route readers to the live routing sources and define the split between route registration, collection, matching, dispatch and URL generation.

## Runtime Owners

| Concern | Owner |
|---|---|
| Route registration | `Catalyst\Framework\Route\Router` |
| Route storage | `Catalyst\Framework\Route\RouteCollection` |
| Route compilation | `Catalyst\Framework\Route\RouteCompiler` |
| Dispatch | `Catalyst\Framework\Route\RouteDispatcher` |
| Groups | `Catalyst\Framework\Route\RouteGroup` |
| Canonical redirects | `Catalyst\Framework\Route\CanonicalPathRedirector` |
| Route CLI truth | `php public/cli.php route:list --json` |

## Current Behavior

Routes are declared by framework and app modules, loaded through the kernel, and exposed through `route:list`. The current runtime has app public surfaces, auth-flow routes, authenticated workspace/admin modules, authenticated API routes and DevTools routes. Route guards are validated by `inspect:lint`; route casing, aliases and work asset publication are validated by `route:lint`.

## Operational Notes

Use `route:list --json` and `docs/runtime-module-catalog.md` for live route truth; historical route snapshots are not kept in `/docs`.

## Related Documentation

- `docs/kernel.md`
- `docs/middleware.md`
- `docs/runtime-module-catalog.md`
- `docs/modules.md`