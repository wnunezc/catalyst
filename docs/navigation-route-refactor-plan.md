# Plan de refactor de navegación y rutas

- Fecha original: 2026-05-24
- Última normalización documental: 2026-05-28
- Estado actual: cerrado en runtime y conservado como documento normativo vigente

## Propósito actual

Este documento ya no describe una migración abierta de navegación.

Su función actual es fijar la taxonomía vigente del producto, dejar claras las rutas canónicas que gobiernan el sidebar y documentar qué fuentes deben usarse para leer el router vivo.

La implementación principal de esta corriente ya quedó cerrada:

- navegación agrupada por contexto y metadata de módulo
- retiro de aliases web legacy en `Users`, `Configuration` y `Workspaces`
- normalización global de casing conocido
- cierre del ownership canónico por familias

## Fuentes vivas

La verdad viva del router y de la navegación ya no se lee desde snapshots históricos.

Usar:

- `php public/cli.php route:list --json`
- `php public/cli.php docs:sync-runtime`
- `docs/runtime-module-catalog.md`
- `app/Framework/Navigation/NavigationRegistry.php`
- `app/Framework/Module/ModuleRegistry.php`

No usar como contrato vivo:

- `docs/navigation-route-matrix-222.md`
- `docs/ui/route-inventory-99.md`

Ambos quedan como evidencia histórica previa al cierre de aliases y al cutover final de familias.

## Objetivo normativo

La navegación autenticada del producto debe respetar estas reglas:

- el sidebar expone solo superficies canónicas reales
- las rutas contextuales, CRUD auxiliares, callbacks y helpers técnicos no son entradas primarias
- los aliases legacy retirados no deben reintroducirse como compatibilidad permanente
- la tolerancia de casing conocido se resuelve por normalización hacia la ruta canónica, no por multiplicación de rutas registradas
- la organización visual debe seguir la IA canónica consolidada desde `demo-ui`, pero las superficies reales viven en las rutas oficiales del producto

## Taxonomía vigente

| Dominio | Contexto runtime | Ownership | Regla de navegación |
|---|---|---|---|
| Público | `public` | `Repository/App/Surface/*` | Navegación pública; no entra al sidebar autenticado |
| Auth Flow | `auth-flow` | `framework.auth` | Flujo de acceso, recuperación, verificación y MFA; no sidebar |
| Entry surfaces internas | `authenticated` + `workspace` | `app.surface.dashboard`, `framework.settings` | Entrada interna y familia `Configuration` |
| Administration / Access | `administration` | `framework.roles` | Grupo `Acceso y usuarios` |
| Administration / Content | `authenticated` | `framework.catalogs`, `framework.documents`, `framework.media` | Familia `Workspaces` en rutas canónicas `/workspaces/*` |
| Administration / Platform | `authenticated` | `framework.audit`, `framework.automation`, `framework.apiplatform` | Grupo `Plataforma` |
| Administration / Operations | `authenticated` | `framework.operations` | Grupo `Operaciones` solo con superficies vivas de esta familia |
| DevTools | `devtools` | `framework.devtools` | Herramientas principales; helpers y smoke quedan contextuales o solo URL |

## Rutas canónicas vigentes

### Entry surfaces públicas

- `/`
- `/home`
- `/landing`
- `/store`

Subrutas narrativas públicas permitidas:

- `/home/journey`
- `/landing/highlights`
- `/store/catalog`

Las variantes `Home`, `Landing` y `Store` deben entenderse solo como normalización de casing.

Decisión vigente:

- `Home` = entrada pública operativa permanente
- `Landing` = entrada narrativa permanente
- `Store` = catálogo público permanente sin checkout

### Auth Flow

- `/login`
- `/register`
- `/forgot-password`
- `/auth/social/{provider}`
- `/auth/social/callback/{provider}`
- `/reset-password/{token}`
- `/verify-email/{token}`
- `/mfa/challenge`
- `/mfa/setup`

Estas rutas no pertenecen al sidebar autenticado.

### Entry surface interna y Configuration

Entrada interna:

- `/dashboard`
- `/dashboard/pulse`

Configuration:

- `/configuration/environment-setup`
- `/configuration/application-health`
- `/configuration/platform-appearance`
- `/configuration/feature-flags`
- `/configuration/plugins`

Las rutas históricas `/setup`, `/health`, `/configuration/platform-appearance`, `/operations/feature-flags`, `/operations/plugins`, `/configuration/manage-backup` y `/configuration/backups` ya no forman parte del contrato web vivo.

### Workspaces

- `/workspaces/catalogs`
- `/workspaces/document-templates`
- `/workspaces/media-fields`
- `/workspaces/media-library`
- `/workspaces/module-designer`
- `/workspaces/locale-tools`

Las rutas históricas `/catalogs`, `/document-templates`, `/media-fields`, `/media-library`, `/operations/module-designer`, `/operations/localization` y `/test-features/module-designer*` ya no forman parte del contrato web vivo.

### Operations

- `/operations`
- `/operations/deployments`
- `/operations/deployments/runs`
- `/operations/tenancy`

No imponer `Charts` como criterio de cierre de esta familia salvo necesidad funcional real.

### Users

- `/users`
- `/users/enroll`
- `/users/roles`
- `/users/permissions`
- `/users/{userId}/roles`

Las rutas históricas `/users/register`, `/roles` y `/permissions` ya no forman parte del contrato web vivo.

### DevTools

Entradas principales:

- `/test-features`
- `/test-features/ui-showcase`
- `/uml`

Rutas helper, smoke o técnicas:

- permanecen accesibles por URL cuando el módulo las expone
- no se promueven a entrada primaria salvo decisión deliberada de producto

## Reglas de navegación

- Todo módulo nuevo con navegación administrativa debe declarar `context`, `group`, `group_label` y `group_order`.
- Los breadcrumbs deben enlazar siempre al parent canónico vivo de la familia.
- Las rutas `create`, `edit`, `show`, `preview`, `generate`, `{id}`, `{token}` y similares son contextuales salvo definición explícita en metadata.
- `route:list --json` es la fuente cruda completa del router.
- `docs/runtime-module-catalog.md` es la vista operativa legible recomendada para trabajo diario.
- Si alguna compatibilidad técnica excepcional vuelve a ser necesaria, debe documentarse como transitoria y verificarse con redirects explícitos.

## Estado por fases

| Fase | Alcance | Estado actual |
|---|---|---|
| 1 | Sidebar con áreas primarias y grupos declarativos | Cerrada |
| 2 | Normalización de submenus, UX y orden de navegación | Cerrada |
| 3 | Retiro de aliases legacy y normalización de casing | Cerrada |
| 4 | Cierre documental contra router vivo y browser real | Cerrada |

No queda una fase técnica abierta en este plan. Si aparece una necesidad futura, debe abrirse como una corriente nueva, no como continuación implícita de este refactor.

## Criterio de reapertura

Este plan solo debe reabrirse si ocurre al menos una de estas condiciones:

- aparece una familia nueva que necesita ownership de navegación
- una familia vigente cambia de dominio funcional
- se detecta una deriva real entre `NavigationRegistry`, `route:list --json` y `runtime-module-catalog.md`
- el producto decide reintroducir compatibilidad temporal controlada para alguna ruta retirada

## Archivo histórico

La evidencia histórica de esta corriente se conserva fuera del cuerpo normativo:

- `docs/navigation-route-matrix-222.md`
- `docs/ui/route-inventory-99.md`
- `visual-audit/route-coverage.csv`
- summaries del 2026-05-28 en `Knowledge/Obsidian-Vault/07-Summaries/`

Esos materiales siguen siendo útiles para trazabilidad, pero no deben leerse como contrato vivo del router actual.
