# Documentation Debt Grounding Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Convert the open concerns about Catalyst shell terminology, framework flow, inline documentation and stale `/docs` material into bounded, reviewable documentation work without inventing modules or changing runtime behavior.

**Architecture:** This plan treats documentation as an auditable contract. It separates glossary clarification, surface-by-surface reasoning, inline PHP/JS contract comments, class documentation sync and generated inventory refreshes so each batch can be reviewed independently.

**Tech Stack:** PHP 8.4, JavaScript ES Modules, Catalyst CLI, Markdown documentation, PowerShell, Git.

---

## Operating Rules

- Do not create a `framework.shell` module.
- Treat `shell` as layout/chrome/composition terminology unless concrete runtime evidence proves otherwise.
- Do not mark any phase complete without explicit user confirmation.
- Do not edit `vendor/`.
- Do not add Composer dependencies.
- Prefer documentation and comments that explain contracts, invariants, ownership and data flow; avoid comments that narrate obvious code.
- Keep DevTools visual/layout internals deferred unless the user explicitly reopens that surface.
- Every batch must end with evidence: changed files, command output summary and remaining debt.

## Documentation Units

- Modify: `docs/documentation-contract.md`
  - Add explicit rules for glossary terms, inline comments and class documentation sync.
- Modify: `docs/harness-context-map.md`
  - Add routing guidance for shell/layout terminology and documentation debt work.
- Modify: `STRUCTURE.md`
  - Keep curated ownership and class map synchronized with touched public classes.
- Modify: `docs/runtime-inventory.md`
  - Refresh through `php public/cli.php docs:inventory`; do not hand-edit.
- Create: `docs/audits/documentation-debt/2026-06-02-shell-and-docs-grounding.md`
  - Record decisions, glossary, audit scope, files reviewed and unresolved debt.
- Modify as touched: `docs/framework-*.md`, `docs/repository-*.md`, `docs/ui/*.md`
  - Correct stale class-level or surface-level documentation only when the owning area is reviewed.
- Modify as touched: PHP files under `app/`, `Repository/`, `boot-core/`
  - Add class/function comments only at contract boundaries.
- Modify as touched: JS files under `Repository/**/front/script.js` and shared JS modules
  - Add concise module headers only where DOM/event/payload behavior matters.

## Task 1: Freeze The Shell Vocabulary

**Files:**
- Modify: `docs/documentation-contract.md`
- Modify: `docs/harness-context-map.md`
- Create: `docs/audits/documentation-debt/2026-06-02-shell-and-docs-grounding.md`

- [ ] **Step 1: Confirm no runtime module exists**

Run:

```powershell
rg -n "framework\.shell|['\"]shell['\"]|module.*shell|shell.*module" -S app Repository boot-core docs STRUCTURE.md API.md --glob "!vendor/**"
```

Expected:

- No `Repository/Framework/Shell/` module.
- No `framework.shell` manifest key.
- Matches should describe layouts, CSS classes, language keys, shell view models or shell assets.

- [ ] **Step 2: Add glossary decision**

Add a short section to `docs/documentation-contract.md`:

```markdown
## Glossary Decisions

- `shell` means layout/chrome/composition, such as admin shell, public shell,
  account shell or Inspinia-derived shell.
- `shell` is not a module or missing surface by itself.
- Before creating a module from terminology, verify runtime ownership through
  `module.php`, `routes.php`, `inspect:modules --json` and `route:list --json`.
```

- [ ] **Step 3: Add context-map routing**

Add one row to `docs/harness-context-map.md` under `Si tocas X, lee Y`:

```markdown
| shell visual, layout chrome, admin/public/account composition | `docs/documentation-contract.md`, `docs/framework-view.md`, `docs/security-conventions.md`, `STRUCTURE.md`, `boot-core/template/layouts/` |
```

- [ ] **Step 4: Record audit evidence**

Create `docs/audits/documentation-debt/2026-06-02-shell-and-docs-grounding.md` with:

```markdown
# Shell And Documentation Debt Grounding

Date: 2026-06-02

Status: terminology grounded / remediation batches pending.

## Decision

`shell` is Catalyst terminology for layout, chrome and visual composition. It
does not imply a missing `framework.shell` module.

## Evidence Commands

```powershell
rg -n "framework\.shell|['\"]shell['\"]|module.*shell|shell.*module" -S app Repository boot-core docs STRUCTURE.md API.md --glob "!vendor/**"
php public/cli.php inspect:modules --json
php public/cli.php route:list --json
```

## Scope Boundary

This document does not close the broader documentation debt. It only prevents
future workers from treating shell terminology as a module-creation signal.
```

- [ ] **Step 5: Verify**

Run:

```powershell
php public/cli.php docs:inventory --json
git diff --check
```

Expected:

- `docs:inventory --json` exits 0.
- `git diff --check` exits 0.

- [ ] **Step 6: Commit**

```powershell
git add docs/documentation-contract.md docs/harness-context-map.md docs/audits/documentation-debt/2026-06-02-shell-and-docs-grounding.md
git commit -m "ground shell terminology and documentation debt"
```

## Task 2: Build The Surface Documentation Matrix

**Files:**
- Create: `docs/audits/documentation-debt/2026-06-02-surface-documentation-matrix.md`
- Modify: `docs/documentation-contract.md`

- [ ] **Step 1: Generate current runtime references**

Run:

```powershell
php public/cli.php inspect:modules --json
php public/cli.php route:list --json
php public/cli.php docs:inventory --json
```

Expected:

- Module inventory exits 0.
- Route inventory exits 0.
- Documentation inventory exits 0.

- [ ] **Step 2: Create the matrix document**

Create a matrix with these columns:

```markdown
| Surface | Runtime Owner | Layout/Shell | Routes Reviewed | Controller Flow | Requests/Validation | Views/Templates | JS/CSS Assets | Docs State | Inline Docs State | Action |
|---|---|---|---:|---|---|---|---|---|---|---|
```

Include rows for:

- Framework: ApiPlatform, Audit, Auth, Automation, Catalogs, DemoUi, Documents, Media, Notification, Operations, Roles, Settings.
- App: Account, Dashboard, Home, Landing, Store.
- Deferred: DevTools.

- [ ] **Step 3: Define status vocabulary**

Use only these status values:

```markdown
- `verified`: reviewed against runtime and docs; no immediate change needed.
- `stale-docs`: runtime is acceptable but docs are stale.
- `inline-docs-needed`: behavior is valid but contract comments are missing.
- `flow-risk`: responsibility, validation, ownership or duplication needs review.
- `planned`: debt is already covered by a plan.
- `deferred`: intentionally outside current scope.
```

- [ ] **Step 4: Link previous evidence**

Link existing evidence instead of rewriting it:

- `docs/audits/architecture-first/2026-06-01-6a1-bootstrap-routing-middleware.md`
- `docs/audits/architecture-first/2026-06-01-6a2-framework-modules.md`
- `docs/audits/architecture-first/2026-06-01-6a3-app-surfaces.md`
- `docs/audits/architecture-first/2026-06-01-6a4-documentation-debt.md`
- `docs/audits/architecture-first/2026-06-02-6c3-residual-batch-plan.md`
- `docs/audits/architecture-first/2026-06-02-6d1-documentation-contract.md`
- `docs/audits/architecture-first/2026-06-02-6d2-js-contracts.md`
- `docs/audits/architecture-first/2026-06-02-6d3-template-migration-plan.md`
- `docs/audits/architecture-first/2026-06-02-6d4-inline-asset-extraction-plan.md`

- [ ] **Step 5: Verify**

Run:

```powershell
php public/cli.php quality:check
git diff --check
```

Expected:

- Quality gate exits 0, allowing the known host Windows DB DNS warning if it appears.
- Diff whitespace check exits 0.

- [ ] **Step 6: Commit**

```powershell
git add docs/documentation-contract.md docs/audits/documentation-debt/2026-06-02-surface-documentation-matrix.md
git commit -m "add surface documentation matrix"
```

## Task 3: Define PHP Inline Documentation Standards

**Files:**
- Modify: `docs/documentation-contract.md`
- Create: `docs/audits/documentation-debt/2026-06-02-php-inline-doc-standards.md`

- [ ] **Step 1: Define what must be commented**

Add this rule:

```markdown
## PHP Inline Contract Comments

Add class-level or method-level comments only for:

- bootstrap, routing, cache and middleware invariants;
- public service and repository contracts;
- security-sensitive auth, setup, storage, export and trusted HTML/JSON flows;
- queue, event, scheduler and async payload schemas;
- request validation objects where accepted input differs from raw HTTP input;
- view-model factories where layout payload shape is non-obvious.

Do not add comments that merely repeat method names, property names or obvious
control flow.
```

- [ ] **Step 2: Define comment shape**

Add:

```markdown
Use this PHP comment shape for non-obvious classes:

```php
/**
 * Defines the contract boundary for <subsystem>.
 *
 * Owns <responsibility>. Does not own <explicit non-responsibility>.
 * Inputs are <input contract>; outputs are <output contract>.
 */
```
```

- [ ] **Step 3: Define priority order**

Document priority:

```markdown
1. `app/Kernel.php`, routing, route cache and middleware.
2. Auth, setup, CSRF, throttling, trusted HTML/JSON and export boundaries.
3. Public repositories and service classes consumed across modules.
4. Request classes and payload normalizers.
5. Module-specific controllers only when orchestration is easy to misread.
```

- [ ] **Step 4: Verify**

Run:

```powershell
php public/cli.php docs:inventory --json
git diff --check
```

- [ ] **Step 5: Commit**

```powershell
git add docs/documentation-contract.md docs/audits/documentation-debt/2026-06-02-php-inline-doc-standards.md
git commit -m "define php inline documentation standards"
```

## Task 4: Define JavaScript Documentation Standards

**Files:**
- Modify: `docs/documentation-contract.md`
- Create: `docs/audits/documentation-debt/2026-06-02-js-contract-standards.md`

- [ ] **Step 1: Define JS header contract**

Add:

```markdown
## JavaScript Contract Headers

Non-trivial JS modules should declare:

- initialization trigger;
- DOM selectors or `data-*` hooks consumed;
- events consumed and emitted;
- expected JSON payload shape;
- CSP assumptions;
- ownership boundary.

Tiny loaders do not need a header unless they bridge a security, payload or
layout contract.
```

- [ ] **Step 2: Define JS header example**

Add:

```javascript
/**
 * Owns the <module> browser behavior for <surface>.
 *
 * Initializes from <event/selector>. Consumes <data attributes/payload>.
 * Emits <events/no events>. Keeps behavior external to templates for CSP.
 */
```

- [ ] **Step 3: Verify current JS count**

Run:

```powershell
php public/cli.php docs:inventory --json
rg -n "^/\\*\\*|Contract|Owns|Initializes|CSP" Repository -g "script.js"
```

Expected:

- `docs:inventory --json` exits 0.
- Search output identifies current scripts with useful headers; no runtime change is required in this task.

- [ ] **Step 4: Commit**

```powershell
git add docs/documentation-contract.md docs/audits/documentation-debt/2026-06-02-js-contract-standards.md
git commit -m "define javascript contract documentation standards"
```

## Task 5: Review One Surface At A Time

**Files:**
- Modify as needed: owning `docs/framework-*.md` or `docs/repository-*.md`
- Modify as needed: owning PHP/JS files for contract comments
- Modify as needed: `STRUCTURE.md`
- Refresh: `docs/runtime-inventory.md`
- Create per surface: `docs/audits/documentation-debt/YYYY-MM-DD-<surface>-documentation-review.md`

- [ ] **Step 1: Choose a single surface**

Start with one of:

```markdown
1. Auth
2. Settings
3. Operations
4. Roles
5. Media
6. Automation
7. Documents
8. Account
```

Do not mix surfaces in one batch.

- [ ] **Step 2: Read the required files**

For each surface, read:

```powershell
Get-Content <owning docs file>
Get-Content <module.php if present>
Get-Content <routes.php>
rg -n "class |function |extends |implements " <surface directory> -S
rg -n "view\\(|trustedHtml|InlineJson|Request|DataGrid|FormBuilder|FrontResourceTrait" <surface directory> -S
```

- [ ] **Step 3: Write review evidence**

Each surface review must include:

```markdown
## Flow

- Entry routes:
- Middleware/guards:
- Controller orchestration:
- Request validation:
- Services/repositories:
- Views/templates:
- Front assets:
- Layout/shell:

## Problems Found

| Severity | Problem | File | Reasoning | Proposed Action |
|---|---|---|---|---|

## Documentation Actions

| File | Action | Why |
|---|---|---|

## Deferred Items

- Item:
- Reason:
- Required user decision:
```

- [ ] **Step 4: Add only necessary inline comments**

For each touched PHP or JS file, add comments only where the next maintainer could misunderstand ownership, input shape, security boundary or lifecycle.

- [ ] **Step 5: Sync docs**

Update the owning docs file and `STRUCTURE.md` only for facts changed or clarified during the review.

- [ ] **Step 6: Refresh inventory**

Run:

```powershell
php public/cli.php docs:inventory
php public/cli.php docs:inventory --json
```

Expected:

- `docs/runtime-inventory.md` refreshes.
- JSON command exits 0.

- [ ] **Step 7: Verify**

Run:

```powershell
php public/cli.php quality:check
git diff --check
```

Expected:

- Quality gate exits 0, allowing known host Windows DB DNS warnings if present.
- Diff check exits 0.

- [ ] **Step 8: Commit**

```powershell
git add <changed files>
git commit -m "document <surface> contracts"
```

## Task 6: Close The Remaining Template And Inline Asset Debt As Plans, Not Claims

**Files:**
- Modify: `docs/audits/architecture-first/2026-06-02-6d3-template-migration-plan.md`
- Modify: `docs/audits/architecture-first/2026-06-02-6d4-inline-asset-extraction-plan.md`
- Create: `docs/audits/documentation-debt/2026-06-02-open-documentation-debt-register.md`

- [ ] **Step 1: Create an open debt register**

Create a register with:

```markdown
| Debt | Source Evidence | Current State | Next Batch | Completion Rule |
|---|---|---|---|---|
| PHP executable templates | `6D.3` | planned | first low-risk surface | inventory reaches zero |
| Inline JS/CSS | `6D.4` | planned | shared shell boot extraction | no behavior inline except approved JSON transport |
| PHP inline docs | `6A.4` | partial | surface-by-surface contract comments | priority boundaries documented |
| Class docs sync | `6D.1` | inventory-backed | touched files only | docs match runtime for reviewed surface |
| DevTools visual/layout | `6A.2`, `6C.3` | deferred | user-approved DevTools batch | explicit scope approval |
```

- [ ] **Step 2: Add completion guard**

Document:

```markdown
Do not describe these debts as closed until the completion rule is met and the
user explicitly confirms closure.
```

- [ ] **Step 3: Verify**

Run:

```powershell
php public/cli.php docs:inventory --json
git diff --check
```

- [ ] **Step 4: Commit**

```powershell
git add docs/audits/documentation-debt/2026-06-02-open-documentation-debt-register.md docs/audits/architecture-first/2026-06-02-6d3-template-migration-plan.md docs/audits/architecture-first/2026-06-02-6d4-inline-asset-extraction-plan.md
git commit -m "register remaining documentation debt"
```

## Recommended Execution Order

1. Freeze vocabulary and prevent `shell` misinterpretation.
2. Build the surface documentation matrix.
3. Define PHP inline documentation standards.
4. Define JS documentation standards.
5. Review one surface at a time, starting with Auth or Settings.
6. Maintain the open debt register until all completion rules are met.

## Acceptance Criteria

- `shell` terminology is explicitly documented as layout/chrome/composition.
- Every active surface has a matrix row with current documentation state.
- PHP inline comments have a documented standard and priority order.
- JS contract headers have a documented standard.
- Each reviewed surface has a reasoning document with flow, problems, documentation actions and deferred items.
- `docs/runtime-inventory.md` is refreshed after documentation-affecting changes.
- No phase or debt category is marked complete without explicit user confirmation.

## Self-Review

- Spec coverage: the plan covers shell terminology, surface-by-surface reasoning, inline PHP documentation, JS contracts, stale `/docs`, class documentation sync and open debt tracking.
- Placeholder scan: no `TBD`, `TODO`, `implement later` or unspecified validation steps remain.
- Type consistency: no new runtime types or APIs are introduced; commands rely on existing Catalyst CLI and PowerShell.
