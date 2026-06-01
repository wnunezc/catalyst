# Inventario de 99 rutas HTML auditadas

- Fuente: `visual-audit/route-coverage.csv`
- Universo: `99` entradas HTML auditadas visualmente
- Criterio de acceso:
  - `Pública`: accesible sin iniciar sesión
  - `Protegida`: requiere login
  - `Protegida especial`: requiere login y además contexto previo, token, MFA o registro existente
- Nota metodológica:
  - este inventario usa las `99` rutas del coverage visual, no las `222` rutas técnicas del router
  - varias rutas son alias, callbacks o helpers de prueba; por eso no todas aparecen en menús
  - la matriz canónica de decisión sidebar/submenu/contextual/solo URL para el inventario visual vive en `docs/navigation-route-refactor-plan.md`
  - la verdad viva del runtime actual vive en `php public/cli.php route:list --json` y `docs/runtime-module-catalog.md`
  - las filas en mayúscula de este inventario (`/Home`, `/Landing`, `/Store`, `/Dashboard`, `/Setup`) deben leerse como evidencia histórica del audit visual previo a la normalización; no como rutas registradas que sigan formando parte del contrato actual

## Taxonomía de navegación

Desde la fase 1 del refactor de navegación, el sidebar autenticado se organiza por dominios declarados desde metadata de módulo:

- `Workspace`
- `Administration` con grupos `Acceso y usuarios`, `Contenido y activos`, `Plataforma` y `Operación del framework`
- `DevTools`

Las rutas auxiliares (`create`, `edit`, `{id}`, callbacks, tokens, helpers y filas históricas previas al cutover) no se consideran entradas canónicas del sidebar salvo que la matriz indique una excepción.

Este documento no reemplaza la matriz de `222` rutas del router; su alcance sigue siendo HTML visual auditado.

## Resumen

- Públicas: `17`
- Protegidas: `82`
- Grupos:
  - Público principal: `10`
  - Auth y recuperación: `9`
  - Workspace y configuración: `6`
  - Plataforma y operaciones: `20`
  - Contenido y activos: `16`
  - Acceso y usuarios: `10`
  - DevTools: `28`

## 1. Público principal

| Ruta | Acceso | Cómo llegar | Solo por URL |
|---|---|---|---|
| `/` | Pública | Entrada principal del dominio `https://catalyst.dock/` | No |
| `/Home` | Pública | Evidencia histórica del audit visual previo al cierre de aliases; hoy el runtime normaliza casing hacia `/home` | Sí |
| `/home` | Pública | Navegación pública o URL directa | No |
| `/home/journey` | Pública | Desde Home por CTA/sección interna o URL directa | No |
| `/Landing` | Pública | Evidencia histórica del audit visual previo al cierre de aliases; hoy el runtime normaliza casing hacia `/landing` | Sí |
| `/landing` | Pública | Navegación pública o URL directa | No |
| `/landing/highlights` | Pública | Desde Landing por CTA/sección interna o URL directa | No |
| `/Store` | Pública | Evidencia histórica del audit visual previo al cierre de aliases; hoy el runtime normaliza casing hacia `/store` | Sí |
| `/store` | Pública | Navegación pública o URL directa | No |
| `/store/catalog` | Pública | Desde Store por CTA/sección interna o URL directa | No |

## 2. Auth y recuperación

| Ruta | Acceso | Cómo llegar | Solo por URL |
|---|---|---|---|
| `/auth/social/{provider}` | Pública | Botón OAuth en `/login`; también admite URL directa por proveedor | No |
| `/auth/social/callback/{provider}` | Pública especial | Callback de proveedor externo; no es navegación manual normal | Sí |
| `/forgot-password` | Pública | Link “Olvidé mi contraseña” desde `/login` | No |
| `/login` | Pública | URL directa o redirección desde rutas protegidas | No |
| `/mfa/challenge` | Protegida especial | Solo aparece después de completar usuario/clave con una cuenta que usa MFA | Sí |
| `/mfa/setup` | Protegida especial | Después de login, dentro del flujo de enrolamiento MFA o por URL directa | Sí |
| `/register` | Pública | Link de registro desde `/login` o URL directa | No |
| `/reset-password/{token}` | Pública especial | Se llega desde el enlace con token enviado por correo | Sí |
| `/verify-email/{token}` | Pública especial | Se llega desde el enlace de verificación enviado por correo | Sí |

## 3. Workspace y configuración

| Ruta | Acceso | Cómo llegar | Solo por URL |
|---|---|---|---|
| `/Dashboard` | Protegida | Evidencia histórica del audit visual previo al cierre de aliases; hoy el runtime normaliza casing hacia `/dashboard` | Sí |
| `/dashboard` | Protegida | Login exitoso; también como destino principal de workspace | No |
| `/dashboard/pulse` | Protegida especial | Ruta auxiliar del dashboard; normalmente uso técnico o URL directa | Sí |
| `/Setup` | Protegida | Evidencia histórica del audit visual previo al cutover de Configuration; la intención funcional actual vive en `/configuration/environment-setup` y el contrato vivo ya no registra `/setup` | Sí |
| `/health` | Protegida | Evidencia histórica previa al cutover de Configuration; la intención funcional actual vive en `/configuration/application-health` | No |
| `/setup` | Protegida | Evidencia histórica previa al cutover de Configuration; la intención funcional actual vive en `/configuration/environment-setup` | No |

## 4. Plataforma y operaciones

### Regla de navegación del grupo

- Las rutas de listado se alcanzan desde el menú lateral de Administración.
- Las rutas dinámicas `{id}` se alcanzan desde la grilla del módulo correspondiente.
- Las rutas `generate` y `preview` del diseñador son rutas auxiliares del mismo módulo.

### Plataforma

| Ruta | Acceso | Cómo llegar | Solo por URL |
|---|---|---|---|
| `/api-platform` | Protegida | Menú lateral > Administración > Plataforma > API Platform | No |
| `/audit-log` | Protegida | Menú lateral > Administración > Plataforma > Bitácora de auditoría | No |
| `/audit-log/{id}` | Protegida especial | Desde `/audit-log` > abrir una fila/entrada existente | No |
| `/automation-rules` | Protegida | Menú lateral > Administración > Plataforma > Reglas de automatización | No |
| `/automation-rules/create` | Protegida | Desde `/automation-rules` > acción “Crear” | No |
| `/automation-rules/{id}` | Protegida especial | Desde `/automation-rules` > abrir una regla existente | No |
| `/automation-rules/{id}/edit` | Protegida especial | Desde `/automation-rules` > editar una regla existente | No |

### Operaciones del framework

| Ruta | Acceso | Cómo llegar | Solo por URL |
|---|---|---|---|
| `/operations` | Protegida | Menú lateral > Administración > Operaciones | No |
| `/configuration/platform-appearance` | Protegida | Menú lateral > Administración > Operaciones > Apariencia | No |
| `/operations/deployments` | Protegida | Menú lateral > Administración > Operaciones > Deployments | No |
| `/operations/feature-flags` | Protegida | Menú lateral > Administración > Operaciones > Feature Flags | No |
| `/operations/localization` | Protegida | Menú lateral > Administración > Operaciones > Localización | No |
| `/operations/module-designer` | Protegida | Menú lateral > Administración > Operaciones > Module Designer | No |
| `/operations/module-designer/generate` | Protegida especial | Desde `/operations/module-designer`, al disparar la acción de generar; también URL directa | No |
| `/operations/module-designer/preview` | Protegida especial | Desde `/operations/module-designer`, al disparar la vista previa; también URL directa | No |
| `/operations/plugins` | Protegida | Menú lateral > Administración > Operaciones > Plugins | No |
| `/operations/tenancy` | Protegida | Menú lateral > Administración > Operaciones > Tenancy | No |
| `/test-features/module-designer` | Protegida | Alias legacy de DevTools; aterriza en el diseñador canónico | Sí |
| `/test-features/module-designer/generate` | Protegida especial | Alias legacy de acción generar del diseñador | Sí |
| `/test-features/module-designer/preview` | Protegida especial | Alias legacy de vista previa del diseñador | Sí |

## 5. Contenido y activos

### Regla de navegación del grupo

- Las pantallas base se alcanzan desde el menú lateral de Administración.
- Las rutas con `{id}` o `{itemId}` dependen de que exista un registro real.
- En la auditoría quedaron cubiertas con registros reales persistentes cuando el flujo lo exigía.

### Catálogos

| Ruta | Acceso | Cómo llegar | Solo por URL |
|---|---|---|---|
| `/catalogs` | Protegida | Menú lateral > Administración > Contenido y activos > Catálogos | No |
| `/catalogs/create` | Protegida | Desde `/catalogs` > acción “Crear” | No |
| `/catalogs/{id}` | Protegida especial | Desde `/catalogs` > abrir un catálogo existente | No |
| `/catalogs/{id}/edit` | Protegida especial | Desde `/catalogs` > editar un catálogo existente | No |
| `/catalogs/{id}/items/create` | Protegida especial | Desde el detalle del catálogo > crear item | No |
| `/catalogs/{id}/items/{itemId}/edit` | Protegida especial | Desde el detalle del catálogo > editar item existente | No |

### Documentos

| Ruta | Acceso | Cómo llegar | Solo por URL |
|---|---|---|---|
| `/document-templates` | Protegida | Menú lateral > Administración > Contenido y activos > Plantillas de documento | No |
| `/document-templates/create` | Protegida | Desde `/document-templates` > acción “Crear” | No |
| `/document-templates/{id}` | Protegida especial | Desde `/document-templates` > abrir plantilla existente | No |
| `/document-templates/{id}/edit` | Protegida especial | Desde `/document-templates` > editar plantilla existente | No |

### Media

| Ruta | Acceso | Cómo llegar | Solo por URL |
|---|---|---|---|
| `/media-fields` | Protegida | Menú lateral > Administración > Contenido y activos > Campos de metadata | No |
| `/media-fields/create` | Protegida | Desde `/media-fields` > acción “Crear” | No |
| `/media-fields/{id}/edit` | Protegida especial | Desde `/media-fields` > editar un campo existente | No |
| `/media-library` | Protegida | Menú lateral > Administración > Contenido y activos > Biblioteca de medios | No |
| `/media-library/upload` | Protegida | Desde `/media-library` > acción “Subir archivo” | No |
| `/media-library/{id}/edit` | Protegida especial | Desde `/media-library` > editar un archivo existente | No |

## 6. Acceso y usuarios

### Regla de navegación del grupo

- Este grupo se alcanza desde el menú lateral de Administración, sección de acceso/RBAC.
- Las rutas dinámicas dependen de un rol, permiso o usuario existente.

| Ruta | Acceso | Cómo llegar | Solo por URL |
|---|---|---|---|
| `/permissions` | Protegida | Menú lateral > Administración > Acceso y usuarios > Permisos | No |
| `/permissions/create` | Protegida | Desde `/permissions` > acción “Crear” | No |
| `/permissions/{id}/edit` | Protegida especial | Desde `/permissions` > editar un permiso existente | No |
| `/roles` | Protegida | Menú lateral > Administración > Acceso y usuarios > Roles | No |
| `/roles/create` | Protegida | Desde `/roles` > acción “Crear” | No |
| `/roles/{id}/edit` | Protegida especial | Desde `/roles` > editar un rol existente | No |
| `/roles/{id}/permissions` | Protegida especial | Desde `/roles` > gestionar permisos de un rol existente | No |
| `/users` | Protegida | Menú lateral > Administración > Acceso y usuarios > Usuarios | No |
| `/users/register` | Protegida | Desde `/users` > acción de registrar usuario | No |
| `/users/{userId}/roles` | Protegida especial | Desde `/users` > gestionar roles de un usuario existente | No |

## 7. DevTools

### Regla de navegación del grupo

- La puerta de entrada principal es `/test-features`.
- Muchas subrutas son helpers de smoke, demostración o redirección interna.
- Cuando en la práctica aterrizan en la misma vista o no tienen entrada de menú propia, se marcan como `Solo por URL = Sí`.

| Ruta | Acceso | Cómo llegar | Solo por URL |
|---|---|---|---|
| `/index` | Protegida | Alias técnico; URL directa que redirige al root | Sí |
| `/index.php` | Protegida | Alias técnico; URL directa que redirige al root | Sí |
| `/test-features` | Protegida | Contexto DevTools > Test Features | No |
| `/test-features/api-response` | Protegida | Helper de DevTools desde Test Features o URL directa | Sí |
| `/test-features/cors-headers` | Protegida | Helper de DevTools desde Test Features o URL directa | Sí |
| `/test-features/db-connection` | Protegida | Helper de DevTools desde Test Features o URL directa | Sí |
| `/test-features/e-helper` | Protegida | Helper de DevTools desde Test Features o URL directa | Sí |
| `/test-features/flash/clear` | Protegida | Helper de DevTools desde Test Features o URL directa | Sí |
| `/test-features/flash/{type}` | Protegida especial | Helper dinámico para flash por tipo; normalmente URL directa | Sí |
| `/test-features/flash/{type}/persistent` | Protegida especial | Helper dinámico para flash persistente; normalmente URL directa | Sí |
| `/test-features/i18n` | Protegida | Helper de DevTools desde Test Features o URL directa | Sí |
| `/test-features/infra` | Protegida | Helper de DevTools desde Test Features o URL directa | Sí |
| `/test-features/json` | Protegida | Helper de DevTools desde Test Features o URL directa | Sí |
| `/test-features/json-error` | Protegida | Helper de DevTools desde Test Features o URL directa | Sí |
| `/test-features/json-success` | Protegida | Helper de DevTools desde Test Features o URL directa | Sí |
| `/test-features/layout-test` | Protegida | Helper de layout desde Test Features o URL directa | Sí |
| `/test-features/logger-email` | Protegida | Helper de logger/mail desde Test Features o URL directa | Sí |
| `/test-features/modal/form-content` | Protegida | Helper/modal de DevTools; normalmente URL directa | Sí |
| `/test-features/modal/sample-content` | Protegida | Helper/modal de DevTools; normalmente URL directa | Sí |
| `/test-features/orm/find-or-fail` | Protegida | Helper ORM desde Test Features o URL directa | Sí |
| `/test-features/orm/status` | Protegida | Helper ORM desde Test Features o URL directa | Sí |
| `/test-features/orm/user-demo` | Protegida | Helper ORM desde Test Features o URL directa | Sí |
| `/test-features/rbac-status` | Protegida | Helper RBAC desde Test Features o URL directa | Sí |
| `/test-features/route-cache` | Protegida | Helper de route cache desde Test Features o URL directa | Sí |
| `/test-features/ui-showcase` | Protegida | Desde Test Features > UI Showcase o URL directa | No |
| `/test-features/validation-error` | Protegida | Helper de validación desde Test Features o URL directa | Sí |
| `/test-layout` | Protegida | Ruta técnica de smoke de layout; normalmente URL directa | Sí |
| `/uml` | Protegida | Contexto DevTools > UML / Arquitectura | No |

## Observaciones útiles

1. Las variantes en mayúscula (`/Home`, `/Landing`, `/Store`, `/Dashboard`, `/Setup`) pertenecen al audit visual histórico; el contrato vivo del sistema usa rutas canónicas en minúscula y normalización global de casing.
2. Las rutas con tokens (`/reset-password/{token}`, `/verify-email/{token}`) y callbacks (`/auth/social/callback/{provider}`) son esencialmente rutas de flujo, no de navegación manual.
3. Las rutas con `{id}`, `{itemId}`, `{userId}` o `{type}` dependen de datos existentes y normalmente se alcanzan desde listados, acciones de fila o enlaces internos.
4. El grupo DevTools concentra la mayoría de rutas “solo URL” porque son helpers de smoke/demostración y no pantallas de negocio.
