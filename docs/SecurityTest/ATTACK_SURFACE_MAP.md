# Attack Surface Map

Inventario de alto nivel obtenido desde `php public/cli.php route:list --json`.

| Superficie | Conteo observado | Control principal | Riesgo relevante |
| --- | --- | --- | --- |
| `/api/*` | 22 rutas | `AuthMiddleware` o `ApiTokenMiddleware` según familia | APIs con lectura y mutación; revisar lifecycle de tokens y autorización por recurso |
| `/operations/*` | 21 rutas | `AuthMiddleware + RoleMiddleware` | Cambios operativos, branding, feature flags, plugins, despliegues |
| `/setup*` | 18 rutas | `SetupGuardMiddleware` | Configuración inicial, credenciales, correo, websocket, reset de setup |
| CRUD administrativos fuera de `/operations` | decenas de POST | `AuthMiddleware + RoleMiddleware` | Roles, permisos, catálogos, media, documentos, automatización |
| Auth invitado | `POST /login`, `POST /register`, `POST /forgot-password`, `POST /reset-password/{token}` | `GuestMiddleware`, throttling parcial | Abuso de credenciales, MFA, recuperación y continuidad de sesión |
| Auth social | 2 rutas | `RouteFeatureMiddleware` | Exposición condicionada por feature flag e integración OAuth |
| APIs públicas | `GET /api/public/*` | sin auth | Lectura pública de superficies App |
| DevTools | varias `POST /test-features/*` | `DevToolsGuardMiddleware` | Riesgo alto solo si el guard queda mal configurado en entornos no locales |

Superficies especialmente sensibles:
- `POST /configuration/platform-appearance`: antes permitía persistir texto que terminaba en un `<script>` inline global; ya quedó remediado en los builders JSON del layout.
- `POST /api-platform/tokens` y `POST /api-platform/tokens/{id}/revoke`: dependen de controles RBAC, pero su persistencia sigue sin FK dura hacia `users`.
- `POST /setup/*`: concentran mutaciones de configuración, secretos y bootstrap; su exposición real depende de que setup siga abierto o no.

Trust boundaries observados:
- Datos persistidos en base de datos que luego llegan a `<script>` inline.
- HTML devuelto por JSON o fetch same-origin que termina en `innerHTML`.
- Cache/route cache en disco rehidratada con `unserialize(...)`.

Prioridad operativa sugerida:
1. Rutas de auth y recuperación
2. Rutas operativas con escritura y branding
3. APIs con token y su persistencia
4. Setup y DevTools como riesgo de despliegue/configuración
