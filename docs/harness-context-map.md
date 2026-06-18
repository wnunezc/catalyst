# Catalyst Harness Context Map

## Proposito

Este archivo enruta la documentacion vigente de `catalyst` para que el contexto caliente se mantenga pequeno y el detalle tecnico siga accesible sin cargar historicos de desarrollo.

## Secuencia de arranque

1. `D:/OpsZone/DevWorkspace/Projects/Web/catalyst/AGENTS.md`
2. este archivo
3. `D:/OpsZone/DevWorkspace/Knowledge/Obsidian-Vault/08-AI-Context/catalyst.md` solo como estado compacto
4. `D:/OpsZone/DevWorkspace/Knowledge/Obsidian-Vault/08-AI-Context/catalyst-reentry.md` si el repo esta sucio o hay trabajo activo en curso
5. docs `warm` segun la tarea

## Documentacion vigente

| Area | Documento |
|---|---|
| Arquitectura e indice central | `docs/architecture.md` |
| Modelo runtime | `docs/runtime-model.md` |
| Entry points | `docs/entry-points.md` |
| Kernel | `docs/kernel.md` |
| Routing | `docs/routing.md` |
| Middleware | `docs/middleware.md` |
| Modulos | `docs/modules.md` |
| Propietarios canonicos | `docs/framework-configuration.md`, `docs/repository-workspaces.md`, `docs/repository-operations.md`, `docs/repository-api.md` |
| Frontera app/framework | `docs/app-boundary.md` |
| Vistas | `docs/views.md`, `docs/framework-view.md` |
| Modals | `docs/framework-modals.md` |
| Seguridad/CSP | `docs/security-conventions.md` |
| Auth | `docs/auth.md`, `docs/framework-auth.md`, `docs/repository-auth.md` |
| Database/ORM | `docs/database.md`, `docs/framework-database.md` |
| Referencias genericas | `docs/entity-references.md` |
| Reverse cascade delete | `docs/reverse-cascade-delete.md` |
| Secuencias transaccionales | `docs/sequences.md` |
| Queue/event/schedule | `docs/framework-event.md`, `docs/framework-queue.md`, `docs/framework-schedule.md` |
| Organization hierarchy | `docs/framework-organization.md` |
| UI actual | `docs/ui/surface-architecture.md`, `docs/ui/css-ownership.md`, `docs/ui/activity-overlay.md` y contratos puntuales bajo `docs/ui/` |
| Demo UI JavaScript y cobertura | `docs/ui/demo-ui-javascript-inventory.md` |
| Test Features JavaScript y cobertura | `docs/ui/test-features-javascript-inventory.md` |
| Operacion/calidad | `docs/testing.md`, `docs/quality-gate.md`, `docs/deployment.md` |
| Setup/workflow | `docs/checklists/setup-completion-e2e.md`, `docs/workflow/*.md` |
| Release/migration | `docs/workflow/release-v0.2.0-rc.3.md`, `docs/workflow/release-v0.2.0-rc.2.md`, `docs/workflow/release-v0.2.0-rc.1.md`, `docs/workflow/release-rc-checklist.md` |
| Inventario generado | `docs/runtime-inventory.md`, `docs/runtime-module-catalog.md` |

## Si tocas X, lee Y

| Area | Leer primero |
|---|---|
| auth, MFA, reset, verification, OAuth | `docs/framework-auth.md`, `docs/repository-auth.md` |
| db, query builder, ORM, relations | `docs/framework-database.md`, `STRUCTURE.md` |
| referencias genericas, cascadas, secuencias | `docs/entity-references.md`, `docs/reverse-cascade-delete.md`, `docs/sequences.md` |
| events, jobs, scheduler, runtime async | `docs/framework-event.md`, `docs/framework-queue.md`, `docs/framework-schedule.md`, `STRUCTURE.md` |
| vistas, CSP, scripts inline, `data-*` | `docs/framework-view.md`, `docs/security-conventions.md` |
| documento, shell, runtime frontend, SSR/AJAX/SPA | `docs/ui/surface-architecture.md`, `docs/framework-view.md` |
| Demo UI, runtime JavaScript, assets condicionales, Playwright Demo UI | `docs/ui/demo-ui-javascript-inventory.md`, `docs/testing.md`, `docs/framework-view.md` |
| Test Features, runtime JavaScript y Playwright DevTools | `docs/ui/test-features-javascript-inventory.md`, `docs/testing.md`, `docs/framework-view.md` |
| modals, backdrops, dialogos, overlays | `docs/framework-modals.md`, `docs/framework-notification.md`, `docs/framework-view.md`, `docs/ui/activity-overlay.md` |
| bootstrap, routing, kernel, entry points | `docs/architecture.md`, `docs/entry-points.md`, `docs/kernel.md`, `docs/routing.md` |
| setup/config | `docs/checklists/setup-completion-e2e.md`, `docs/helpers-config.md` |
| Configuration, Workspaces, Operations, API | `docs/framework-configuration.md`, `docs/repository-workspaces.md`, `docs/repository-operations.md`, `docs/repository-api.md` |
| modulos, navegacion, permisos, assets | `docs/modules.md`, `docs/runtime-module-catalog.md`, `docs/ui/sidebar-navigation.md` |
| organizaciones, jerarquias, unidades, clasificacion visual | `docs/framework-organization.md`, `docs/framework-auth.md` |
| frontera app/framework, updates upstream | `docs/app-boundary.md`, `docs/workflow/reusable-base-install.md` |
| specs grandes, apps derivadas, mapeo de producto | `docs/spec-to-catalyst-guide.md`, `docs/framework-scaffolding.md`, `docs/app-boundary.md` |
| clases existentes o nuevas | `STRUCTURE.md`, `docs/runtime-inventory.md` |
| contrato documental | `docs/documentation-contract.md`, `docs/runtime-inventory.md`, `docs/runtime-module-catalog.md` |
| API rapida de subsistemas | `API.md` |
| continuidad historica | `Knowledge/Obsidian-Vault/07-Summaries/`, `Knowledge/Obsidian-Vault/99-Archive/ai-context-heavy/` |

## Regla de carga

- No usar historicos de desarrollo dentro de `/docs` como fuente de verdad.
- `docs/architecture.md` es el indice central vigente de `/docs` y tambien resume el estado actual de arquitectura.
- `docs/runtime-module-catalog.md` y `php public/cli.php route:list --json` sustituyen snapshots manuales de rutas.
- `docs/runtime-inventory.md` sustituye inventarios manuales de clases, templates y scripts.
- Los indices amplios (`auth`, `database`, `routing`, `middleware`, `views`, `modules`, `testing`, `runtime-model`) existen para discovery y enrutan al doc puntual.
- Antes de crear clases o tocar dominios grandes, pasar por `STRUCTURE.md` y el inventario runtime.

## Relacionado

- `D:/OpsZone/DevWorkspace/Projects/Web/catalyst/AGENTS.md`
- `D:/OpsZone/DevWorkspace/Knowledge/Obsidian-Vault/08-AI-Context/catalyst.md`
- `D:/OpsZone/DevWorkspace/Knowledge/Obsidian-Vault/08-AI-Context/catalyst-reentry.md`
- `D:/OpsZone/DevWorkspace/Projects/Web/catalyst/README.md`
