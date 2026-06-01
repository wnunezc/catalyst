# Catalyst\Repository\Auth\Controllers

**Module location**: `Repository/Framework/Auth/`  
**Module structure**:
```text
Auth/
├── Controllers/    ← 7 controllers
├── front/          ← module work assets published automatically to /assets/*/work/auth/
├── Views/
│   ├── pages/      ← login, register, verify-email, forgot-password, reset-password, mfa-setup, mfa-challenge
│   ├── partials/   ← auth reusable fragments
│   └── scope/      ← presentation-only scope companions
├── lang/           ← en/ and es/ auth.json
└── routes.php      ← loaded by Kernel glob
```

## Class: LoginController
**File**: `Repository/Framework/Auth/Controllers/LoginController.php`  
**Namespace**: `Catalyst\Repository\Auth\Controllers`  
**Extends**: `Controller`

### Methods
- `showForm(Request $request): Response` — Renders `auth.login`; `GuestMiddleware` handles redirect for already-authenticated users
- `login(Request $request): Response` — Validates fields, normalizes redirect targets through `RedirectTarget`, checks user status, and branches to full login, forced MFA setup, or MFA challenge depending on framework MFA state

### MFA-aware login flow
- Invalid credentials return a generic failure message
- Unverified email blocks login before session creation
- Inactive users are rejected
- Global MFA enabled + `mfa_enabled=0` → pending setup state + redirect to `/mfa/setup`
- Global MFA enabled + `mfa_enabled=1` → pending challenge state + redirect to `/mfa/challenge`
- Global MFA disabled → `AuthManager::loginFromUser(...)`

---

## Class: LogoutController
**File**: `Repository/Framework/Auth/Controllers/LogoutController.php`  
**Namespace**: `Catalyst\Repository\Auth\Controllers`  
**Extends**: `Controller`

### Methods
- `logout(Request $request): Response` — Calls `AuthManager::logout()`, queues success flash, redirects to `/login`

---

## Class: RegisterController
**File**: `Repository/Framework/Auth/Controllers/RegisterController.php`  
**Namespace**: `Catalyst\Repository\Auth\Controllers`  
**Extends**: `Controller`

### Methods
- `showForm(Request $request): Response` — Renders `auth.register`
- `register(Request $request): Response` — Validates input, checks uniqueness, creates user with `UserProvider::create(...)`, creates verification token, sends verification email

---

## Class: EmailVerificationController
**File**: `Repository/Framework/Auth/Controllers/EmailVerificationController.php`  
**Namespace**: `Catalyst\Repository\Auth\Controllers`  
**Extends**: `Controller`

### Methods
- `showManualForm(): Response` — Renders `auth.verify-email` for manual token activation when the email link cannot be opened directly
- `manualVerify(Request $request): Response` — Validates a pasted 64-character hexadecimal token through `EmailVerificationTokenRequest` and consumes it
- `verify(Request $request, string $token): Response` — Validates the URL token format, consumes the verification token, marks email as verified, redirects to `/login`

---

## Class: PasswordResetController
**File**: `Repository/Framework/Auth/Controllers/PasswordResetController.php`  
**Namespace**: `Catalyst\Repository\Auth\Controllers`  
**Extends**: `Controller`

### Methods
- `showRequestForm(Request $request): Response` — Renders `auth.forgot-password`
- `sendResetLink(Request $request): Response` — Anti-enumeration response; sends email only when user exists
- `showResetForm(Request $request, string $token): Response` — Renders `auth.reset-password`
- `reset(Request $request, string $token): Response` — Validates password input, consumes reset token, updates password

---

## Class: SocialAuthController
**File**: `Repository/Framework/Auth/Controllers/SocialAuthController.php`  
**Namespace**: `Catalyst\Repository\Auth\Controllers`  
**Extends**: `Controller`

### Methods
- `redirect(Request $request, string $provider): Response` — Checks provider configuration and redirects to OAuth provider
- `callback(Request $request, string $provider): Response` — Resolves provider user, links or creates local account, marks OAuth-created emails as verified, logs user in

### Account linking
- Existing local account with same email: social account is linked to that user
- New social account: user is created and treated as email-verified

---

## Class: MfaController
**File**: `Repository/Framework/Auth/Controllers/MfaController.php`  
**Namespace**: `Catalyst\Repository\Auth\Controllers`  
**Extends**: `Controller`

### Methods
- `setup(Request $request): Response` — Renders QR provisioning page; accepts authenticated users or forced-setup pending state
- `enable(Request $request): Response` — Validates the setup code through `MfaCodeRequest`, verifies first TOTP code, enables MFA, returns backup codes once to the user, and persists only their hashes
- `disable(Request $request): Response` — Verifies current password and disables MFA for an authenticated user
- `challenge(Request $request): Response` — Renders login challenge form when pending MFA state exists
- `verify(Request $request): Response` — Validates the submitted MFA format through `MfaCodeRequest`, verifies TOTP or backup code, updates the persisted hashed backup-code set when one is consumed, and completes the pending login session

### Access rules
- `setup()` / `enable()` — authenticated session or pending forced-setup flow
- `disable()` — authenticated session only
- `challenge()` / `verify()` — pending MFA challenge only

---

## Auth Routes (`Repository/Framework/Auth/routes.php`)

| Method | Path | Controller | Middleware |
|--------|------|-----------|-----------|
| GET | `/login` | `LoginController::showForm` | `GuestMiddleware` |
| POST | `/login` | `LoginController::login` | `GuestMiddleware`, `LoginThrottleMiddleware` |
| GET | `/register` | `RegisterController::showForm` | `GuestMiddleware` |
| POST | `/register` | `RegisterController::register` | `GuestMiddleware`, `LoginThrottleMiddleware` |
| GET | `/forgot-password` | `PasswordResetController::showRequestForm` | `GuestMiddleware` |
| POST | `/forgot-password` | `PasswordResetController::sendResetLink` | `GuestMiddleware` |
| GET | `/reset-password/{token}` | `PasswordResetController::showResetForm` | `GuestMiddleware` |
| POST | `/reset-password/{token}` | `PasswordResetController::reset` | `GuestMiddleware` |
| GET | `/verify-email` | `EmailVerificationController::showManualForm` | `GuestMiddleware` |
| POST | `/verify-email` | `EmailVerificationController::manualVerify` | `GuestMiddleware`, throttle `auth_recovery` |
| GET | `/verify-email/{token}` | `EmailVerificationController::verify` | — |
| POST | `/logout` | `LogoutController::logout` | — |
| GET | `/auth/social/{provider}` | `SocialAuthController::redirectToProvider` | `RouteFeatureMiddleware('social_auth')` |
| GET | `/auth/social/callback/{provider}` | `SocialAuthController::callback` | `RouteFeatureMiddleware('social_auth')` |
| GET | `/mfa/setup` | `MfaController::setup` | `RouteFeatureMiddleware('mfa')` |
| POST | `/mfa/enable` | `MfaController::enable` | `RouteFeatureMiddleware('mfa')`, throttle `mfa_challenge` |
| POST | `/mfa/disable` | `MfaController::disable` | `AuthMiddleware`, `RouteFeatureMiddleware('mfa')`, throttle `mfa_challenge` |
| GET | `/mfa/challenge` | `MfaController::challenge` | `RouteFeatureMiddleware('mfa')` |
| POST | `/mfa/verify` | `MfaController::verify` | `RouteFeatureMiddleware('mfa')`, throttle `mfa_challenge` |

### Route notes
- Login redirect targets are normalized by `Catalyst\Framework\Http\RedirectTarget` to local paths only. Absolute URLs, protocol-relative URLs and ambiguous backslash paths fall back to `/`.
- `LoginThrottleMiddleware` is bypassed entirely when `IS_DEVELOPMENT` is `true`; lockouts apply only outside local development
- `/mfa/setup` and `/mfa/enable` intentionally do **not** use `AuthMiddleware` because forced first-time setup happens before a full auth session exists
- `/mfa/challenge` and `/mfa/verify` are guarded by pending MFA state inside the controller, not by `AuthMiddleware`
- `/mfa/verify` uses the dedicated `mfa_challenge` throttle only; it does not reuse `LoginThrottleMiddleware`, so a correctable MFA formatting error does not poison the normal login throttle bucket
- Auth views no longer rely on inline page scripts for password policy or QR provisioning; route-specific behavior now enters through `Repository/Framework/Auth/front/script.js`
- `/verify-email/{token}` remains link-compatible and middleware-free because the one-time token is consumed as the identity proof; `/verify-email` is the guest manual activation surface for copy/paste recovery

---

## Auth Database Tables

| Table | Purpose | No-Delete column |
|-------|---------|-----------------|
| `users` | User accounts + MFA fields | `active` |
| `remember_tokens` | Persistent login cookies | `active` |
| `email_verification_tokens` | Account activation | `active` |
| `password_reset_tokens` | Password reset flow | `active` |
| `user_social_accounts` | OAuth linked accounts | `active` |

### MFA fields stored on `users`
- `mfa_secret`
- `mfa_enabled`
- `mfa_backup_codes`
