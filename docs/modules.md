# Module Index

This file is a thin navigation index for Catalyst's module model.

It exists to satisfy the generic Phase 4 target without merging the split repository docs back into one monolith.

## Runtime module model

- Framework-owned modules live under `Repository/Framework/{Module}/`
- Application-owned modules live under `Repository/App/Surface/{Module}/`
- A module may include `Controllers/`, `Models/`, `Views/pages`, `Views/partials`, `Views/components`, `Views/scope`, `front/`, `lang/`, and `routes.php`
- `ModuleRegistry`, `NavigationRegistry` y `PermissionRegistry` son los mapas canonicos de metadata, navegacion y acceso de cada modulo
- Las entradas administrativas que aparecen en el sidebar deben declarar `context`, `group`, `group_label` y `group_order`; la taxonomia vigente vive en `docs/navigation-route-refactor-plan.md`

## Canonical references

- Module architecture and loading model: `docs/architecture.md`
- Public API index by subsystem/module: `API.md`
- Full class and directory dictionary: `STRUCTURE.md`
- Runtime surface truth: `docs/runtime-module-catalog.md`
- Navigation and route taxonomy: `docs/navigation-route-refactor-plan.md`
- Complete route matrix: `docs/navigation-route-matrix-222.md`
- CLI/runtime inspection: `TERMINAL.md`

## Framework modules with dedicated docs

- Auth: `docs/repository-auth.md`
- DevTools: `docs/repository-devtools.md`
- Notification: `docs/repository-notification.md`

## Framework modules documented through runtime maps

- Settings: `STRUCTURE.md`, `docs/checklists/setup-completion-e2e.md`
- Roles / Permissions: `STRUCTURE.md`
- Audit: `STRUCTURE.md`, `docs/runtime-module-catalog.md`
- Operations: `STRUCTURE.md`, `docs/runtime-module-catalog.md`, `TERMINAL.md`
- Media: `STRUCTURE.md`, `docs/runtime-module-catalog.md`, `TERMINAL.md`
- Documents: `STRUCTURE.md`, `docs/runtime-module-catalog.md`, `TERMINAL.md`
- Automation: `STRUCTURE.md`, `docs/runtime-module-catalog.md`, `TERMINAL.md`
- API Platform: `STRUCTURE.md`, `docs/runtime-module-catalog.md`, `TERMINAL.md`

## Usage note

Use this file when a task starts from the broad label `modules`.
Detailed contracts stay in the split docs and the structure dictionary.
