# Middleware Index

This file is a thin navigation index for Catalyst's middleware surface.

It exists to satisfy the generic Phase 4 target while preserving the current split documentation model.

## Canonical references

- Middleware entry in the request lifecycle: `docs/kernel.md`
- Architectural placement and route loading context: `docs/architecture.md`
- CSP, headers, nonce, and frontend guardrails: `docs/security-conventions.md`
- Auth and access-control behavior: `docs/framework-auth.md`, `docs/repository-auth.md`
- Setup gating behavior: `docs/checklists/setup-completion-e2e.md`
- Full middleware namespace dictionary: `STRUCTURE.md`

## Scope note

Catalyst does not currently maintain a single deep-dive document for every middleware class.
The canonical story is split by behavior domain, and `STRUCTURE.md` remains the directory/class map for the full middleware tree.

## Usage note

Use this file when a task starts from the broad label `middleware`.
