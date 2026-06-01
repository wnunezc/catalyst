# Remediation Roadmap

Estado: roadmap ejecutado y cerrado el `2026-05-21`.

## Ejecutado

- Se consolidó el fix de JSON inline seguro en los builders que inyectan payloads en `<script>`.
- Se consolidó la revocación de `remember_tokens` tras `POST /reset-password/{token}` y quedó cubierta por regresión.
- Se resolvió el advisory de `symfony/routing` dentro del árbol permitido por `cboden/ratchet`.

## Ejecutado en la misma pasada

- Se creó la migración con FK compuesta tenant-aware en `api_tokens` y se limpiaron huérfanos previos.
- Se añadieron regresiones automatizadas para:
  - payload `</script>` en branding/localization/register payloads
  - invalidación de sesión persistente después de reset de contraseña
  - signed file-cache y signed route-cache
- Se definió el contrato explícito para `JsonResponse::withHtml()` y sinks `innerHTML`.

## Seguimiento preventivo fuera del batch

- Mantener el checklist de despliegue para asegurar `SetupGuardMiddleware` y `DevToolsGuardMiddleware` fuera de alcance en producción.
- Revisar boundaries de autorización por recurso en APIs autenticadas y por token cuando se abran nuevos endpoints o abilities.
