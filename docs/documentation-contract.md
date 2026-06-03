# Documentation Contract

## Purpose

Keep Catalyst documentation aligned with code by separating current canonical docs from generated inventories and historical evidence.

## Runtime Owners

| Concern | Owner |
|---|---|
| Symbol/template/script inventory | `Catalyst\Framework\Documentation\RuntimeInventoryGenerator` |
| Module runtime catalog | `Catalyst\Framework\Cli\Commands\DocsSyncRuntimeCommand` |
| Documentation inventory command | `Catalyst\Framework\Cli\Commands\DocsInventoryCommand` |
| Structural lint | `Catalyst\Framework\Cli\Commands\InspectLintCommand` |
| Route lint | `Catalyst\Framework\Cli\Commands\RouteLintCommand` |

## Current Behavior

Hot docs are loaded at session start and stay compact: `AGENTS.md` and `docs/harness-context-map.md`. Warm docs describe current runtime behavior and are loaded by area. Generated docs are `docs/runtime-inventory.md` and `docs/runtime-module-catalog.md`. Historical evidence is not the source of current behavior.

The canonical update path is:

```powershell
php public/cli.php docs:inventory --json
php public/cli.php docs:sync-runtime --stdout
php public/cli.php inspect:lint
php public/cli.php route:lint
```

## Operational Notes

If a task changes classes, templates or scripts, refresh `docs/runtime-inventory.md`. If it changes module registration, route guards, assets, permissions or settings, refresh `docs/runtime-module-catalog.md`. Do not keep obsolete historical archives inside `/docs` after they are classified and references are updated.

## Related Documentation

- `docs/harness-context-map.md`
- `docs/runtime-inventory.md`
- `docs/runtime-module-catalog.md`
- `docs/quality-gate.md`