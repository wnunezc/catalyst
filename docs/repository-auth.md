# Catalyst\Repository\Auth

## Purpose

Document framework Auth module controllers, model and request payload boundaries.

## Runtime Owners

| Concern | Owner |
|---|---|
| Validates verification tokens, consumes active token records, and marks matching users as verified. | `Catalyst\Repository\Auth\Controllers\EmailVerificationController` |
| Validates login input, protects account state checks, and creates either pending MFA state or a full session. | `Catalyst\Repository\Auth\Controllers\LoginController` |
| Invalidates the active auth state and returns the user to a same-origin destination with feedback. | `Catalyst\Repository\Auth\Controllers\LogoutController` |
| Enforces MFA access rules, provisions TOTP secrets, persists backup-code state, and completes pending logins. | `Catalyst\Repository\Auth\Controllers\MfaController` |
| Issues password reset emails without account enumeration and updates credentials after token validation. | `Catalyst\Repository\Auth\Controllers\PasswordResetController` |
| Validates registration input, creates unverified users, and sends one-time verification links. | `Catalyst\Repository\Auth\Controllers\RegisterController` |
| Starts provider authorization, validates callback data, links OAuth identities, and signs in local users. | `Catalyst\Repository\Auth\Controllers\SocialAuthController` |
| Represents authenticated application users for ORM reads and writes while hiding credential data. | `Catalyst\Repository\Auth\Models\User` |
| Normalizes token input and rejects values that cannot match a 64-character verification token. | `Catalyst\Repository\Auth\Requests\EmailVerificationTokenRequest` |
| Accepts TOTP codes and, when allowed, backup-code input before controllers verify the secret. | `Catalyst\Repository\Auth\Requests\MfaCodeRequest` |

## Current Behavior

This file is regenerated from current PHP docblocks and the runtime inventory scope for `Catalyst\Repository\Auth`. It intentionally replaces stale historical API notes with the classes and methods that exist in code now.

During a pending MFA login, a valid TOTP code is accepted only when its verified
timestep can be atomically consumed for the current tenant and account. Backup
codes retain their existing one-time consumption contract.

## API From Docblocks

### `Catalyst\Repository\Auth\Controllers\EmailVerificationController`

- File: `Repository/Framework/Auth/Controllers/EmailVerificationController.php`
- Kind: `class`
- Summary: Handles manual and link-based account email verification.
- Responsibility: Validates verification tokens, consumes active token records, and marks matching users as verified.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `showManualForm()` | `public` | Renders the guest form where users can paste a verification token manually. | Renders the guest form where users can paste a verification token manually. |
| `manualVerify()` | `public` | Validates the submitted manual verification token before consuming it. | Validates the submitted manual verification token before consuming it. |
| `verify()` | `public` | Validates a URL verification token and activates the matching account. | Validates a URL verification token and activates the matching account. |
| `consumeToken()` | `private` | Consumes a valid verification token, marks the account as verified, and redirects to login. | Consumes a valid verification token, marks the account as verified, and redirects to login. |

### `Catalyst\Repository\Auth\Controllers\LoginController`

- File: `Repository/Framework/Auth/Controllers/LoginController.php`
- Kind: `class`
- Summary: Handles credential login and MFA-aware authentication branching.
- Responsibility: Validates login input, protects account state checks, and creates either pending MFA state or a full session.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `showForm()` | `public` | Renders the guest login form with sanitized redirect and preserved email input. | Renders the guest login form with sanitized redirect and preserved email input. |
| `login()` | `public` | Validates credentials, enforces account status, and routes the user through MFA or session creation. | Validates credentials, enforces account status, and routes the user through MFA or session creation. |

### `Catalyst\Repository\Auth\Controllers\LogoutController`

- File: `Repository/Framework/Auth/Controllers/LogoutController.php`
- Kind: `class`
- Summary: Handles authenticated session termination.
- Responsibility: Invalidates the active auth state and returns the user to a same-origin destination with feedback.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `logout()` | `public` | Destroy the session and redirect back (or to / if Referer is absent). Public pages: stay on same page, show success toaster. Protected pages: AuthMiddleware will catch the next request and redirect to /login, where the toaster will render after that redirect. | Destroy the session and redirect back (or to / if Referer is absent). Public pages: stay on same page, show success toaster. Protected pages: AuthMiddleware will catch the next request and redirect to /login, where the toaster will render after that redirect. |

### `Catalyst\Repository\Auth\Controllers\MfaController`

- File: `Repository/Framework/Auth/Controllers/MfaController.php`
- Kind: `class`
- Summary: Handles MFA setup, recovery-code use, and login challenge completion.
- Responsibility: Enforces MFA access rules, provisions TOTP secrets, persists backup-code state, and completes pending logins.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `setup()` | `public` | Show the MFA setup page with a QR code to scan. Accessible in two modes: - Normal: user is already authenticated and wants to enable MFA. - Forced: MFA is globally required; user arrived here via login with no MFA configured yet (hasMfaSetupPending = true, no auth session). | Show the MFA setup page with a QR code to scan. Accessible in two modes: - Normal: user is already authenticated and wants to enable MFA. - Forced: MFA is globally required; user arrived here via login with no MFA configured yet (hasMfaSetupPending = true, no auth session). |
| `enable()` | `public` | Confirm the first TOTP code and permanently activate MFA. If the user arrived via the forced-setup flow (hasMfaSetupPending), the full login session is created here after successful activation. | Confirm the first TOTP code and permanently activate MFA. If the user arrived via the forced-setup flow (hasMfaSetupPending), the full login session is created here after successful activation. |
| `disable()` | `public` | Disable MFA after verifying the user's current password. | Disable MFA after verifying the user's current password. |
| `challenge()` | `public` | Show the MFA challenge form during a pending login. | Show the MFA challenge form during a pending login. |
| `verify()` | `public` | Verify TOTP code (or backup code) and complete the pending login session. | Verify TOTP code (or backup code) and complete the pending login session. |
| `isMfaGloballyEnabled()` | `private` | True when the framework-level MFA toggle is on in security.json. | n/a |
| `resolveUser()` | `private` | Resolve the user row from either the active session or the forced-setup pending state. | Resolve the user row from either the active session or the forced-setup pending state. |
| `resolveIssuer()` | `private` | Resolve the application name for the otpauth:// URI issuer field. | Resolve the application name for the otpauth:// URI issuer field. |

### `Catalyst\Repository\Auth\Controllers\PasswordResetController`

- File: `Repository/Framework/Auth/Controllers/PasswordResetController.php`
- Kind: `class`
- Summary: Handles forgot-password and reset-token credential replacement.
- Responsibility: Issues password reset emails without account enumeration and updates credentials after token validation.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `showRequestForm()` | `public` | Show the forgot-password form. | Show the forgot-password form. |
| `sendResetLink()` | `public` | Send a password-reset link to the given email address. Always returns a success message to prevent user enumeration. | Send a password-reset link to the given email address. Always returns a success message to prevent user enumeration. |
| `showResetForm()` | `public` | Show the password-reset form for a given token. | Show the password-reset form for a given token. |
| `reset()` | `public` | Apply the new password if the token is valid. | Apply the new password if the token is valid. |
| `sendResetEmail()` | `private` | Send the password-reset email. | Send the password-reset email. |
| `resolveAppUrl()` | `private` | Resolves the public application URL used to build password-reset links. | Resolves the public application URL used to build password-reset links. |

### `Catalyst\Repository\Auth\Controllers\RegisterController`

- File: `Repository/Framework/Auth/Controllers/RegisterController.php`
- Kind: `class`
- Summary: Handles self-service account registration and email verification delivery.
- Responsibility: Validates registration input, creates unverified users, and sends one-time verification links.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `showForm()` | `public` | Show the registration form. | Show the registration form. |
| `register()` | `public` | Process registration and send email verification link. | Process registration and send email verification link. |
| `sendVerificationEmail()` | `private` | Send the email-verification message. | Send the email-verification message. |
| `resolveAppUrl()` | `private` | Resolves the public application URL used to build verification links. | Resolves the public application URL used to build verification links. |

### `Catalyst\Repository\Auth\Controllers\SocialAuthController`

- File: `Repository/Framework/Auth/Controllers/SocialAuthController.php`
- Kind: `class`
- Summary: Handles OAuth provider redirects and callbacks for social login.
- Responsibility: Starts provider authorization, validates callback data, links OAuth identities, and signs in local users.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `redirectToProvider()` | `public` | Redirect the user to the OAuth provider's authorization page. | Redirect the user to the OAuth provider's authorization page. |
| `callback()` | `public` | Handle the OAuth provider callback, find or create the user, and log them in. | Handle the OAuth provider callback, find or create the user, and log them in. |

### `Catalyst\Repository\Auth\Models\User`

- File: `Repository/Framework/Auth/Models/User.php`
- Kind: `class`
- Summary: User entity — maps to the `users` table.
- Responsibility: Represents authenticated application users for ORM reads and writes while hiding credential data.

### `Catalyst\Repository\Auth\Requests\EmailVerificationTokenRequest`

- File: `Repository/Framework/Auth/Requests/EmailVerificationTokenRequest.php`
- Kind: `class`
- Summary: Validates manually submitted email verification tokens.
- Responsibility: Normalizes token input and rejects values that cannot match a 64-character verification token.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `only()` | `public` | Limits validation data to the pasted verification token. | Limits validation data to the pasted verification token. |
| `rules()` | `public` | Requires the verification token field and constrains its accepted length. | Requires the verification token field and constrains its accepted length. |
| `labels()` | `public` | Provides the translated field label used in validation feedback. | Provides the translated field label used in validation feedback. |
| `validationMessage()` | `public` | Returns the generic message displayed when verification token validation fails. | Returns the generic message displayed when verification token validation fails. |
| `validated()` | `public` | Returns normalized data, resolving validation once when needed. | Returns normalized data, resolving validation once when needed. |
| `validateResolved()` | `public` | Authorizes and validates the request, then stores the normalized token payload. | Authorizes and validates the request, then stores the normalized token payload. |
| `validationData()` | `protected` | Builds validation data from the request using the trimmed token value. | Builds validation data from the request using the trimmed token value. |
| `tokenErrors()` | `private` | Adds token format errors after the base validator confirms a value is present. | Adds token format errors after the base validator confirms a value is present. |
| `isWellFormedToken()` | `public` | Checks that the token is exactly 64 hexadecimal characters. | n/a |
| `normalizeToken()` | `private` | Trims surrounding whitespace from a submitted verification token. | Trims surrounding whitespace from a submitted verification token. |

### `Catalyst\Repository\Auth\Requests\MfaCodeRequest`

- File: `Repository/Framework/Auth/Requests/MfaCodeRequest.php`
- Kind: `class`
- Summary: Validates MFA challenge and setup confirmation codes.
- Responsibility: Accepts TOTP codes and, when allowed, backup-code input before controllers verify the secret.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `__construct()` | `public` | Captures whether backup-code format is allowed for this MFA request. | Captures whether backup-code format is allowed for this MFA request. |
| `only()` | `public` | Limits validation data to the MFA code field. | Limits validation data to the MFA code field. |
| `rules()` | `public` | Requires the MFA code field and constrains the accepted input length. | Requires the MFA code field and constrains the accepted input length. |
| `labels()` | `public` | Provides the translated field label used in validation feedback. | Provides the translated field label used in validation feedback. |
| `validationMessage()` | `public` | Returns the generic message displayed when MFA code validation fails. | Returns the generic message displayed when MFA code validation fails. |
| `validated()` | `public` | Returns normalized data, resolving validation once when needed. | Returns normalized data, resolving validation once when needed. |
| `validateResolved()` | `public` | Authorizes and validates the request, then stores the normalized MFA code payload. | Authorizes and validates the request, then stores the normalized MFA code payload. |
| `validationData()` | `protected` | Builds validation data from the request using the trimmed MFA code value. | Builds validation data from the request using the trimmed MFA code value. |
| `codeErrors()` | `private` | Rejects codes that match neither TOTP format nor an allowed backup-code format. | Rejects codes that match neither TOTP format nor an allowed backup-code format. |

## Operational Notes

When PHP symbols or method contracts in this namespace change, refresh this document from docblocks and run `php public/cli.php docs:inventory --json`.

## Related Documentation

- `docs/runtime-inventory.md`
- `docs/runtime-module-catalog.md`
- `docs/harness-context-map.md`
