# Runtime Model

## Purpose

Provide the high-level runtime map for Catalyst and route readers to the specific canonical documents that own each concern.

## Runtime Owners

| Concern | Owner |
|---|---|
| Web entry point | `public/index.php` |
| CLI entry point | `public/cli.php` |
| Kernel lifecycle | `Catalyst\Kernel` |
| Route collection and dispatch | `Catalyst\Framework\Route\Router`, `Catalyst\Framework\Route\RouteDispatcher` |
| Module runtime catalog | `Catalyst\Framework\Module\ModuleInspector`, `Catalyst\Framework\Module\ModuleHarnessInspector`, `Catalyst\Framework\Module\ModuleLinter` |
| CLI commands | `Catalyst\Framework\Cli\CommandRegistry` |

## Current Behavior

The runtime starts from explicit entry points. HTTP requests bootstrap the kernel and dispatch routes. CLI requests bootstrap the command kernel and execute registered command classes. Module and route documentation is generated from runtime registries, so `docs/runtime-module-catalog.md` and `route:list --json` are the live source for routes, guards, permissions and representative module surfaces.

## Operational Notes

Do not use historical route snapshots as live truth. For a current read, run the CLI commands and compare with generated docs. If module registration, route guards, work assets or permissions change, regenerate the runtime module catalog before closing the work.

## Related Documentation

- `docs/architecture.md`
- `docs/entry-points.md`
- `docs/kernel.md`
- `docs/runtime-module-catalog.md`
- `docs/runtime-inventory.md`