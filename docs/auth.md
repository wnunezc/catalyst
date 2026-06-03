# Auth Index

## Purpose

Serve as the broad entry point for Catalyst authentication documentation.

## Runtime Owners

| Concern | Owner |
|---|---|
| Auth primitives | `Catalyst\Framework\Auth\AuthManager` |
| User provider | `Catalyst\Framework\Auth\UserProvider` |
| MFA runtime | `Catalyst\Framework\Auth\MfaManager` |
| OAuth runtime | `Catalyst\Framework\Auth\OAuthManager` |
| Auth routes/controllers | `Catalyst\Repository\Auth\Controllers\*` |

## Current Behavior

Authentication is split between framework primitives and repository-facing routes. Framework auth owns identity, session, remember-me, MFA and OAuth primitives. Repository auth owns `/login`, `/register`, `/logout`, reset password, email verification, social callbacks and MFA-facing controller flows.

## Operational Notes

Use `docs/framework-auth.md` for class and method contracts generated from PHP docblocks. Use `docs/repository-auth.md` for routed module behavior. Route truth comes from `php public/cli.php route:list --json` and `docs/runtime-module-catalog.md`.

## Related Documentation

- `docs/framework-auth.md`
- `docs/repository-auth.md`
- `docs/middleware.md`
- `docs/checklists/setup-completion-e2e.md`