# Migration UI cutover aplicado

Fecha: 2026-05-27
Estado: implementado para validación local del usuario

Este parche aplica el cutover visual del plan de migración usando el shell INSPINIA como baseline de las rutas canónicas del producto.

## Decisiones aplicadas

- Las rutas simples públicas se preservan: `/`, `/home`, `/landing`, `/store`, `/dashboard`.
- Las rutas complejas se normalizan hacia la taxonomía final del menú izquierdo: `Configuration`, `Workspaces`, `Operations`, `Users`.
- Las rutas legacy quedan vivas como compatibilidad temporal.
- Las rutas normalizadas apuntan a los controladores reales existentes para preservar lógica, permisos, mutaciones y servicios.
- El layout `admin` carga el baseline visual de INSPINIA para que las rutas canónicas no sigan viéndose como shell legacy.

## Rutas normalizadas principales

- `/configuration/environment-setup`
- `/configuration/application-health`
- `/configuration/platform-appearance`
- `/configuration/feature-flags`
- `/configuration/plugins`
- `/configuration/backups`
- `/workspaces/catalogs`
- `/workspaces/module-designer`
- `/workspaces/media-fields`
- `/workspaces/media-library`
- `/workspaces/document-templates`
- `/workspaces/locale-tools`
- `/operations/deployments`
- `/operations/tenancy`
- `/users`
- `/users/enroll`
- `/users/roles`
- `/users/permissions`

## Compatibilidad temporal

Se mantienen aliases/rutas legacy para no romper hábitos, enlaces internos ni POSTs durante la transición.

## Pendiente de retiro

No se elimina ningún archivo legacy en este parche. El retiro debe hacerse después de validación E2E real en el entorno local `https://catalyst.dock/`.
