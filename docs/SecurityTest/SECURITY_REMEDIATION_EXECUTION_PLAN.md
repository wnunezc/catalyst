# Security Remediation Execution Plan

Fecha base: 2026-05-21

Objetivo:
- convertir los hallazgos negativos, abiertos o parciales de la auditoría en un plan ejecutable con trazabilidad completa
- agrupar problemas relacionados para resolverlos sin duplicación ni parches aislados
- mantener evidencia de análisis, implementación, verificación y cierre por hallazgo

Alcance:
- usa como fuentes canónicas los entregables existentes en `SecurityTest/`
- no toca `vendor`
- no agrega dependencias salvo decisión futura explícita del proyecto

## Fuentes canónicas a citar durante la ejecución

- `SecurityTest/SECURITY_AUDIT_SUMMARY.md`
- `SecurityTest/VULNERABILITY_REGISTER.md`
- `SecurityTest/ATTACK_SURFACE_MAP.md`
- `SecurityTest/AUTHORIZATION_MATRIX.md`
- `SecurityTest/BUSINESS_LOGIC_REVIEW.md`
- `SecurityTest/DATABASE_SECURITY_REVIEW.md`
- `SecurityTest/REMEDIATION_ROADMAP.md`
- `SecurityTest/SECURITY_TEST_PLAN.md`
- `SecurityTest/SECURITY_BACKLOG.md`
- `SecurityTest/EXECUTIVE_DECISION_NOTES.md`

## Principios obligatorios de ejecución

1. `SOLID`: cada remediación debe caer en servicios, repositorios, middleware o helpers con responsabilidad clara.
2. `DRY`: si dos hallazgos comparten flujo, se resuelven desde una misma pieza reusable.
3. `KISS`: primero endurecer el diseño actual; solo abstraer cuando la repetición o el riesgo lo justifiquen.
4. `DAO / Repository`: cambios de persistencia o cleanup deben quedar en repositorios o managers, no en controladores.
5. `MVC estricto`: controladores delgados; la lógica de seguridad vive en servicios, middleware y request validation.
6. `Separation of Concerns`: no mezclar saneamiento, autorización, persistencia y render en una sola capa.
7. `Dependency Inversion / Service Layer`: la lógica de revocación, escaping, enforcement y verificación debe entrar por servicios.
8. `Middleware Pipeline`: todo control transversal debe evaluarse para moverse a middleware o frontera reusable.
9. `PSR-4 / Namespaces consistentes`: nuevos artefactos deben seguir la estructura actual del framework.
10. `Validación centralizada`: no introducir validaciones ad hoc en controladores o vistas.
11. `Escaping por defecto`: toda salida dinámica HTML o JS debe usar el mecanismo seguro del framework.
12. `Prepared Statements`: no abrir excepciones al uso actual de builders/queries seguras.
13. `Configuración por entorno`: cualquier hardening dependiente de entorno debe vivir en config, no hardcodeado.
14. `Menor privilegio`: cualquier remediación sobre roles, setup, DevTools o tokens debe cerrar permisos por defecto.

## Inventario de hallazgos a ejecutar

| ID | Estado actual | Grupo | Fuente principal | Tipo de trabajo |
| --- | --- | --- | --- | --- |
| SEC-003 | Closed | G4 Supply chain y platform hardening | `VULNERABILITY_REGISTER.md` | actualización controlada de `composer.lock` + auditoría limpia |
| SEC-004 | Closed | G3 Integridad de datos y lifecycle de tokens | `VULNERABILITY_REGISTER.md` | migración + cleanup + regresión |
| SEC-005 | Closed | G2 Output encoding y DOM sinks | `VULNERABILITY_REGISTER.md` | diseño reusable + refactor focalizado + pruebas |
| SEC-006 | Closed | G5 Persistencia local y deserialización | `VULNERABILITY_REGISTER.md` | endurecimiento técnico + reducción de superficie |

Hallazgos ya corregidos pero que deben cerrarse formalmente:

| ID | Estado actual | Grupo | Fuente principal | Tipo de trabajo |
| --- | --- | --- | --- | --- |
| SEC-001 | Closed | G2 Output encoding y DOM sinks | `VULNERABILITY_REGISTER.md` | regresión + evidencia de cierre |
| SEC-002 | Closed | G1 Auth, sesiones y recovery | `VULNERABILITY_REGISTER.md` | regresión + evidencia de cierre |

## Agrupación de trabajo

### G1. Auth, sesiones y recovery

Incluye:
- SEC-002 como cierre formal

Razón de agrupación:
- comparte lógica de lifecycle de sesión, revocación y seguridad post-cambio de credenciales

Objetivo técnico:
- garantizar que cualquier cambio de contraseña invalide continuidad de sesión persistente sin duplicar lógica entre flows

Estrategia:
- mover o consolidar la revocación de sesiones persistentes en una frontera de servicio si aparece más de un caso de uso
- evitar que futuros flujos de cambio de contraseña queden fuera del contrato

Entregables:
- prueba de regresión para reset de contraseña + remember-me
- nota de cierre en el registro de vulnerabilidades

### G2. Output encoding y DOM sinks

Incluye:
- SEC-001 como cierre formal
- SEC-005 como remediación abierta

Razón de agrupación:
- ambos dependen del mismo problema estructural: datos dinámicos que terminan en sinks HTML/JS

Objetivo técnico:
- establecer un contrato explícito y reusable para:
  - JSON embebido en `<script>`
  - HTML parcial inyectado con `innerHTML`
  - contenido trusted vs user-influenced

Estrategia:
- no resolver cada sink con parches aislados
- introducir una frontera clara:
  - helper/serializer seguro para JSON inline
  - criterio único para `withHtml()` y sinks DOM
  - si hace falta, encapsular la inserción HTML en una utilidad central que rechace o marque contenido no confiable

Posibles piezas a tocar:
- capa HTTP de respuestas parciales
- helpers de vistas/layout
- JS de `response-actions.js`, `modal.js`, `Settings/front/script.js`
- callsites que hoy generan HTML parcial

Entregables:
- matriz de sinks y origen de datos
- helper o contrato reusable documentado
- pruebas de regresión XSS inline
- pruebas dirigidas sobre HTML parcial same-origin

### G3. Integridad de datos y lifecycle de tokens

Incluye:
- SEC-004

Razón de agrupación:
- el problema real no es solo un schema incompleto; es lifecycle inconsistente entre usuarios y tokens API

Objetivo técnico:
- impedir estados huérfanos en `api_tokens` y consolidar el contrato de ownership del token

Estrategia:
- resolver desde esquema + repositorio + pruebas
- no dejar el cleanup solo en migración si el dominio requiere enforcement continuo

Trabajo esperado:
- inventario de huérfanos actuales
- plan de saneamiento previo
- migración para agregar FK
- revisión de delete/deactivate de usuario para no romper el lifecycle

Entregables:
- migración de saneamiento + constraint
- regresión sobre creación, revocación y borrado/desactivación de usuario

### G4. Supply chain y platform hardening

Incluye:
- SEC-003

Razón de agrupación:
- depende de una decisión de mantenimiento y de las restricciones del lockfile, no de un cambio de negocio aislado

Objetivo técnico:
- eliminar el advisory de `symfony/routing` sin romper el baseline permitido por el proyecto

Estrategia:
- analizar el árbol permitido por `cboden/ratchet`
- confirmar la versión mínima segura compatible
- si no existe combinación segura dentro de restricciones actuales, escalar decisión de mantenimiento explícita

Entregables:
- nota técnica de compatibilidad
- actualización de lock controlada cuando corresponda
- `composer audit` limpio o riesgo aceptado formalmente con fecha

### G5. Persistencia local y deserialización

Incluye:
- SEC-006

Razón de agrupación:
- es una misma clase de riesgo: rehidratación de objetos desde disco local

Objetivo técnico:
- reducir el impacto de escritura local comprometida y eliminar deserialización amplia cuando sea viable

Estrategia:
- revisar si route cache y file cache pueden persistir estructuras escalares/arrays en vez de objetos arbitrarios
- si no se puede reemplazar completo, estrechar clases permitidas y documentar el boundary residual

Entregables:
- diseño técnico de reemplazo o restricción
- cambio focalizado en cache/route cache
- prueba de compatibilidad de cache y bootstrap

## Orden de ejecución recomendado

### Fase 1. Cierre formal de remediaciones ya aplicadas

Objetivos:
- cerrar SEC-001 y SEC-002 con evidencia reproducible

Tareas:
- agregar regresión para JSON inline seguro
- agregar regresión para revocación de `remember_tokens` tras reset
- actualizar `VULNERABILITY_REGISTER.md` y `SECURITY_AUDIT_SUMMARY.md` con evidencia de prueba

Salida esperada:
- hallazgos remediados con prueba y criterio de no regresión

### Fase 2. Resolver G3 antes de tocar sinks complejos

Objetivos:
- cerrar `api_tokens.user_id` huérfano

Tareas:
- inspección de datos reales o fixtures
- diseñar cleanup idempotente
- agregar migración con FK
- verificar flows de emisión y revocación

Salida esperada:
- SEC-004 cerrado con schema consistente

### Fase 3. Diseñar y ejecutar G2 como corriente única

Objetivos:
- cerrar SEC-005 sin duplicar reglas de saneamiento entre backend y frontend

Tareas:
- inventario de sinks HTML/JS activos
- clasificación por trust boundary
- decidir helper/servicio/contrato central
- aplicar remediación incremental sobre callsites de mayor riesgo

Salida esperada:
- reducción clara de sinks peligrosos y contrato reusable para futuras superficies

### Fase 4. Resolver G5 con mínimo impacto

Objetivos:
- reducir o encapsular `unserialize(... allowed_classes => true)`

Tareas:
- revisar formato actual de route cache y file cache
- elegir formato seguro o lista acotada de clases
- validar compatibilidad con `route:cache`, `route:clear`, bootstrap y storage

Salida esperada:
- SEC-006 degradado o cerrado con justificación técnica fuerte

### Fase 5. Resolver G4 con decisión explícita

Objetivos:
- cerrar SEC-003 o dejar aceptación de riesgo formal

Tareas:
- revisar compatibilidad de árbol transitorio
- definir ventana de actualización
- ejecutar `composer audit` como criterio de salida

Salida esperada:
- advisory eliminado o riesgo formalmente aceptado con motivo y fecha

## Trazabilidad por hallazgo

Cada hallazgo debe cerrarse con una ficha mínima en el mismo commit o en el mismo bloque de trabajo:

| Campo | Requerido |
| --- | --- |
| ID del hallazgo | Sí |
| Grupo | Sí |
| Fuente canónica citada | Sí |
| Riesgo resumido | Sí |
| Decisión técnica | Sí |
| Artefactos tocados | Sí |
| Prueba ejecutada | Sí |
| Resultado | Sí |
| Estado final | Sí |
| Riesgo residual | Sí, si aplica |

Plantilla sugerida:

```md
### Cierre SEC-XXX
- Fuente: `SecurityTest/VULNERABILITY_REGISTER.md`
- Grupo: `Gx`
- Riesgo: ...
- Decisión técnica: ...
- Artefactos tocados: ...
- Pruebas ejecutadas: ...
- Resultado: ...
- Riesgo residual: ...
- Estado: `Closed` | `Partial` | `Accepted Risk`
```

## Criterios de diseño por tipo de solución

### Si el cambio toca controladores

- el controlador solo orquesta request, servicio y respuesta
- no debe contener:
  - saneamiento HTML/JS ad hoc
  - SQL
  - lógica de lifecycle de tokens
  - autorización dispersa fuera de middleware/policies

### Si el cambio toca persistencia

- usar repositorios, managers o migration helpers
- cleanup de datos debe ser idempotente
- cualquier enforcement recurrente debe vivir fuera de la migración cuando sea regla de dominio

### Si el cambio toca vistas o layout

- escaping seguro por defecto
- nada de lógica compleja en `.phtml`
- JSON inline solo mediante helper/serializer seguro reusable

### Si el cambio toca frontend JS

- evitar lógica de seguridad distribuida en varios módulos sin contrato único
- priorizar una utilidad central para inserción HTML o rechazo de contenido inseguro
- mantener compatibilidad con surfaces actuales sin introducir framework paralelo

## Criterios de cierre por hallazgo

SEC-001:
- payload `</script>` no rompe el contexto inline
- existe prueba de regresión

SEC-002:
- un cookie remember-me previo no restablece sesión tras reset
- existe prueba de regresión

SEC-003:
- `composer audit` deja de reportar `CVE-2026-45065`
- si no se corrige, debe existir aceptación formal de riesgo

SEC-004:
- no quedan tokens API huérfanos
- `api_tokens.user_id` queda protegido por FK o decisión técnica equivalente formalizada

SEC-005:
- sinks HTML quedan inventariados y clasificados
- los de mayor riesgo quedan encapsulados o limitados por contrato técnico reusable

SEC-006:
- `unserialize(... allowed_classes => true)` se elimina, se estrecha o se deja con boundary residual justificado

## Verificación mínima por iteración

- `composer dump-autoload`
- `php public/cli.php help`
- `php public/cli.php security:check`

Verificaciones adicionales según grupo:
- G2: pruebas dirigidas de XSS/HTML partials
- G3: pruebas de emisión, revocación y ownership de tokens
- G4: `composer audit --no-interaction`
- G5: `php public/cli.php route:cache` y `php public/cli.php route:clear`

## Riesgos de ejecución a evitar

- resolver sinks HTML uno por uno sin contrato reusable
- meter lógica de seguridad en controladores por rapidez
- cerrar hallazgos sin prueba reproducible
- mezclar supply chain con refactor funcional no relacionado
- endurecer cache/route cache sin validar bootstrap real

## Recomendación operativa final

Ejecutar la remediación como un programa corto de 5 fases, no como tickets aislados. El orden correcto es:

1. cerrar con pruebas lo ya remediado
2. resolver integridad de tokens API
3. diseñar y ejecutar la corriente única de sinks HTML/JS
4. endurecer deserialización local
5. cerrar advisory o formalizar aceptación de riesgo

Ese orden respeta `KISS`, evita duplicación, mantiene controladores delgados y deja la trazabilidad de seguridad alineada con `SOLID`, `Service Layer`, `Repository Pattern`, `Middleware Pipeline` y `menor privilegio`.

## Ejecucion final 2026-05-21

Resultado global:
- Plan ejecutado de inicio a fin en una sola pasada.
- Estado final: `SEC-001..SEC-006 = Closed`.
- Sin `Accepted Risk` ni `Partial` residuales para este batch.

### Cierre SEC-001
- Fuente: `SecurityTest/VULNERABILITY_REGISTER.md`
- Grupo: `G2`
- Riesgo: payloads persistidos podían cerrar `<script>` inline y ejecutar JS arbitrario.
- Decisión técnica: introducir `app/Framework/View/InlineJson.php` como helper reusable y sustituir todos los builders inline vulnerables en layout/auth/operations.
- Artefactos tocados: `app/Framework/View/InlineJson.php`, `boot-core/template/scope/components/_head-assets.php`, `_catalyst-init.php`, `_status-bar.php`, `Repository/Framework/Auth/Views/scope/pages/register.php`, `Repository/Framework/Operations/Views/scope/pages/localization.php`.
- Pruebas ejecutadas: `docker exec WSDD-Web-Server-PHP8.4 php /var/www/html/catalyst.dock/public/cli.php security:regression --json`, E2E guest `/login`, `/register`, `/forgot-password`, `/operations/localization?locale=es`.
- Resultado: `inline-json-escaping` OK; las superficies reales respondieron `200` sin ruptura del contexto inline.
- Riesgo residual: ninguno relevante para este hallazgo.
- Estado: `Closed`

### Cierre SEC-002
- Fuente: `SecurityTest/VULNERABILITY_REGISTER.md`
- Grupo: `G1`
- Riesgo: un reset exitoso conservaba `remember_tokens` válidos y permitía continuidad de sesión persistente.
- Decisión técnica: invalidar `remember_tokens` en `PasswordResetController::reset()` y dejar regresión automatizada reusable.
- Artefactos tocados: `Repository/Framework/Auth/Controllers/PasswordResetController.php`, `app/Framework/Cli/Commands/SecurityRegressionCommand.php`.
- Pruebas ejecutadas: `security:regression --json`, E2E real de `/forgot-password` + `/reset-password/{token}` + login MFA de `qa-auth`.
- Resultado: `reset-invalidates-remember` OK y recovery real revalidado end-to-end.
- Riesgo residual: ninguno relevante para este hallazgo.
- Estado: `Closed`

### Cierre SEC-003
- Fuente: `SecurityTest/VULNERABILITY_REGISTER.md`
- Grupo: `G4`
- Riesgo: advisory abierto sobre `symfony/routing` transitivo vía `cboden/ratchet`.
- Decisión técnica: actualizar sólo el árbol permitido por el lock actual sin agregar paquetes.
- Artefactos tocados: `composer.lock`.
- Pruebas ejecutadas: `composer update symfony/routing --with-all-dependencies --no-interaction`, `composer audit --no-interaction`.
- Resultado: `symfony/routing` pasó de `v6.4.34` a `v6.4.40`, `symfony/deprecation-contracts` a `v3.7.0`, auditoría limpia.
- Riesgo residual: ninguno relevante para este hallazgo.
- Estado: `Closed`

### Cierre SEC-004
- Fuente: `SecurityTest/VULNERABILITY_REGISTER.md`
- Grupo: `G3`
- Riesgo: `api_tokens.user_id` podía quedar huérfano y el runtime no cerraba estados inválidos de ownership.
- Decisión técnica: saneamiento previo + FK compuesta tenant-aware + enforcement continuo en el manager.
- Artefactos tocados: `boot-core/database/migrations/20260521153000_harden_api_tokens_user_ownership.php`, `app/Framework/Api/ApiTokenManager.php`, `app/Framework/Api/ApiTokenRepository.php`, `app/Framework/Cli/Commands/ApiTokensSmokeCommand.php`, `public/cli.php`.
- Pruebas ejecutadas: `docker exec WSDD-Web-Server-PHP8.4 php /var/www/html/catalyst.dock/public/cli.php migrate`, `api-tokens:smoke --json`.
- Resultado: `no-orphaned-tokens` OK, creación/resolución OK, desactivación revoca, inserción inválida rechazada por FK.
- Riesgo residual: ninguno relevante para este hallazgo.
- Estado: `Closed`

### Cierre SEC-005
- Fuente: `SecurityTest/VULNERABILITY_REGISTER.md`
- Grupo: `G2`
- Riesgo: sinks `withHtml()` / `innerHTML` aceptaban HTML sin un contrato reusable de confianza.
- Decisión técnica: establecer un contrato full-stack `TrustedHtml` / `trusted-html` y reemplazar sinks dinámicos de datos por builders DOM o helpers comunes.
- Artefactos tocados: `app/Framework/Http/JsonResponse.php`, `app/Framework/Controllers/Controller.php`, `public/assets/js/catalyst/modules/trusted-dom.js`, `response-actions.js`, `modal.js`, `public/assets/js/catalyst/modules/utils.js`, `Repository/Framework/Settings/front/script.js`, `Repository/App/Surface/{Home,Landing,Dashboard,Store}/front/script.js`, `Repository/Framework/DevTools/Controllers/{ToasterTestController,ModalTestController}.php`.
- Pruebas ejecutadas: `security:regression --json`, E2E real `/test-features/api/js-enhancements/partial-refresh`, `/test-features/modal/sample-content`, `/operations/*`, `/setup`, republicación y verificación de `public/assets/js/work/*`.
- Resultado: JSON parcial expone `html_policy=trusted-html`, fragmentos modales responden `X-Catalyst-Fragment-Policy: trusted-html`, los work assets publicados críticos ya no usan `innerHTML` para listas/valores dinámicos.
- Riesgo residual: ninguno relevante para este hallazgo.
- Estado: `Closed`

### Cierre SEC-006
- Fuente: `SecurityTest/VULNERABILITY_REGISTER.md`
- Grupo: `G5`
- Riesgo: deserialización amplia desde disco local en file-cache y route-cache.
- Decisión técnica: firmar payloads serializados y rehidratar sólo clases exactas declaradas por el propio payload firmado.
- Artefactos tocados: `app/Framework/Security/SignedSerializedPayload.php`, `app/Framework/Cache/FileCacheStore.php`, `app/Framework/Route/Route.php`, `app/Framework/Cli/Commands/SecurityRegressionCommand.php`.
- Pruebas ejecutadas: `security:regression --json`, `docker exec WSDD-Web-Server-PHP8.4 php /var/www/html/catalyst.dock/public/cli.php route:cache`, `route:clear`, E2E `/test-features/route-cache`.
- Resultado: `signed-file-cache` OK, `signed-route-cache` OK y compatibilidad runtime conservada.
- Riesgo residual: si un atacante obtiene escritura local y `APP_KEY`, el impacto ya equivale a compromiso profundo del runtime; no queda un vector adicional específico de deserialización amplia.
- Estado: `Closed`
