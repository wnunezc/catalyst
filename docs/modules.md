# Module Index

## Purpose

Route readers to the current module model and generated module catalog.

## Runtime Owners

| Concern | Owner |
|---|---|
| Built-in declarations | `Catalyst\Framework\Module\BuiltInModuleDeclarations` |
| Module registry | `Catalyst\Framework\Module\ModuleRegistry` |
| Module discovery | `Catalyst\Framework\Module\ModuleDiscovery` |
| Module inspection | `Catalyst\Framework\Module\ModuleInspector` |
| Harness inspection | `Catalyst\Framework\Module\ModuleHarnessInspector` |
| Module lint | `Catalyst\Framework\Module\ModuleLinter` |
| Navigation | `Catalyst\Framework\Navigation\NavigationRegistry` |
| Permissions | `Catalyst\Framework\Authorization\PermissionRegistry` |

## Current Behavior

The generated runtime module catalog currently reports 17 modules with structural lint OK. It records surfaces, representative routes, permissions, assets, settings, feature flags and route guard behavior. Module docs should treat that generated catalog as live truth.

Module code is split between `Repository/App/Surface/*` for app surfaces and `Repository/Framework/*` for framework-owned modules. Core framework primitives remain under `app/Framework/*`.

## Operational Notes

Run `php public/cli.php docs:sync-runtime --stdout` after module registration, routes, assets, permissions, settings or harness behavior changes. Do not preserve static route inventories as current module documentation.

## Related Documentation

- `docs/runtime-module-catalog.md`
- `docs/runtime-inventory.md`
- `docs/routing.md`
- `docs/harness-context-map.md`
