# Catalyst Framework - Architecture

## Purpose

Describe the current Catalyst runtime shape without preserving historical roadmap details. Catalyst is a PHP 8.4 MVC framework with framework-owned modules under `Repository/Framework`, app-owned surfaces under `Repository/App`, generated runtime inventories, and a CLI-first quality gate.

## Runtime Owners

| Concern | Owner |
|---|---|
| HTTP bootstrap and dispatch | `Catalyst\Kernel` |
| Route registration | `Catalyst\Framework\Route\Router` |
| Route matching and execution | `Catalyst\Framework\Route\RouteDispatcher` |
| Module declarations and runtime catalog | `Catalyst\Framework\Module\ModuleRegistry` |
| Navigation registry | `Catalyst\Framework\Navigation\NavigationRegistry` |
| Permission registry | `Catalyst\Framework\Authorization\PermissionRegistry` |
| View rendering | `Catalyst\Framework\View\View` |
| CLI command execution | `Catalyst\Framework\Cli\CliKernel` |

## Current Behavior

Catalyst loads HTTP requests through `public/index.php` and CLI commands through `public/cli.php`. The kernel wires services, route loaders, module view paths, middleware and dispatch. Runtime module truth is generated from registries, inspectors, harness checks and route lint rather than from static route snapshots.

The active module catalog reports 18 modules across app public surfaces, auth flow, authenticated workspaces, administration, devtools and authenticated APIs. Public app surfaces live under `Repository/App/Surface/*`; reusable framework modules live under `Repository/Framework/*`; core primitives live under `app/Framework/*`.

Framework responsibilities are split intentionally: routing classes register, compile, collect and dispatch routes; module classes declare, discover, inspect and lint modules; view classes render templates while helpers handle trusted HTML, inline JSON and token rendering.

## Operational Notes

Use `php public/cli.php docs:sync-runtime --stdout` for the live module catalog, `php public/cli.php docs:inventory --json` for symbol/template/script inventory, and `php public/cli.php route:list --json` for route truth. Historical audit files and old route matrices are not canonical runtime sources.

## Related Documentation

- `docs/runtime-model.md`
- `docs/entry-points.md`
- `docs/kernel.md`
- `docs/routing.md`
- `docs/modules.md`
- `docs/runtime-module-catalog.md`
- `docs/runtime-inventory.md`