# Security Backlog

Estado del batch `SEC-001..SEC-006` al `2026-05-21`: cerrado al `100%`.

| Prioridad | Estado | Item | Resultado esperado |
| --- | --- | --- |
| P1 | Done | Resolver advisory de `symfony/routing` transitivo vía `cboden/ratchet` | `composer audit` sin `CVE-2026-45065` |
| P1 | Done | Agregar FK para `api_tokens.user_id` con script de saneamiento previo | No más tokens huérfanos ni inserciones inválidas |
| P1 | Done | Añadir prueba de regresión para reset de contraseña + remember-me | Un reset invalida sesiones persistentes previas |
| P1 | Done | Añadir prueba de regresión para payloads JSON inline en layouts | No ejecutar `</script>` inyectado desde datos persistidos |
| P2 | Done | Definir helper o contrato de HTML confiable para `withHtml()` y `innerHTML` | Reducir riesgo de futuras XSS DOM |
| P2 | Future hardening | Revisar autorización por recurso en APIs autenticadas y por token | Evitar bypass horizontal en lectura/mutación |
| P3 | Done | Reemplazar o encapsular `unserialize()` sobre cache de disco | Menor impacto ante escritura local comprometida |
| P3 | Future hardening | Checklist de despliegue para setup y devtools | Evitar exposición operacional por configuración |

Notas:
- `Future hardening` no corresponde a hallazgos `SEC-001..SEC-006` abiertos; queda como línea preventiva independiente del batch ya cerrado.
