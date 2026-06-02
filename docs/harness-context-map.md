# Catalyst Harness Context Map

## Proposito

Este archivo enruta la documentacion de `catalyst` para que el contexto caliente se mantenga pequeno y el detalle tecnico siga siendo accesible.

## Secuencia de arranque

1. `D:/OpsZone/DevWorkspace/Projects/Web/catalyst/AGENTS.md`
2. este archivo
3. `D:/OpsZone/DevWorkspace/Knowledge/Obsidian-Vault/08-AI-Context/catalyst.md` solo como estado compacto
4. `D:/OpsZone/DevWorkspace/Knowledge/Obsidian-Vault/08-AI-Context/catalyst-reentry.md` si el repo esta sucio o hay trabajo activo en curso
5. summary mas reciente y relevante de `catalyst` solo si hay continuidad real no cubierta por reentry
6. docs `warm` segun la tarea

## Capas documentales

### Hot

- `AGENTS.md` - contrato corto del proyecto
- `docs/harness-context-map.md` - mapa documental y regla `Si tocas X, lee Y`

### Warm

- `STRUCTURE.md` - diccionario tecnico y prevencion de duplicacion
- `API.md` - indice rapido de subsistemas
- `docs/architecture.md` - mapa arquitectonico del framework
- `docs/runtime-model.md` - entrada amplia al modelo runtime; enruta a `architecture.md`, `entry-points.md` y `kernel.md`
- `docs/security-conventions.md` - CSP, vistas, JS, nonce, patrones `data-*`
- `docs/documentation-contract.md` - contrato hot/warm/cold e inventarios generados
- `docs/deployment.md` - checklist de seguridad previa a despliegue y empaquetado limpio
- `docs/workflow/patch-intake.md` - intake seguro de zips/parches antes de tocar el repo activo
- `docs/workflow/first-run.md` - primer arranque reproducible de un checkout fresco
- `docs/workflow/reusable-base-install.md` - instalacion reusable como base para nuevos proyectos
- `docs/auth.md` - indice amplio de auth; enruta a `framework-auth.md` y `repository-auth.md`
- `docs/database.md` - indice amplio de DB/ORM; enruta a `framework-database.md`
- `docs/routing.md` - indice amplio de routing/dispatch; enruta a `architecture.md`, `kernel.md` y docs de modulo
- `docs/middleware.md` - indice amplio de middleware; enruta por dominio y al diccionario en `STRUCTURE.md`
- `docs/views.md` - indice amplio de vistas; enruta a `framework-view.md` y `security-conventions.md`
- `docs/helpers-i18n.md` - contrato runtime de i18n, cobertura por locale y gobierno `LocalizationManager`
- `docs/framework-appearance.md` - contrato runtime de theming institucional, branding y watermark PDF
- `docs/framework-datagrid.md` - refactor real de `DataGrid`, fachada/orquestacion, toolbars y export `csv/xls/print`
- `docs/ui/visual-refactor-v2.md` - estado consolidado del parche visual v2, diferencias contra v1 y alcance por shell/superficies
- `docs/ui/institutional-themes.md` - skins visuales de respuesta y separacion frente al branding neutral
- `docs/ui/sidebar-navigation.md` - criterio de agrupacion, scroll y submenu del sidebar administrativo
- `docs/ui/datagrid-visual-guidelines.md` - lineamientos visuales y de CSP para el DataGrid
- `docs/ui/validation-checklist.md` - rutina de validacion tecnica del parche visual
- `docs/ui/route-inventory-99.md` - inventario de las 99 rutas HTML auditadas, clasificadas por acceso y navegacion
- `docs/navigation-route-refactor-plan.md` - plan formal, taxonomia canonica y matriz de rutas para navegacion/sidebar
- `docs/navigation-route-matrix-222.md` - snapshot histórico de la matriz runtime previa al cierre de aliases; la verdad viva actual sale de `route:list --json` y `docs/runtime-module-catalog.md`
- `docs/modules.md` - indice amplio del modelo de modulos y sus rutas documentales
- `docs/testing.md` - indice amplio de la superficie de verificacion vigente
- `docs/quality-gate.md` - checks locales pre-commit/pre-push, bloqueantes y warnings aceptables
- `docs/runtime-module-catalog.md` - catalogo vivo generado desde runtime/registries para modulos, guards, assets, settings y permisos
- `docs/runtime-inventory.md` - inventario vivo generado de simbolos, templates y scripts
- `docs/framework-auth.md` - Auth core
- `docs/runtime-module-catalog.md` - catalogo vivo suficiente para leer surfaces, guards, permisos, assets y representative routes sin depender del snapshot histórico `222`
- `docs/framework-geo.md` - Geo reusable primitive
- `docs/repository-auth.md` - flujos y rutas del modulo Auth
- `docs/framework-database.md` - DB, QueryBuilder, ORM y relaciones
- `docs/framework-concurrency.md` - optimistic locking reusable, claims expirables y probes CLI de `PA-01`
- `docs/framework-event.md` - Event bus, listeners sync/async y envelopes
- `docs/framework-queue.md` - Cola persistente, retry, failed jobs y worker CLI
- `docs/framework-schedule.md` - Schedule registry, locking y runner CLI
- `docs/entry-points.md` - `index.php` / `cli.php`
- `docs/kernel.md` - bootstrap y carga de rutas
- `docs/checklists/setup-completion-e2e.md` - flujo `/configuration/environment-setup`

### Cold

- `docs/update-log.md` - historial tecnico detallado
- `Knowledge/Obsidian-Vault/99-Archive/ai-context-heavy/catalyst-history-2026-05-28.md` - AI Context pesado archivado
- `Knowledge/Obsidian-Vault/99-Archive/ai-context-heavy/catalyst-reentry-history-2026-05-28.md` - reentry pesado archivado
- summaries antiguos de `catalyst`
- `README.md` - overview general y onboarding humano
- `TERMINAL.md` - referencia CLI secundaria; util para discovery humana, pero `public/cli.php` y `help` siguen siendo la verdad final

## Si tocas X, lee Y

| Area | Leer primero |
|---|---|
| auth, MFA, reset, verification, OAuth | `docs/framework-auth.md`, `docs/repository-auth.md` |
| apariencia institucional, logos, favicon, watermark PDF, tooling i18n admin | `docs/framework-appearance.md`, `docs/helpers-i18n.md`, `docs/runtime-module-catalog.md`, `Repository/Framework/Operations`, `app/Framework/Appearance`, `app/Framework/Localization`, `app/Framework/Document` |
| shell visual, auth, densidad compacta, skins de respuesta, branding neutral, sidebar, topbar y superficies work/admin | `docs/ui/visual-refactor-v2.md`, `docs/ui/institutional-themes.md`, `docs/ui/sidebar-navigation.md`, `docs/framework-appearance.md`, `docs/security-conventions.md` |
| inventario visual de rutas, clasificacion publica/protegida y reachability de las 99 entradas HTML auditadas | `docs/ui/route-inventory-99.md`, `visual-audit/route-coverage.csv`, `docs/runtime-module-catalog.md` |
| taxonomia de navegacion, ownership de rutas y segregacion Workspace/Administration/DevTools | `docs/navigation-route-refactor-plan.md`, `docs/ui/sidebar-navigation.md`, `docs/runtime-module-catalog.md`, `php public/cli.php route:list --json`, `app/Framework/Navigation/NavigationRegistry.php`, `app/Framework/Module/ModuleRegistry.php` |
| geo reusable, distance, radius, bounding box | `docs/framework-geo.md`, `STRUCTURE.md` |
| fixtures auth/RBAC, snapshots reversibles, overlay dev | `TERMINAL.md`, `docs/testing.md`, `app/Framework/Testing/`, `app/Framework/Cli/Commands/ExportDevelopmentOverlayCommand.php` |
| harness por modulo, rutas estaticas, guards/surfaces, estados MFA y presencia sobre claims | `docs/runtime-module-catalog.md`, `TERMINAL.md`, `STRUCTURE.md` |
| db, query builder, ORM, relations | `docs/framework-database.md`, `STRUCTURE.md` |
| concurrency, optimistic locking, record claiming | `docs/framework-concurrency.md`, `docs/framework-database.md`, `STRUCTURE.md`, `TERMINAL.md` |
| events, jobs, scheduler, runtime async | `docs/framework-event.md`, `docs/framework-queue.md`, `docs/framework-schedule.md`, `STRUCTURE.md` |
| vistas, CSP, scripts inline, `data-*` | `docs/security-conventions.md` |
| grids administrativos, toolbar, export `csv/xls/print`, `per_page`, structured values | `docs/framework-datagrid.md`, `docs/framework-view.md`, `docs/security-conventions.md` |
| checklist de validacion visual, CLI, CSP e integridad de assets CSS publicados | `docs/ui/validation-checklist.md`, `docs/testing.md`, `docs/security-conventions.md` |
| reestructuracion del arbol `Views`, companions y convencion `scope/` | `docs/framework-view.md`, `docs/views.md`, `STRUCTURE.md` |
| despliegue, empaquetado, rotacion de secretos | `docs/deployment.md`, `docs/security-conventions.md` |
| zips/parches externos, intake, primer arranque, base reusable | `docs/workflow/patch-intake.md`, `docs/workflow/first-run.md`, `docs/workflow/reusable-base-install.md`, `docs/deployment.md`, `docs/quality-gate.md` |
| quality gate, pre-commit/pre-push, checks CLI | `docs/quality-gate.md`, `TERMINAL.md`, `public/cli.php` |
| bootstrap, routing, kernel, entry points | `docs/architecture.md`, `docs/entry-points.md`, `docs/kernel.md` |
| setup/config | `docs/checklists/setup-completion-e2e.md`, `docs/helpers-config.md` |
| clases existentes o nuevas | `STRUCTURE.md`, `docs/runtime-inventory.md` |
| contrato documental, inventarios, hot/warm/cold | `docs/documentation-contract.md`, `docs/runtime-inventory.md`, `docs/runtime-module-catalog.md` |
| API rapida de subsistemas | `API.md` |
| historial tecnico | `docs/update-log.md` |
| trazabilidad cerrada o historial de sesiones | `Knowledge/Obsidian-Vault/07-Summaries/`, `Knowledge/Obsidian-Vault/99-Archive/ai-context-heavy/` |

## Regla de carga

- No cargar `docs/update-log.md` al inicio salvo que la tarea sea historica.
- No usar `README.md` como contexto caliente si la tarea ya esta orientada por `AGENTS.md` y este archivo.
- Los indices amplios (`docs/auth.md`, `docs/database.md`, `docs/routing.md`, `docs/middleware.md`, `docs/views.md`, `docs/modules.md`, `docs/testing.md`, `docs/runtime-model.md`) existen para discoverability y cierre de matriz; cargar primero el indice y luego solo el split doc puntual que la tarea necesite.
- Antes de crear clases o tocar dominios grandes, pasar por `STRUCTURE.md`.

## Relacionado

- `D:/OpsZone/DevWorkspace/Projects/Web/catalyst/AGENTS.md`
- `D:/OpsZone/DevWorkspace/Knowledge/Obsidian-Vault/08-AI-Context/catalyst.md`
- `D:/OpsZone/DevWorkspace/Knowledge/Obsidian-Vault/08-AI-Context/catalyst-reentry.md`
- `D:/OpsZone/DevWorkspace/Knowledge/Obsidian-Vault/99-Archive/ai-context-heavy/catalyst-history-2026-05-28.md`
- `D:/OpsZone/DevWorkspace/Projects/Web/catalyst/README.md`
