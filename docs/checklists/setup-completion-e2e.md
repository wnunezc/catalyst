# Checklist E2E — `/setup` admin + finalización

Verificación manual del flujo real de configuración inicial de Catalyst.

Usar esta checklist cuando cambien:

- `Repository/Framework/Settings/Controllers/SetupCompletionController.php`
- `Repository/Framework/Settings/Controllers/AppConfigSaveController.php`
- `Repository/Framework/Settings/Controllers/DbConfigSaveController.php`
- `Repository/Framework/Settings/Controllers/MailConfigSaveController.php`
- `Repository/Framework/Settings/Controllers/SessionConfigSaveController.php`
- `Repository/Framework/Settings/Controllers/CacheConfigSaveController.php`
- `Repository/Framework/Settings/Controllers/LoggingConfigSaveController.php`
- `Repository/Framework/Settings/Controllers/SecurityConfigSaveController.php`
- `Repository/Framework/Settings/Controllers/WebSocketConfigSaveController.php`
- `Repository/Framework/Settings/Controllers/DevToolsConfigSaveController.php`
- `app/Framework/Middleware/SetupGuardMiddleware.php`
- `app/Helpers/Config/ConfigManager.php`

## Contratos runtime vigentes

- El contrato público para saber si el framework ya quedó configurado es `ConfigManager::isConfigured(): bool`.
- `ConfigManager::detectConfigured()` existe, pero es **privado**; no documentarlo como superficie de uso.
- `POST /setup/admin` crea el administrador inicial.
- `POST /setup/complete` **no** crea admin ni consume `admin_name`, `admin_email` o passwords.
- `POST /setup/complete` solo finaliza si:
  - `app.json` y `db.json` existen
  - la DB es alcanzable
  - existen `users`, `roles` y `user_roles` o el bootstrap SQL puede crearlas
  - ya existe al menos un admin activo
- La respuesta JSON del stack usa `JsonResponse::api()`:
  - base: `success`, `data`, `noFlash`
  - opcionales: `message`, `meta`, `notifications`, `redirect`, `redirectDelay`, `refresh`, `refreshDelay`, `in`, `html`
- `SetupGuardMiddleware` redirige HTML no autenticado a `/login?redirect=%2Fsetup`.
- Si `/setup` ya está configurado y el caller no está autenticado como admin, el middleware bloquea antes de llegar al controller:
  - HTML no autenticado: redirect a `/login?redirect=%2Fsetup`
  - JSON/AJAX no autenticado: `401` con mensaje `Login required.`
  - usuario autenticado no admin: `403` con mensaje `Admin access required.`

## Pre-requisitos

- `https://catalyst.dock/` responde.
- Browser con DevTools.
- Acceso al contenedor MariaDB del stack WSDD si se va a verificar DB real.
- Terminal con `php`.

## Estado inicial esperado

`boot-core/config/development/app.json` debe tener:

```json
{ "project": { "project_config": false } }
```

Verificación rápida:

```powershell
Select-String -Path 'D:/OpsZone/DevWorkspace/Projects/Web/catalyst/boot-core/config/development/app.json' -Pattern '"project_config"\s*:\s*false'
```

## Escenario A — Guardados parciales no finalizan setup

Objetivo:
- confirmar que los POST parciales de `/setup/*` preservan `project.project_config=false`

Pasos:
1. Abrir `https://catalyst.dock/setup`.
2. Guardar varias secciones (`app`, `db`, `mail`, `session`, `cache`, `logging`, `security`, `websocket`, `cors`).
3. Tras cada guardado, verificar que `app.json` sigue con `"project_config": false`.

Criterio de éxito:
- los guardados parciales no llaman el flujo de finalización
- sigue visible la tarjeta de admin/finalización
- `ConfigManager::isConfigured()` seguiría resolviendo `false`

## Escenario B — `POST /setup/admin` crea el admin inicial

Objetivo:
- validar el contrato real de aprovisionamiento de admin antes de finalizar

Request esperado:

```text
POST /setup/admin
csrf_token
admin_name
admin_email
admin_password
admin_password_confirm
```

Happy path esperado:

```json
{
  "success": true,
  "data": { "admin_created": true },
  "noFlash": true,
  "message": "...",
  "notifications": { "...": "..." }
}
```

Notas:
- si ya existe un admin activo, devuelve `success=true` con `data.admin_exists=true`
- si el email ya existe o el password no confirma, devuelve `422` con `errors`
- si la DB no es alcanzable, falla desde `openSetupDatabase()`

## Escenario C — `POST /setup/complete` falla con DB/config inválida

Objetivo:
- validar errores reales del endpoint de finalización

Request esperado:

```text
POST /setup/complete
csrf_token
```

No enviar campos `admin_*`; el controller no los consume.

Errores runtime vigentes:

| Condición | HTTP | Señal real |
|---|---:|---|
| `app.json` ausente | 422 | mensaje de `app_json_missing` |
| `db.json` ausente | 422 | mensaje de `db_json_missing` |
| `db.db1.db_database` vacío | 422 | mensaje de `db_incomplete` |
| credenciales/host inválidos | 422 | mensaje de `db_unreachable` + detalle |
| fallo al materializar `users/roles/user_roles` | 500 | mensaje de `auth_tables_missing` + detalle |
| no existe admin activo | 422 | mensaje de `admin_required` |

Envelope esperado en error:

```json
{
  "success": false,
  "data": null,
  "noFlash": true,
  "message": "...",
  "notifications": { "...": "..." }
}
```

## Escenario D — `POST /setup/complete` finaliza cuando ya existe admin

Objetivo:
- validar el happy path real de finalización

Precondición:
- `POST /setup/admin` ya creó un admin activo

Happy path esperado:

```json
{
  "success": true,
  "data": { "admin_created": false },
  "noFlash": true,
  "message": "...",
  "notifications": { "...": "..." },
  "redirect": "/login",
  "redirectDelay": 1500
}
```

Criterio de éxito:
- `POST /setup/complete` responde `200`
- `app.project.project_config` cambia a `true`
- el browser redirige a `/login` aproximadamente 1.5 s después
- no se crea un segundo admin; solo se completa la configuración

## Escenario E — Gate post-configuración

Objetivo:
- confirmar el acceso real a `/setup` una vez configurado

Pasos:
1. Con `project_config=true`, abrir `https://catalyst.dock/setup` sin sesión.
2. Verificar redirect a `/login?redirect=%2Fsetup`.
3. Autenticarse como admin.
4. Reabrir `/setup`.

Criterio de éxito:
- el paso 2 usa el querystring URL-encoded real
- el admin autenticado sí puede entrar
- la vista ya no muestra la tarjeta de finalización; muestra la tarjeta de reset

Variante AJAX:
- un caller no autenticado a `/setup/complete` no debería recibir `{"error":"already_configured"}`; el middleware responde antes con `401 Login required.`
- un admin autenticado que fuerce `POST /setup/complete` con `project_config=true` sí llega al controller y recibe `409` con `success=false` y mensaje de `already_configured`

## Escenario F — Doble finalización

Objetivo:
- confirmar que un segundo finalize no reabre ni reprovisiona

Resultado esperado cuando el caller sí supera `SetupGuardMiddleware`:

```json
{
  "success": false,
  "data": null,
  "noFlash": true,
  "message": "Framework is already configured.",
  "notifications": { "...": "..." }
}
```

No esperar un payload tipo:

```json
{ "error": "already_configured" }
```

## Rollback / Reset para repetir

Opciones:

1. Cambiar `project_config` a `false` manualmente en `app.json`.
2. Usar `POST /setup/reset` como admin autenticado para reabrir el wizard sin borrar la configuración.
3. Si además hace falta reprobar bootstrap de DB desde cero, limpiar la base aparte.

Advertencia:
- `POST /test-features/db-reset` es **destructivo**. Borra tablas y re-seedea desde `boot-core/database/create-catalyst-db.sql`.
- No tratarlo como alternativa “segura”.

## Registro de ejecución

| Fecha | Ejecutado por | Admin create | Finalize error | Finalize success | Gate | Notas |
|---|---|---|---|---|---|---|
| 2026-05-14 | Walter | ✅ | ✅ | ✅ | ✅ | Contrato reconciliado con `SetupCompletionController`, `JsonResponse` y `SetupGuardMiddleware`. |
