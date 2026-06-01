# Matriz completa de rutas runtime

- Fuente: `php public/cli.php route:list --json`, `php public/cli.php inspect:modules --json`, `php public/cli.php inspect:harness --json`
- Total cubierto: `222` rutas runtime registradas
- Fecha: 2026-05-24
- Uso: snapshot histórico de clasificación de rutas canónicas, submenus, contextuales, técnicas, APIs y aliases legacy previo al cierre de aliases.
- Addendum 2026-05-28: este archivo debe leerse como snapshot histórico previo al cierre de aliases y a la normalización global de casing. La verdad viva actual del router está en `php public/cli.php route:list --json` y `docs/runtime-module-catalog.md`.
- Decisión operativa 2026-05-28: no reescribir este snapshot en caliente. Si se necesita una matriz viva nueva, debe generarse como documento separado a partir del router actual en lugar de sobrescribir esta evidencia histórica.

## Resumen por dominio

- `administration/access`: 22
- `administration/content`: 38
- `administration/operations`: 21
- `administration/platform`: 15
- `api-autenticada`: 6
- `api-tokenizada`: 12
- `auth-flow`: 17
- `devtools`: 52
- `public`: 10
- `public-api`: 3
- `shell-runtime`: 1
- `workspace`: 24
- `workspace-api`: 1

## Resumen por tipo

- `alias-legacy`: 14
- `api`: 22
- `contextual`: 33
- `entrada-canonica`: 28
- `flujo`: 5
- `helper-tecnico`: 30
- `mutacion`: 90

## Matriz 100% runtime

| Ruta | Métodos | Módulo | Superficie | Dominio | Tipo | Sidebar | Submenu | Contextual | Solo URL/API | Alias legacy | Deprecación futura |
|---|---|---|---|---|---|---|---|---|---|---|---|
| `/` | `GET,HEAD` | `app.surface.home` | `public` | `public` | `entrada-canonica` | `publico` | `no` | `no` | `no` | `no` | `no` |
| `/Dashboard` | `GET,HEAD` | `app.surface.dashboard` | `workspace` | `workspace` | `alias-legacy` | `no` | `no` | `no` | `si` | `si` | `candidata` |
| `/Home` | `GET,HEAD` | `app.surface.home` | `public` | `public` | `alias-legacy` | `no` | `no` | `no` | `si` | `si` | `candidata` |
| `/Landing` | `GET,HEAD` | `app.surface.landing` | `public` | `public` | `alias-legacy` | `no` | `no` | `no` | `si` | `si` | `candidata` |
| `/Setup` | `GET,HEAD` | `framework.settings` | `workspace` | `workspace` | `alias-legacy` | `no` | `no` | `no` | `si` | `si` | `candidata` |
| `/Store` | `GET,HEAD` | `app.surface.store` | `public` | `public` | `alias-legacy` | `no` | `no` | `no` | `si` | `si` | `candidata` |
| `/api-platform` | `GET,HEAD` | `framework.apiplatform` | `administration` | `administration/platform` | `entrada-canonica` | `principal` | `no` | `no` | `no` | `no` | `no` |
| `/api-platform/tokens` | `POST` | `framework.apiplatform` | `administration` | `administration/platform` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/api-platform/tokens/{id}/revoke` | `POST` | `framework.apiplatform` | `administration` | `administration/platform` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/api/notifications` | `GET,HEAD` | `framework.notification` | `authenticated-api` | `api-autenticada` | `api` | `no` | `no` | `no` | `si` | `no` | `no` |
| `/api/notifications/read-all` | `POST` | `framework.notification` | `authenticated-api` | `api-autenticada` | `api` | `no` | `no` | `no` | `si` | `no` | `no` |
| `/api/notifications/unread-count` | `GET,HEAD` | `framework.notification` | `authenticated-api` | `api-autenticada` | `api` | `no` | `no` | `no` | `si` | `no` | `no` |
| `/api/notifications/{id}/read` | `POST` | `framework.notification` | `authenticated-api` | `api-autenticada` | `api` | `no` | `no` | `no` | `si` | `no` | `no` |
| `/api/presence/{resourceKey}/{recordId}/heartbeat` | `POST` | `framework.notification` | `authenticated-api` | `api-autenticada` | `api` | `no` | `no` | `no` | `si` | `no` | `no` |
| `/api/public/dashboard` | `GET,HEAD` | `app.surface.dashboard` | `workspace` | `workspace-api` | `api` | `no` | `no` | `no` | `si` | `no` | `no` |
| `/api/public/home` | `GET,HEAD` | `app.surface.home` | `public` | `public-api` | `api` | `no` | `no` | `no` | `si` | `no` | `no` |
| `/api/public/landing` | `GET,HEAD` | `app.surface.landing` | `public` | `public-api` | `api` | `no` | `no` | `no` | `si` | `no` | `no` |
| `/api/public/store` | `GET,HEAD` | `app.surface.store` | `public` | `public-api` | `api` | `no` | `no` | `no` | `si` | `no` | `no` |
| `/api/v1/automation-rules` | `GET,HEAD` | `framework.automation` | `administration` | `api-tokenizada` | `api` | `no` | `no` | `no` | `si` | `no` | `no` |
| `/api/v1/automation-rules/{id}` | `GET,HEAD` | `framework.automation` | `administration` | `api-tokenizada` | `api` | `no` | `no` | `no` | `si` | `no` | `no` |
| `/api/v1/automation-rules/{id}/run` | `POST` | `framework.automation` | `administration` | `api-tokenizada` | `api` | `no` | `no` | `no` | `si` | `no` | `no` |
| `/api/v1/catalog` | `GET,HEAD` | `framework.apiplatform` | `administration` | `api-tokenizada` | `api` | `no` | `no` | `no` | `si` | `no` | `no` |
| `/api/v1/document-templates` | `GET,HEAD` | `framework.documents` | `administration` | `api-tokenizada` | `api` | `no` | `no` | `no` | `si` | `no` | `no` |
| `/api/v1/document-templates/{id}` | `GET,HEAD` | `framework.documents` | `administration` | `api-tokenizada` | `api` | `no` | `no` | `no` | `si` | `no` | `no` |
| `/api/v1/document-templates/{id}/export` | `POST` | `framework.documents` | `administration` | `api-tokenizada` | `api` | `no` | `no` | `no` | `si` | `no` | `no` |
| `/api/v1/document-templates/{id}/preview` | `POST` | `framework.documents` | `administration` | `api-tokenizada` | `api` | `no` | `no` | `no` | `si` | `no` | `no` |
| `/api/v1/versions/{id}/restore` | `POST` | `framework.apiplatform` | `administration` | `api-tokenizada` | `api` | `no` | `no` | `no` | `si` | `no` | `no` |
| `/api/v1/versions/{resourceKey}/{recordId}` | `GET,HEAD` | `framework.apiplatform` | `administration` | `api-tokenizada` | `api` | `no` | `no` | `no` | `si` | `no` | `no` |
| `/api/v1/workflows` | `GET,HEAD` | `framework.apiplatform` | `administration` | `api-tokenizada` | `api` | `no` | `no` | `no` | `si` | `no` | `no` |
| `/api/v1/workflows/{id}/transition` | `POST` | `framework.apiplatform` | `administration` | `api-tokenizada` | `api` | `no` | `no` | `no` | `si` | `no` | `no` |
| `/api/ws-token` | `GET,HEAD` | `framework.notification` | `authenticated-api` | `api-autenticada` | `api` | `no` | `no` | `no` | `si` | `no` | `no` |
| `/audit-log` | `GET,HEAD` | `framework.audit` | `administration` | `administration/platform` | `entrada-canonica` | `principal` | `no` | `no` | `no` | `no` | `no` |
| `/audit-log/{id}` | `GET,HEAD` | `framework.audit` | `administration` | `administration/platform` | `contextual` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/auth/social/callback/{provider}` | `GET,HEAD` | `framework.auth` | `auth-flow` | `auth-flow` | `flujo` | `no` | `no` | `si` | `si` | `no` | `no` |
| `/auth/social/{provider}` | `GET,HEAD` | `framework.auth` | `auth-flow` | `auth-flow` | `contextual` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/automation-rules` | `GET,HEAD` | `framework.automation` | `administration` | `administration/platform` | `entrada-canonica` | `principal` | `no` | `no` | `no` | `no` | `no` |
| `/automation-rules` | `POST` | `framework.automation` | `administration` | `administration/platform` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/automation-rules/create` | `GET,HEAD` | `framework.automation` | `administration` | `administration/platform` | `contextual` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/automation-rules/{id}` | `GET,HEAD` | `framework.automation` | `administration` | `administration/platform` | `contextual` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/automation-rules/{id}` | `POST` | `framework.automation` | `administration` | `administration/platform` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/automation-rules/{id}/delete` | `POST` | `framework.automation` | `administration` | `administration/platform` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/automation-rules/{id}/edit` | `GET,HEAD` | `framework.automation` | `administration` | `administration/platform` | `contextual` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/automation-rules/{id}/run` | `POST` | `framework.automation` | `administration` | `administration/platform` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/automation-rules/{id}/transition` | `POST` | `framework.automation` | `administration` | `administration/platform` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/automation-rules/{id}/versions/{versionId}/restore` | `POST` | `framework.automation` | `administration` | `administration/platform` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/catalogs` | `GET,HEAD` | `framework.catalogs` | `administration` | `administration/content` | `entrada-canonica` | `principal` | `no` | `no` | `no` | `no` | `no` |
| `/catalogs` | `POST` | `framework.catalogs` | `administration` | `administration/content` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/catalogs/create` | `GET,HEAD` | `framework.catalogs` | `administration` | `administration/content` | `contextual` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/catalogs/{id}` | `GET,HEAD` | `framework.catalogs` | `administration` | `administration/content` | `contextual` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/catalogs/{id}` | `POST` | `framework.catalogs` | `administration` | `administration/content` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/catalogs/{id}/delete` | `POST` | `framework.catalogs` | `administration` | `administration/content` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/catalogs/{id}/edit` | `GET,HEAD` | `framework.catalogs` | `administration` | `administration/content` | `contextual` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/catalogs/{id}/items` | `POST` | `framework.catalogs` | `administration` | `administration/content` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/catalogs/{id}/items/create` | `GET,HEAD` | `framework.catalogs` | `administration` | `administration/content` | `contextual` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/catalogs/{id}/items/{itemId}` | `POST` | `framework.catalogs` | `administration` | `administration/content` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/catalogs/{id}/items/{itemId}/delete` | `POST` | `framework.catalogs` | `administration` | `administration/content` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/catalogs/{id}/items/{itemId}/edit` | `GET,HEAD` | `framework.catalogs` | `administration` | `administration/content` | `contextual` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/catalogs/{id}/transition` | `POST` | `framework.catalogs` | `administration` | `administration/content` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/catalogs/{id}/versions/{versionId}/restore` | `POST` | `framework.catalogs` | `administration` | `administration/content` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/dashboard` | `GET,HEAD` | `app.surface.dashboard` | `workspace` | `workspace` | `entrada-canonica` | `principal` | `no` | `no` | `no` | `no` | `no` |
| `/dashboard/pulse` | `GET,HEAD` | `app.surface.dashboard` | `workspace` | `workspace` | `contextual` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/document-templates` | `GET,HEAD` | `framework.documents` | `administration` | `administration/content` | `entrada-canonica` | `principal` | `no` | `no` | `no` | `no` | `no` |
| `/document-templates` | `POST` | `framework.documents` | `administration` | `administration/content` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/document-templates/create` | `GET,HEAD` | `framework.documents` | `administration` | `administration/content` | `contextual` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/document-templates/{id}` | `GET,HEAD` | `framework.documents` | `administration` | `administration/content` | `contextual` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/document-templates/{id}` | `POST` | `framework.documents` | `administration` | `administration/content` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/document-templates/{id}/delete` | `POST` | `framework.documents` | `administration` | `administration/content` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/document-templates/{id}/edit` | `GET,HEAD` | `framework.documents` | `administration` | `administration/content` | `contextual` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/document-templates/{id}/export` | `POST` | `framework.documents` | `administration` | `administration/content` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/document-templates/{id}/preview` | `POST` | `framework.documents` | `administration` | `administration/content` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/document-templates/{id}/transition` | `POST` | `framework.documents` | `administration` | `administration/content` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/document-templates/{id}/versions/{versionId}/restore` | `POST` | `framework.documents` | `administration` | `administration/content` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/flash/dismiss` | `POST` | `framework.shell` | `runtime` | `shell-runtime` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/forgot-password` | `GET,HEAD` | `framework.auth` | `auth-flow` | `auth-flow` | `contextual` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/forgot-password` | `POST` | `framework.auth` | `auth-flow` | `auth-flow` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/health` | `GET,HEAD` | `framework.settings` | `workspace` | `workspace` | `entrada-canonica` | `principal` | `no` | `no` | `no` | `no` | `no` |
| `/health/live` | `GET,HEAD` | `framework.settings` | `workspace` | `workspace` | `contextual` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/health/ready` | `GET,HEAD` | `framework.settings` | `workspace` | `workspace` | `contextual` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/home` | `GET,HEAD` | `app.surface.home` | `public` | `public` | `contextual` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/home/journey` | `GET,HEAD` | `app.surface.home` | `public` | `public` | `contextual` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/index` | `GET,HEAD` | `framework.devtools` | `devtools` | `devtools` | `alias-legacy` | `no` | `no` | `no` | `si` | `si` | `candidata` |
| `/index.php` | `GET,HEAD` | `framework.devtools` | `devtools` | `devtools` | `alias-legacy` | `no` | `no` | `no` | `si` | `si` | `candidata` |
| `/landing` | `GET,HEAD` | `app.surface.landing` | `public` | `public` | `entrada-canonica` | `publico` | `no` | `no` | `no` | `no` | `no` |
| `/landing/highlights` | `GET,HEAD` | `app.surface.landing` | `public` | `public` | `contextual` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/login` | `GET,HEAD` | `framework.auth` | `auth-flow` | `auth-flow` | `contextual` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/login` | `POST` | `framework.auth` | `auth-flow` | `auth-flow` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/logout` | `POST` | `framework.auth` | `auth-flow` | `auth-flow` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/media-fields` | `GET,HEAD` | `framework.media` | `administration` | `administration/content` | `entrada-canonica` | `principal` | `no` | `no` | `no` | `no` | `no` |
| `/media-fields` | `POST` | `framework.media` | `administration` | `administration/content` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/media-fields/create` | `GET,HEAD` | `framework.media` | `administration` | `administration/content` | `contextual` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/media-fields/{id}` | `POST` | `framework.media` | `administration` | `administration/content` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/media-fields/{id}/delete` | `POST` | `framework.media` | `administration` | `administration/content` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/media-fields/{id}/edit` | `GET,HEAD` | `framework.media` | `administration` | `administration/content` | `contextual` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/media-library` | `GET,HEAD` | `framework.media` | `administration` | `administration/content` | `entrada-canonica` | `principal` | `no` | `no` | `no` | `no` | `no` |
| `/media-library` | `POST` | `framework.media` | `administration` | `administration/content` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/media-library/bulk-delete` | `POST` | `framework.media` | `administration` | `administration/content` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/media-library/upload` | `GET,HEAD` | `framework.media` | `administration` | `administration/content` | `contextual` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/media-library/{id}` | `POST` | `framework.media` | `administration` | `administration/content` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/media-library/{id}/delete` | `POST` | `framework.media` | `administration` | `administration/content` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/media-library/{id}/edit` | `GET,HEAD` | `framework.media` | `administration` | `administration/content` | `contextual` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/mfa/challenge` | `GET,HEAD` | `framework.auth` | `auth-flow` | `auth-flow` | `flujo` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/mfa/disable` | `POST` | `framework.auth` | `auth-flow` | `auth-flow` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/mfa/enable` | `POST` | `framework.auth` | `auth-flow` | `auth-flow` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/mfa/setup` | `GET,HEAD` | `framework.auth` | `auth-flow` | `auth-flow` | `flujo` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/mfa/verify` | `POST` | `framework.auth` | `auth-flow` | `auth-flow` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/operations` | `GET,HEAD` | `framework.operations` | `administration` | `administration/operations` | `entrada-canonica` | `principal` | `no` | `no` | `no` | `no` | `no` |
| `/configuration/platform-appearance` | `GET,HEAD` | `framework.operations` | `administration` | `administration/operations` | `entrada-canonica` | `submenu` | `Operations` | `no` | `no` | `no` | `no` |
| `/configuration/platform-appearance` | `POST` | `framework.operations` | `administration` | `administration/operations` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/operations/deployments` | `GET,HEAD` | `framework.operations` | `administration` | `administration/operations` | `entrada-canonica` | `submenu` | `Operations` | `no` | `no` | `no` | `no` |
| `/operations/deployments/runs` | `POST` | `framework.operations` | `administration` | `administration/operations` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/operations/feature-flags` | `GET,HEAD` | `framework.operations` | `administration` | `administration/operations` | `entrada-canonica` | `submenu` | `Operations` | `no` | `no` | `no` | `no` |
| `/operations/feature-flags/defaults/{flagKey}` | `POST` | `framework.operations` | `administration` | `administration/operations` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/operations/feature-flags/overrides` | `POST` | `framework.operations` | `administration` | `administration/operations` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/operations/feature-flags/overrides/{id}/delete` | `POST` | `framework.operations` | `administration` | `administration/operations` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/operations/localization` | `GET,HEAD` | `framework.operations` | `administration` | `administration/operations` | `entrada-canonica` | `submenu` | `Operations` | `no` | `no` | `no` | `no` |
| `/operations/localization/create-locale` | `POST` | `framework.operations` | `administration` | `administration/operations` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/operations/localization/settings` | `POST` | `framework.operations` | `administration` | `administration/operations` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/operations/localization/sync-locale` | `POST` | `framework.operations` | `administration` | `administration/operations` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/operations/module-designer` | `GET,HEAD` | `framework.operations` | `administration` | `administration/operations` | `entrada-canonica` | `submenu` | `Operations` | `no` | `no` | `no` | `no` |
| `/operations/module-designer/generate` | `GET,HEAD` | `framework.operations` | `administration` | `administration/operations` | `alias-legacy` | `no` | `no` | `no` | `si` | `si` | `candidata` |
| `/operations/module-designer/generate` | `POST` | `framework.operations` | `administration` | `administration/operations` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/operations/module-designer/preview` | `GET,HEAD` | `framework.operations` | `administration` | `administration/operations` | `alias-legacy` | `no` | `no` | `no` | `si` | `si` | `candidata` |
| `/operations/module-designer/preview` | `POST` | `framework.operations` | `administration` | `administration/operations` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/operations/plugins` | `GET,HEAD` | `framework.operations` | `administration` | `administration/operations` | `entrada-canonica` | `submenu` | `Operations` | `no` | `no` | `no` | `no` |
| `/operations/plugins/{pluginKey}/toggle` | `POST` | `framework.operations` | `administration` | `administration/operations` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/operations/tenancy` | `GET,HEAD` | `framework.operations` | `administration` | `administration/operations` | `entrada-canonica` | `submenu` | `Operations` | `no` | `no` | `no` | `no` |
| `/permissions` | `GET,HEAD` | `framework.roles` | `administration` | `administration/access` | `entrada-canonica` | `principal` | `no` | `no` | `no` | `no` | `no` |
| `/permissions` | `POST` | `framework.roles` | `administration` | `administration/access` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/permissions/bulk-delete` | `POST` | `framework.roles` | `administration` | `administration/access` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/permissions/create` | `GET,HEAD` | `framework.roles` | `administration` | `administration/access` | `contextual` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/permissions/{id}` | `POST` | `framework.roles` | `administration` | `administration/access` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/permissions/{id}/delete` | `POST` | `framework.roles` | `administration` | `administration/access` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/permissions/{id}/edit` | `GET,HEAD` | `framework.roles` | `administration` | `administration/access` | `contextual` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/register` | `GET,HEAD` | `framework.auth` | `auth-flow` | `auth-flow` | `contextual` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/register` | `POST` | `framework.auth` | `auth-flow` | `auth-flow` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/reset-password/{token}` | `GET,HEAD` | `framework.auth` | `auth-flow` | `auth-flow` | `flujo` | `no` | `no` | `si` | `si` | `no` | `no` |
| `/reset-password/{token}` | `POST` | `framework.auth` | `auth-flow` | `auth-flow` | `mutacion` | `no` | `no` | `si` | `si` | `no` | `no` |
| `/roles` | `GET,HEAD` | `framework.roles` | `administration` | `administration/access` | `entrada-canonica` | `principal` | `no` | `no` | `no` | `no` | `no` |
| `/roles` | `POST` | `framework.roles` | `administration` | `administration/access` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/roles/bulk-delete` | `POST` | `framework.roles` | `administration` | `administration/access` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/roles/create` | `GET,HEAD` | `framework.roles` | `administration` | `administration/access` | `contextual` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/roles/{id}` | `POST` | `framework.roles` | `administration` | `administration/access` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/roles/{id}/delete` | `POST` | `framework.roles` | `administration` | `administration/access` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/roles/{id}/edit` | `GET,HEAD` | `framework.roles` | `administration` | `administration/access` | `contextual` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/roles/{id}/permissions` | `GET,HEAD` | `framework.roles` | `administration` | `administration/access` | `contextual` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/roles/{id}/permissions` | `POST` | `framework.roles` | `administration` | `administration/access` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/setup` | `GET,HEAD` | `framework.settings` | `workspace` | `workspace` | `entrada-canonica` | `principal` | `no` | `no` | `no` | `no` | `no` |
| `/setup/admin` | `POST` | `framework.settings` | `workspace` | `workspace` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/setup/app` | `POST` | `framework.settings` | `workspace` | `workspace` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/setup/cache` | `POST` | `framework.settings` | `workspace` | `workspace` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/setup/complete` | `POST` | `framework.settings` | `workspace` | `workspace` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/setup/cors` | `POST` | `framework.settings` | `workspace` | `workspace` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/setup/db` | `POST` | `framework.settings` | `workspace` | `workspace` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/setup/devtools` | `POST` | `framework.settings` | `workspace` | `workspace` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/setup/dkim/generate` | `POST` | `framework.settings` | `workspace` | `workspace` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/setup/ftp` | `POST` | `framework.settings` | `workspace` | `workspace` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/setup/ftp/pretest` | `POST` | `framework.settings` | `workspace` | `workspace` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/setup/logging` | `POST` | `framework.settings` | `workspace` | `workspace` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/setup/mail` | `POST` | `framework.settings` | `workspace` | `workspace` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/setup/reset` | `POST` | `framework.settings` | `workspace` | `workspace` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/setup/security` | `POST` | `framework.settings` | `workspace` | `workspace` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/setup/session` | `POST` | `framework.settings` | `workspace` | `workspace` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/setup/websocket` | `POST` | `framework.settings` | `workspace` | `workspace` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/store` | `GET,HEAD` | `app.surface.store` | `public` | `public` | `entrada-canonica` | `publico` | `no` | `no` | `no` | `no` | `no` |
| `/store/catalog` | `GET,HEAD` | `app.surface.store` | `public` | `public` | `contextual` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/test-features` | `GET,HEAD` | `framework.devtools` | `devtools` | `devtools` | `entrada-canonica` | `principal` | `no` | `no` | `no` | `no` | `no` |
| `/test-features/api-response` | `GET,HEAD` | `framework.devtools` | `devtools` | `devtools` | `helper-tecnico` | `no` | `no` | `si` | `si` | `no` | `no` |
| `/test-features/api/js-enhancements/partial-refresh` | `GET,HEAD` | `framework.devtools` | `devtools` | `devtools` | `helper-tecnico` | `no` | `no` | `si` | `si` | `no` | `no` |
| `/test-features/api/modal-trigger` | `GET,HEAD` | `framework.devtools` | `devtools` | `devtools` | `helper-tecnico` | `no` | `no` | `si` | `si` | `no` | `no` |
| `/test-features/api/multiple-toasters` | `GET,HEAD` | `framework.devtools` | `devtools` | `devtools` | `helper-tecnico` | `no` | `no` | `si` | `si` | `no` | `no` |
| `/test-features/api/toaster-error` | `GET,HEAD` | `framework.devtools` | `devtools` | `devtools` | `helper-tecnico` | `no` | `no` | `si` | `si` | `no` | `no` |
| `/test-features/api/toaster-info` | `GET,HEAD` | `framework.devtools` | `devtools` | `devtools` | `helper-tecnico` | `no` | `no` | `si` | `si` | `no` | `no` |
| `/test-features/api/toaster-success` | `GET,HEAD` | `framework.devtools` | `devtools` | `devtools` | `helper-tecnico` | `no` | `no` | `si` | `si` | `no` | `no` |
| `/test-features/api/toaster-warning` | `GET,HEAD` | `framework.devtools` | `devtools` | `devtools` | `helper-tecnico` | `no` | `no` | `si` | `si` | `no` | `no` |
| `/test-features/api/validator-test` | `POST` | `framework.devtools` | `devtools` | `devtools` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/test-features/api/validator-unique` | `POST` | `framework.devtools` | `devtools` | `devtools` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/test-features/cors-headers` | `GET,HEAD` | `framework.devtools` | `devtools` | `devtools` | `helper-tecnico` | `no` | `no` | `si` | `si` | `no` | `no` |
| `/test-features/db-connection` | `GET,HEAD` | `framework.devtools` | `devtools` | `devtools` | `helper-tecnico` | `no` | `no` | `si` | `si` | `no` | `no` |
| `/test-features/db-reset` | `POST` | `framework.devtools` | `devtools` | `devtools` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/test-features/e-helper` | `GET,HEAD` | `framework.devtools` | `devtools` | `devtools` | `helper-tecnico` | `no` | `no` | `si` | `si` | `no` | `no` |
| `/test-features/flash/clear` | `GET,HEAD` | `framework.devtools` | `devtools` | `devtools` | `helper-tecnico` | `no` | `no` | `si` | `si` | `no` | `no` |
| `/test-features/flash/{type}` | `GET,HEAD` | `framework.devtools` | `devtools` | `devtools` | `helper-tecnico` | `no` | `no` | `si` | `si` | `no` | `no` |
| `/test-features/flash/{type}/persistent` | `GET,HEAD` | `framework.devtools` | `devtools` | `devtools` | `helper-tecnico` | `no` | `no` | `si` | `si` | `no` | `no` |
| `/test-features/form-demo` | `POST` | `framework.devtools` | `devtools` | `devtools` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/test-features/i18n` | `GET,HEAD` | `framework.devtools` | `devtools` | `devtools` | `helper-tecnico` | `no` | `no` | `si` | `si` | `no` | `no` |
| `/test-features/i18n/set-locale` | `POST` | `framework.devtools` | `devtools` | `devtools` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/test-features/infra` | `GET,HEAD` | `framework.devtools` | `devtools` | `devtools` | `helper-tecnico` | `no` | `no` | `si` | `si` | `no` | `no` |
| `/test-features/json` | `GET,HEAD` | `framework.devtools` | `devtools` | `devtools` | `helper-tecnico` | `no` | `no` | `si` | `si` | `no` | `no` |
| `/test-features/json-error` | `GET,HEAD` | `framework.devtools` | `devtools` | `devtools` | `helper-tecnico` | `no` | `no` | `si` | `si` | `no` | `no` |
| `/test-features/json-success` | `GET,HEAD` | `framework.devtools` | `devtools` | `devtools` | `helper-tecnico` | `no` | `no` | `si` | `si` | `no` | `no` |
| `/test-features/layout-test` | `GET,HEAD` | `framework.devtools` | `devtools` | `devtools` | `helper-tecnico` | `no` | `no` | `si` | `si` | `no` | `no` |
| `/test-features/logger-email` | `GET,HEAD` | `framework.devtools` | `devtools` | `devtools` | `helper-tecnico` | `no` | `no` | `si` | `si` | `no` | `no` |
| `/test-features/mail-test` | `POST` | `framework.devtools` | `devtools` | `devtools` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/test-features/make-admin` | `POST` | `framework.devtools` | `devtools` | `devtools` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/test-features/modal/form-content` | `GET,HEAD` | `framework.devtools` | `devtools` | `devtools` | `helper-tecnico` | `no` | `no` | `si` | `si` | `no` | `no` |
| `/test-features/modal/form-submit` | `POST` | `framework.devtools` | `devtools` | `devtools` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/test-features/modal/sample-content` | `GET,HEAD` | `framework.devtools` | `devtools` | `devtools` | `helper-tecnico` | `no` | `no` | `si` | `si` | `no` | `no` |
| `/test-features/module-designer` | `GET,HEAD` | `framework.operations` | `administration` | `devtools` | `alias-legacy` | `no` | `no` | `no` | `si` | `si` | `candidata` |
| `/test-features/module-designer/generate` | `GET,HEAD` | `framework.operations` | `administration` | `devtools` | `alias-legacy` | `no` | `no` | `no` | `si` | `si` | `candidata` |
| `/test-features/module-designer/generate` | `POST` | `framework.operations` | `administration` | `devtools` | `alias-legacy` | `no` | `no` | `no` | `si` | `si` | `candidata` |
| `/test-features/module-designer/preview` | `GET,HEAD` | `framework.operations` | `administration` | `devtools` | `alias-legacy` | `no` | `no` | `no` | `si` | `si` | `candidata` |
| `/test-features/module-designer/preview` | `POST` | `framework.operations` | `administration` | `devtools` | `alias-legacy` | `no` | `no` | `no` | `si` | `si` | `candidata` |
| `/test-features/orm/create` | `POST` | `framework.devtools` | `devtools` | `devtools` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/test-features/orm/delete-latest` | `POST` | `framework.devtools` | `devtools` | `devtools` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/test-features/orm/find-or-fail` | `GET,HEAD` | `framework.devtools` | `devtools` | `devtools` | `helper-tecnico` | `no` | `no` | `si` | `si` | `no` | `no` |
| `/test-features/orm/status` | `GET,HEAD` | `framework.devtools` | `devtools` | `devtools` | `helper-tecnico` | `no` | `no` | `si` | `si` | `no` | `no` |
| `/test-features/orm/update` | `POST` | `framework.devtools` | `devtools` | `devtools` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/test-features/orm/user-demo` | `GET,HEAD` | `framework.devtools` | `devtools` | `devtools` | `helper-tecnico` | `no` | `no` | `si` | `si` | `no` | `no` |
| `/test-features/rbac-status` | `GET,HEAD` | `framework.devtools` | `devtools` | `devtools` | `helper-tecnico` | `no` | `no` | `si` | `si` | `no` | `no` |
| `/test-features/route-cache` | `GET,HEAD` | `framework.devtools` | `devtools` | `devtools` | `helper-tecnico` | `no` | `no` | `si` | `si` | `no` | `no` |
| `/test-features/ui-showcase` | `GET,HEAD` | `framework.devtools` | `devtools` | `devtools` | `entrada-canonica` | `principal` | `no` | `no` | `no` | `no` | `no` |
| `/test-features/upload` | `POST` | `framework.devtools` | `devtools` | `devtools` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/test-features/validation-error` | `GET,HEAD` | `framework.devtools` | `devtools` | `devtools` | `helper-tecnico` | `no` | `no` | `si` | `si` | `no` | `no` |
| `/test-layout` | `GET,HEAD` | `framework.devtools` | `devtools` | `devtools` | `helper-tecnico` | `no` | `no` | `si` | `si` | `no` | `no` |
| `/uml` | `GET,HEAD` | `framework.devtools` | `devtools` | `devtools` | `entrada-canonica` | `principal` | `no` | `no` | `no` | `no` | `no` |
| `/users` | `GET,HEAD` | `framework.roles` | `administration` | `administration/access` | `entrada-canonica` | `principal` | `no` | `no` | `no` | `no` | `no` |
| `/users/register` | `GET,HEAD` | `framework.roles` | `administration` | `administration/access` | `entrada-canonica` | `submenu` | `Users` | `no` | `no` | `no` | `no` |
| `/users/register` | `POST` | `framework.roles` | `administration` | `administration/access` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/users/{userId}/roles` | `GET,HEAD` | `framework.roles` | `administration` | `administration/access` | `contextual` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/users/{userId}/roles/{roleId}` | `POST` | `framework.roles` | `administration` | `administration/access` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/users/{userId}/roles/{roleId}/remove` | `POST` | `framework.roles` | `administration` | `administration/access` | `mutacion` | `no` | `no` | `si` | `no` | `no` | `no` |
| `/verify-email/{token}` | `GET,HEAD` | `framework.auth` | `auth-flow` | `auth-flow` | `flujo` | `no` | `no` | `si` | `si` | `no` | `no` |

## Reglas derivadas

- `principal`: entrada canónica de dominio en sidebar autenticado.
- `submenu`: entrada visible bajo un dominio padre, sin duplicar la misma URL como entrada plana.
- `contextual`: alcanzable desde listados, formularios, filas, flujos o acciones internas.
- `solo URL/API`: endpoints técnicos, APIs, callbacks, tokens, helpers o aliases que no deben contaminar navegación primaria.
- `alias-legacy`: ruta conservada por compatibilidad; candidata a deprecación futura solo con estrategia explícita.
