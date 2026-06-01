# Security Test Plan

Pruebas prioritarias posteriores a esta corrida:

1. XSS inline regresión
- Guardar en apariencia o textos equivalentes un payload como `</script><script>alert(1)</script>`.
- Verificar que el HTML resultante no cierre el `<script>` inline y que el payload aparezca hex-escaped dentro del JSON.

2. Password reset + remember me
- Iniciar sesión con remember-me.
- Ejecutar reset de contraseña por token.
- Confirmar que el cookie viejo ya no restaura sesión.

3. Integridad de API tokens
- Crear token API.
- Simular borrado o desactivación del usuario.
- Verificar si quedan tokens huérfanos y documentar cleanup previo a la futura FK.

4. Sinks HTML de framework
- Forzar payloads HTML/JS en flujos que usan `JsonResponse::withHtml()`, `response-actions.js`, `modal.js` y `Settings/front/script.js`.
- Distinguir explícitamente entre contenido confiable same-origin y contenido influenciable por usuario.

5. Setup y DevTools
- Verificar en entorno objetivo que setup no pueda reabrirse.
- Verificar que `DevToolsGuardMiddleware` niegue acceso fuera del modo previsto.

6. Dependencias
- Repetir `composer audit --no-interaction` después de la futura estrategia de actualización de lock.

Verificación mínima de cierre para esta sesión:
- `composer dump-autoload`
- `php public/cli.php help`
- `php public/cli.php security:check`
