# Checklist E2E — `/configuration/environment-setup` privileged + finalización

Verificación manual del flujo real de configuración inicial de Catalyst.

Usar esta checklist cuando cambien:

- `Repository/Framework/Configuration/Controllers/SetupCompletionController.php`
- `Repository/Framework/Configuration/Controllers/AppConfigSaveController.php`
- `Repository/Framework/Configuration/Controllers/DbConfigSaveController.php`
- `Repository/Framework/Configuration/Controllers/MailConfigSaveController.php`
- `Repository/Framework/Configuration/Controllers/SessionConfigSaveController.php`
- `Repository/Framework/Configuration/Controllers/CacheConfigSaveController.php`
- `Repository/Framework/Configuration/Controllers/LoggingConfigSaveController.php`
- `Repository/Framework/Configuration/Controllers/SecurityConfigSaveController.php`
- `Repository/Framework/Configuration/Controllers/WebSocketConfigSaveController.php`
- `Repository/Framework/Configuration/Controllers/DevToolsConfigSaveController.php`
- `app/Framework/Middleware/SetupGuardMiddleware.php`
- `app/Helpers/Config/ConfigManager.php`

## Contratos runtime vigentes

- El contrato público para saber si el framework ya quedó configurado es `ConfigManager::isConfigured(): bool`.
- `ConfigManager::detectConfigured()` existe, pero es **privado**; no documentarlo como superficie de uso.
- `POST /configuration/environment-setup/privileged-account-account` crea la cuenta privilegiada inicial.
- `POST /configuration/environment-setup/complete` **no** crea privileged ni consume `account_name`, `account_email` o passwords.
- `POST /configuration/environment-setup/complete` solo finaliza si:
  - `app.json` y `db.json` existen
  - la DB es alcanzable
  - existen `users`, `roles` y `user_roles` o el bootstrap SQL puede crearlas
  - ya existe al menos un privileged activo
- La respuesta JSON del stack usa `JsonResponse::api()`:
  - base: `success`, `data`, `noFlash`
  - opcionales: `message`, `meta`, `notifications`, `redirect`, `refresh`, `in`, `html`
- `SetupGuardMiddleware` redirige HTML no autenticado a `/login?redirect=/configuration/environment-setup`.
- Si `/configuration/environment-setup` ya está configurado y el caller no está autenticado como privileged, el middleware bloquea antes de llegar al controller:
  - HTML no autenticado: redirect a `/login?redirect=/configuration/environment-setup`
  - JSON/AJAX no autenticado: `401` con mensaje `Login required.`
  - usuario autenticado no privileged: `403` con mensaje `Privileged access required.`

## Pre-requisitos

- La URL local configurada responde, por ejemplo `http://localhost/` o el
  VirtualHost elegido para el proyecto.
- Browser con DevTools.
- Acceso al servidor MySQL/MariaDB de la instalación si se va a verificar DB real.
- Terminal con `php`.

## Estado inicial esperado

En un checkout fresco, los archivos activos locales se crean desde
`boot-core/config/templates/*.json` cuando corre `ConfigManager` o:

```powershell
php public/cli.php config:sync
```

`boot-core/config/development/` es runtime local ignorado por Git. Para probar
el setup incompleto, `boot-core/config/development/app.json` debe existir
localmente y tener:

```json
{ "project": { "project_config": false } }
```

Verificación rápida:

```powershell
Select-String -Path 'D:/OpsZone/DevWorkspace/Projects/Web/catalyst/boot-core/config/development/app.json' -Pattern '"project_config"\s*:\s*false'
php public/cli.php config:contract-smoke --json
php public/cli.php config:e2e-readiness --json
```

## Escenario A — Guardados parciales no finalizan setup

Objetivo:
- confirmar que los POST parciales de `/configuration/environment-setup/*` preservan `project.project_config=false`

Pasos:
1. Abrir `{APP_URL}/configuration/environment-setup`.
2. Guardar varias secciones (`app`, `db`, `mail`, `session`, `cache`, `logging`, `security`, `websocket`, `cors`).
3. Tras cada guardado, verificar que `app.json` sigue con `"project_config": false`.

Criterio de éxito:
- los guardados parciales no llaman el flujo de finalización
- sigue visible la tarjeta de privileged/finalización
- `ConfigManager::isConfigured()` seguiría resolviendo `false`

## Escenario B — `POST /configuration/environment-setup/privileged-account-account` crea el privileged inicial

Objetivo:
- validar el contrato real de aprovisionamiento de privileged antes de finalizar

Request esperado:

```text
POST /configuration/environment-setup/privileged-account-account
csrf_token
account_name
account_email
account_password
account_password_confirm
```

Happy path esperado:

```json
{
  "success": true,
  "data": { "privileged_account_created": true },
  "noFlash": true,
  "message": "...",
  "notifications": { "...": "..." }
}
```

Notas:
- si ya existe un privileged activo, devuelve `success=true` con `data.privileged_account_exists=true`
- si el email ya existe o el password no confirma, devuelve `422` con `errors`
- si la DB no es alcanzable, falla desde `SetupDatabaseService::open()`
- usuario, rol privileged y asignación se confirman en una sola transacción; un fallo revierte el aprovisionamiento

## Escenario C — `POST /configuration/environment-setup/complete` falla con DB/config inválida

Objetivo:
- validar errores reales del endpoint de finalización

Request esperado:

```text
POST /configuration/environment-setup/complete
csrf_token
```

No enviar campos `privileged_*`; el controller no los consume.

Errores runtime vigentes:

| Condición | HTTP | Señal real |
|---|---:|---|
| `app.json` ausente | 422 | mensaje de `app_json_missing` |
| `db.json` ausente | 422 | mensaje de `db_json_missing` |
| `db.db1.db_database` vacío | 422 | mensaje de `db_incomplete` |
| credenciales/host inválidos | 422 | mensaje de `db_unreachable` + detalle |
| fallo al materializar `users/roles/user_roles` | 500 | mensaje de `auth_tables_missing` + detalle |
| no existe privileged activo | 422 | mensaje de `privileged_account_required` |

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

## Escenario D — `POST /configuration/environment-setup/complete` finaliza cuando ya existe privileged

Objetivo:
- validar el happy path real de finalización

Precondición:
- `POST /configuration/environment-setup/privileged-account-account` ya creó un privileged activo

Happy path esperado:

```json
{
  "success": true,
  "data": { "privileged_account_created": false },
  "noFlash": true,
  "message": "...",
  "notifications": { "...": "..." },
  "redirect": "/login"
}
```

Criterio de éxito:
- `POST /configuration/environment-setup/complete` responde `200`
- `app.project.project_config` cambia a `true`
- el browser redirige a `/login` inmediatamente después de procesar la respuesta
- no se crea un segundo privileged; solo se completa la configuración

## Escenario E — Gate post-configuración

Objetivo:
- confirmar el acceso real a `/configuration/environment-setup` una vez configurado

Pasos:
1. Con `project_config=true`, abrir `{APP_URL}/configuration/environment-setup` sin sesión.
2. Verificar redirect a `/login?redirect=/configuration/environment-setup`.
3. Autenticarse como privileged.
4. Reabrir `/configuration/environment-setup`.

Criterio de éxito:
- el paso 2 usa el querystring URL-encoded real
- el privileged autenticado sí puede entrar
- la vista ya no muestra la tarjeta de finalización; muestra la tarjeta de reset

Variante AJAX:
- un caller no autenticado a `/configuration/environment-setup/complete` no debería recibir `{"error":"already_configured"}`; el middleware responde antes con `401 Login required.`
- un privileged autenticado que fuerce `POST /configuration/environment-setup/complete` con `project_config=true` sí llega al controller y recibe `409` con `success=false` y mensaje de `already_configured`

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
2. Usar `POST /configuration/environment-setup/reset` como privileged autenticado para reabrir el wizard sin borrar la configuración.
3. Si además hace falta reprobar bootstrap de DB desde cero, limpiar la base aparte.

Advertencia:
- `POST /test-features/db-reset` es **destructivo**. Borra tablas y re-seedea desde `boot-core/database/create-catalyst-db.sql`.
- No tratarlo como alternativa “segura”.

## Registro de ejecución

| Fecha | Ejecutado por | Privileged create | Finalize error | Finalize success | Gate | Notas |
|---|---|---|---|---|---|---|
| 2026-05-14 | Walter | ✅ | ✅ | ✅ | ✅ | Contrato reconciliado con `SetupCompletionController`, `JsonResponse` y `SetupGuardMiddleware`. |
