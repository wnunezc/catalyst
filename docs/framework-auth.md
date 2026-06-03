# Catalyst\Framework\Auth

## Purpose

Document authentication primitives: users, sessions, remember-me, MFA and OAuth support.

## Runtime Owners

| Concern | Owner |
|---|---|
| Guard redirect targets, auth tokens, MFA codes and password-policy checks at auth boundaries. | `Catalyst\Framework\Auth\AuthInputGuard` |
| Orchestrate user authentication state through SessionManager, RememberMe and tenant-aware user context. | `Catalyst\Framework\Auth\AuthManager` |
| Generate MFA credentials, verify submitted codes and hash backup codes for persistence. | `Catalyst\Framework\Auth\MfaManager` |
| Create provider redirects, validate callback state and normalize provider users. | `Catalyst\Framework\Auth\OAuthManager` |
| Supply GitHub endpoints, scopes, headers, error handling and normalized OAuth users. | `Catalyst\Framework\Auth\OAuth\GitHubProvider` |
| Supply Google endpoints, scopes, error handling and normalized OAuth users. | `Catalyst\Framework\Auth\OAuth\GoogleProvider` |
| Expose provider identity, display name, email and raw payload through one resource-owner interface. | `Catalyst\Framework\Auth\OAuth\OAuthUser` |
| Issue, resolve and invalidate remember-me tokens without storing raw token values. | `Catalyst\Framework\Auth\RememberMe` |
| Create, consume and invalidate user recovery tokens without persisting raw token values. | `Catalyst\Framework\Auth\TokenRepository` |
| Provide tenant-scoped user summaries, select options and admin listings. | `Catalyst\Framework\Auth\UserDirectoryRepository` |
| Resolve users, manage credentials, link OAuth accounts and persist MFA state. | `Catalyst\Framework\Auth\UserProvider` |
| Represents the subject passed to resource-level authorization checks. | `Catalyst\Framework\Authorization\AbilitySubject` |
| Evaluates authorization abilities through registered closures and policy classes. | `Catalyst\Framework\Authorization\Gate` |
| Bridges module permission metadata with Gate and RoleRepository checks. | `Catalyst\Framework\Authorization\PermissionRegistry` |
| Lets concrete policies short-circuit ability checks before can* methods run. | `Catalyst\Framework\Authorization\Policy` |
| Normalizes role and permission changes into audit operations. | `Catalyst\Framework\Authorization\RbacAuditLogger` |
| Clears user-scoped and global cache entries after RBAC mutations. | `Catalyst\Framework\Authorization\RbacCacheInvalidator` |
| Constrains user-provided sort options to repository-approved SQL fragments. | `Catalyst\Framework\Authorization\RbacSortResolver` |
| Authorizes AbilitySubject instances through resource permission definitions. | `Catalyst\Framework\Authorization\ResourcePolicy` |
| Provides the database boundary for role and permission reads and mutations. | `Catalyst\Framework\Authorization\RoleRepository` |

## Current Behavior

This file is regenerated from current PHP docblocks and the runtime inventory scope for `Catalyst\Framework\Auth`. It intentionally replaces stale historical API notes with the classes and methods that exist in code now.

Public self-service registration is controlled by the `auth.registration_enabled` feature flag. The installation default is managed from `/configuration/environment-setup` in the Features card, which writes `features.json` under `boot-core/config/{environment}` through the Settings module. Advanced runtime governance remains available from `/configuration/feature-flags` for post-setup review and overrides. When disabled, `/register` GET and POST are blocked by `RouteFeatureMiddleware` and the login screen hides the create-account link. Login, logout, password reset, MFA and email verification remain separate auth surfaces.

Resource permissions are declared in module metadata and evaluated by `PermissionRegistry` through `AbilitySubject`. Apps should pass domain records and contextual data through `authorizeResource()` or `canResource()` instead of embedding authorization shortcuts in controllers. Supported declarative constraints include `record_required`, `owner_field`, `owner_context_key`, `state_field` with `states_any`, `visibility_field` or `visibility_context_key` with `visibility_any`, `scope_context_key` with `scopes_any`, generic `context_any` maps, and optional `policy_ability` delegation. This keeps ownership, visibility and workflow-scope checks separate from strong tenancy requirements.

Example metadata fragment:

```php
[
    'slug' => 'documents-update-own-draft',
    'resource' => 'documents',
    'abilities_any' => ['update'],
    'record_required' => true,
    'owner_field' => 'owner_id',
    'state_field' => 'state',
    'states_any' => ['draft'],
    'visibility_field' => 'visibility',
    'visibility_any' => ['internal'],
    'context_any' => [
        'department' => ['ops', 'quality'],
    ],
]
```

Controller checks should provide the same subject data without reaching into RBAC storage:

```php
$this->authorizeResource('update', 'documents', $document, [
    'department' => $departmentSlug,
    'scope' => 'workflow',
]);
```

## API From Docblocks

### `Catalyst\Framework\Auth\AuthInputGuard`

- File: `app/Framework/Auth/AuthInputGuard.php`
- Kind: `class`
- Summary: Validates and normalizes public authentication inputs.
- Responsibility: Guard redirect targets, auth tokens, MFA codes and password-policy checks at auth boundaries.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `localRedirect()` | `public` | Return a safe local redirect target. External URLs, protocol-relative URLs, control characters and backslash-based browser ambiguities fall back safely. | n/a |
| `isRawToken()` | `public` | Checks whether a token matches the expected raw 64-character hex format. | n/a |
| `normalizeMfaCode()` | `public` | Trims and uppercases an MFA code candidate before validation or comparison. | n/a |
| `isMfaCodeCandidate()` | `public` | Checks whether input could be a TOTP code or backup MFA code. | n/a |
| `passwordPolicy()` | `public` | Password policy is intentionally backwards-compatible: defaults mirror the existing min:8 behavior unless security.json opts into stricter flags. | n/a |
| `passwordPolicyErrors()` | `public` | Builds translated password-policy validation errors for the supplied password. | n/a |
| `fallbackPath()` | `private` | Normalizes redirect fallback values to local absolute paths. | n/a |
| `boolean()` | `private` | Converts config-style boolean values into strict booleans. | n/a |

### `Catalyst\Framework\Auth\AuthManager`

- File: `app/Framework/Auth/AuthManager.php`
- Kind: `class`
- Summary: Manages authenticated sessions, remember-me restoration and MFA pending states.
- Responsibility: Orchestrate user authentication state through SessionManager, RememberMe and tenant-aware user context.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `__construct()` | `protected` | Initializes authentication storage, session and logging collaborators. | Initializes authentication storage, session and logging collaborators. |
| `login()` | `public` | Attempt to log in a user with email and password. | Attempt to log in a user with email and password. |
| `loginUser()` | `public` | Log in a user directly from their data array (used after OAuth / registration). Does NOT issue a remember-me token. | Log in a user directly from their data array (used after OAuth / registration). Does NOT issue a remember-me token. |
| `loginFromUser()` | `public` | Create a full authenticated session from a pre-verified user row. Optionally issues a remember-me token. Used by LoginController after MFA-aware credential check and by MfaController after successful TOTP/backup verification (via completeMfaLogin()). | Create a full authenticated session from a pre-verified user row. Optionally issues a remember-me token. Used by LoginController after MFA-aware credential check and by MfaController after successful TOTP/backup verification (via completeMfaLogin()). |
| `loginFromRemember()` | `public` | Attempt to restore a session from a remember-me cookie. | Attempt to restore a session from a remember-me cookie. |
| `logout()` | `public` | Destroy the current authenticated session and remember-me token. | Destroy the current authenticated session and remember-me token. |
| `check()` | `public` | Check whether a user is currently authenticated. | Check whether a user is currently authenticated. |
| `user()` | `public` | Get the authenticated user's data array. | Exposes the authenticated user payload stored in the active session. |
| `id()` | `public` | Get the authenticated user's ID. | Exposes the authenticated user identifier from the active session payload. |
| `beginScopedUser()` | `public` | Scope an authenticated user to the current request without mutating the session. Used by non-session guards such as bearer API tokens so Gate, middleware and audit logging can keep consuming AuthManager as the single auth boundary. | Scope an authenticated user to the current request without mutating the session. Used by non-session guards such as bearer API tokens so Gate, middleware and audit logging can keep consuming AuthManager as the single auth boundary. |
| `clearScopedUser()` | `public` | Clears the request-only authenticated user context. | Clears the request-only authenticated user context. |
| `setPendingMfa()` | `public` | Store a pending-MFA state after successful credential verification. The full session is NOT created yet — it will be completed by completeMfaLogin(). Session keys written: _mfa_pending_user_id — int _mfa_pending_remember — bool _mfa_pending_redirect — string. | Store a pending-MFA state after successful credential verification. The full session is NOT created yet — it will be completed by completeMfaLogin(). Session keys written: _mfa_pending_user_id — int _mfa_pending_remember — bool _mfa_pending_redirect — string. |
| `hasMfaPending()` | `public` | Check whether a pending MFA challenge is in progress. | Check whether a pending MFA challenge is in progress. |
| `getMfaPendingUserId()` | `public` | Return the user ID stored in the pending MFA state, or null if absent. | Return the user ID stored in the pending MFA state, or null if absent. |
| `getMfaPendingRemember()` | `public` | Return the remember flag stored in the pending MFA state. | Return the remember flag stored in the pending MFA state. |
| `getMfaPendingRedirect()` | `public` | Return the redirect path stored in the pending MFA state. | Return the redirect path stored in the pending MFA state. |
| `completeMfaLogin()` | `public` | Complete the MFA challenge: create full auth session and clear pending state. | Complete the MFA challenge: create full auth session and clear pending state. |
| `clearPendingMfa()` | `public` | Remove all pending MFA session keys. | Remove all pending MFA session keys. |
| `setPendingMfaSetup()` | `public` | Store a pending-MFA-setup state after successful credential verification. Used when MFA is globally on and the user has never configured it. | Store a pending-MFA-setup state after successful credential verification. Used when MFA is globally on and the user has never configured it. |
| `hasMfaSetupPending()` | `public` | Check whether a forced-MFA-setup flow is in progress. | Check whether a forced-MFA-setup flow is in progress. |
| `getMfaSetupPendingUserId()` | `public` | Returns the user ID stored in the pending MFA setup state, or null if absent. | Returns the user ID stored in the pending MFA setup state, or null if absent. |
| `getMfaSetupPendingRemember()` | `public` | Returns the remember flag stored in the pending MFA setup state. | Returns the remember flag stored in the pending MFA setup state. |
| `getMfaSetupPendingRedirect()` | `public` | Returns the safe redirect path stored in the pending MFA setup state. | Returns the safe redirect path stored in the pending MFA setup state. |
| `completeMfaSetupLogin()` | `public` | Complete a forced-setup login: create full session, issue remember-me if needed, and clear the pending-setup state. | Complete a forced-setup login: create full session, issue remember-me if needed, and clear the pending-setup state. |
| `clearMfaSetupPending()` | `public` | Remove all pending-MFA-setup session keys. | Remove all pending-MFA-setup session keys. |
| `createSession()` | `private` | Creates the tenant-aware session keys for a fully authenticated user. | Creates the tenant-aware session keys for a fully authenticated user. |
| `tenantMatches()` | `private` | Checks whether a user row belongs to the active tenant context. | Checks whether a user row belongs to the active tenant context. |

### `Catalyst\Framework\Auth\MfaManager`

- File: `app/Framework/Auth/MfaManager.php`
- Kind: `class`
- Summary: Provides TOTP secrets, verification and one-time backup code handling.
- Responsibility: Generate MFA credentials, verify submitted codes and hash backup codes for persistence.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `generateSecret()` | `public` | Generate a cryptographically random base32 TOTP secret. 20 bytes of entropy → 32-character base32 string. | Generate a cryptographically random base32 TOTP secret. 20 bytes of entropy → 32-character base32 string. |
| `generateQrUri()` | `public` | Build the otpauth:// URI used by authenticator apps (Google Authenticator, Aegis, etc.). | Build the otpauth:// URI used by authenticator apps (Google Authenticator, Aegis, etc.). |
| `verifyCode()` | `public` | Verify a 6-digit TOTP code with a ±window step tolerance. Default window=1 allows codes from the previous and next 30-second windows, accommodating minor clock drift between client and server. | Verify a 6-digit TOTP code with a ±window step tolerance. Default window=1 allows codes from the previous and next 30-second windows, accommodating minor clock drift between client and server. |
| `normalizeTotpCode()` | `public` | Removes formatting and accepts only fixed-width numeric TOTP codes. | Removes formatting and accepts only fixed-width numeric TOTP codes. |
| `normalizeBackupCode()` | `public` | Removes separators and uppercases a backup code for hashing or comparison. | Removes separators and uppercases a backup code for hashing or comparison. |
| `generateBackupCodes()` | `public` | Generate $count one-time backup codes. Format: XXXX-XXXX (4 uppercase hex + dash + 4 uppercase hex). | Generate $count one-time backup codes. Format: XXXX-XXXX (4 uppercase hex + dash + 4 uppercase hex). |
| `hashBackupCodes()` | `public` | Hash backup codes before persistence so DB rows never store them in clear text. | Hash backup codes before persistence so DB rows never store them in clear text. |
| `verifyBackupCode()` | `public` | Verify a backup code against the stored list. Removes the matching code on success (one-time use). The $codes array is modified in-place; the caller must persist the updated list. | Verify a backup code against the stored list. Removes the matching code on success (one-time use). The $codes array is modified in-place; the caller must persist the updated list. |
| `computeTotp()` | `private` | Compute the TOTP code for a given binary key and counter value. Algorithm: 1. Pack counter as 8-byte big-endian unsigned integer 2. HMAC-SHA1(key, counter_bytes) 3. Dynamic truncation → 31-bit integer 4. Modulo 10^DIGITS → zero-pad to DIGITS. | Compute the TOTP code for a given binary key and counter value. Algorithm: 1. Pack counter as 8-byte big-endian unsigned integer 2. HMAC-SHA1(key, counter_bytes) 3. Dynamic truncation → 31-bit integer 4. Modulo 10^DIGITS → zero-pad to DIGITS. |
| `base32Encode()` | `private` | Encode a binary string to base32 (RFC 4648, no padding). | Encode a binary string to base32 (RFC 4648, no padding). |
| `base32Decode()` | `private` | Decode a base32 string to binary. Silently skips invalid characters and strips '=' padding. | Decode a base32 string to binary. Silently skips invalid characters and strips '=' padding. |
| `hashBackupCode()` | `private` | Hashes a normalized backup code for database storage. | Hashes a normalized backup code for database storage. |
| `isHashedBackupCode()` | `private` | Checks whether a stored backup-code value is already a SHA-256 hash. | Checks whether a stored backup-code value is already a SHA-256 hash. |

### `Catalyst\Framework\Auth\OAuthManager`

- File: `app/Framework/Auth/OAuthManager.php`
- Kind: `class`
- Summary: Orchestrates OAuth authorization-code login for configured providers.
- Responsibility: Create provider redirects, validate callback state and normalize provider users.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `__construct()` | `protected` | Initializes session and logging collaborators for OAuth flows. | Initializes session and logging collaborators for OAuth flows. |
| `getAuthorizationUrl()` | `public` | Get the authorization URL for the given provider and store CSRF state. | Builds the provider authorization URL while storing OAuth CSRF state in the session. |
| `handleCallback()` | `public` | Handle the OAuth callback: validate state, exchange code, return OAuthUser. | Handle the OAuth callback: validate state, exchange code, return OAuthUser. |
| `isConfigured()` | `public` | Check whether a provider is configured (has client ID/secret in env). | Check whether a provider is configured (has client ID/secret in env). |
| `getProvider()` | `private` | Get (or build and cache) a provider instance. | Get (or build and cache) a provider instance. |
| `buildProvider()` | `private` | Build a provider from env configuration. | Build a provider from env configuration. |

### `Catalyst\Framework\Auth\OAuth\GitHubProvider`

- File: `app/Framework/Auth/OAuth/GitHubProvider.php`
- Kind: `class`
- Summary: Implements the GitHub OAuth2 provider contract.
- Responsibility: Supply GitHub endpoints, scopes, headers, error handling and normalized OAuth users.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `getBaseAuthorizationUrl()` | `public` | Returns the GitHub authorization endpoint. | Returns the GitHub authorization endpoint. |
| `getBaseAccessTokenUrl()` | `public` | Returns the GitHub token exchange endpoint. | Returns the GitHub token exchange endpoint. |
| `getResourceOwnerDetailsUrl()` | `public` | Returns the GitHub user profile endpoint. | Returns the GitHub user profile endpoint. |
| `getDefaultScopes()` | `protected` | Provides the default GitHub scopes required for user email access. | Provides the default GitHub scopes required for user email access. |
| `getScopeSeparator()` | `protected` | Uses the GitHub-supported space separator for OAuth scopes. | Uses the GitHub-supported space separator for OAuth scopes. |
| `getDefaultHeaders()` | `protected` | Provides default GitHub API headers for resource-owner requests. | Provides default GitHub API headers for resource-owner requests. |
| `checkResponse()` | `protected` | Converts GitHub OAuth and API errors into identity-provider exceptions. | Converts GitHub OAuth and API errors into identity-provider exceptions. |
| `createResourceOwner()` | `protected` | Wraps the GitHub resource-owner response in the framework OAuth user type. | Wraps the GitHub resource-owner response in the framework OAuth user type. |

### `Catalyst\Framework\Auth\OAuth\GoogleProvider`

- File: `app/Framework/Auth/OAuth/GoogleProvider.php`
- Kind: `class`
- Summary: Implements the Google OAuth2 provider contract.
- Responsibility: Supply Google endpoints, scopes, error handling and normalized OAuth users.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `getBaseAuthorizationUrl()` | `public` | Returns the Google authorization endpoint. | Returns the Google authorization endpoint. |
| `getBaseAccessTokenUrl()` | `public` | Returns the Google token exchange endpoint. | Returns the Google token exchange endpoint. |
| `getResourceOwnerDetailsUrl()` | `public` | Returns the Google user-info endpoint. | Returns the Google user-info endpoint. |
| `getDefaultScopes()` | `protected` | Provides the default Google scopes required for identity and email. | Provides the default Google scopes required for identity and email. |
| `getScopeSeparator()` | `protected` | Uses the Google-required space separator for OAuth scopes. | Uses the Google-required space separator for OAuth scopes. |
| `checkResponse()` | `protected` | Converts Google error payloads into identity-provider exceptions. | Converts Google error payloads into identity-provider exceptions. |
| `createResourceOwner()` | `protected` | Wraps the Google resource-owner response in the framework OAuth user type. | Wraps the Google resource-owner response in the framework OAuth user type. |

### `Catalyst\Framework\Auth\OAuth\OAuthUser`

- File: `app/Framework/Auth/OAuth/OAuthUser.php`
- Kind: `class`
- Summary: Normalizes OAuth provider resource-owner payloads.
- Responsibility: Expose provider identity, display name, email and raw payload through one resource-owner interface.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `__construct()` | `public` | Initializes the object with the collaborators or state required for its responsibility. | Initializes the object with the collaborators or state required for its responsibility. |
| `getId()` | `public` | Returns the provider's unique user ID. | Returns the provider's unique user ID. |
| `getEmail()` | `public` | Returns the user's email address. | Returns the user's email address. |
| `getName()` | `public` | Returns the user's display name. | Returns the user's display name. |
| `getProvider()` | `public` | Returns the provider identifier. | Returns the provider identifier. |
| `toArray()` | `public` | Returns the complete raw provider response payload. | Returns the complete raw provider response payload. |

### `Catalyst\Framework\Auth\RememberMe`

- File: `app/Framework/Auth/RememberMe.php`
- Kind: `class`
- Summary: Manages persistent login cookies backed by hashed remember tokens.
- Responsibility: Issue, resolve and invalidate remember-me tokens without storing raw token values.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `__construct()` | `protected` | Initializes database and logging collaborators for remember-token storage. | Initializes database and logging collaborators for remember-token storage. |
| `create()` | `public` | Create a remember-me token for a user and set the cookie. | Create a remember-me token for a user and set the cookie. |
| `resolve()` | `public` | Resolve a remember-me cookie to a user ID. Returns the user_id if the token is active and not expired, otherwise null. | Resolve a remember-me cookie to a user ID. Returns the user_id if the token is active and not expired, otherwise null. |
| `invalidate()` | `public` | Invalidate all active remember-me tokens for a user. Sets active=0 — never physically deletes rows. | Invalidate all active remember-me tokens for a user. Sets active=0 — never physically deletes rows. |
| `hasToken()` | `public` | Check whether a remember-me cookie is present on the request. | Check whether a remember-me cookie is present on the request. |
| `clearCookie()` | `private` | Delete the remember-me cookie from the client. | Delete the remember-me cookie from the client. |
| `isSecureRequest()` | `private` | Detects HTTPS requests, including common reverse-proxy headers, for secure cookies. | Detects HTTPS requests, including common reverse-proxy headers, for secure cookies. |

### `Catalyst\Framework\Auth\TokenRepository`

- File: `app/Framework/Auth/TokenRepository.php`
- Kind: `class`
- Summary: Stores email-verification and password-reset tokens as one-time hashes.
- Responsibility: Create, consume and invalidate user recovery tokens without persisting raw token values.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `__construct()` | `protected` | Initializes database and logging collaborators for token storage. | Initializes database and logging collaborators for token storage. |
| `createVerificationToken()` | `public` | Create a new email-verification token for a user. Invalidates any existing active tokens for that user first. | Create a new email-verification token for a user. Invalidates any existing active tokens for that user first. |
| `consumeVerificationToken()` | `public` | Consume an email-verification token. Returns the user_id on success, null if invalid/expired/already used. | Consume an email-verification token. Returns the user_id on success, null if invalid/expired/already used. |
| `createPasswordResetToken()` | `public` | Create a new password-reset token for a user. Invalidates any existing active tokens for that user first. | Create a new password-reset token for a user. Invalidates any existing active tokens for that user first. |
| `consumePasswordResetToken()` | `public` | Consume a password-reset token. Returns the user_id on success, null if invalid/expired/already used. | Consume a password-reset token. Returns the user_id on success, null if invalid/expired/already used. |
| `invalidatePrevious()` | `private` | Invalidate all active tokens for a user in the given table. | Invalidate all active tokens for a user in the given table. |

### `Catalyst\Framework\Auth\UserDirectoryRepository`

- File: `app/Framework/Auth/UserDirectoryRepository.php`
- Kind: `class`
- Summary: Read-side repository for administration surfaces that need user directory data.
- Responsibility: Provide tenant-scoped user summaries, select options and admin listings.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `__construct()` | `protected` | Initializes database and logging collaborators for user directory reads. | Initializes database and logging collaborators for user directory reads. |
| `findActiveSummary()` | `public` | Returns a compact active-user summary for display and lookup surfaces. | Returns a compact active-user summary for display and lookup surfaces. |
| `activeUserOptions()` | `public` | Builds active-user select options for tenant-scoped forms. | Builds active-user select options for tenant-scoped forms. |
| `searchAdminUsers()` | `public` | Searches users for administration grids with filters, roles and pagination. | Searches users for administration grids with filters, roles and pagination. |
| `resolveUserSort()` | `private` | Restricts requested user sort columns to safe SQL expressions. | Restricts requested user sort columns to safe SQL expressions. |
| `resolveUserDirection()` | `private` | Normalizes requested user sort direction to SQL ASC or DESC. | Normalizes requested user sort direction to SQL ASC or DESC. |
| `currentTenantId()` | `private` | Resolves the required tenant identifier for user directory queries. | Resolves the required tenant identifier for user directory queries. |

### `Catalyst\Framework\Auth\UserProvider`

- File: `app/Framework/Auth/UserProvider.php`
- Kind: `class`
- Summary: Provides user lookup and mutation operations used by authentication flows.
- Responsibility: Resolve users, manage credentials, link OAuth accounts and persist MFA state.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `__construct()` | `protected` | Initializes database and logging collaborators for user auth storage. | Initializes database and logging collaborators for user auth storage. |
| `findByEmail()` | `public` | Find an active, email-verified user by email address. | Find an active, email-verified user by email address. |
| `findById()` | `public` | Find an active user by ID (does not require email_verified). | Find an active user by ID (does not require email_verified). |
| `findByEmailAny()` | `public` | Find a user by email regardless of verified/active status (used during registration). | Find a user by email regardless of verified/active status (used during registration). |
| `bcryptCost()` | `private` | Read bcrypt cost from security.json (primary) or default to 12. Clamped to PHP's supported range (4–31); practical minimum is 10. | Read bcrypt cost from security.json (primary) or default to 12. Clamped to PHP's supported range (4–31); practical minimum is 10. |
| `verifyPassword()` | `public` | Verify a plain-text password against a stored bcrypt hash. | Verify a plain-text password against a stored bcrypt hash. |
| `updateLastLogin()` | `public` | Update last_login timestamp for the given user. | Update last_login timestamp for the given user. |
| `create()` | `public` | Create a new user and return their ID. | Create a new user and return their ID. |
| `updatePassword()` | `public` | Update password for a user. | Update password for a user. |
| `markEmailVerified()` | `public` | Mark a user as email-verified. | Mark a user as email-verified. |
| `linkSocialAccount()` | `public` | Link a social provider account to an existing user. Uses active=1; never physically deletes rows. | Link a social provider account to an existing user. Uses active=1; never physically deletes rows. |
| `findBySocialAccount()` | `public` | Find a user by social provider account (active only). | Find a user by social provider account (active only). |
| `getMfaData()` | `public` | Return the MFA fields for a user (mfa_secret, mfa_enabled, mfa_backup_codes). Returns null if the user doesn't exist. | Return the MFA fields for a user (mfa_secret, mfa_enabled, mfa_backup_codes). Returns null if the user doesn't exist. |
| `enableMfa()` | `public` | Activate MFA for a user: store the confirmed secret and backup codes. | Activate MFA for a user: store the confirmed secret and backup codes. |
| `disableMfa()` | `public` | Deactivate MFA for a user: clear secret and backup codes. | Deactivate MFA for a user: clear secret and backup codes. |
| `updateMfaBackupCodes()` | `public` | Persist an updated backup-codes list after one has been consumed. | Persist an updated backup-codes list after one has been consumed. |
| `currentTenantId()` | `private` | Resolves the required tenant identifier for user authentication queries. | Resolves the required tenant identifier for user authentication queries. |

### `Catalyst\Framework\Authorization\AbilitySubject`

- File: `app/Framework/Authorization/AbilitySubject.php`
- Kind: `class`
- Summary: Carries the resource, record, and context used by resource authorization policies.
- Responsibility: Represents the subject passed to resource-level authorization checks.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `__construct()` | `public` | Stores the target resource, optional record, and contextual authorization data. | Stores the target resource, optional record, and contextual authorization data. |
| `make()` | `public` | Builds an authorization subject for a resource ability check. | n/a |
| `resource()` | `public` | Returns the canonical resource name being authorized. | Returns the canonical resource name being authorized. |
| `record()` | `public` | Returns the optional record attached to the authorization subject. | Returns the optional record attached to the authorization subject. |
| `context()` | `public` | Returns additional data used by permission condition matching. | Returns additional data used by permission condition matching. |

### `Catalyst\Framework\Authorization\Gate`

- File: `app/Framework/Authorization/Gate.php`
- Kind: `class`
- Summary: Resolves named gates and model policies for the current or scoped user.
- Responsibility: Evaluates authorization abilities through registered closures and policy classes.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `define()` | `public` | Registers a closure callback for a named ability. | Registers a closure callback for a named ability. |
| `policy()` | `public` | Registers the policy class responsible for a model class. | Registers the policy class responsible for a model class. |
| `allows()` | `public` | Checks whether the resolved user is allowed to perform an ability. | Checks whether the resolved user is allowed to perform an ability. |
| `denies()` | `public` | Checks whether the resolved user is denied an ability. | Checks whether the resolved user is denied an ability. |
| `authorize()` | `public` | Enforces an ability and raises a forbidden exception when it is denied. | Enforces an ability and raises a forbidden exception when it is denied. |
| `forUser()` | `public` | Returns a cloned gate instance scoped to an explicit user payload. | Returns a cloned gate instance scoped to an explicit user payload. |
| `resolveUser()` | `private` | Resolves the explicit scoped user or the authenticated session user. | Resolves the explicit scoped user or the authenticated session user. |
| `check()` | `private` | Evaluates an ability through a registered gate closure or matching policy. | Evaluates an ability through a registered gate closure or matching policy. |
| `findPolicyForArg()` | `private` | Finds the registered policy class for an object, class string, parent, or interface. | Finds the registered policy class for an object, class string, parent, or interface. |
| `callPolicy()` | `private` | Instantiates a policy and evaluates its before hook and ability method. | Instantiates a policy and evaluates its before hook and ability method. |

### `Catalyst\Framework\Authorization\PermissionRegistry`

- File: `app/Framework/Authorization/PermissionRegistry.php`
- Kind: `class`
- Summary: Loads permission definitions and evaluates role, permission, and resource abilities.
- Responsibility: Bridges module permission metadata with Gate and RoleRepository checks.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `all()` | `public` | Returns all module-declared permission definitions cached for the request. | Returns all module-declared permission definitions cached for the request. |
| `flushCache()` | `public` | Clears cached permission definitions so module metadata can be reloaded. | Clears cached permission definitions so module metadata can be reloaded. |
| `forModule()` | `public` | Returns permission definitions declared by a module key. | Returns permission definitions declared by a module key. |
| `find()` | `public` | Finds a permission definition by slug. | Finds a permission definition by slug. |
| `registerGateDefinitions()` | `public` | Registers permission slugs and resource policies on the given gate instance. | Registers permission slugs and resource policies on the given gate instance. |
| `userHasRole()` | `public` | Checks whether the resolved user has a specific role slug. | Checks whether the resolved user has a specific role slug. |
| `userHasAnyRole()` | `public` | Checks whether the resolved user has at least one role slug. | Checks whether the resolved user has at least one role slug. |
| `userHasPermission()` | `public` | Checks whether the resolved user has a permission and satisfies its conditions. | Checks whether the resolved user has a permission and satisfies its conditions. |
| `userHasAnyPermission()` | `public` | Checks whether the resolved user has at least one permission slug. | Checks whether the resolved user has at least one permission slug. |
| `userHasResourceAbility()` | `public` | Checks whether the resolved user has a permission matching a resource ability. | Checks whether the resolved user has a permission matching a resource ability. |
| `resourceAbilityDefinitions()` | `public` | Returns permission definitions matching a resource and ability pair. | Returns permission definitions matching a resource and ability pair. |
| `matchesConditions()` | `private` | Validates record ownership, state, and delegated policy constraints. | Validates record ownership, state, and delegated policy constraints. |
| `definitionMatchesResource()` | `private` | Checks whether a permission definition applies to the requested resource. | Checks whether a permission definition applies to the requested resource. |
| `definitionMatchesAbility()` | `private` | Checks whether a permission definition applies to the requested ability. | Checks whether a permission definition applies to the requested ability. |
| `abilityActionAliases()` | `private` | Returns action aliases accepted for a generic resource ability. | Returns action aliases accepted for a generic resource ability. |
| `resolveUserId()` | `private` | Resolves the numeric user ID from an authorization user payload. | Resolves the numeric user ID from an authorization user payload. |
| `extractValue()` | `private` | Extracts a field value from an array, object property, or getter method. | Extracts a field value from an array, object property, or getter method. |

### `Catalyst\Framework\Authorization\Policy`

- File: `app/Framework/Authorization/Policy.php`
- Kind: `class`
- Summary: Provides the base hook for policy-based authorization decisions.
- Responsibility: Lets concrete policies short-circuit ability checks before can* methods run.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `before()` | `public` | Optionally grants, denies, or defers an ability before the concrete policy method runs. | Optionally grants, denies, or defers an ability before the concrete policy method runs. |

### `Catalyst\Framework\Authorization\RbacAuditLogger`

- File: `app/Framework/Authorization/RbacAuditLogger.php`
- Kind: `class`
- Summary: Writes RBAC mutation entries to the framework audit log.
- Responsibility: Normalizes role and permission changes into audit operations.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `record()` | `public` | Records a role, permission, or assignment mutation with before and after state. | Records a role, permission, or assignment mutation with before and after state. |

### `Catalyst\Framework\Authorization\RbacCacheInvalidator`

- File: `app/Framework/Authorization/RbacCacheInvalidator.php`
- Kind: `class`
- Summary: Invalidates in-memory and persistent RBAC assignment caches.
- Responsibility: Clears user-scoped and global cache entries after RBAC mutations.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `flushAll()` | `public` | Clears all request-local RBAC cache entries and advances the persistent cache version. | Clears all request-local RBAC cache entries and advances the persistent cache version. |
| `flushUser()` | `public` | Clears request-local and persistent RBAC cache entries for a single user. | Clears request-local and persistent RBAC cache entries for a single user. |

### `Catalyst\Framework\Authorization\RbacSortResolver`

- File: `app/Framework/Authorization/RbacSortResolver.php`
- Kind: `class`
- Summary: Resolves safe sort columns and directions for RBAC listing queries.
- Responsibility: Constrains user-provided sort options to repository-approved SQL fragments.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `column()` | `public` | Resolves the requested sort key to an allowed SQL column. | Resolves the requested sort key to an allowed SQL column. |
| `direction()` | `public` | Normalizes the requested sort direction to ASC or DESC. | Normalizes the requested sort direction to ASC or DESC. |

### `Catalyst\Framework\Authorization\ResourcePolicy`

- File: `app/Framework/Authorization/ResourcePolicy.php`
- Kind: `class`
- Summary: Maps generic resource abilities to permission registry checks.
- Responsibility: Authorizes AbilitySubject instances through resource permission definitions.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `canViewAny()` | `public` | Checks whether the user can view a resource collection. | Checks whether the user can view a resource collection. |
| `canView()` | `public` | Checks whether the user can view a resource record. | Checks whether the user can view a resource record. |
| `canCreate()` | `public` | Checks whether the user can create a resource record. | Checks whether the user can create a resource record. |
| `canUpdate()` | `public` | Checks whether the user can update a resource record. | Checks whether the user can update a resource record. |
| `canDelete()` | `public` | Checks whether the user can delete a resource record. | Checks whether the user can delete a resource record. |
| `canRestore()` | `public` | Checks whether the user can restore a resource record. | Checks whether the user can restore a resource record. |
| `canExport()` | `public` | Checks whether the user can export resource data. | Checks whether the user can export resource data. |
| `canRun()` | `public` | Checks whether the user can run a resource operation. | Checks whether the user can run a resource operation. |
| `canRevoke()` | `public` | Checks whether the user can revoke a resource credential or grant. | Checks whether the user can revoke a resource credential or grant. |
| `canBulkDelete()` | `public` | Checks whether the user can bulk delete resource records. | Checks whether the user can bulk delete resource records. |
| `canBulkRestore()` | `public` | Checks whether the user can bulk restore resource records. | Checks whether the user can bulk restore resource records. |
| `canAssign()` | `public` | Checks whether the user can assign a resource relationship. | Checks whether the user can assign a resource relationship. |
| `canSync()` | `public` | Checks whether the user can synchronize resource data. | Checks whether the user can synchronize resource data. |
| `canManage()` | `public` | Checks whether the user can manage a resource. | Checks whether the user can manage a resource. |
| `allows()` | `private` | Delegates a resource ability decision to the permission registry. | Delegates a resource ability decision to the permission registry. |

### `Catalyst\Framework\Authorization\RoleRepository`

- File: `app/Framework/Authorization/RoleRepository.php`
- Kind: `class`
- Summary: Manages tenant-scoped roles, permissions, assignments, RBAC cache, and audit entries.
- Responsibility: Provides the database boundary for role and permission reads and mutations.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `__construct()` | `protected` | Initializes database, logging, cache invalidation, audit, and sort collaborators. | Initializes database, logging, cache invalidation, audit, and sort collaborators. |
| `conn()` | `private` | Returns the active database connection used by RBAC queries. | Returns the active database connection used by RBAC queries. |
| `getUserRoles()` | `public` | Returns tenant-scoped roles assigned to a user with request and persistent caching. | Returns tenant-scoped roles assigned to a user with request and persistent caching. |
| `getUserPermissions()` | `public` | Returns tenant-scoped permissions inherited through a user's roles. | Returns tenant-scoped permissions inherited through a user's roles. |
| `userHasRole()` | `public` | Checks whether a user has a specific role slug. | Checks whether a user has a specific role slug. |
| `userHasAnyRole()` | `public` | Checks whether a user has at least one role slug. | Checks whether a user has at least one role slug. |
| `userHasPermission()` | `public` | Checks whether a user has a specific permission slug. | Checks whether a user has a specific permission slug. |
| `userHasAnyPermission()` | `public` | Checks whether a user has at least one permission slug. | Checks whether a user has at least one permission slug. |
| `allRoles()` | `public` | Returns all roles for the current tenant. | Returns all roles for the current tenant. |
| `createRole()` | `public` | Creates a role for the current tenant and records the mutation. | Creates a role for the current tenant and records the mutation. |
| `updateRole()` | `public` | Updates a role for the current tenant and records before and after state. | Updates a role for the current tenant and records before and after state. |
| `deleteRole()` | `public` | Deletes a role from the current tenant and records the removed state. | Deletes a role from the current tenant and records the removed state. |
| `findRole()` | `public` | Finds a role by ID within the current tenant. | Finds a role by ID within the current tenant. |
| `findRoleBySlug()` | `public` | Finds a role by slug within the current tenant. | Finds a role by slug within the current tenant. |
| `searchRoles()` | `public` | Searches current-tenant roles using filters, pagination, and safe sorting. | Searches current-tenant roles using filters, pagination, and safe sorting. |
| `allPermissions()` | `public` | Returns all permissions for the current tenant. | Returns all permissions for the current tenant. |
| `createPermission()` | `public` | Creates a permission for the current tenant and records the mutation. | Creates a permission for the current tenant and records the mutation. |
| `updatePermission()` | `public` | Updates a permission for the current tenant and records before and after state. | Updates a permission for the current tenant and records before and after state. |
| `deletePermission()` | `public` | Deletes a permission from the current tenant and records the removed state. | Deletes a permission from the current tenant and records the removed state. |
| `findPermission()` | `public` | Finds a permission by ID within the current tenant. | Finds a permission by ID within the current tenant. |
| `searchPermissions()` | `public` | Searches current-tenant permissions using filters, pagination, and safe sorting. | Searches current-tenant permissions using filters, pagination, and safe sorting. |
| `permissionPrefixes()` | `public` | Returns unique permission slug prefixes available in the current tenant. | Returns unique permission slug prefixes available in the current tenant. |
| `getRolePermissions()` | `public` | Returns permissions assigned to a role within the current tenant. | Returns permissions assigned to a role within the current tenant. |
| `assignPermissionToRole()` | `public` | Assigns a permission to a role and records the assignment. | Assigns a permission to a role and records the assignment. |
| `removePermissionFromRole()` | `public` | Removes a permission from a role and records the removed assignment. | Removes a permission from a role and records the removed assignment. |
| `assignRoleToUser()` | `public` | Assigns a role to a user and clears that user's RBAC cache. | Assigns a role to a user and clears that user's RBAC cache. |
| `assignRoleSlugToUser()` | `public` | Resolves a role slug and assigns the matching role to a user. | Resolves a role slug and assigns the matching role to a user. |
| `removeRoleFromUser()` | `public` | Removes a role from a user and clears that user's RBAC cache. | Removes a role from a user and clears that user's RBAC cache. |
| `clearCache()` | `public` | Clears all in-memory and persistent RBAC assignment caches. | Clears all in-memory and persistent RBAC assignment caches. |
| `clearUserCache()` | `public` | Clears in-memory and persistent RBAC assignment caches for one user. | Clears in-memory and persistent RBAC assignment caches for one user. |
| `persistentCacheKey()` | `private` | Builds a tenant-scoped persistent cache key for user RBAC assignments. | Builds a tenant-scoped persistent cache key for user RBAC assignments. |
| `persistentCacheVersion()` | `private` | Returns the persistent RBAC cache version, initializing it when absent. | Returns the persistent RBAC cache version, initializing it when absent. |
| `memoryCacheKey()` | `private` | Builds a tenant-scoped request-memory cache key for user RBAC assignments. | Builds a tenant-scoped request-memory cache key for user RBAC assignments. |
| `currentTenantId()` | `private` | Returns the active tenant ID required for RBAC queries. | Returns the active tenant ID required for RBAC queries. |

## Operational Notes

When PHP symbols or method contracts in this namespace change, refresh this document from docblocks and run `php public/cli.php docs:inventory --json`.

## Related Documentation

- `docs/runtime-inventory.md`
- `docs/runtime-module-catalog.md`
- `docs/harness-context-map.md`
