# Documentation Contract

## Purpose

This contract keeps Catalyst documentation split by operating temperature and
prevents curated files from becoming stale exhaustive inventories.

## Documentation Classes

### Hot

Hot documents are loaded at session start and must stay compact.

- `AGENTS.md` - project contract, constraints and verification entry points.
- `docs/harness-context-map.md` - routing map for warm/cold documentation.

### Warm

Warm documents are loaded only when a task touches their area. They describe
current architecture and operating contracts.

- `STRUCTURE.md` - curated dictionary of important namespaces, modules and
  commands.
- `API.md` - public documentation index.
- `docs/runtime-module-catalog.md` - generated module/runtime catalog.
- `docs/runtime-inventory.md` - generated symbol/template/script inventory.
- Domain docs under `docs/framework-*.md`, `docs/repository-*.md`,
  `docs/helpers-*.md`, `docs/ui/*.md` and `docs/checklists/*.md`.

### Cold

Cold documents are historical or bulky. Load them only for traceability,
archaeology or explicit continuity gaps.

- `docs/update-log.md`
- historical summaries under
  `D:/OpsZone/DevWorkspace/Knowledge/Obsidian-Vault/07-Summaries/`
- archived AI context under
  `D:/OpsZone/DevWorkspace/Knowledge/Obsidian-Vault/99-Archive/`
- legacy snapshots such as `docs/navigation-route-matrix-222.md`

## Inventory Contract

`STRUCTURE.md` is curated. It should explain important ownership and prevent
duplication, but it is not the exhaustive list of every file.

Use generated inventories for exhaustive truth:

```powershell
php public/cli.php docs:sync-runtime
php public/cli.php docs:inventory
php public/cli.php docs:inventory --json
```

- `docs/runtime-module-catalog.md` is generated from registries, module
  inspectors, harness data and lint.
- `docs/runtime-inventory.md` is generated from filesystem scans and PHP token
  parsing. It records symbols, templates and scripts.
- Any task that creates or removes classes, templates or canonical scripts must
  refresh `docs/runtime-inventory.md` before closure.
- Any task that changes module registration, routes, assets, permissions or
  settings must refresh `docs/runtime-module-catalog.md` before closure.

## Risk Boundary Notes

Add concise inline comments only where the contract is easy to misread:

- bootstrap/cache branches: explain why cached and cold paths must register the
  same middleware, views and route order.
- routing loaders: explain ownership and ordering, not every route.
- cache/security helpers: explain trust boundaries, signing and fail-closed
  behavior.
- middleware: explain cross-cutting guarantees and bypass policy.
- public repositories and services: explain persistence or external boundary
  assumptions when they are not visible from method names.

Do not add comments that repeat method names, assignments or obvious control
flow.

## Closure Checklist

For documentation-contract work:

```powershell
php public/cli.php docs:inventory --json
php public/cli.php docs:sync-runtime --stdout
php public/cli.php quality:check
git diff --check
```

Expected result: generated inventories reflect the runtime, blocker checks pass
and `STRUCTURE.md` remains a curated map instead of an exhaustive dump.
