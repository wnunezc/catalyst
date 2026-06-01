# Security First Audit - 2026-06-01

## Estado

Fase 5: auditoria inicial generada / pendiente de decision.

Esta auditoria es de solo lectura. No se modifico runtime, no se borraron archivos
y no se rotaron secretos.

## Resumen ejecutivo

No se detectaron secretos reales trackeados por Git ni rutas administrativas sin
guard. El quality gate completo pasa cuando se ejecuta con red y permisos locales
normales. `composer audit` no reporta advisories y `security:check` no reporta
fallos ni warnings.

La auditoria encontro dos riesgos altos de despliegue que requieren decision antes
de corregir:

1. Componentes criptograficos usan una clave fija de fallback si falta `APP_KEY`.
2. Existen artefactos runtime ignorados por Git dentro de `public/`; Apache sirve
   archivos existentes directamente antes de pasar por el router.

Tambien se identificaron superficies medias: `display_errors On` en la config
Apache publica, archivos INI trackeados bajo webroot y varios usos amplios de
`TrustedHtml` que deben cerrarse por origen antes de afirmar que su contenido
siempre es confiable.

## Alcance revisado

- Inventario local y tracking Git de `.env`, `secrets.json`, DKIM y extensiones
  tipicas de claves/certificados.
- Reglas `.gitignore` y guia de despliegue.
- PHP, uploads y archivos inusuales bajo `public/`.
- Las 273 rutas emitidas por `php public/cli.php route:list --json`.
- Pipeline global CSRF/throttle, guards de Setup y DevTools.
- Escaneo CSP y usos de `TrustedHtml`, `trusted-html`, `withHtml()` y HTML crudo.

## Hallazgos

### Critico

No se detectaron hallazgos criticos confirmados.

### Alto

#### SEC-01 - Clave criptografica fija como fallback si falta `APP_KEY`

- Evidencia:
  - `app/Framework/WebSocket/WebSocketToken.php:114,120,124`
  - `app/Framework/Security/SignedSerializedPayload.php:116,122,125`
  - `boot-core/bin/websocket-server.php:50`
- Observacion: tokens WebSocket y payloads serializados firmados caen a una clave
  constante conocida cuando no se resuelve `APP_KEY`. El `status` actual confirma
  que el proyecto local tiene clave; el riesgo aparece en bootstrap incompleto o
  despliegue mal configurado.
- Recomendacion: fallar cerrado si la clave no existe o no cumple longitud minima.
  No iniciar WebSocket ni aceptar/verificar payloads firmados con fallback.
- Prioridad: corregir ahora, antes de reutilizar Catalyst como base desplegable.

#### SEC-02 - Artefactos runtime servibles bajo webroot

- Evidencia:
  - `public/.htaccess:253` permite acceso directo a archivos existentes.
  - Existen localmente rutas ignoradas bajo `public/generated-documents/`,
    `public/generated-reports/`, `public/smoke/` y `public/uploads/`.
  - Inventario local: 5 documentos generados, 4 reportes, 10 artefactos smoke y
    6 uploads DevTools. Ninguno esta trackeado por Git.
  - `.gitignore:207-216` evita commit, pero no evita acceso HTTP ni inclusion
    accidental en un ZIP creado desde la raiz.
- Observacion: el contenido observado es runtime local; no se inspeccionaron ni
  copiaron sus valores al reporte. La presencia bajo webroot permite exposicion
  directa si el entorno queda accesible o el empaquetado incluye esos archivos.
- Recomendacion: decidir entre mover runtime fuera de `public/`, servirlo mediante
  controladores autorizados o denegar HTTP por defecto y publicar solo artefactos
  explicitamente permitidos.
- Prioridad: corregir ahora. La limpieza fisica de archivos existentes requiere
  confirmacion separada.

### Medio

#### SEC-03 - `display_errors On` en configuracion Apache publica

- Evidencia: `public/.htaccess:298`.
- Observacion: en un despliegue que use esta configuracion, errores PHP pueden
  revelar paths internos y detalles de ejecucion.
- Recomendacion: desactivar `display_errors` fuera de development y enviar
  detalle solo a logs.
- Prioridad: corregir ahora con una regla dependiente del entorno o una plantilla
  de despliegue segura.

#### SEC-04 - Archivos INI trackeados dentro de `public/`

- Evidencia:
  - `public/.user.ini`
  - `public/php.ini`
  - `public/.htaccess:253`
- Observacion: los archivos actuales contienen directivas operativas y no se
  observaron secretos. Aun asi, mantener INI bajo webroot amplia la superficie de
  fingerprinting y depende de la configuracion del servidor para evitar descarga.
- Recomendacion: mover configuracion fuera del document root cuando sea posible o
  agregar denegacion explicita para `*.ini` y dotfiles.
- Prioridad: corregir ahora junto con SEC-03.

#### SEC-05 - Contratos `TrustedHtml` amplios pendientes de clasificar por origen

- Evidencia representativa:
  - `Repository/Framework/Documents/Views/scope/pages/show.php:97`
  - `boot-core/template/scope/components/_admin-form-builder.php:81,96,228`
  - `boot-core/template/scope/components/_admin-datagrid.php:125`
  - `Repository/Framework/DemoUi/Controllers/DemoUiController.php:910`
- Observacion: `security:check` pasa y no se detectaron handlers inline inseguros
  en vistas runtime normales. Sin embargo, estos puntos convierten strings a
  `TrustedHtml`; la seguridad depende de que cada productor sea exclusivamente
  server-owned o saneado antes de llegar a la vista.
- Recomendacion: documentar el origen permitido de cada llamada. Para contenido
  editable o legado, sanitizar con allowlist o retirar el bypass raw.
- Prioridad: auditar en profundidad antes de corregir; evitar cambios masivos sin
  validar contratos funcionales.

### Bajo

#### SEC-06 - Secretos reales locales correctamente ignorados, con riesgo operativo de empaquetado

- Evidencia:
  - `.gitignore:11-14,121-123`
  - `docs/deployment.md:9-18,46-53`
  - `git check-ignore -v` confirma exclusion de `.env`, `secrets.json`, DKIM y
    uploads locales.
- Observacion: no se detectaron valores reales trackeados. Los archivos sensibles
  existen localmente como requiere el runtime. El riesgo restante es operativo:
  copiar o comprimir la raiz sin staging limpio.
- Recomendacion: conservar la exclusion y reforzar el flujo de export limpio en la
  fase 7.
- Prioridad: dejar como deuda planificada de fase 7.

#### SEC-07 - POST de framework sin throttle declarativo por ruta

- Evidencia:
  - `boot-core/routes/global-routes.php:70` (`/flash/dismiss`)
  - `Repository/Framework/Auth/routes.php:108` (`/logout`)
  - `boot-core/routes/global-routes.php:50-51` registra throttle generico y CSRF
    global para requests mutantes.
- Observacion: no hay ausencia de proteccion global. Estos endpoints no declaran
  perfil especifico; heredan CSRF y throttle generico fuera de development.
- Recomendacion: documentar que el perfil generico es intencional o declarar uno
  explicito si se quiere trazabilidad uniforme.
- Prioridad: deuda baja.

## Controles confirmados

- No se encontraron secretos reales trackeados por Git.
- Solo `public/index.php` y `public/cli.php` aparecen como PHP bajo `public/`.
  `cli.php` bloquea acceso web tras bootstrap.
- CSRF global: `boot-core/routes/global-routes.php:51`.
- Throttle mutante global: `boot-core/routes/global-routes.php:50`.
- Recuperacion publica: POST con `auth_recovery` throttle en
  `Repository/App/Surface/Account/routes.php:70-79`.
- Rutas administrativas `/admin/*`: 4 revisadas, 0 sin `AuthMiddleware` +
  `RoleMiddleware`.
- DevTools: 45 rutas revisadas con `DevToolsGuardMiddleware`.
- Setup: 17 rutas revisadas con `SetupGuardMiddleware`.
- API con token: 12 rutas revisadas con `ApiTokenMiddleware`.
- `php public/cli.php security:check`: sin hard failures ni warnings.

## Archivos revisados

- `AGENTS.md`
- `docs/harness-context-map.md`
- `docs/security-conventions.md`
- `docs/deployment.md`
- `docs/quality-gate.md`
- `docs/superpowers/plans/2026-06-01-catalyst-stabilization-roadmap.md`
- `.gitignore`
- `public/.htaccess`
- `public/.user.ini`
- `public/php.ini`
- `public/index.php`
- `public/cli.php`
- `boot-core/routes/global-routes.php`
- `Repository/App/Surface/Account/routes.php`
- `Repository/Framework/Auth/routes.php`
- `Repository/Framework/DevTools/routes.php`
- `Repository/Framework/Settings/routes.php`
- `app/Framework/Middleware/CsrfMiddleware.php`
- `app/Framework/Middleware/RequestThrottlingMiddleware.php`
- `app/Framework/Middleware/DevToolsGuardMiddleware.php`
- `app/Framework/Middleware/SetupGuardMiddleware.php`
- `app/Framework/WebSocket/WebSocketToken.php`
- `app/Framework/Security/SignedSerializedPayload.php`
- `boot-core/bin/websocket-server.php`
- Usos de `TrustedHtml` bajo `app/`, `boot-core/` y `Repository/`.

## Comandos ejecutados

| Comando | Resultado resumido |
|---|---|
| `git status --short` | Limpio antes de generar el reporte. |
| `php public/cli.php quality:check` | PASS fuera del sandbox. El primer intento restringido fallo por red Packagist y permisos de log, no por regresion. |
| `composer validate --strict` | PASS. |
| `composer audit` | PASS fuera del sandbox: sin advisories. |
| `php public/cli.php route:list --json` | PASS: 273 rutas inventariadas. |
| `php public/cli.php inspect:lint` | PASS: estructura coherente. |
| `php public/cli.php security:check` | PASS: sin hard failures ni warnings. |
| `php public/cli.php status` | `Overall: Ready`; warnings esperados de DNS Docker para queue/scheduler desde host. |
| `git check-ignore -v ...` | Confirma exclusion de secretos y runtime local sensible. |
| Escaneos `rg`, `git ls-files`, `Get-ChildItem` | Inventario de webroot, rutas, CSP y `TrustedHtml` generado sin imprimir secretos. |

## Riesgos que requieren decision del usuario

1. Autorizar correccion fail-closed de `APP_KEY` para SEC-01.
2. Elegir politica de artefactos runtime publicos para SEC-02:
   mover fuera de webroot, servir mediante controlador o negar HTTP por defecto.
3. Autorizar hardening de Apache para SEC-03 y SEC-04.
4. Definir si SEC-05 se aborda en esta fase con auditoria profunda por productor o
   se registra como deuda separada.
5. Confirmar por separado si se deben limpiar los artefactos runtime locales
   existentes. Esta auditoria no borra archivos.

## Proximo paso recomendado

Decisiones iniciales recibidas. La especificacion aprobada vive en
`docs/superpowers/specs/2026-06-01-phase-5-security-hardening.md` y el plan de
implementacion en `docs/superpowers/plans/2026-06-01-phase-5-security-hardening.md`.

Mantener fase 5 como pendiente hasta implementar, verificar y recibir confirmacion
explicita del usuario.

## Addendum de decisiones - 2026-06-01

- `APP_KEY`: diferido al futuro control de licencias.
- `display_errors On`: se mantiene durante desarrollo; revisar antes de `v1.0.0`.
- INI bajo `public/`: conservar ubicacion y negar descarga HTTP.
- `public/generated-documents/`: nuevos exports normales exclusivamente PDF.
- `public/generated-reports/`: nuevos outputs exclusivamente CSV o XLS.
- `public/smoke/`: evaluar y mover auxiliares CLI a storage privado; conservar los
  smoke commands utiles.
- `public/uploads/devtools/`: conservar como herramienta de prueba.
- `TrustedHtml`: resolver preview editable de Documents y retirar bypass raw de
  DataGrid sin consumidores activos.

## Addendum de implementacion - 2026-06-01

Estado: hardening inicial implementado y verificado localmente. Fase 5 sigue
pendiente de revision explicita del usuario.

- `public/.user.ini` y `public/php.ini` permanecen en su ubicacion, pero Apache
  niega descarga HTTP con `403`.
- Se agrego disco privado `runtime` bajo `boot-core/storage/runtime/`; su metodo
  `url()` devuelve cadena vacia intencionalmente.
- Los auxiliares TXT de `attachments:smoke`, `catalogs:smoke`,
  `reporting:smoke` y `retention:smoke` usan storage privado y limpieza fisica.
- `retention:smoke` quedo aislado por IDs de su propia prueba para no aplicar
  politicas destructivas sobre registros historicos ajenos.
- Nuevos exports normales de Documents se persisten exclusivamente como PDF.
- Reporting valida formato y persiste CSV o XLS con extension y MIME coherentes.
- Preview HTML editable de Documents pasa por `HtmlAllowlistSanitizer`.
- Se retiro el bypass HTML crudo de DataGrid; permanecen tipos estructurados.

Evidencia runtime ejecutada:

| Verificacion | Resultado |
|---|---|
| `docker exec WSDD-Web-Server-PHP8.4 apachectl -t` | `Syntax OK`. |
| HTTP `php.ini` y `.user.ini` | Ambos responden `403`. |
| `security:regression --json` dentro de WSDD | PASS, incluyendo storage privado y sanitizer. |
| `attachments:smoke --json` dentro de WSDD | PASS, incluyendo artifact PDF. |
| `catalogs:smoke --json` dentro de WSDD | PASS. |
| `reporting:smoke --json` dentro de WSDD | PASS para CSV, XLS y rechazo de formato no soportado. |
| `retention:smoke --json` dentro de WSDD | PASS con alcance aislado. |
| `php public/cli.php quality:check` | PASS. |
| `git diff --check` | PASS, sin errores de whitespace. |
| HTTP de un archivo existente en `public/uploads/devtools/` | `200`, superficie conservada. |

Tras confirmacion explicita del usuario, se eliminaron los residuos historicos
inventariados: 5 archivos de `public/generated-documents/`, 4 de
`public/generated-reports/` y 10 TXT de `public/smoke/`. Las carpetas raiz se
conservaron vacias.

Tras ejecutar la bateria smoke final, `boot-core/storage/runtime/` tambien quedo
sin archivos.
