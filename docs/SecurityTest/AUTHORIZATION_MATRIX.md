# Authorization Matrix

| Familia | Mutacion | Middleware observado | Evaluacion |
| --- | --- | --- | --- |
| Login / register / forgot-password | Si | `GuestMiddleware`, `LoginThrottleMiddleware` en login y register | Expuestos a invitados; revisar throttling y respuestas uniformes |
| Reset de contraseña | Si | `GuestMiddleware` | Quedó corregida la revocación de `remember_tokens` tras cambio de clave |
| MFA | Si | `RouteFeatureMiddleware`, `AuthMiddleware` parcial, `LoginThrottleMiddleware` en verify | Requiere validar exhaustivamente estados pendientes MFA y feature flags |
| APIs públicas de App | No | `-` | Lectura pública intencional; no se vieron mutaciones |
| APIs de notificaciones/presencia | Si parcial | `AuthMiddleware` | Usan sesión autenticada; revisar autorización por usuario/registro en heartbeat |
| API v1 por token | Si | `ApiTokenMiddleware` | El control de acceso depende del token; persiste el hueco de integridad en `api_tokens.user_id` |
| Operations | Si | `AuthMiddleware + RoleMiddleware` | Superficie administrativa amplia; buena candidata a pruebas de autorización horizontal/vertical |
| Roles / permisos / usuarios | Si | `AuthMiddleware + RoleMiddleware` | Alto impacto por capacidad de escalado de privilegios |
| Setup | Si | `SetupGuardMiddleware` | Riesgo condicionado al estado de bootstrap; no debe quedar accesible tras cierre |
| DevTools | Si | `DevToolsGuardMiddleware` | Riesgo de entorno, no defecto explotable por defecto dentro del baseline revisado |

Observaciones:
- El patrón dominante para escritura administrativa es `POST` + `AuthMiddleware + RoleMiddleware`.
- La auditoría no confirmó bypass directo de RBAC en rutas administrativas, pero el volumen de endpoints mutantes justifica tests dirigidos por permiso.
- En la API por token conviene agregar pruebas de scopes/capacidades y limpieza de tokens huérfanos.
