# Promptd — histórico / obsoleto

Este archivo se conserva solo como registro del prompt usado cuando el rollout
post-auditoría seguía abierto.

Estado actual:

- el rollout ya no está activo
- el roadmap histórico ya no debe retomarse como canon operativo
- cualquier trabajo futuro debe abrirse como incidencia o sesión nueva contra
  runtime real

No reutilizar este prompt como instrucción vigente sin antes degradarlo o
reescribirlo para el objetivo actual.

## Prompt histórico preservado literalmente — no ejecutar

Las referencias a roadmap dentro del bloque siguiente pertenecen al snapshot histórico del rollout ya cerrado.

```text
Continuar proyecto PHP: catalyst

Workspace:
D:/OpsZone/DevWorkspace

Proyecto:
D:/OpsZone/DevWorkspace/Projects/Web/catalyst

Objetivo de esta sesion:
- Continuar el rollout de remediacion posterior a la auditoria interna del framework PHP.
- Usar como canon de trabajo el roadmap de remediacion:
  D:/OpsZone/DevWorkspace/Projects/Web/catalyst/docs/audit-remediation-roadmap.md
- Resolver de forma ordenada los hallazgos confirmados de runtime, wiring, drift documental y superficies residuales.
- Actualizar el seguimiento tecnico mientras avances, no solo al final.

Antes de actuar:
1. Leer D:/OpsZone/DevWorkspace/AGENTS.md
2. Leer D:/OpsZone/DevWorkspace/WORKSPACE-HARNESS.md
3. Leer D:/OpsZone/DevWorkspace/Projects/Web/catalyst/AGENTS.md
4. Leer D:/OpsZone/DevWorkspace/Knowledge/Obsidian-Vault/08-AI-Context/catalyst.md solo como estado compacto
5. Leer D:/OpsZone/DevWorkspace/Knowledge/Obsidian-Vault/08-AI-Context/catalyst-reentry.md solo si el repo esta sucio o hay continuidad activa
6. Leer el summary mas reciente y relevante de catalyst
7. Leer D:/OpsZone/DevWorkspace/Projects/Web/catalyst/docs/harness-context-map.md
8. Leer D:/OpsZone/DevWorkspace/Projects/Web/catalyst/docs/audit-remediation-roadmap.md
9. Cargar docs warm solo si hacen falta para ejecutar el bloque actual sin contradicciones

Estado que debes asumir:
- Etapas 0-18 cerradas formalmente
- Roadmap historico finalizado
- Deuda tecnica Core y Lateral cerrada dentro del alcance no diferido
- Stage 10 Cache sigue diferida y no debe convertirse en feature nueva
- Asset Versioning sigue fuera de alcance
- La auditoria profunda del framework ya fue realizada
- El problema ahora no es descubrir desde cero, sino ejecutar la remediacion con disciplina

Hallazgos base ya confirmados que no debes redescubrir desde cero:
- `Repository/Framework/Notification/Controllers/NotificationController.php` tiene un bug real:
  `markRead()` llama `Request::routeParam('id')`, pero `Request` no implementa `routeParam()`
- `docs/repository-notification.md` no refleja el runtime real:
  hoy existe UI de notificaciones con panel, badge, REST y token WS
- `app/Framework/Middleware/RequestThrottlingMiddleware.php` es un stub pass-through,
  aunque mapas/docs lo presentan como throttling general
- `app/Framework/View/View.php` mantiene una ruta residual a `project/Repository/Views`
- `app/Framework/Notification/NotificationManager.php` y
  `app/Framework/WebSocket/WebSocketPublisher.php` no muestran productores reales confirmados
- `app/Framework/Mail/MailAttachment.php` y
  `app/Framework/Notification/NotificationPosition.php` no muestran uso PHP real
- Hay drift adicional en:
  `STRUCTURE.md`
  `docs/framework-auth.md`
  `docs/framework-database.md`
  `docs/framework-mail.md`
  `docs/framework-traits.md`
  `docs/framework-view.md`
  `docs/framework-websocket.md`
  `docs/helpers-error.md`
  `Repository/Framework/DevTools/Views/uml.phtml`

Modo de trabajo obligatorio:
- Comunicacion en espanol
- Usar PowerShell
- No tocar vendor
- No agregar dependencias Composer
- No reabrir etapas del roadmap historico
- No inventar backlog por etapas
- No hacer cambios por reflejo fuera del bloque actual
- No asumir que una clase debe vivir solo por antiguedad
- No asumir que una clase debe morir solo porque parece poco usada; validar su estado dentro del roadmap

Prioridad de ejecucion:
1. Corregir primero runtime breaks confirmados
2. Luego alinear wiring y documentacion de Notification/WebSocket
3. Luego resolver middleware/documentacion inflada o engañosa
4. Luego limpiar contratos residuales o deprecables
5. Mantener el tracking actualizado durante todo el rollout

Regla de seguimiento:
- `D:/OpsZone/DevWorkspace/Projects/Web/catalyst/docs/audit-remediation-roadmap.md`
  debe tratarse como roadmap canonico del rollout actual
- Al completar o avanzar un bloque, actualizar ese roadmap
- Si cambia el estado real del framework, actualizar:
  `D:/OpsZone/DevWorkspace/Knowledge/Obsidian-Vault/08-AI-Context/catalyst.md` como estado compacto
- Si la sesion queda a medias o el repo queda en estado de continuidad activa,
  mantener corto y operativo:
  `D:/OpsZone/DevWorkspace/Knowledge/Obsidian-Vault/08-AI-Context/catalyst-reentry.md` solo si hay continuidad activa

Politica documental:
- Si runtime y docs se contradicen, gana el runtime
- No maquillar docs: deben reflejar comportamiento real, rutas reales, consumidores reales y clases realmente vivas
- Si una clase queda residual, marcarla como tal con claridad
- Si una clase se conserva por valor estructural, dejarlo explicitado

Verificacion minima:
- pwsh -Command "composer dump-autoload"
- pwsh -Command "php public/cli.php help"
- Ejecutar verificaciones adicionales solo para el bloque actual

Forma de cierre esperada:
- Cambios implementados del bloque actual
- Verificacion minima ejecutada
- Roadmap de remediacion actualizado
- AI context actualizado si el estado real cambio
- Summary al cierre productivo si corresponde
- Prompt de continuidad por pantalla para la siguiente sesion

Primera accion esperada:
- Leer los archivos canonicos
- Elegir el siguiente bloque pendiente mas importante del roadmap de remediacion
- Ejecutarlo de punta a punta con cambios, verificacion y actualizacion de seguimiento
```
