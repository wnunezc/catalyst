# Auth Index

This file is a thin navigation index for Catalyst's authentication surface.

It exists to satisfy the generic Phase 4 target while keeping the detailed auth story split between framework and repository layers.

## Canonical references

- Auth core, provider, remember-me, OAuth, MFA primitives: `docs/framework-auth.md`
- User-facing routes, controllers, and flows: `docs/repository-auth.md`
- Setup completion path that materializes the initial admin user: `docs/checklists/setup-completion-e2e.md`
- Runtime architecture and entry points: `docs/architecture.md`, `docs/entry-points.md`
- Full class dictionary: `STRUCTURE.md`

## Scope split

- `docs/framework-auth.md` covers the framework auth core.
- `docs/repository-auth.md` covers login, register, logout, password reset, verification, OAuth callbacks, and MFA-facing controllers.
- This file stays as the broad entry point for the generic `auth.md` target.

## Usage note

Use this file when a task starts from the broad label `auth`.
