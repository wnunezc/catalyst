# DevTools Deferred Debt

Decision: DevTools is intentionally out of scope for Phase 2 cleanup execution.

Current state:

- Runtime routes still exist under `/test-features`, `/uml`, and helper endpoints.
- Access is guarded by `DevToolsGuardMiddleware`.
- The module is known to be stale compared with the newer UI direction.

Rules:

- Do not delete DevTools in this phase.
- Do not refactor DevTools while cleaning Inspinia, branding, or configs.
- Revisit in a later dedicated phase focused on DevTools scope, routes, security, and UI.
