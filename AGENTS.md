# AGENTS.md — Catalyst

## Contrato del proyecto

- Este archivo aplica a todo agente que trabaje en `catalyst`.
- Si hay conflicto con el `AGENTS.md` global del workspace, este archivo manda en lo especifico del proyecto.
- Este archivo es contexto `hot`: contrato corto, comandos y mapa de lectura.
- El detalle documental vive en `docs/harness-context-map.md`.

## Stack activo

- PHP 8.4 strict types
- Bootstrap 5.3.8 + JavaScript ES Modules
- MySQL/MariaDB
- Docker via WSDD en `https://catalyst.dock/`
- Dependencias aprobadas: `phpmailer/phpmailer`, `league/oauth2-client`, `cboden/ratchet`, `react/http`
- Debug: Xdebug puerto `9003` con `host.docker.internal`

## Restricciones

1. No agregar dependencias Composer sin aprobacion explicita.
2. No editar `vendor/` manualmente.
3. No modificar el orden de bootstrap ni el mecanismo de inicializacion salvo tarea explicita.
4. `app/Framework/` y `app/Helpers/` son core del framework: tocarlos solo cuando la tarea realmente requiere cambio de framework; si no, preferir `Repository/`.
5. No mezclar cambios de harness/documentacion con codigo de producto fuera del alcance pedido.
6. Mantener `AGENTS.md` corto; estado y continuidad viven fuera de este archivo.
7. Regla de assets de vistas: todo CSS/JS especifico de modulo o pantalla debe vivir en `Repository/{Framework|App}/{Modulo}/front/{style.css,script.js}` y cargarse via `Controller::view()` + `FrontResourceTrait` hacia `public/assets/*/work/{slug}/`; solo el shell compartido del framework o vendors externos imprescindibles quedan fuera de esa regla.

## Verificacion base

```powershell
pwsh -Command "composer dump-autoload"
pwsh -Command "php public/cli.php help"
pwsh -Command "php public/cli.php status"
pwsh -Command "php public/cli.php make:controller --help"
```

Uso local:

- Web: `https://catalyst.dock/`
- CLI: `php public/cli.php`

## Documentacion canonica

- Estado breve: `D:/OpsZone/DevWorkspace/Knowledge/Obsidian-Vault/08-AI-Context/catalyst.md`
- Reentrada operativa: `D:/OpsZone/DevWorkspace/Knowledge/Obsidian-Vault/08-AI-Context/catalyst-reentry.md`
- Mapa documental: `D:/OpsZone/DevWorkspace/Projects/Web/catalyst/docs/harness-context-map.md`
- Diccionario tecnico: `D:/OpsZone/DevWorkspace/Projects/Web/catalyst/STRUCTURE.md`
- API index: `D:/OpsZone/DevWorkspace/Projects/Web/catalyst/API.md`
- Historial tecnico: `D:/OpsZone/DevWorkspace/Projects/Web/catalyst/docs/update-log.md`
- Historial de sesiones y trazabilidad cerrada: `D:/OpsZone/DevWorkspace/Knowledge/Obsidian-Vault/07-Summaries/`
- Historico AI Context pesado archivado: `D:/OpsZone/DevWorkspace/Knowledge/Obsidian-Vault/99-Archive/ai-context-heavy/catalyst-history-2026-05-28.md`

## Si tocas X, lee Y

| Si tocas | Leer primero |
|---|---|
| auth, MFA, reset, verification, OAuth | `docs/framework-auth.md`, `docs/repository-auth.md` |
| db, query builder, ORM, relations | `docs/framework-database.md`, `STRUCTURE.md` |
| events, jobs, scheduler, runtime async | `docs/framework-event.md`, `docs/framework-queue.md`, `docs/framework-schedule.md`, `STRUCTURE.md` |
| vistas, CSP, scripts inline, `data-*` | `docs/security-conventions.md` |
| bootstrap, routing, kernel, entry points | `docs/architecture.md`, `docs/entry-points.md`, `docs/kernel.md` |
| setup/config | `docs/checklists/setup-completion-e2e.md`, `docs/helpers-config.md` |
| clases existentes o nuevas | `STRUCTURE.md` |
| historial tecnico | `docs/update-log.md` |
| trazabilidad cerrada o continuidad historica | `Knowledge/Obsidian-Vault/07-Summaries/`, `Knowledge/Obsidian-Vault/99-Archive/ai-context-heavy/` |

## Reglas de documentacion

- Si se crea o modifica una clase, actualizar `STRUCTURE.md`.
- Si cambia la estructura de directorios o el onboarding humano, actualizar `README.md`.
- Si cambia el enrutamiento documental del proyecto, actualizar `docs/harness-context-map.md`.
- Si cambia el estado de la sesion o de la fase, actualizar `08-AI-Context/catalyst.md`.

## Seguridad y salida

- Escape de salida via `e($value)` en templates.
- Reglas CSP, nonce y patrones `data-*` viven en `docs/security-conventions.md`.
- Credenciales solo en variables de entorno y config local.

## Estado actual

Ver `D:/OpsZone/DevWorkspace/Knowledge/Obsidian-Vault/08-AI-Context/catalyst.md` como estado compacto.
Si el repo esta sucio o la tarea es retomar desarrollo activo, leer tambien `D:/OpsZone/DevWorkspace/Knowledge/Obsidian-Vault/08-AI-Context/catalyst-reentry.md`.
