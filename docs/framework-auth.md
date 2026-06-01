# Catalyst\Framework\Auth

## Class: UserProvider
**File**: `app/Framework/Auth/UserProvider.php`  
**Namespace**: `Catalyst\Framework\Auth`  
**Type**: Class  
**Pattern**: Singleton (`SingletonTrait`)  
**Purpose**: Database-level user lookups and mutation. Single source of truth for the `users` and `user_social_accounts` tables, plus MFA fields stored on `users`.

### Methods
- `findByEmail(string $email): ?array` — **public** — Returns an active, email-verified user (`active=1`, `email_verified=1`)
- `findByEmailAny(string $email): ?array` — **public** — Returns user by email without active/verified filters (used by registration/login prechecks)
- `findById(int $id): ?array` — **public** — Returns an active user by ID (`active=1`)
- `findBySocialAccount(string $provider, string $providerUserId): ?array` — **public** — Resolves an active row in `user_social_accounts`, then loads the user via `findById()`
- `create(string $name, string $email, string $password, string $role = 'user', bool $emailVerified = false): int` — **public** — Inserts a user, hashes password internally, and attempts role assignment through `roles` + `user_roles`
- `updateLastLogin(int $userId): void` — **public** — Sets `last_login`
- `markEmailVerified(int $userId): void` — **public** — Sets `email_verified = 1`
- `updatePassword(int $userId, string $plainPassword): void` — **public** — Hashes and updates `password`
- `linkSocialAccount(int $userId, string $provider, string $providerUserId): void` — **public** — Inserts or re-activates a row in `user_social_accounts`
- `verifyPassword(string $plain, string $hash): bool` — **public** — `password_verify()` wrapper
- `getMfaData(int $userId): ?array` — **public** — Returns `mfa_secret`, `mfa_enabled`, `mfa_backup_codes`
- `enableMfa(int $userId, string $secret, array $backupCodes): void` — **public** — Persists confirmed TOTP secret and hashed backup codes
- `disableMfa(int $userId): void` — **public** — Clears MFA secret and backup codes, sets `mfa_enabled = 0`
- `updateMfaBackupCodes(int $userId, array $codes): void` — **public** — Persists remaining backup codes after one is consumed; runtime stores hashes, not clear-text codes

### Password hashing
- Bcrypt cost is read from `security.security.bcrypt_rounds` when available.
- Runtime clamps the configured cost to PHP-supported bounds and defaults to `12`.

### No-Delete Rule
- `users.active`: `1 = active`, `0 = deactivated`
- `user_social_accounts.active`: `1 = linked`, `0 = unlinked`
- Social links are re-activated instead of physically re-inserted when possible

---

## Class: RememberMe
**File**: `app/Framework/Auth/RememberMe.php`  
**Namespace**: `Catalyst\Framework\Auth`  
**Type**: Class  
**Pattern**: Singleton (`SingletonTrait`)  
**Purpose**: Persistent login via remember-me cookie backed by `remember_tokens`.

### Constants
- `COOKIE_NAME = 'catalyst_remember'`
- `COOKIE_DAYS = 30`

### Methods
- `create(int $userId): void` — **public** — Generates raw token, stores `sha256` hash, sets HttpOnly/SameSite cookie, and marks it `Secure` when the effective request is HTTPS (including reverse-proxy headers)
- `resolve(): ?int` — **public** — Resolves cookie token to active, non-expired `user_id`
- `invalidate(int $userId): void` — **public** — Sets `active = 0` on remember tokens for the user
- `hasToken(): bool` — **public** — Checks whether the remember-me cookie is present

### No-Delete Rule
- `remember_tokens.active`: `1 = valid`, `0 = invalidated`

---

## Class: AuthManager
**File**: `app/Framework/Auth/AuthManager.php`  
**Namespace**: `Catalyst\Framework\Auth`  
**Type**: Class  
**Pattern**: Singleton (`SingletonTrait`)  
**Purpose**: Central authentication facade. Composes `UserProvider`, `RememberMe`, `SessionManager`, and role resolution.

### Authenticated session keys
| Key | Type | Content |
|-----|------|---------|
| `_auth_logged_in` | bool | `true` when authenticated |
| `_auth_user_id` | int | User ID |
| `_auth_user_email` | string | User email |
| `_auth_user_name` | string | User display name |
| `_auth_user_role` | string | Primary role slug |

### Pending MFA challenge keys
| Key | Type | Content |
|-----|------|---------|
| `_mfa_pending_user_id` | int | User that passed credentials and must solve MFA |
| `_mfa_pending_remember` | bool | Whether remember-me should be issued after MFA |
| `_mfa_pending_redirect` | string | Safe post-login redirect |

### Pending forced-setup keys
| Key | Type | Content |
|-----|------|---------|
| `_mfa_setup_pending_user_id` | int | User that passed credentials but must configure MFA first |
| `_mfa_setup_pending_remember` | bool | Whether remember-me should be issued after setup |
| `_mfa_setup_pending_redirect` | string | Safe post-setup redirect |

### Methods
- `login(string $email, string $password, bool $remember = false): bool` — **public** — Legacy direct credential path; creates full session immediately when credentials pass
- `loginUser(array $user): void` — **public** — Direct session creation without remember-me issuance
- `loginFromUser(array $user, bool $remember = false): void` — **public** — MFA-aware full login path from a pre-verified user row
- `logout(): void` — **public** — Invalidates remember-me and removes `_auth_*` session keys
- `check(): bool` — **public** — Returns `_auth_logged_in`
- `user(): ?array` — **public** — Returns session user data (`id`, `email`, `name`, `role`)
- `id(): ?int` — **public** — Returns authenticated user ID
- `loginFromRemember(): bool` — **public** — Restores a session from `RememberMe`
- `setPendingMfa(int $userId, bool $remember, string $redirect): void` — **public** — Stores pending challenge state
- `hasMfaPending(): bool` — **public** — Whether challenge flow is active
- `getMfaPendingUserId(): ?int` — **public** — Pending MFA user ID
- `getMfaPendingRemember(): bool` — **public** — Pending MFA remember flag
- `getMfaPendingRedirect(): string` — **public** — Pending MFA redirect path
- `completeMfaLogin(): bool` — **public** — Creates full session from pending MFA state and clears it
- `clearPendingMfa(): void` — **public** — Removes `_mfa_pending_*` keys
- `setPendingMfaSetup(int $userId, bool $remember, string $redirect): void` — **public** — Stores forced setup state
- `hasMfaSetupPending(): bool` — **public** — Whether forced setup flow is active
- `getMfaSetupPendingUserId(): ?int` — **public** — Pending setup user ID
- `getMfaSetupPendingRemember(): bool` — **public** — Pending setup remember flag
- `getMfaSetupPendingRedirect(): string` — **public** — Pending setup redirect path
- `completeMfaSetupLogin(): bool` — **public** — Creates full session after MFA setup and clears pending setup state
- `clearMfaSetupPending(): void` — **public** — Removes `_mfa_setup_pending_*` keys

### Runtime notes
- Primary role is resolved from `RoleRepository::getUserRoles()` and stored as `_auth_user_role`.
- LoginController uses the MFA-aware flow directly: credentials are validated first, then it branches to full login, MFA challenge, or forced setup.

---

## Class: TokenRepository
**File**: `app/Framework/Auth/TokenRepository.php`  
**Namespace**: `Catalyst\Framework\Auth`  
**Type**: Class  
**Pattern**: Singleton (`SingletonTrait`)  
**Purpose**: Email verification and password reset token lifecycle.

### Constants
- `TTL_SECONDS = 3600`

### Methods
- `createVerificationToken(int $userId): string` — **public** — Invalidates prior active tokens, stores hashed token, returns raw token
- `createPasswordResetToken(int $userId): string` — **public** — Same pattern for password reset tokens
- `consumeVerificationToken(string $rawToken): ?int` — **public** — Validates, consumes, and returns `user_id`
- `consumePasswordResetToken(string $rawToken): ?int` — **public** — Validates, consumes, and returns `user_id`

### No-Delete Rule
- Token tables are consumed by `active = 0`, not by physical delete

---

## Class: OAuthManager
**File**: `app/Framework/Auth/OAuthManager.php`  
**Namespace**: `Catalyst\Framework\Auth`  
**Type**: Class  
**Pattern**: Singleton (`SingletonTrait`)  
**Purpose**: OAuth2 Authorization Code Flow orchestration for supported providers.

### Supported Providers
| Provider | Env Vars Required |
|----------|------------------|
| `google` | `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET` |
| `github` | `GITHUB_CLIENT_ID`, `GITHUB_CLIENT_SECRET` |

### Methods
- `getAuthorizationUrl(string $provider): string` — **public** — Generates URL and stores `_oauth_state_{provider}`
- `handleCallback(string $provider, string $code, string $state): ?OAuthUser` — **public** — Validates state, exchanges code, resolves normalized user or returns `null` on CSRF/provider failure
- `isConfigured(string $provider): bool` — **public** — Checks provider env vars

### Internal methods
- `getProvider(string $provider): AbstractProvider` — **private** — Lazy provider factory/cache
- `buildProvider(string $provider): AbstractProvider` — **private** — Instantiates Google/GitHub providers
- `buildRedirectUri(string $provider): string` — **private** — Builds callback URL from effective app config or `.env`

### CSRF Protection
- OAuth state is stored per provider in `_oauth_state_{provider}` and validated with `hash_equals()`

---

## Namespace: Catalyst\Framework\Auth\OAuth

### Class: OAuthUser
**File**: `app/Framework/Auth/OAuth/OAuthUser.php`  
**Namespace**: `Catalyst\Framework\Auth\OAuth`  
**Type**: Class  
**Implements**: `League\OAuth2\Client\Provider\ResourceOwnerInterface`  
**Purpose**: Unified OAuth user wrapper across providers.

#### Methods
- `getId(): string` — Returns provider identifier (`id` or `sub`)
- `getName(): ?string` — Returns display name (`name` or `login`) or `null`
- `getEmail(): ?string` — Returns email when provider supplies it, otherwise `null`
- `getProvider(): string` — Returns provider slug
- `toArray(): array` — Returns raw provider payload

---

### Class: GoogleProvider
**File**: `app/Framework/Auth/OAuth/GoogleProvider.php`  
**Namespace**: `Catalyst\Framework\Auth\OAuth`  
**Type**: Class  
**Extends**: `League\OAuth2\Client\Provider\AbstractProvider`

#### Endpoints
- Auth: `https://accounts.google.com/o/oauth2/v2/auth`
- Token: `https://oauth2.googleapis.com/token`
- User Info: `https://www.googleapis.com/oauth2/v3/userinfo`

#### Scopes
- `['openid', 'email', 'profile']`

---

### Class: GitHubProvider
**File**: `app/Framework/Auth/OAuth/GitHubProvider.php`  
**Namespace**: `Catalyst\Framework\Auth\OAuth`  
**Type**: Class  
**Extends**: `League\OAuth2\Client\Provider\AbstractProvider`

#### Endpoints
- Auth: `https://github.com/login/oauth/authorize`
- Token: `https://github.com/login/oauth/access_token`
- User Info: `https://api.github.com/user`

#### Scopes
- `['user:email']`

---

## Namespace: Catalyst\Framework\Middleware

### Class: AuthMiddleware
**File**: `app/Framework/Middleware/AuthMiddleware.php`  
**Namespace**: `Catalyst\Framework\Middleware`  
**Type**: Class  
**Extends**: `CoreMiddleware`  
**Implements**: `MiddlewareInterface`  
**Purpose**: Route guard for authenticated routes.

#### process() logic
1. `AuthManager::check()` succeeds → pass through
2. `AuthManager::loginFromRemember()` succeeds → pass through
3. JSON request → `401` JSON unauthenticated response
4. HTML request → redirect to `/login?redirect=...`

---

### Class: GuestMiddleware
**File**: `app/Framework/Middleware/GuestMiddleware.php`  
**Namespace**: `Catalyst\Framework\Middleware`  
**Type**: Class  
**Extends**: `CoreMiddleware`  
**Implements**: `MiddlewareInterface`  
**Purpose**: Redirects authenticated users away from guest-only routes to `/`.

#### process() logic
1. `AuthManager::check()` succeeds → redirect to `/` (fixed root redirect, not a dynamic entry point)
2. Otherwise → pass through

---

### Interface: FeatureFlagInterface
**File**: `app/Framework/Middleware/FeatureFlagInterface.php`  
**Namespace**: `Catalyst\Framework\Middleware`  
**Type**: Interface  
**Purpose**: Contract for middleware that can be disabled by config.

#### Methods
- `isEnabled(): bool`

#### Implementations
- `CorsMiddleware`
- `WebSocketBootMiddleware`

---

### Class: CorsMiddleware
**File**: `app/Framework/Middleware/CorsMiddleware.php`  
**Namespace**: `Catalyst\Framework\Middleware`  
**Type**: Class  
**Extends**: `CoreMiddleware`  
**Implements**: `FeatureFlagInterface`  
**Traits**: `LoadsFeatureConfigTrait`  
**Purpose**: Config-driven CORS middleware.

#### Configuration (`cors.json`)
| Key | Default | Description |
|-----|---------|-------------|
| `enabled` | `true` | Enable/disable middleware |
| `allowed_origins` | `['*']` | Allowed origins |
| `allowed_methods` | `['GET','POST','PUT','PATCH','DELETE','OPTIONS']` | Allowed methods |
| `allowed_headers` | `['Content-Type','Authorization','X-Requested-With','X-CSRF-TOKEN']` | Allowed headers |
| `exposed_headers` | `[]` | Headers exposed to browser |
| `allow_credentials` | `false` | Allow credentials |
| `max_age` | `86400` | Preflight cache TTL |

#### Behaviour
- Requests without `Origin` header pass through unchanged
- `OPTIONS` preflight returns `204`
- Wildcard origin plus credentials is downgraded to the actual origin
