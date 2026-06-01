# Database Security Review

Foco real encontrado en esta corrida:

| Tema | Estado | Nota |
| --- | --- | --- |
| SQL dinámico del framework | Parcialmente revisado | `QueryBuilder` y `SqlReference` ya aplican allowlists a tablas, columnas, direcciones y operadores; no apareció un SQLi evidente en esa capa |
| FKs de auth clásico | Cubierto | La migración `20260421000000_add_auth_foreign_keys.php` sí ata `remember_tokens`, `email_verification_tokens`, `password_reset_tokens` y `user_social_accounts` |
| FK de `api_tokens.user_id` | Abierto | `20260519144000_create_api_tokens_table.php` crea índice pero no foreign key |
| Tenant boundaries | Parcial | `20260519190000_add_tenant_boundaries_to_shared_runtime.php` agrega `tenant_id` e índices, pero no endurece integridad relacional completa |
| Deserialización desde cache | Parcial | No es un problema SQL, pero sí de persistencia local y rehidratación de objetos |

Reconsideración del roadmap:
- Bajar prioridad a una caza genérica de SQLi sobre el builder central salvo que aparezcan callsites con SQL raw.
- Subir prioridad a integridad relacional de tokens, ownership por tenant y pruebas de consistencia al borrar usuarios.

Acciones concretas pendientes:
- Añadir migración para `api_tokens.user_id` con limpieza previa de huérfanos.
- Revisar si otras tablas multi-tenant requieren FK o constraints adicionales.
