# Kernel

## Purpose

Define the runtime owner for HTTP bootstrap, service wiring, route loading and dispatch.

## Runtime Owners

| Concern | Owner |
|---|---|
| Kernel bootstrap | `Catalyst\Kernel` |
| Route loading | `Catalyst\Framework\Route\Router` |
| Dispatch | `Catalyst\Framework\Route\RouteDispatcher` |
| Global middleware registration | `Catalyst\Framework\Route\GlobalMiddlewareRegistrar` |
| Module view path registration | `Catalyst\Framework\View\ModuleViewPathRegistrar` |

## Current Behavior

`Catalyst\Kernel` bootstraps runtime services, loads routes and dispatches HTTP requests. Route and middleware behavior is validated by `route:lint`, `route:bootstrap-regression` and `inspect:lint`. The kernel does not own domain behavior; controllers, managers, repositories, requests and module services own feature-specific work.

## Operational Notes

Bootstrap/cache paths must keep route order, middleware registration and module view paths coherent. When editing routing or bootstrap behavior, run `php public/cli.php route:bootstrap-regression`, `php public/cli.php route:list --json`, `php public/cli.php inspect:lint` and `php public/cli.php route:lint`.

## Related Documentation

- `docs/entry-points.md`
- `docs/routing.md`
- `docs/middleware.md`
- `docs/framework-view.md`