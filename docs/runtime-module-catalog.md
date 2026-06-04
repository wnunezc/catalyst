# Runtime Module Catalog

> Auto-generated from `ModuleRegistry`, `PermissionRegistry`, `NavigationRegistry`, `ModuleInspector`, `ModuleHarnessInspector` and `ModuleLinter`.
> Last generated: 2026-06-04 06:52:08

## Runtime Summary

- Modules: 18
- Structural lint: OK

| Key | Surface | HTML | JSON | Mutations | Assets | Permissions | Settings | Seeds |
|---|---|---:|---:|---:|---|---|---|---|
| `app.surface.account` | `authenticated` | 15 | 0 | 8 | `ok` | `manage-account-recovery` | `n/a` | `n/a` |
| `app.surface.dashboard` | `public` | 1 | 1 | 0 | `ok` | `n/a` | `n/a` | `n/a` |
| `app.surface.home` | `public` | 2 | 1 | 0 | `ok` | `n/a` | `n/a` | `n/a` |
| `app.surface.landing` | `public` | 1 | 1 | 0 | `ok` | `n/a` | `n/a` | `n/a` |
| `app.surface.store` | `public` | 1 | 1 | 0 | `ok` | `n/a` | `n/a` | `n/a` |
| `framework.apiplatform` | `authenticated` | 1 | 4 | 4 | `ok` | `manage-api-platform` | `n/a` | `n/a` |
| `framework.audit` | `authenticated` | 2 | 0 | 0 | `ok` | `manage-audit-log` | `n/a` | `n/a` |
| `framework.auth` | `auth-flow` | 10 | 0 | 9 | `ok` | `n/a` | `n/a` | `n/a` |
| `framework.automation` | `authenticated` | 4 | 2 | 7 | `ok` | `manage-automation-rules` | `n/a` | `n/a` |
| `framework.catalogs` | `authenticated` | 6 | 0 | 8 | `ok` | `manage-catalogs` | `n/a` | `n/a` |
| `framework.demoui` | `authenticated` | 40 | 0 | 0 | `ok` | `n/a` | `n/a` | `n/a` |
| `framework.devtools` | `devtools` | 26 | 7 | 12 | `ok` | `access-devtools` | `n/a` | `n/a` |
| `framework.documents` | `authenticated` | 4 | 2 | 9 | `ok` | `manage-document-templates` | `n/a` | `n/a` |
| `framework.media` | `authenticated` | 6 | 0 | 7 | `ok` | `manage-media-library`, `manage-media-metadata` | `n/a` | `n/a` |
| `framework.notification` | `authenticated-api` | 0 | 3 | 3 | `n/a` | `n/a` | `n/a` | `n/a` |
| `framework.operations` | `authenticated` | 10 | 0 | 11 | `ok` | `manage-platform-operations` | `n/a` | `n/a` |
| `framework.roles` | `administration` | 11 | 0 | 16 | `ok` | `manage-roles`, `manage-users` | `n/a` | `n/a` |
| `framework.settings` | `workspace` | 4 | 0 | 17 | `ok` | `n/a` | `n/a` | `n/a` |

## Module Detail

### app.surface.account

- Scope: `App`
- Surface: `authenticated`
- Runtime enabled: `yes`
- Slug: `account`
- Description: Personal user account center, account security and assisted recovery flows.
- Plugin: `standalone`
- Views: `yes`
- Assets: `ok`
- Settings: `n/a`
- Permissions: `manage-account-recovery`
- Seeds: `n/a`
- Feature flags: `n/a`
- Module flag key: `module.app.surface.account`
- Representative HTML: `/account/profile`
- Representative JSON: `n/a`

#### HTML routes

| Pattern | Methods | Guest | User | Admin | State Profiles | Permissions | Middleware |
|---|---|---|---|---|---|---|---|
| `/account-recovery/compromised` | `GET,HEAD` | `200` | `200` | `200` | `n/a` | `n/a` | `n/a` |
| `/account-recovery/mfa` | `GET,HEAD` | `200` | `200` | `200` | `n/a` | `n/a` | `n/a` |
| `/account-recovery/mfa/{token}` | `GET,HEAD` | `200` | `200` | `200` | `n/a` | `n/a` | `n/a` |
| `/account-recovery/start` | `GET,HEAD` | `200` | `200` | `200` | `n/a` | `n/a` | `n/a` |
| `/account-recovery/support` | `GET,HEAD` | `200` | `200` | `200` | `n/a` | `n/a` | `n/a` |
| `/account/activity` | `GET,HEAD` | `login` | `200` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\AuthMiddleware` |
| `/account/profile` | `GET,HEAD` | `login` | `200` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\AuthMiddleware` |
| `/account/recovery` | `GET,HEAD` | `login` | `200` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\AuthMiddleware` |
| `/account/recovery/compromised` | `GET,HEAD` | `login` | `200` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\AuthMiddleware` |
| `/account/recovery/mfa` | `GET,HEAD` | `login` | `200` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\AuthMiddleware` |
| `/account/recovery/support` | `GET,HEAD` | `login` | `200` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\AuthMiddleware` |
| `/account/security` | `GET,HEAD` | `login` | `200` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\AuthMiddleware` |
| `/account/security/mfa` | `GET,HEAD` | `login` | `200` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\AuthMiddleware` |
| `/admin/account-recovery` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-account-recovery` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/admin/account-recovery/{id}` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-account-recovery` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |

#### JSON routes

_No JSON routes declared for harness._

#### Mutations

| Pattern | Methods | Guest | User | Admin | State Profiles | Permissions | Middleware |
|---|---|---|---|---|---|---|---|
| `/account-recovery/compromised` | `POST` | `200` | `200` | `200` | `n/a` | `n/a` | `n/a` |
| `/account-recovery/mfa` | `POST` | `200` | `200` | `200` | `n/a` | `n/a` | `n/a` |
| `/account-recovery/support` | `POST` | `200` | `200` | `200` | `n/a` | `n/a` | `n/a` |
| `/account/recovery/compromised` | `POST` | `401` | `200` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\AuthMiddleware` |
| `/account/recovery/mfa` | `POST` | `401` | `200` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\AuthMiddleware` |
| `/account/recovery/support` | `POST` | `401` | `200` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\AuthMiddleware` |
| `/admin/account-recovery/{id}/approve` | `POST` | `401` | `403` | `200` | `n/a` | `manage-account-recovery` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/admin/account-recovery/{id}/reject` | `POST` | `401` | `403` | `200` | `n/a` | `manage-account-recovery` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |

### app.surface.dashboard

- Scope: `App`
- Surface: `public`
- Runtime enabled: `yes`
- Slug: `dashboard`
- Description: Personal account dashboard surface. Guests see a safe login/register gateway; authenticated users see the Account Shell.
- Plugin: `standalone`
- Views: `yes`
- Assets: `ok`
- Settings: `n/a`
- Permissions: `n/a`
- Seeds: `n/a`
- Feature flags: `n/a`
- Module flag key: `module.app.surface.dashboard`
- Representative HTML: `/dashboard`
- Representative JSON: `/api/public/dashboard`

#### HTML routes

| Pattern | Methods | Guest | User | Admin | State Profiles | Permissions | Middleware |
|---|---|---|---|---|---|---|---|
| `/dashboard` | `GET,HEAD` | `200` | `200` | `200` | `n/a` | `n/a` | `n/a` |

#### JSON routes

| Pattern | Methods | Guest | User | Admin | State Profiles | Permissions | Middleware |
|---|---|---|---|---|---|---|---|
| `/api/public/dashboard` | `GET,HEAD` | `401` | `200` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\AuthMiddleware` |

#### Mutations

_No mutation routes declared for harness._

### app.surface.home

- Scope: `App`
- Surface: `public`
- Runtime enabled: `yes`
- Slug: `home`
- Description: Public application home demo surface and canonical root entry.
- Plugin: `standalone`
- Views: `yes`
- Assets: `ok`
- Settings: `n/a`
- Permissions: `n/a`
- Seeds: `n/a`
- Feature flags: `n/a`
- Module flag key: `module.app.surface.home`
- Representative HTML: `/home`
- Representative JSON: `/api/public/home`

#### HTML routes

| Pattern | Methods | Guest | User | Admin | State Profiles | Permissions | Middleware |
|---|---|---|---|---|---|---|---|
| `/` | `GET,HEAD` | `200` | `200` | `200` | `n/a` | `n/a` | `n/a` |
| `/home` | `GET,HEAD` | `200` | `200` | `200` | `n/a` | `n/a` | `n/a` |

#### JSON routes

| Pattern | Methods | Guest | User | Admin | State Profiles | Permissions | Middleware |
|---|---|---|---|---|---|---|---|
| `/api/public/home` | `GET,HEAD` | `200` | `200` | `200` | `n/a` | `n/a` | `n/a` |

#### Mutations

_No mutation routes declared for harness._

### app.surface.landing

- Scope: `App`
- Surface: `public`
- Runtime enabled: `yes`
- Slug: `landing`
- Description: Public marketing landing page demo surface.
- Plugin: `standalone`
- Views: `yes`
- Assets: `ok`
- Settings: `n/a`
- Permissions: `n/a`
- Seeds: `n/a`
- Feature flags: `n/a`
- Module flag key: `module.app.surface.landing`
- Representative HTML: `/landing`
- Representative JSON: `/api/public/landing`

#### HTML routes

| Pattern | Methods | Guest | User | Admin | State Profiles | Permissions | Middleware |
|---|---|---|---|---|---|---|---|
| `/landing` | `GET,HEAD` | `200` | `200` | `200` | `n/a` | `n/a` | `n/a` |

#### JSON routes

| Pattern | Methods | Guest | User | Admin | State Profiles | Permissions | Middleware |
|---|---|---|---|---|---|---|---|
| `/api/public/landing` | `GET,HEAD` | `200` | `200` | `200` | `n/a` | `n/a` | `n/a` |

#### Mutations

_No mutation routes declared for harness._

### app.surface.store

- Scope: `App`
- Surface: `public`
- Runtime enabled: `yes`
- Slug: `store`
- Description: Public store and catalog demo surface.
- Plugin: `standalone`
- Views: `yes`
- Assets: `ok`
- Settings: `n/a`
- Permissions: `n/a`
- Seeds: `n/a`
- Feature flags: `n/a`
- Module flag key: `module.app.surface.store`
- Representative HTML: `/store`
- Representative JSON: `/api/public/store`

#### HTML routes

| Pattern | Methods | Guest | User | Admin | State Profiles | Permissions | Middleware |
|---|---|---|---|---|---|---|---|
| `/store` | `GET,HEAD` | `200` | `200` | `200` | `n/a` | `n/a` | `n/a` |

#### JSON routes

| Pattern | Methods | Guest | User | Admin | State Profiles | Permissions | Middleware |
|---|---|---|---|---|---|---|---|
| `/api/public/store` | `GET,HEAD` | `200` | `200` | `200` | `n/a` | `n/a` | `n/a` |

#### Mutations

_No mutation routes declared for harness._

### framework.apiplatform

- Scope: `Framework`
- Surface: `authenticated`
- Runtime enabled: `yes`
- Slug: `apiplatform`
- Description: Versioned API platform with token management, route catalog, workflow operations and version restore endpoints.
- Plugin: `framework.business`
- Views: `yes`
- Assets: `ok`
- Settings: `n/a`
- Permissions: `manage-api-platform`
- Seeds: `n/a`
- Feature flags: `n/a`
- Module flag key: `module.framework.apiplatform`
- Representative HTML: `/api-platform`
- Representative JSON: `/api/v1/catalog`

#### HTML routes

| Pattern | Methods | Guest | User | Admin | State Profiles | Permissions | Middleware |
|---|---|---|---|---|---|---|---|
| `/api-platform` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-api-platform` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |

#### JSON routes

| Pattern | Methods | Guest | User | Admin | State Profiles | Permissions | Middleware |
|---|---|---|---|---|---|---|---|
| `/api/v1/calendar/events` | `GET,HEAD` | `401` | `401` | `401` | `api_token=200` | `n/a` | `Catalyst\Framework\Middleware\ApiTokenMiddleware` |
| `/api/v1/catalog` | `GET,HEAD` | `401` | `401` | `401` | `api_token=200` | `n/a` | `Catalyst\Framework\Middleware\ApiTokenMiddleware` |
| `/api/v1/versions/{resourceKey}/{recordId}` | `GET,HEAD` | `401` | `401` | `401` | `api_token=200` | `n/a` | `Catalyst\Framework\Middleware\ApiTokenMiddleware` |
| `/api/v1/workflows` | `GET,HEAD` | `401` | `401` | `401` | `api_token=200` | `n/a` | `Catalyst\Framework\Middleware\ApiTokenMiddleware` |

#### Mutations

| Pattern | Methods | Guest | User | Admin | State Profiles | Permissions | Middleware |
|---|---|---|---|---|---|---|---|
| `/api-platform/tokens` | `POST` | `401` | `403` | `200` | `n/a` | `manage-api-platform` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/api-platform/tokens/{id}/revoke` | `POST` | `401` | `403` | `200` | `n/a` | `manage-api-platform` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/api/v1/versions/{id}/restore` | `POST` | `401` | `401` | `401` | `api_token=200` | `n/a` | `Catalyst\Framework\Middleware\ApiTokenMiddleware` |
| `/api/v1/workflows/{id}/transition` | `POST` | `401` | `401` | `401` | `api_token=200` | `n/a` | `Catalyst\Framework\Middleware\ApiTokenMiddleware` |

### framework.audit

- Scope: `Framework`
- Surface: `authenticated`
- Runtime enabled: `yes`
- Slug: `audit`
- Description: Operational audit log for administrative mutations and framework runtime events.
- Plugin: `framework.core`
- Views: `yes`
- Assets: `ok`
- Settings: `n/a`
- Permissions: `manage-audit-log`
- Seeds: `n/a`
- Feature flags: `n/a`
- Module flag key: `module.framework.audit`
- Representative HTML: `/audit-log`
- Representative JSON: `n/a`

#### HTML routes

| Pattern | Methods | Guest | User | Admin | State Profiles | Permissions | Middleware |
|---|---|---|---|---|---|---|---|
| `/audit-log` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-audit-log` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/audit-log/{id}` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-audit-log` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |

#### JSON routes

_No JSON routes declared for harness._

#### Mutations

_No mutation routes declared for harness._

### framework.auth

- Scope: `Framework`
- Surface: `auth-flow`
- Runtime enabled: `yes`
- Slug: `auth`
- Description: Authentication, recovery, MFA and social access surfaces.
- Plugin: `framework.core`
- Views: `yes`
- Assets: `ok`
- Settings: `n/a`
- Permissions: `n/a`
- Seeds: `n/a`
- Feature flags: `social_auth`, `mfa`
- Module flag key: `module.framework.auth`
- Representative HTML: `/login`
- Representative JSON: `n/a`

#### HTML routes

| Pattern | Methods | Guest | User | Admin | State Profiles | Permissions | Middleware |
|---|---|---|---|---|---|---|---|
| `/auth/social/callback/{provider}` | `GET,HEAD` | `200` | `200` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\RouteFeatureMiddleware` |
| `/auth/social/{provider}` | `GET,HEAD` | `200` | `200` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\RouteFeatureMiddleware` |
| `/forgot-password` | `GET,HEAD` | `200` | `root` | `root` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\GuestMiddleware` |
| `/login` | `GET,HEAD` | `200` | `root` | `root` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\GuestMiddleware` |
| `/mfa/challenge` | `GET,HEAD` | `login` | `root` | `root` | `pending_mfa=200` | `n/a` | `Catalyst\Framework\Middleware\RouteFeatureMiddleware` |
| `/mfa/setup` | `GET,HEAD` | `login` | `200` | `200` | `pending_setup=200` | `n/a` | `Catalyst\Framework\Middleware\RouteFeatureMiddleware` |
| `/register` | `GET,HEAD` | `200` | `root` | `root` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\RouteFeatureMiddleware`, `Catalyst\Framework\Middleware\GuestMiddleware` |
| `/reset-password/{token}` | `GET,HEAD` | `200` | `root` | `root` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\GuestMiddleware` |
| `/verify-email` | `GET,HEAD` | `200` | `root` | `root` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\GuestMiddleware` |
| `/verify-email/{token}` | `GET,HEAD` | `200` | `200` | `200` | `n/a` | `n/a` | `n/a` |

#### JSON routes

_No JSON routes declared for harness._

#### Mutations

| Pattern | Methods | Guest | User | Admin | State Profiles | Permissions | Middleware |
|---|---|---|---|---|---|---|---|
| `/forgot-password` | `POST` | `200` | `409` | `409` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\GuestMiddleware` |
| `/login` | `POST` | `200` | `409` | `409` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\GuestMiddleware`, `Catalyst\Framework\Middleware\LoginThrottleMiddleware` |
| `/logout` | `POST` | `200` | `200` | `200` | `n/a` | `n/a` | `n/a` |
| `/mfa/disable` | `POST` | `401` | `200` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RouteFeatureMiddleware` |
| `/mfa/enable` | `POST` | `200` | `200` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\RouteFeatureMiddleware` |
| `/mfa/verify` | `POST` | `200` | `200` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\RouteFeatureMiddleware` |
| `/register` | `POST` | `200` | `409` | `409` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\RouteFeatureMiddleware`, `Catalyst\Framework\Middleware\GuestMiddleware`, `Catalyst\Framework\Middleware\LoginThrottleMiddleware` |
| `/reset-password/{token}` | `POST` | `200` | `409` | `409` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\GuestMiddleware` |
| `/verify-email` | `POST` | `200` | `409` | `409` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\GuestMiddleware` |

### framework.automation

- Scope: `Framework`
- Surface: `authenticated`
- Runtime enabled: `yes`
- Slug: `automation`
- Description: Reusable internal automation rules with workflow lifecycle, queue/schedule execution, logs and version history.
- Plugin: `framework.business`
- Views: `yes`
- Assets: `ok`
- Settings: `n/a`
- Permissions: `manage-automation-rules`
- Seeds: `n/a`
- Feature flags: `n/a`
- Module flag key: `module.framework.automation`
- Representative HTML: `/automation-rules`
- Representative JSON: `/api/v1/automation-rules`

#### HTML routes

| Pattern | Methods | Guest | User | Admin | State Profiles | Permissions | Middleware |
|---|---|---|---|---|---|---|---|
| `/automation-rules` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-automation-rules` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/automation-rules/create` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-automation-rules` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/automation-rules/{id}` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-automation-rules` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/automation-rules/{id}/edit` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-automation-rules` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |

#### JSON routes

| Pattern | Methods | Guest | User | Admin | State Profiles | Permissions | Middleware |
|---|---|---|---|---|---|---|---|
| `/api/v1/automation-rules` | `GET,HEAD` | `401` | `401` | `401` | `api_token=200` | `n/a` | `Catalyst\Framework\Middleware\ApiTokenMiddleware` |
| `/api/v1/automation-rules/{id}` | `GET,HEAD` | `401` | `401` | `401` | `api_token=200` | `n/a` | `Catalyst\Framework\Middleware\ApiTokenMiddleware` |

#### Mutations

| Pattern | Methods | Guest | User | Admin | State Profiles | Permissions | Middleware |
|---|---|---|---|---|---|---|---|
| `/api/v1/automation-rules/{id}/run` | `POST` | `401` | `401` | `401` | `api_token=200` | `n/a` | `Catalyst\Framework\Middleware\ApiTokenMiddleware` |
| `/automation-rules` | `POST` | `401` | `403` | `200` | `n/a` | `manage-automation-rules` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/automation-rules/{id}` | `POST` | `401` | `403` | `200` | `n/a` | `manage-automation-rules` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/automation-rules/{id}/delete` | `POST` | `401` | `403` | `200` | `n/a` | `manage-automation-rules` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/automation-rules/{id}/run` | `POST` | `401` | `403` | `200` | `n/a` | `manage-automation-rules` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/automation-rules/{id}/transition` | `POST` | `401` | `403` | `200` | `n/a` | `manage-automation-rules` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/automation-rules/{id}/versions/{versionId}/restore` | `POST` | `401` | `403` | `200` | `n/a` | `manage-automation-rules` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |

### framework.catalogs

- Scope: `Framework`
- Surface: `authenticated`
- Runtime enabled: `yes`
- Slug: `catalogs`
- Description: Reusable governed catalogs with workflow lifecycle, validity windows, version history and metadata consumption.
- Plugin: `standalone`
- Views: `yes`
- Assets: `ok`
- Settings: `n/a`
- Permissions: `manage-catalogs`
- Seeds: `n/a`
- Feature flags: `n/a`
- Module flag key: `module.framework.catalogs`
- Representative HTML: `/workspaces/catalogs`
- Representative JSON: `n/a`

#### HTML routes

| Pattern | Methods | Guest | User | Admin | State Profiles | Permissions | Middleware |
|---|---|---|---|---|---|---|---|
| `/workspaces/catalogs` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-catalogs` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/catalogs/create` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-catalogs` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/catalogs/{id}` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-catalogs` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/catalogs/{id}/edit` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-catalogs` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/catalogs/{id}/items/create` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-catalogs` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/catalogs/{id}/items/{itemId}/edit` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-catalogs` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |

#### JSON routes

_No JSON routes declared for harness._

#### Mutations

| Pattern | Methods | Guest | User | Admin | State Profiles | Permissions | Middleware |
|---|---|---|---|---|---|---|---|
| `/workspaces/catalogs` | `POST` | `401` | `403` | `200` | `n/a` | `manage-catalogs` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/catalogs/{id}` | `POST` | `401` | `403` | `200` | `n/a` | `manage-catalogs` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/catalogs/{id}/delete` | `POST` | `401` | `403` | `200` | `n/a` | `manage-catalogs` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/catalogs/{id}/items` | `POST` | `401` | `403` | `200` | `n/a` | `manage-catalogs` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/catalogs/{id}/items/{itemId}` | `POST` | `401` | `403` | `200` | `n/a` | `manage-catalogs` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/catalogs/{id}/items/{itemId}/delete` | `POST` | `401` | `403` | `200` | `n/a` | `manage-catalogs` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/catalogs/{id}/transition` | `POST` | `401` | `403` | `200` | `n/a` | `manage-catalogs` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/catalogs/{id}/versions/{versionId}/restore` | `POST` | `401` | `403` | `200` | `n/a` | `manage-catalogs` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |

### framework.demoui

- Scope: `Framework`
- Surface: `authenticated`
- Runtime enabled: `yes`
- Slug: `demoui`
- Description: Authenticated frozen demo baseline surface for the INSPINIA UI reference work.
- Plugin: `standalone`
- Views: `yes`
- Assets: `ok`
- Settings: `n/a`
- Permissions: `n/a`
- Seeds: `n/a`
- Feature flags: `n/a`
- Module flag key: `module.framework.demoui`
- Representative HTML: `/demo-ui`
- Representative JSON: `n/a`

#### HTML routes

| Pattern | Methods | Guest | User | Admin | State Profiles | Permissions | Middleware |
|---|---|---|---|---|---|---|---|
| `/demo-ui` | `GET,HEAD` | `login` | `200` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\AuthMiddleware` |
| `/demo-ui/accordions` | `GET,HEAD` | `login` | `200` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\AuthMiddleware` |
| `/demo-ui/alerts` | `GET,HEAD` | `login` | `200` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\AuthMiddleware` |
| `/demo-ui/badges` | `GET,HEAD` | `login` | `200` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\AuthMiddleware` |
| `/demo-ui/basic-elements` | `GET,HEAD` | `login` | `200` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\AuthMiddleware` |
| `/demo-ui/breadcrumb` | `GET,HEAD` | `login` | `200` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\AuthMiddleware` |
| `/demo-ui/buttons` | `GET,HEAD` | `login` | `200` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\AuthMiddleware` |
| `/demo-ui/cards` | `GET,HEAD` | `login` | `200` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\AuthMiddleware` |
| `/demo-ui/carousel` | `GET,HEAD` | `login` | `200` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\AuthMiddleware` |
| `/demo-ui/charts/{family}/{page}` | `GET,HEAD` | `login` | `200` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\AuthMiddleware` |
| `/demo-ui/collapse` | `GET,HEAD` | `login` | `200` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\AuthMiddleware` |
| `/demo-ui/colors` | `GET,HEAD` | `login` | `200` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\AuthMiddleware` |
| `/demo-ui/dropdowns` | `GET,HEAD` | `login` | `200` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\AuthMiddleware` |
| `/demo-ui/file-uploads` | `GET,HEAD` | `login` | `200` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\AuthMiddleware` |
| `/demo-ui/grid-options` | `GET,HEAD` | `login` | `200` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\AuthMiddleware` |
| `/demo-ui/images` | `GET,HEAD` | `login` | `200` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\AuthMiddleware` |
| `/demo-ui/links` | `GET,HEAD` | `login` | `200` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\AuthMiddleware` |
| `/demo-ui/list-group` | `GET,HEAD` | `login` | `200` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\AuthMiddleware` |
| `/demo-ui/modals` | `GET,HEAD` | `login` | `200` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\AuthMiddleware` |
| `/demo-ui/notifications` | `GET,HEAD` | `login` | `200` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\AuthMiddleware` |
| `/demo-ui/offcanvas` | `GET,HEAD` | `login` | `200` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\AuthMiddleware` |
| `/demo-ui/pagination` | `GET,HEAD` | `login` | `200` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\AuthMiddleware` |
| `/demo-ui/pickers` | `GET,HEAD` | `login` | `200` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\AuthMiddleware` |
| `/demo-ui/placeholders` | `GET,HEAD` | `login` | `200` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\AuthMiddleware` |
| `/demo-ui/popovers` | `GET,HEAD` | `login` | `200` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\AuthMiddleware` |
| `/demo-ui/progress` | `GET,HEAD` | `login` | `200` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\AuthMiddleware` |
| `/demo-ui/range-slider` | `GET,HEAD` | `login` | `200` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\AuthMiddleware` |
| `/demo-ui/scrollspy` | `GET,HEAD` | `login` | `200` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\AuthMiddleware` |
| `/demo-ui/select` | `GET,HEAD` | `login` | `200` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\AuthMiddleware` |
| `/demo-ui/spinners` | `GET,HEAD` | `login` | `200` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\AuthMiddleware` |
| `/demo-ui/tables/datatables/{page}` | `GET,HEAD` | `login` | `200` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\AuthMiddleware` |
| `/demo-ui/tables/{page}` | `GET,HEAD` | `login` | `200` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\AuthMiddleware` |
| `/demo-ui/tabs` | `GET,HEAD` | `login` | `200` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\AuthMiddleware` |
| `/demo-ui/text-editors` | `GET,HEAD` | `login` | `200` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\AuthMiddleware` |
| `/demo-ui/tooltips` | `GET,HEAD` | `login` | `200` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\AuthMiddleware` |
| `/demo-ui/typography` | `GET,HEAD` | `login` | `200` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\AuthMiddleware` |
| `/demo-ui/utilities` | `GET,HEAD` | `login` | `200` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\AuthMiddleware` |
| `/demo-ui/validation` | `GET,HEAD` | `login` | `200` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\AuthMiddleware` |
| `/demo-ui/videos` | `GET,HEAD` | `login` | `200` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\AuthMiddleware` |
| `/demo-ui/wizard` | `GET,HEAD` | `login` | `200` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\AuthMiddleware` |

#### JSON routes

_No JSON routes declared for harness._

#### Mutations

_No mutation routes declared for harness._

### framework.devtools

- Scope: `Framework`
- Surface: `devtools`
- Runtime enabled: `yes`
- Slug: `devtools`
- Description: Developer tooling, UML and runtime smoke surfaces.
- Plugin: `framework.devtools`
- Views: `yes`
- Assets: `ok`
- Settings: `n/a`
- Permissions: `access-devtools`
- Seeds: `n/a`
- Feature flags: `project_debug`
- Module flag key: `module.framework.devtools`
- Representative HTML: `/test-layout`
- Representative JSON: `/test-features/api/toaster-success`

#### HTML routes

| Pattern | Methods | Guest | User | Admin | State Profiles | Permissions | Middleware |
|---|---|---|---|---|---|---|---|
| `/test-features` | `GET,HEAD` | `login` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\DevToolsGuardMiddleware` |
| `/test-features/api-response` | `GET,HEAD` | `login` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\DevToolsGuardMiddleware` |
| `/test-features/cors-headers` | `GET,HEAD` | `login` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\DevToolsGuardMiddleware` |
| `/test-features/db-connection` | `GET,HEAD` | `login` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\DevToolsGuardMiddleware` |
| `/test-features/e-helper` | `GET,HEAD` | `login` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\DevToolsGuardMiddleware` |
| `/test-features/flash/clear` | `GET,HEAD` | `login` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\DevToolsGuardMiddleware` |
| `/test-features/flash/{type}` | `GET,HEAD` | `login` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\DevToolsGuardMiddleware` |
| `/test-features/flash/{type}/persistent` | `GET,HEAD` | `login` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\DevToolsGuardMiddleware` |
| `/test-features/i18n` | `GET,HEAD` | `login` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\DevToolsGuardMiddleware` |
| `/test-features/infra` | `GET,HEAD` | `login` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\DevToolsGuardMiddleware` |
| `/test-features/json` | `GET,HEAD` | `login` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\DevToolsGuardMiddleware` |
| `/test-features/json-error` | `GET,HEAD` | `login` | `403` | `400` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\DevToolsGuardMiddleware` |
| `/test-features/json-success` | `GET,HEAD` | `login` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\DevToolsGuardMiddleware` |
| `/test-features/layout-test` | `GET,HEAD` | `login` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\DevToolsGuardMiddleware` |
| `/test-features/logger-email` | `GET,HEAD` | `login` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\DevToolsGuardMiddleware` |
| `/test-features/modal/form-content` | `GET,HEAD` | `login` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\DevToolsGuardMiddleware` |
| `/test-features/modal/sample-content` | `GET,HEAD` | `login` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\DevToolsGuardMiddleware` |
| `/test-features/orm/find-or-fail` | `GET,HEAD` | `login` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\DevToolsGuardMiddleware` |
| `/test-features/orm/status` | `GET,HEAD` | `login` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\DevToolsGuardMiddleware` |
| `/test-features/orm/user-demo` | `GET,HEAD` | `login` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\DevToolsGuardMiddleware` |
| `/test-features/rbac-status` | `GET,HEAD` | `login` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\DevToolsGuardMiddleware` |
| `/test-features/route-cache` | `GET,HEAD` | `login` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\DevToolsGuardMiddleware` |
| `/test-features/ui-showcase` | `GET,HEAD` | `login` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\DevToolsGuardMiddleware` |
| `/test-features/validation-error` | `GET,HEAD` | `login` | `403` | `422` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\DevToolsGuardMiddleware` |
| `/test-layout` | `GET,HEAD` | `login` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\DevToolsGuardMiddleware` |
| `/uml` | `GET,HEAD` | `login` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\DevToolsGuardMiddleware` |

#### JSON routes

| Pattern | Methods | Guest | User | Admin | State Profiles | Permissions | Middleware |
|---|---|---|---|---|---|---|---|
| `/test-features/api/js-enhancements/partial-refresh` | `GET,HEAD` | `401` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\DevToolsGuardMiddleware` |
| `/test-features/api/modal-trigger` | `GET,HEAD` | `401` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\DevToolsGuardMiddleware` |
| `/test-features/api/multiple-toasters` | `GET,HEAD` | `401` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\DevToolsGuardMiddleware` |
| `/test-features/api/toaster-error` | `GET,HEAD` | `401` | `403` | `400` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\DevToolsGuardMiddleware` |
| `/test-features/api/toaster-info` | `GET,HEAD` | `401` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\DevToolsGuardMiddleware` |
| `/test-features/api/toaster-success` | `GET,HEAD` | `401` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\DevToolsGuardMiddleware` |
| `/test-features/api/toaster-warning` | `GET,HEAD` | `401` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\DevToolsGuardMiddleware` |

#### Mutations

| Pattern | Methods | Guest | User | Admin | State Profiles | Permissions | Middleware |
|---|---|---|---|---|---|---|---|
| `/test-features/api/validator-test` | `POST` | `401` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\DevToolsGuardMiddleware` |
| `/test-features/api/validator-unique` | `POST` | `401` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\DevToolsGuardMiddleware` |
| `/test-features/db-reset` | `POST` | `401` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\DevToolsGuardMiddleware` |
| `/test-features/form-demo` | `POST` | `401` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\DevToolsGuardMiddleware` |
| `/test-features/i18n/set-locale` | `POST` | `401` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\DevToolsGuardMiddleware` |
| `/test-features/mail-test` | `POST` | `401` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\DevToolsGuardMiddleware` |
| `/test-features/make-admin` | `POST` | `401` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\DevToolsGuardMiddleware` |
| `/test-features/modal/form-submit` | `POST` | `401` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\DevToolsGuardMiddleware` |
| `/test-features/orm/create` | `POST` | `401` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\DevToolsGuardMiddleware` |
| `/test-features/orm/delete-latest` | `POST` | `401` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\DevToolsGuardMiddleware` |
| `/test-features/orm/update` | `POST` | `401` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\DevToolsGuardMiddleware` |
| `/test-features/upload` | `POST` | `401` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\DevToolsGuardMiddleware` |

### framework.documents

- Scope: `Framework`
- Surface: `authenticated`
- Runtime enabled: `yes`
- Slug: `documents`
- Description: Reusable document templates with workflow, preview, exports and persisted version history.
- Plugin: `framework.business`
- Views: `yes`
- Assets: `ok`
- Settings: `n/a`
- Permissions: `manage-document-templates`
- Seeds: `n/a`
- Feature flags: `n/a`
- Module flag key: `module.framework.documents`
- Representative HTML: `/workspaces/document-templates`
- Representative JSON: `/api/v1/document-templates`

#### HTML routes

| Pattern | Methods | Guest | User | Admin | State Profiles | Permissions | Middleware |
|---|---|---|---|---|---|---|---|
| `/workspaces/document-templates` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-document-templates` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/document-templates/create` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-document-templates` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/document-templates/{id}` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-document-templates` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/document-templates/{id}/edit` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-document-templates` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |

#### JSON routes

| Pattern | Methods | Guest | User | Admin | State Profiles | Permissions | Middleware |
|---|---|---|---|---|---|---|---|
| `/api/v1/document-templates` | `GET,HEAD` | `401` | `401` | `401` | `api_token=200` | `n/a` | `Catalyst\Framework\Middleware\ApiTokenMiddleware` |
| `/api/v1/document-templates/{id}` | `GET,HEAD` | `401` | `401` | `401` | `api_token=200` | `n/a` | `Catalyst\Framework\Middleware\ApiTokenMiddleware` |

#### Mutations

| Pattern | Methods | Guest | User | Admin | State Profiles | Permissions | Middleware |
|---|---|---|---|---|---|---|---|
| `/api/v1/document-templates/{id}/export` | `POST` | `401` | `401` | `401` | `api_token=200` | `n/a` | `Catalyst\Framework\Middleware\ApiTokenMiddleware` |
| `/api/v1/document-templates/{id}/preview` | `POST` | `401` | `401` | `401` | `api_token=200` | `n/a` | `Catalyst\Framework\Middleware\ApiTokenMiddleware` |
| `/workspaces/document-templates` | `POST` | `401` | `403` | `200` | `n/a` | `manage-document-templates` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/document-templates/{id}` | `POST` | `401` | `403` | `200` | `n/a` | `manage-document-templates` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/document-templates/{id}/delete` | `POST` | `401` | `403` | `200` | `n/a` | `manage-document-templates` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/document-templates/{id}/export` | `POST` | `401` | `403` | `200` | `n/a` | `manage-document-templates` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/document-templates/{id}/preview` | `POST` | `401` | `403` | `200` | `n/a` | `manage-document-templates` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/document-templates/{id}/transition` | `POST` | `401` | `403` | `200` | `n/a` | `manage-document-templates` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/document-templates/{id}/versions/{versionId}/restore` | `POST` | `401` | `403` | `200` | `n/a` | `manage-document-templates` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |

### framework.media

- Scope: `Framework`
- Surface: `authenticated`
- Runtime enabled: `yes`
- Slug: `media`
- Description: Reusable media library plus dynamic metadata field definitions for business entities.
- Plugin: `framework.business`
- Views: `yes`
- Assets: `ok`
- Settings: `n/a`
- Permissions: `manage-media-library`, `manage-media-metadata`
- Seeds: `n/a`
- Feature flags: `n/a`
- Module flag key: `module.framework.media`
- Representative HTML: `/workspaces/media-library`
- Representative JSON: `n/a`

#### HTML routes

| Pattern | Methods | Guest | User | Admin | State Profiles | Permissions | Middleware |
|---|---|---|---|---|---|---|---|
| `/workspaces/media-fields` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-media-metadata` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/media-fields/create` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-media-metadata` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/media-fields/{id}/edit` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-media-metadata` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/media-library` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-media-library` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/media-library/upload` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-media-library` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/media-library/{id}/edit` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-media-library` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |

#### JSON routes

_No JSON routes declared for harness._

#### Mutations

| Pattern | Methods | Guest | User | Admin | State Profiles | Permissions | Middleware |
|---|---|---|---|---|---|---|---|
| `/workspaces/media-fields` | `POST` | `401` | `403` | `200` | `n/a` | `manage-media-metadata` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/media-fields/{id}` | `POST` | `401` | `403` | `200` | `n/a` | `manage-media-metadata` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/media-fields/{id}/delete` | `POST` | `401` | `403` | `200` | `n/a` | `manage-media-metadata` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/media-library` | `POST` | `401` | `403` | `200` | `n/a` | `manage-media-library` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/media-library/bulk-delete` | `POST` | `401` | `403` | `200` | `n/a` | `manage-media-library` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/media-library/{id}` | `POST` | `401` | `403` | `200` | `n/a` | `manage-media-library` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/media-library/{id}/delete` | `POST` | `401` | `403` | `200` | `n/a` | `manage-media-library` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |

### framework.notification

- Scope: `Framework`
- Surface: `authenticated-api`
- Runtime enabled: `yes`
- Slug: `notification`
- Description: Authenticated notification APIs and websocket token issuance.
- Plugin: `framework.core`
- Views: `no`
- Assets: `n/a`
- Settings: `n/a`
- Permissions: `n/a`
- Seeds: `n/a`
- Feature flags: `websocket_enabled`, `notifications`
- Module flag key: `module.framework.notification`
- Representative HTML: `n/a`
- Representative JSON: `/api/ws-token`

#### HTML routes

_No HTML routes declared for harness._

#### JSON routes

| Pattern | Methods | Guest | User | Admin | State Profiles | Permissions | Middleware |
|---|---|---|---|---|---|---|---|
| `/api/notifications` | `GET,HEAD` | `401` | `200` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\AuthMiddleware` |
| `/api/notifications/unread-count` | `GET,HEAD` | `401` | `200` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\AuthMiddleware` |
| `/api/ws-token` | `GET,HEAD` | `401` | `200` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\AuthMiddleware` |

#### Mutations

| Pattern | Methods | Guest | User | Admin | State Profiles | Permissions | Middleware |
|---|---|---|---|---|---|---|---|
| `/api/notifications/read-all` | `POST` | `401` | `200` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\AuthMiddleware` |
| `/api/notifications/{id}/read` | `POST` | `401` | `200` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\AuthMiddleware` |
| `/api/presence/{resourceKey}/{recordId}/heartbeat` | `POST` | `401` | `200` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\AuthMiddleware` |

### framework.operations

- Scope: `Framework`
- Surface: `authenticated`
- Runtime enabled: `yes`
- Slug: `operations`
- Description: Platform operations console for feature flags, plugins, deployment, localization, appearance and tenancy.
- Plugin: `framework.core`
- Views: `yes`
- Assets: `ok`
- Settings: `n/a`
- Permissions: `manage-platform-operations`
- Seeds: `n/a`
- Feature flags: `module.framework.operations`
- Module flag key: `module.framework.operations`
- Representative HTML: `/operations`
- Representative JSON: `n/a`

#### HTML routes

| Pattern | Methods | Guest | User | Admin | State Profiles | Permissions | Middleware |
|---|---|---|---|---|---|---|---|
| `/configuration/feature-flags` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-platform-operations` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/configuration/platform-appearance` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-platform-operations` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/configuration/plugins` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-platform-operations` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/operations` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-platform-operations` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/operations/deployments` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-platform-operations` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/operations/tenancy` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-platform-operations` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/locale-tools` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-platform-operations` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/module-designer` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-platform-operations` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/module-designer/generate` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-platform-operations` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/module-designer/preview` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-platform-operations` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |

#### JSON routes

_No JSON routes declared for harness._

#### Mutations

| Pattern | Methods | Guest | User | Admin | State Profiles | Permissions | Middleware |
|---|---|---|---|---|---|---|---|
| `/configuration/feature-flags/defaults/{flagKey}` | `POST` | `401` | `403` | `200` | `n/a` | `manage-platform-operations` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/configuration/feature-flags/overrides` | `POST` | `401` | `403` | `200` | `n/a` | `manage-platform-operations` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/configuration/feature-flags/overrides/{id}/delete` | `POST` | `401` | `403` | `200` | `n/a` | `manage-platform-operations` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/configuration/platform-appearance` | `POST` | `401` | `403` | `200` | `n/a` | `manage-platform-operations` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/configuration/plugins/{pluginKey}/toggle` | `POST` | `401` | `403` | `200` | `n/a` | `manage-platform-operations` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/operations/deployments/runs` | `POST` | `401` | `403` | `200` | `n/a` | `manage-platform-operations` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/locale-tools/create-locale` | `POST` | `401` | `403` | `200` | `n/a` | `manage-platform-operations` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/locale-tools/settings` | `POST` | `401` | `403` | `200` | `n/a` | `manage-platform-operations` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/locale-tools/sync-locale` | `POST` | `401` | `403` | `200` | `n/a` | `manage-platform-operations` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/module-designer/generate` | `POST` | `401` | `403` | `200` | `n/a` | `manage-platform-operations` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/module-designer/preview` | `POST` | `401` | `403` | `200` | `n/a` | `manage-platform-operations` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |

### framework.roles

- Scope: `Framework`
- Surface: `administration`
- Runtime enabled: `yes`
- Slug: `roles`
- Description: RBAC administration, users, roles and permissions.
- Plugin: `framework.core`
- Views: `yes`
- Assets: `ok`
- Settings: `n/a`
- Permissions: `manage-roles`, `manage-users`
- Seeds: `n/a`
- Feature flags: `n/a`
- Module flag key: `module.framework.roles`
- Representative HTML: `/users`
- Representative JSON: `n/a`

#### HTML routes

| Pattern | Methods | Guest | User | Admin | State Profiles | Permissions | Middleware |
|---|---|---|---|---|---|---|---|
| `/users` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-users` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/users/enroll` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-users` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/users/organization-hierarchy` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-roles` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/users/permissions` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-roles` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/users/permissions/create` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-roles` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/users/permissions/{id}/edit` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-roles` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/users/roles` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-roles` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/users/roles/create` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-roles` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/users/roles/{id}/edit` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-roles` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/users/roles/{id}/permissions` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-roles` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/users/{userId}/roles` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-users` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |

#### JSON routes

_No JSON routes declared for harness._

#### Mutations

| Pattern | Methods | Guest | User | Admin | State Profiles | Permissions | Middleware |
|---|---|---|---|---|---|---|---|
| `/users/enroll` | `POST` | `401` | `403` | `200` | `n/a` | `manage-users` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/users/organization-hierarchy/levels` | `POST` | `401` | `403` | `200` | `n/a` | `manage-roles` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/users/organization-hierarchy/organizations` | `POST` | `401` | `403` | `200` | `n/a` | `manage-roles` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/users/organization-hierarchy/scopes` | `POST` | `401` | `403` | `200` | `n/a` | `manage-roles` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/users/organization-hierarchy/units` | `POST` | `401` | `403` | `200` | `n/a` | `manage-roles` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/users/permissions` | `POST` | `401` | `403` | `200` | `n/a` | `manage-roles` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/users/permissions/bulk-delete` | `POST` | `401` | `403` | `200` | `n/a` | `manage-roles` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/users/permissions/{id}` | `POST` | `401` | `403` | `200` | `n/a` | `manage-roles` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/users/permissions/{id}/delete` | `POST` | `401` | `403` | `200` | `n/a` | `manage-roles` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/users/roles` | `POST` | `401` | `403` | `200` | `n/a` | `manage-roles` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/users/roles/bulk-delete` | `POST` | `401` | `403` | `200` | `n/a` | `manage-roles` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/users/roles/{id}` | `POST` | `401` | `403` | `200` | `n/a` | `manage-roles` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/users/roles/{id}/delete` | `POST` | `401` | `403` | `200` | `n/a` | `manage-roles` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/users/roles/{id}/permissions` | `POST` | `401` | `403` | `200` | `n/a` | `manage-roles` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/users/{userId}/roles/{roleId}` | `POST` | `401` | `403` | `200` | `n/a` | `manage-users` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/users/{userId}/roles/{roleId}/remove` | `POST` | `401` | `403` | `200` | `n/a` | `manage-users` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |

### framework.settings

- Scope: `Framework`
- Surface: `workspace`
- Runtime enabled: `yes`
- Slug: `settings`
- Description: Setup, configuration and health surfaces.
- Plugin: `framework.core`
- Views: `yes`
- Assets: `ok`
- Settings: `n/a`
- Permissions: `n/a`
- Seeds: `n/a`
- Feature flags: `n/a`
- Module flag key: `module.framework.settings`
- Representative HTML: `/configuration/environment-setup`
- Representative JSON: `n/a`

#### HTML routes

| Pattern | Methods | Guest | User | Admin | State Profiles | Permissions | Middleware |
|---|---|---|---|---|---|---|---|
| `/configuration/application-health` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/configuration/application-health/live` | `GET,HEAD` | `200` | `200` | `200` | `n/a` | `n/a` | `n/a` |
| `/configuration/application-health/ready` | `GET,HEAD` | `200` | `200` | `200` | `n/a` | `n/a` | `n/a` |
| `/configuration/environment-setup` | `GET,HEAD` | `login` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\SetupGuardMiddleware` |

#### JSON routes

_No JSON routes declared for harness._

#### Mutations

| Pattern | Methods | Guest | User | Admin | State Profiles | Permissions | Middleware |
|---|---|---|---|---|---|---|---|
| `/configuration/environment-setup/admin` | `POST` | `401` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\SetupGuardMiddleware` |
| `/configuration/environment-setup/app` | `POST` | `401` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\SetupGuardMiddleware` |
| `/configuration/environment-setup/cache` | `POST` | `401` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\SetupGuardMiddleware` |
| `/configuration/environment-setup/complete` | `POST` | `401` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\SetupGuardMiddleware` |
| `/configuration/environment-setup/cors` | `POST` | `401` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\SetupGuardMiddleware` |
| `/configuration/environment-setup/db` | `POST` | `401` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\SetupGuardMiddleware` |
| `/configuration/environment-setup/devtools` | `POST` | `401` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\SetupGuardMiddleware` |
| `/configuration/environment-setup/dkim/generate` | `POST` | `401` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\SetupGuardMiddleware` |
| `/configuration/environment-setup/features` | `POST` | `401` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\SetupGuardMiddleware` |
| `/configuration/environment-setup/ftp` | `POST` | `401` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\SetupGuardMiddleware` |
| `/configuration/environment-setup/ftp/pretest` | `POST` | `401` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\SetupGuardMiddleware` |
| `/configuration/environment-setup/logging` | `POST` | `401` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\SetupGuardMiddleware` |
| `/configuration/environment-setup/mail` | `POST` | `401` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\SetupGuardMiddleware` |
| `/configuration/environment-setup/reset` | `POST` | `401` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\SetupGuardMiddleware` |
| `/configuration/environment-setup/security` | `POST` | `401` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\SetupGuardMiddleware` |
| `/configuration/environment-setup/session` | `POST` | `401` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\SetupGuardMiddleware` |
| `/configuration/environment-setup/websocket` | `POST` | `401` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\SetupGuardMiddleware` |

