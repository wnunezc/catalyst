# Security Audit Summary

Fecha de ejecucion: 2026-05-21

Scope aplicado:
- Inventario de rutas con `php public/cli.php route:list --json`
- Revisión de auth, sesiones, CSRF, headers, rutas sensibles y sinks DOM
- Revisión de capa SQL, migraciones y estado de `composer audit`

Restricciones operativas:
- Baseline funcional asumido
- Sin cambios en `vendor`
- Sin agregar dependencias

Hallazgos principales:

| ID | Severidad | Estado | Resumen |
| --- | --- | --- | --- |
| SEC-001 | Alta | Closed | JSON inline movido a helper reusable seguro (`InlineJson`) y revalidado con regresión sobre `</script>` |
| SEC-002 | Media | Closed | `PasswordResetController::reset()` invalida `remember_tokens` y el recovery real quedó reprobado con MFA |
| SEC-003 | Media | Closed | `composer audit` quedó limpio tras actualizar `symfony/routing` a `v6.4.40` dentro del árbol permitido |
| SEC-004 | Media | Closed | `api_tokens` quedó saneado y protegido por FK compuesta tenant-aware; el lifecycle activo revoca tokens inválidos |
| SEC-005 | Media | Closed | `withHtml()` y los sinks DOM críticos quedaron bajo un contrato reusable `TrustedHtml` / `trusted-html` |
| SEC-006 | Baja | Closed | Cache y route-cache dejaron de usar `allowed_classes => true` amplio; ahora sólo aceptan payloads firmados |

Remediaciones aplicadas en esta sesion:
- Se introdujo `app/Framework/View/InlineJson.php` y se reemplazaron los builders inline vulnerables de layout/auth/operations para forzar `JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT`.
- `JsonResponse::withHtml()` ahora exige `TrustedHtml`, `Controller::trustedHtmlResponse()` emite `X-Catalyst-Fragment-Policy: trusted-html` y `public/assets/js/catalyst/modules/trusted-dom.js` centraliza la inserción DOM confiable.
- `Repository/Framework/Settings/front/script.js` y las superficies `Repository/App/Surface/{Home,Landing,Dashboard,Store}/front/script.js` dejaron de construir listas dinámicas con `innerHTML`.
- `boot-core/database/migrations/20260521153000_harden_api_tokens_user_ownership.php` sanea orphans, crea índice único `(tenant_id, id)` en `users` y agrega FK compuesta `api_tokens(tenant_id, user_id) -> users(tenant_id, id)`.
- `ApiTokenManager` ahora rechaza usuarios inexistentes/inactivos al emitir tokens y revoca tokens activos si el usuario queda inválido al resolver el bearer.
- `app/Framework/Security/SignedSerializedPayload.php` encapsula la persistencia serializada firmada y reemplaza la deserialización amplia en `FileCacheStore` y `Route`.
- Se añadieron los comandos `security:regression` y `api-tokens:smoke` para dejar evidencia reproducible de cierre.

Puntos del roadmap que conviene reconsiderar:
- La corriente de seguridad quedó mejor cerrada cuando se trató como contratos reusables de framework y no como parches de callsite.
- El boundary WSDD/Docker sigue importando para cualquier smoke DB-backed; desde host Windows el fallo de resolución a `WSDD-MySql-Server` no debe confundirse con regresión del framework.
- La publicación de `work/{slug}` debe verificarse visitando la superficie dueña cuando el entry root delega a otra vista, como ocurrió con `Home`.

Verificaciones ejecutadas durante la auditoria:
- `composer dump-autoload` -> OK
- `php public/cli.php help` -> OK
- `php public/cli.php security:check` -> OK
- `composer audit --no-interaction` -> OK, sin advisories
- `docker exec WSDD-Web-Server-PHP8.4 php /var/www/html/catalyst.dock/public/cli.php migrate` -> OK, batch `13`
- `docker exec WSDD-Web-Server-PHP8.4 php /var/www/html/catalyst.dock/public/cli.php security:regression --json` -> OK (`inline-json-escaping`, `trusted-html-contract`, `reset-invalidates-remember`, `signed-file-cache`, `signed-route-cache`)
- `docker exec WSDD-Web-Server-PHP8.4 php /var/www/html/catalyst.dock/public/cli.php api-tokens:smoke --json` -> OK (`create-and-resolve`, `inactive-user-revokes-token`, `fk-rejects-invalid-owner`, `no-orphaned-tokens`)
- `docker exec WSDD-Web-Server-PHP8.4 php /var/www/html/catalyst.dock/public/cli.php route:cache` y `route:clear` -> OK
- E2E real `https://catalyst.dock/` con MFA/TOTP vía `MFA-Forge` -> OK sobre guest, auth/recovery, `/operations/*`, `/setup`, partial HTML, modal fragments, `api-platform`, `/catalogs`, `/test-features` y `/test-features/route-cache`

Estado recomendado:
- El batch de remediación queda cerrado.
- No queda riesgo residual abierto que requiera `Accepted Risk` formal para `SEC-001..SEC-006`.
