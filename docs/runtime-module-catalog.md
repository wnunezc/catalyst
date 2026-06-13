# Runtime Module Catalog

> Auto-generated from `ModuleRegistry`, `PermissionRegistry`, `NavigationRegistry`, `ModuleInspector`, `ModuleHarnessInspector` and `ModuleLinter`.
> Last generated: 2026-06-13 19:00:40

## Runtime Summary

- Modules: 14
- Structural lint: OK

| Key | Surface | HTML | JSON | Mutations | Assets | Permissions | Settings | Seeds |
|---|---|---:|---:|---:|---|---|---|---|
| `app.surface.dashboard` | `public` | 1 | 0 | 0 | `ok` | `n/a` | `n/a` | `n/a` |
| `app.surface.home` | `public` | 2 | 0 | 0 | `ok` | `n/a` | `n/a` | `n/a` |
| `app.surface.landing` | `public` | 1 | 0 | 0 | `ok` | `n/a` | `n/a` | `n/a` |
| `app.surface.store` | `public` | 1 | 0 | 0 | `ok` | `n/a` | `n/a` | `n/a` |
| `framework.account` | `authenticated` | 13 | 0 | 6 | `ok` | `n/a` | `n/a` | `n/a` |
| `framework.api` | `authenticated-api` | 0 | 4 | 2 | `n/a` | `n/a` | `n/a` | `n/a` |
| `framework.auth` | `auth-flow` | 10 | 0 | 9 | `ok` | `n/a` | `n/a` | `n/a` |
| `framework.configuration` | `workspace` | 7 | 0 | 22 | `ok` | `manage-platform-configuration` | `n/a` | `n/a` |
| `framework.demoui` | `devtools` | 40 | 0 | 0 | `ok` | `n/a` | `n/a` | `n/a` |
| `framework.devtools` | `devtools` | 24 | 7 | 12 | `ok` | `access-devtools` | `n/a` | `n/a` |
| `framework.notification` | `authenticated-api` | 0 | 3 | 3 | `n/a` | `n/a` | `n/a` | `n/a` |
| `framework.operations` | `authenticated` | 9 | 2 | 10 | `n/a` | `manage-operations-api-management`, `manage-operations-audit-log`, `manage-operations-automation-rules`, `manage-operations-deployments`, `manage-operations-tenancy` | `n/a` | `n/a` |
| `framework.users` | `privileged` | 13 | 0 | 18 | `ok` | `manage-account-recovery`, `manage-roles`, `manage-users` | `n/a` | `n/a` |
| `framework.workspaces` | `authenticated` | 18 | 2 | 29 | `ok` | `manage-workspaces-catalogs`, `manage-workspaces-document-templates`, `manage-workspaces-localization`, `manage-workspaces-media-fields`, `manage-workspaces-media-library`, `manage-workspaces-module-designer` | `n/a` | `n/a` |

## Module Detail

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
- Representative JSON: `n/a`

#### HTML routes

| Pattern | Methods | Guest | User | Privileged | State Profiles | Permissions | Middleware |
|---|---|---|---|---|---|---|---|
| `/dashboard` | `GET,HEAD` | `200` | `200` | `200` | `n/a` | `n/a` | `n/a` |

#### JSON routes

_No JSON routes declared for harness._

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
- Representative JSON: `n/a`

#### HTML routes

| Pattern | Methods | Guest | User | Privileged | State Profiles | Permissions | Middleware |
|---|---|---|---|---|---|---|---|
| `/` | `GET,HEAD` | `200` | `200` | `200` | `n/a` | `n/a` | `n/a` |
| `/home` | `GET,HEAD` | `200` | `200` | `200` | `n/a` | `n/a` | `n/a` |

#### JSON routes

_No JSON routes declared for harness._

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
- Representative JSON: `n/a`

#### HTML routes

| Pattern | Methods | Guest | User | Privileged | State Profiles | Permissions | Middleware |
|---|---|---|---|---|---|---|---|
| `/landing` | `GET,HEAD` | `200` | `200` | `200` | `n/a` | `n/a` | `n/a` |

#### JSON routes

_No JSON routes declared for harness._

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
- Representative JSON: `n/a`

#### HTML routes

| Pattern | Methods | Guest | User | Privileged | State Profiles | Permissions | Middleware |
|---|---|---|---|---|---|---|---|
| `/store` | `GET,HEAD` | `200` | `200` | `200` | `n/a` | `n/a` | `n/a` |

#### JSON routes

_No JSON routes declared for harness._

#### Mutations

_No mutation routes declared for harness._

### framework.account

- Scope: `Framework`
- Surface: `authenticated`
- Runtime enabled: `yes`
- Slug: `account`
- Description: Personal user account center, account security and assisted recovery flows.
- Plugin: `framework.core`
- Views: `yes`
- Assets: `ok`
- Settings: `n/a`
- Permissions: `n/a`
- Seeds: `n/a`
- Feature flags: `n/a`
- Module flag key: `module.framework.account`
- Representative HTML: `/account/profile`
- Representative JSON: `n/a`

#### HTML routes

| Pattern | Methods | Guest | User | Privileged | State Profiles | Permissions | Middleware |
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

#### JSON routes

_No JSON routes declared for harness._

#### Mutations

| Pattern | Methods | Guest | User | Privileged | State Profiles | Permissions | Middleware |
|---|---|---|---|---|---|---|---|
| `/account-recovery/compromised` | `POST` | `200` | `200` | `200` | `n/a` | `n/a` | `n/a` |
| `/account-recovery/mfa` | `POST` | `200` | `200` | `200` | `n/a` | `n/a` | `n/a` |
| `/account-recovery/support` | `POST` | `200` | `200` | `200` | `n/a` | `n/a` | `n/a` |
| `/account/recovery/compromised` | `POST` | `401` | `200` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\AuthMiddleware` |
| `/account/recovery/mfa` | `POST` | `401` | `200` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\AuthMiddleware` |
| `/account/recovery/support` | `POST` | `401` | `200` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\AuthMiddleware` |

### framework.api

- Scope: `Framework`
- Surface: `authenticated-api`
- Runtime enabled: `yes`
- Slug: `api`
- Description: Versioned transversal framework API.
- Plugin: `framework.core`
- Views: `no`
- Assets: `n/a`
- Settings: `n/a`
- Permissions: `n/a`
- Seeds: `n/a`
- Feature flags: `n/a`
- Module flag key: `module.framework.api`
- Representative HTML: `n/a`
- Representative JSON: `/api/v1/catalog`

#### HTML routes

_No HTML routes declared for harness._

#### JSON routes

| Pattern | Methods | Guest | User | Privileged | State Profiles | Permissions | Middleware |
|---|---|---|---|---|---|---|---|
| `/api/v1/calendar/events` | `GET,HEAD` | `401` | `401` | `401` | `api_token=200` | `n/a` | `Catalyst\Framework\Middleware\ApiTokenMiddleware` |
| `/api/v1/catalog` | `GET,HEAD` | `401` | `401` | `401` | `api_token=200` | `n/a` | `Catalyst\Framework\Middleware\ApiTokenMiddleware` |
| `/api/v1/versions/{resourceKey}/{recordId}` | `GET,HEAD` | `401` | `401` | `401` | `api_token=200` | `n/a` | `Catalyst\Framework\Middleware\ApiTokenMiddleware` |
| `/api/v1/workflows` | `GET,HEAD` | `401` | `401` | `401` | `api_token=200` | `n/a` | `Catalyst\Framework\Middleware\ApiTokenMiddleware` |

#### Mutations

| Pattern | Methods | Guest | User | Privileged | State Profiles | Permissions | Middleware |
|---|---|---|---|---|---|---|---|
| `/api/v1/versions/{id}/restore` | `POST` | `401` | `401` | `401` | `api_token=200` | `n/a` | `Catalyst\Framework\Middleware\ApiTokenMiddleware` |
| `/api/v1/workflows/{id}/transition` | `POST` | `401` | `401` | `401` | `api_token=200` | `n/a` | `Catalyst\Framework\Middleware\ApiTokenMiddleware` |

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

| Pattern | Methods | Guest | User | Privileged | State Profiles | Permissions | Middleware |
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

| Pattern | Methods | Guest | User | Privileged | State Profiles | Permissions | Middleware |
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

### framework.configuration

- Scope: `Framework`
- Surface: `workspace`
- Runtime enabled: `yes`
- Slug: `configuration`
- Description: Setup, configuration and health surfaces.
- Plugin: `framework.core`
- Views: `yes`
- Assets: `ok`
- Settings: `n/a`
- Permissions: `manage-platform-configuration`
- Seeds: `n/a`
- Feature flags: `n/a`
- Module flag key: `module.framework.configuration`
- Representative HTML: `/configuration/environment-setup`
- Representative JSON: `n/a`

#### HTML routes

| Pattern | Methods | Guest | User | Privileged | State Profiles | Permissions | Middleware |
|---|---|---|---|---|---|---|---|
| `/configuration/application-health` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-platform-configuration` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/configuration/application-health/live` | `GET,HEAD` | `200` | `200` | `200` | `n/a` | `n/a` | `n/a` |
| `/configuration/application-health/ready` | `GET,HEAD` | `200` | `200` | `200` | `n/a` | `n/a` | `n/a` |
| `/configuration/environment-setup` | `GET,HEAD` | `login` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\SetupGuardMiddleware` |
| `/configuration/feature-flags` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-platform-configuration` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/configuration/platform-appearance` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-platform-configuration` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/configuration/plugins` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-platform-configuration` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |

#### JSON routes

_No JSON routes declared for harness._

#### Mutations

| Pattern | Methods | Guest | User | Privileged | State Profiles | Permissions | Middleware |
|---|---|---|---|---|---|---|---|
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
| `/configuration/environment-setup/privileged-account-account` | `POST` | `401` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\SetupGuardMiddleware` |
| `/configuration/environment-setup/reset` | `POST` | `401` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\SetupGuardMiddleware` |
| `/configuration/environment-setup/security` | `POST` | `401` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\SetupGuardMiddleware` |
| `/configuration/environment-setup/session` | `POST` | `401` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\SetupGuardMiddleware` |
| `/configuration/environment-setup/websocket` | `POST` | `401` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\SetupGuardMiddleware` |
| `/configuration/feature-flags/defaults/{flagKey}` | `POST` | `401` | `403` | `200` | `n/a` | `manage-platform-configuration` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/configuration/feature-flags/overrides` | `POST` | `401` | `403` | `200` | `n/a` | `manage-platform-configuration` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/configuration/feature-flags/overrides/{id}/delete` | `POST` | `401` | `403` | `200` | `n/a` | `manage-platform-configuration` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/configuration/platform-appearance` | `POST` | `401` | `403` | `200` | `n/a` | `manage-platform-configuration` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/configuration/plugins/{pluginKey}/toggle` | `POST` | `401` | `403` | `200` | `n/a` | `manage-platform-configuration` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |

### framework.demoui

- Scope: `Framework`
- Surface: `devtools`
- Runtime enabled: `yes`
- Slug: `demoui`
- Description: Public frozen demo baseline surface for the INSPINIA UI reference work.
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

| Pattern | Methods | Guest | User | Privileged | State Profiles | Permissions | Middleware |
|---|---|---|---|---|---|---|---|
| `/demo-ui` | `GET,HEAD` | `200` | `200` | `200` | `n/a` | `n/a` | `n/a` |
| `/demo-ui/accordions` | `GET,HEAD` | `200` | `200` | `200` | `n/a` | `n/a` | `n/a` |
| `/demo-ui/alerts` | `GET,HEAD` | `200` | `200` | `200` | `n/a` | `n/a` | `n/a` |
| `/demo-ui/badges` | `GET,HEAD` | `200` | `200` | `200` | `n/a` | `n/a` | `n/a` |
| `/demo-ui/basic-elements` | `GET,HEAD` | `200` | `200` | `200` | `n/a` | `n/a` | `n/a` |
| `/demo-ui/breadcrumb` | `GET,HEAD` | `200` | `200` | `200` | `n/a` | `n/a` | `n/a` |
| `/demo-ui/buttons` | `GET,HEAD` | `200` | `200` | `200` | `n/a` | `n/a` | `n/a` |
| `/demo-ui/cards` | `GET,HEAD` | `200` | `200` | `200` | `n/a` | `n/a` | `n/a` |
| `/demo-ui/carousel` | `GET,HEAD` | `200` | `200` | `200` | `n/a` | `n/a` | `n/a` |
| `/demo-ui/charts/{family}/{page}` | `GET,HEAD` | `200` | `200` | `200` | `n/a` | `n/a` | `n/a` |
| `/demo-ui/collapse` | `GET,HEAD` | `200` | `200` | `200` | `n/a` | `n/a` | `n/a` |
| `/demo-ui/colors` | `GET,HEAD` | `200` | `200` | `200` | `n/a` | `n/a` | `n/a` |
| `/demo-ui/dropdowns` | `GET,HEAD` | `200` | `200` | `200` | `n/a` | `n/a` | `n/a` |
| `/demo-ui/file-uploads` | `GET,HEAD` | `200` | `200` | `200` | `n/a` | `n/a` | `n/a` |
| `/demo-ui/grid-options` | `GET,HEAD` | `200` | `200` | `200` | `n/a` | `n/a` | `n/a` |
| `/demo-ui/images` | `GET,HEAD` | `200` | `200` | `200` | `n/a` | `n/a` | `n/a` |
| `/demo-ui/links` | `GET,HEAD` | `200` | `200` | `200` | `n/a` | `n/a` | `n/a` |
| `/demo-ui/list-group` | `GET,HEAD` | `200` | `200` | `200` | `n/a` | `n/a` | `n/a` |
| `/demo-ui/modals` | `GET,HEAD` | `200` | `200` | `200` | `n/a` | `n/a` | `n/a` |
| `/demo-ui/notifications` | `GET,HEAD` | `200` | `200` | `200` | `n/a` | `n/a` | `n/a` |
| `/demo-ui/offcanvas` | `GET,HEAD` | `200` | `200` | `200` | `n/a` | `n/a` | `n/a` |
| `/demo-ui/pagination` | `GET,HEAD` | `200` | `200` | `200` | `n/a` | `n/a` | `n/a` |
| `/demo-ui/pickers` | `GET,HEAD` | `200` | `200` | `200` | `n/a` | `n/a` | `n/a` |
| `/demo-ui/placeholders` | `GET,HEAD` | `200` | `200` | `200` | `n/a` | `n/a` | `n/a` |
| `/demo-ui/popovers` | `GET,HEAD` | `200` | `200` | `200` | `n/a` | `n/a` | `n/a` |
| `/demo-ui/progress` | `GET,HEAD` | `200` | `200` | `200` | `n/a` | `n/a` | `n/a` |
| `/demo-ui/range-slider` | `GET,HEAD` | `200` | `200` | `200` | `n/a` | `n/a` | `n/a` |
| `/demo-ui/scrollspy` | `GET,HEAD` | `200` | `200` | `200` | `n/a` | `n/a` | `n/a` |
| `/demo-ui/select` | `GET,HEAD` | `200` | `200` | `200` | `n/a` | `n/a` | `n/a` |
| `/demo-ui/spinners` | `GET,HEAD` | `200` | `200` | `200` | `n/a` | `n/a` | `n/a` |
| `/demo-ui/tables/datatables/{page}` | `GET,HEAD` | `200` | `200` | `200` | `n/a` | `n/a` | `n/a` |
| `/demo-ui/tables/{page}` | `GET,HEAD` | `200` | `200` | `200` | `n/a` | `n/a` | `n/a` |
| `/demo-ui/tabs` | `GET,HEAD` | `200` | `200` | `200` | `n/a` | `n/a` | `n/a` |
| `/demo-ui/text-editors` | `GET,HEAD` | `200` | `200` | `200` | `n/a` | `n/a` | `n/a` |
| `/demo-ui/tooltips` | `GET,HEAD` | `200` | `200` | `200` | `n/a` | `n/a` | `n/a` |
| `/demo-ui/typography` | `GET,HEAD` | `200` | `200` | `200` | `n/a` | `n/a` | `n/a` |
| `/demo-ui/utilities` | `GET,HEAD` | `200` | `200` | `200` | `n/a` | `n/a` | `n/a` |
| `/demo-ui/validation` | `GET,HEAD` | `200` | `200` | `200` | `n/a` | `n/a` | `n/a` |
| `/demo-ui/videos` | `GET,HEAD` | `200` | `200` | `200` | `n/a` | `n/a` | `n/a` |
| `/demo-ui/wizard` | `GET,HEAD` | `200` | `200` | `200` | `n/a` | `n/a` | `n/a` |

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
- Representative HTML: `/test-features`
- Representative JSON: `/test-features/api/toaster-success`

#### HTML routes

| Pattern | Methods | Guest | User | Privileged | State Profiles | Permissions | Middleware |
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
| `/test-features/validation-error` | `GET,HEAD` | `login` | `403` | `422` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\DevToolsGuardMiddleware` |
| `/uml` | `GET,HEAD` | `login` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\DevToolsGuardMiddleware` |

#### JSON routes

| Pattern | Methods | Guest | User | Privileged | State Profiles | Permissions | Middleware |
|---|---|---|---|---|---|---|---|
| `/test-features/api/js-enhancements/partial-refresh` | `GET,HEAD` | `401` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\DevToolsGuardMiddleware` |
| `/test-features/api/modal-trigger` | `GET,HEAD` | `401` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\DevToolsGuardMiddleware` |
| `/test-features/api/multiple-toasters` | `GET,HEAD` | `401` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\DevToolsGuardMiddleware` |
| `/test-features/api/toaster-error` | `GET,HEAD` | `401` | `403` | `400` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\DevToolsGuardMiddleware` |
| `/test-features/api/toaster-info` | `GET,HEAD` | `401` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\DevToolsGuardMiddleware` |
| `/test-features/api/toaster-success` | `GET,HEAD` | `401` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\DevToolsGuardMiddleware` |
| `/test-features/api/toaster-warning` | `GET,HEAD` | `401` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\DevToolsGuardMiddleware` |

#### Mutations

| Pattern | Methods | Guest | User | Privileged | State Profiles | Permissions | Middleware |
|---|---|---|---|---|---|---|---|
| `/test-features/api/validator-test` | `POST` | `401` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\DevToolsGuardMiddleware` |
| `/test-features/api/validator-unique` | `POST` | `401` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\DevToolsGuardMiddleware` |
| `/test-features/assign-privileged-role` | `POST` | `401` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\DevToolsGuardMiddleware` |
| `/test-features/db-reset` | `POST` | `401` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\DevToolsGuardMiddleware` |
| `/test-features/form-demo` | `POST` | `401` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\DevToolsGuardMiddleware` |
| `/test-features/i18n/set-locale` | `POST` | `401` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\DevToolsGuardMiddleware` |
| `/test-features/mail-test` | `POST` | `401` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\DevToolsGuardMiddleware` |
| `/test-features/modal/form-submit` | `POST` | `401` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\DevToolsGuardMiddleware` |
| `/test-features/orm/create` | `POST` | `401` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\DevToolsGuardMiddleware` |
| `/test-features/orm/delete-latest` | `POST` | `401` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\DevToolsGuardMiddleware` |
| `/test-features/orm/update` | `POST` | `401` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\DevToolsGuardMiddleware` |
| `/test-features/upload` | `POST` | `401` | `403` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\DevToolsGuardMiddleware` |

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
- Representative JSON: `/runtime/websocket/token`

#### HTML routes

_No HTML routes declared for harness._

#### JSON routes

| Pattern | Methods | Guest | User | Privileged | State Profiles | Permissions | Middleware |
|---|---|---|---|---|---|---|---|
| `/runtime/notifications` | `GET,HEAD` | `401` | `200` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\AuthMiddleware` |
| `/runtime/notifications/unread-count` | `GET,HEAD` | `401` | `200` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\AuthMiddleware` |
| `/runtime/websocket/token` | `GET,HEAD` | `401` | `200` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\AuthMiddleware` |

#### Mutations

| Pattern | Methods | Guest | User | Privileged | State Profiles | Permissions | Middleware |
|---|---|---|---|---|---|---|---|
| `/runtime/notifications/read-all` | `POST` | `401` | `200` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\AuthMiddleware` |
| `/runtime/notifications/{id}/read` | `POST` | `401` | `200` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\AuthMiddleware` |
| `/runtime/presence/{resourceKey}/{recordId}/heartbeat` | `POST` | `401` | `200` | `200` | `n/a` | `n/a` | `Catalyst\Framework\Middleware\AuthMiddleware` |

### framework.operations

- Scope: `Framework`
- Surface: `authenticated`
- Runtime enabled: `yes`
- Slug: `operations`
- Description: Canonical owner of framework operations surfaces.
- Plugin: `framework.core`
- Views: `no`
- Assets: `n/a`
- Settings: `n/a`
- Permissions: `manage-operations-api-management`, `manage-operations-audit-log`, `manage-operations-automation-rules`, `manage-operations-deployments`, `manage-operations-tenancy`
- Seeds: `n/a`
- Feature flags: `n/a`
- Module flag key: `module.framework.operations`
- Representative HTML: `/operations/audit-log`
- Representative JSON: `/api/v1/automation-rules`

#### HTML routes

| Pattern | Methods | Guest | User | Privileged | State Profiles | Permissions | Middleware |
|---|---|---|---|---|---|---|---|
| `/operations/api-management` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-operations-api-management` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/operations/audit-log` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-operations-audit-log` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/operations/audit-log/{id}` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-operations-audit-log` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/operations/automation-rules` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-operations-automation-rules` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/operations/automation-rules/create` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-operations-automation-rules` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/operations/automation-rules/{id}` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-operations-automation-rules` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/operations/automation-rules/{id}/edit` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-operations-automation-rules` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/operations/deployments` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-operations-deployments` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/operations/tenancy` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-operations-tenancy` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |

#### JSON routes

| Pattern | Methods | Guest | User | Privileged | State Profiles | Permissions | Middleware |
|---|---|---|---|---|---|---|---|
| `/api/v1/automation-rules` | `GET,HEAD` | `401` | `401` | `401` | `api_token=200` | `n/a` | `Catalyst\Framework\Middleware\ApiTokenMiddleware` |
| `/api/v1/automation-rules/{id}` | `GET,HEAD` | `401` | `401` | `401` | `api_token=200` | `n/a` | `Catalyst\Framework\Middleware\ApiTokenMiddleware` |

#### Mutations

| Pattern | Methods | Guest | User | Privileged | State Profiles | Permissions | Middleware |
|---|---|---|---|---|---|---|---|
| `/api/v1/automation-rules/{id}/run` | `POST` | `401` | `401` | `401` | `api_token=200` | `n/a` | `Catalyst\Framework\Middleware\ApiTokenMiddleware` |
| `/operations/api-management/tokens` | `POST` | `401` | `403` | `200` | `n/a` | `manage-operations-api-management` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/operations/api-management/tokens/{id}/revoke` | `POST` | `401` | `403` | `200` | `n/a` | `manage-operations-api-management` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/operations/automation-rules` | `POST` | `401` | `403` | `200` | `n/a` | `manage-operations-automation-rules` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/operations/automation-rules/{id}` | `POST` | `401` | `403` | `200` | `n/a` | `manage-operations-automation-rules` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/operations/automation-rules/{id}/delete` | `POST` | `401` | `403` | `200` | `n/a` | `manage-operations-automation-rules` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/operations/automation-rules/{id}/run` | `POST` | `401` | `403` | `200` | `n/a` | `manage-operations-automation-rules` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/operations/automation-rules/{id}/transition` | `POST` | `401` | `403` | `200` | `n/a` | `manage-operations-automation-rules` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/operations/automation-rules/{id}/versions/{versionId}/restore` | `POST` | `401` | `403` | `200` | `n/a` | `manage-operations-automation-rules` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/operations/deployments/runs` | `POST` | `401` | `403` | `200` | `n/a` | `manage-operations-deployments` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |

### framework.users

- Scope: `Framework`
- Surface: `privileged`
- Runtime enabled: `yes`
- Slug: `users`
- Description: RBAC privileged, users, roles and permissions.
- Plugin: `framework.core`
- Views: `yes`
- Assets: `ok`
- Settings: `n/a`
- Permissions: `manage-account-recovery`, `manage-roles`, `manage-users`
- Seeds: `n/a`
- Feature flags: `n/a`
- Module flag key: `module.framework.users`
- Representative HTML: `/users`
- Representative JSON: `n/a`

#### HTML routes

| Pattern | Methods | Guest | User | Privileged | State Profiles | Permissions | Middleware |
|---|---|---|---|---|---|---|---|
| `/users` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-users` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/users/account-recovery` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-account-recovery` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/users/account-recovery/{id}` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-account-recovery` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
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

| Pattern | Methods | Guest | User | Privileged | State Profiles | Permissions | Middleware |
|---|---|---|---|---|---|---|---|
| `/users/account-recovery/{id}/approve` | `POST` | `401` | `403` | `200` | `n/a` | `manage-account-recovery` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/users/account-recovery/{id}/reject` | `POST` | `401` | `403` | `200` | `n/a` | `manage-account-recovery` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
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

### framework.workspaces

- Scope: `Framework`
- Surface: `authenticated`
- Runtime enabled: `yes`
- Slug: `workspaces`
- Description: Canonical owner of framework workspace surfaces.
- Plugin: `framework.business`
- Views: `yes`
- Assets: `ok`
- Settings: `n/a`
- Permissions: `manage-workspaces-catalogs`, `manage-workspaces-document-templates`, `manage-workspaces-localization`, `manage-workspaces-media-fields`, `manage-workspaces-media-library`, `manage-workspaces-module-designer`
- Seeds: `n/a`
- Feature flags: `n/a`
- Module flag key: `module.framework.workspaces`
- Representative HTML: `/workspaces/catalogs`
- Representative JSON: `/api/v1/document-templates`

#### HTML routes

| Pattern | Methods | Guest | User | Privileged | State Profiles | Permissions | Middleware |
|---|---|---|---|---|---|---|---|
| `/workspaces/catalogs` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-workspaces-catalogs` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/catalogs/create` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-workspaces-catalogs` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/catalogs/{id}` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-workspaces-catalogs` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/catalogs/{id}/edit` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-workspaces-catalogs` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/catalogs/{id}/items/create` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-workspaces-catalogs` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/catalogs/{id}/items/{itemId}/edit` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-workspaces-catalogs` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/document-templates` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-workspaces-document-templates` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/document-templates/create` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-workspaces-document-templates` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/document-templates/{id}` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-workspaces-document-templates` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/document-templates/{id}/edit` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-workspaces-document-templates` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/locale-tools` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-workspaces-localization` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/media-fields` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-workspaces-media-fields` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/media-fields/create` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-workspaces-media-fields` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/media-fields/{id}/edit` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-workspaces-media-fields` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/media-library` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-workspaces-media-library` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/media-library/upload` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-workspaces-media-library` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/media-library/{id}/edit` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-workspaces-media-library` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/module-designer` | `GET,HEAD` | `login` | `root` | `200` | `n/a` | `manage-workspaces-module-designer` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |

#### JSON routes

| Pattern | Methods | Guest | User | Privileged | State Profiles | Permissions | Middleware |
|---|---|---|---|---|---|---|---|
| `/api/v1/document-templates` | `GET,HEAD` | `401` | `401` | `401` | `api_token=200` | `n/a` | `Catalyst\Framework\Middleware\ApiTokenMiddleware` |
| `/api/v1/document-templates/{id}` | `GET,HEAD` | `401` | `401` | `401` | `api_token=200` | `n/a` | `Catalyst\Framework\Middleware\ApiTokenMiddleware` |

#### Mutations

| Pattern | Methods | Guest | User | Privileged | State Profiles | Permissions | Middleware |
|---|---|---|---|---|---|---|---|
| `/api/v1/document-templates/{id}/export` | `POST` | `401` | `401` | `401` | `api_token=200` | `n/a` | `Catalyst\Framework\Middleware\ApiTokenMiddleware` |
| `/api/v1/document-templates/{id}/preview` | `POST` | `401` | `401` | `401` | `api_token=200` | `n/a` | `Catalyst\Framework\Middleware\ApiTokenMiddleware` |
| `/workspaces/catalogs` | `POST` | `401` | `403` | `200` | `n/a` | `manage-workspaces-catalogs` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/catalogs/{id}` | `POST` | `401` | `403` | `200` | `n/a` | `manage-workspaces-catalogs` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/catalogs/{id}/delete` | `POST` | `401` | `403` | `200` | `n/a` | `manage-workspaces-catalogs` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/catalogs/{id}/items` | `POST` | `401` | `403` | `200` | `n/a` | `manage-workspaces-catalogs` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/catalogs/{id}/items/{itemId}` | `POST` | `401` | `403` | `200` | `n/a` | `manage-workspaces-catalogs` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/catalogs/{id}/items/{itemId}/delete` | `POST` | `401` | `403` | `200` | `n/a` | `manage-workspaces-catalogs` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/catalogs/{id}/transition` | `POST` | `401` | `403` | `200` | `n/a` | `manage-workspaces-catalogs` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/catalogs/{id}/versions/{versionId}/restore` | `POST` | `401` | `403` | `200` | `n/a` | `manage-workspaces-catalogs` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/document-templates` | `POST` | `401` | `403` | `200` | `n/a` | `manage-workspaces-document-templates` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/document-templates/{id}` | `POST` | `401` | `403` | `200` | `n/a` | `manage-workspaces-document-templates` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/document-templates/{id}/delete` | `POST` | `401` | `403` | `200` | `n/a` | `manage-workspaces-document-templates` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/document-templates/{id}/export` | `POST` | `401` | `403` | `200` | `n/a` | `manage-workspaces-document-templates` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/document-templates/{id}/preview` | `POST` | `401` | `403` | `200` | `n/a` | `manage-workspaces-document-templates` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/document-templates/{id}/transition` | `POST` | `401` | `403` | `200` | `n/a` | `manage-workspaces-document-templates` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/document-templates/{id}/versions/{versionId}/restore` | `POST` | `401` | `403` | `200` | `n/a` | `manage-workspaces-document-templates` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/locale-tools/create-locale` | `POST` | `401` | `403` | `200` | `n/a` | `manage-workspaces-localization` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/locale-tools/settings` | `POST` | `401` | `403` | `200` | `n/a` | `manage-workspaces-localization` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/locale-tools/sync-locale` | `POST` | `401` | `403` | `200` | `n/a` | `manage-workspaces-localization` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/media-fields` | `POST` | `401` | `403` | `200` | `n/a` | `manage-workspaces-media-fields` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/media-fields/{id}` | `POST` | `401` | `403` | `200` | `n/a` | `manage-workspaces-media-fields` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/media-fields/{id}/delete` | `POST` | `401` | `403` | `200` | `n/a` | `manage-workspaces-media-fields` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/media-library` | `POST` | `401` | `403` | `200` | `n/a` | `manage-workspaces-media-library` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/media-library/bulk-delete` | `POST` | `401` | `403` | `200` | `n/a` | `manage-workspaces-media-library` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/media-library/{id}` | `POST` | `401` | `403` | `200` | `n/a` | `manage-workspaces-media-library` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/media-library/{id}/delete` | `POST` | `401` | `403` | `200` | `n/a` | `manage-workspaces-media-library` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/module-designer/generate` | `POST` | `401` | `403` | `200` | `n/a` | `manage-workspaces-module-designer` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |
| `/workspaces/module-designer/preview` | `POST` | `401` | `403` | `200` | `n/a` | `manage-workspaces-module-designer` | `Catalyst\Framework\Middleware\AuthMiddleware`, `Catalyst\Framework\Middleware\RoleMiddleware` |

