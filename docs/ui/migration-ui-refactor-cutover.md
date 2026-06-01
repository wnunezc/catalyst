# Migration UI Refactor — Cutover Patch

Status: implementado para revisión local  
Fecha: 2026-05-27  
Alcance: normalización de rutas complejas, navegación administrativa y compatibilidad legacy controlada.

## Decisión aplicada

Este parche conserva las rutas simples que ya funcionan como superficies finales del producto y normaliza las rutas complejas hacia la taxonomía del menú izquierdo validada en `MigrationUi`.

Rutas simples preservadas:

- `/`
- `/landing`
- `/store`
- `/dashboard`
- `/operations/deployments`
- `/operations/tenancy`
- `/users`

Rutas complejas normalizadas con compatibilidad legacy:

- `/setup` → `/configuration/environment-setup`
- `/health` → `/configuration/application-health`
- `/configuration/platform-appearance` es la superficie canónica de apariencia
- `/operations/feature-flags` → `/configuration/feature-flags`
- `/operations/plugins` → `/configuration/plugins`
- nueva superficie `/configuration/backups` y alias `/configuration/manage-backup`
- `/operations/module-designer` → `/workspaces/module-designer`
- `/operations/localization` → `/workspaces/locale-tools`
- `/catalogs*` → `/workspaces/catalogs*`
- `/media-fields*` → `/workspaces/media-fields*`
- `/media-library*` → `/workspaces/media-library*`
- `/document-templates*` → `/workspaces/document-templates*`
- `/roles*` → `/users/roles*`
- `/permissions*` → `/users/permissions*`
- `/users/register` → `/users/enroll`

Las rutas legacy quedan activas como compatibilidad temporal para no romper enlaces, hábitos operativos ni pruebas existentes.

## Menú izquierdo

La navegación administrativa queda organizada por familias:

- `Configuration`
- `Workspaces`
- `Operations`
- `Users`

El registry mantiene normalización de contextos antiguos (`workspace`, `administration`) hacia la taxonomía nueva para reducir fricción durante el corte.

## Superficies nuevas o ajustadas

### Configuration

- `Environment Setup` queda disponible en `/configuration/environment-setup` sin romper `/setup`.
- `Application Health` queda disponible en `/configuration/application-health` sin romper `/health` ni sus probes.
- `Platform Appearance`, `Feature Flags` y `Plugins` salen conceptualmente de `Operations` y quedan bajo `Configuration`.
- `Manage Backup` se crea como superficie nueva de entrada visual, sin inventar backend de mutaciones.

### Workspaces

- `Catalogs`, `Media Fields`, `Media Library`, `Document Templates`, `Module Designer` y `Locale Tools` quedan bajo prefijos normalizados `/workspaces/*`.
- Las rutas legacy permanecen activas como aliases temporales.
- Las APIs `/api/v1/document-templates*` se preservan sin cambios de contrato.

### Users

- `User Management` permanece en `/users`.
- `Roles` queda normalizado en `/users/roles*`.
- `Permissions` queda normalizado en `/users/permissions*`.
- `User Enroll` queda normalizado en `/users/enroll`.
- Las rutas `/roles*`, `/permissions*` y `/users/register` siguen disponibles como compatibilidad temporal.

## Limpieza legacy

No se eliminan archivos legacy en este parche porque la compatibilidad temporal sigue siendo parte del contrato de migración. La limpieza física de vistas/assets debe ejecutarse cuando el usuario confirme que las rutas normalizadas ya fueron validadas en entorno real.

## Validación esperada en local

Ejecutar en el entorno local con `mbstring`, `pdo_mysql` y Composer disponibles:

```powershell
composer dump-autoload -o
php public/cli.php route:clear
php public/cli.php route:lint
php public/cli.php inspect:lint
php public/cli.php route:list
```

Superficies recomendadas para prueba manual/E2E:

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

## Nota de decommission

Cuando el usuario confirme el corte final, se puede abrir una fase posterior para eliminar aliases temporales, vistas legacy no usadas y assets huérfanos. Este parche no ejecuta esa eliminación para evitar romper compatibilidad antes de la validación local real.
