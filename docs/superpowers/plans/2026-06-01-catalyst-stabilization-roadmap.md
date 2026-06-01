# Catalyst Stabilization Roadmap Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Stabilize Catalyst as a trustworthy reusable PHP framework base after several zip-based AI edits.

**Architecture:** Work in small gated phases. Each phase must leave a Git checkpoint, update repo-local documentation, run CLI checks, and avoid touching unrelated framework behavior.

**Tech Stack:** PHP 8.4, Composer, Catalyst CLI, PowerShell, Git/GitHub, WSDD/Docker.

---

## Current Phase Map

1. **Congelar una linea base confiable** — Finalizada.
   - Git iniciado desde cero.
   - Commit baseline: `30546ad` (`baseline import`).
   - Remoto privado: `wnunezc/catalyst`.

2. **Separar codigo fuente de basura operacional** — Finalizada.
   - Assets Inspinia usados por runtime consolidados bajo `public/assets/vendor/inspinia/`.
   - Branding institucional estatico eliminado.
   - `MigrationUi` alineado como `DemoUi`.
   - Config templates sanitizados creados.

3. **Inventario inicial** — Finalizada junto con fase 2.
   - Evidencia en `docs/audits/source-operational-cleanup/`.
   - Plan detallado en `docs/superpowers/plans/2026-06-01-phase-2-source-operational-cleanup.md`.

4. **Fortalecer checks automaticos** — Siguiente fase activa.
   - Crear una puerta local estandar de calidad.
   - Reducir dependencia de memoria manual.
   - Documentar que checks bloquean cambios.

5. **Auditar seguridad primero** — Completada por confirmacion explicita del usuario.
   - Secretos, `.env`, `secrets.json`, DKIM, uploads publicos.
   - CSRF/throttle, middleware admin, PHP expuesto en `public`.
   - Evidencia: `docs/audits/security-first/2026-06-01-security-first-audit.md`.
   - Hardening consolidado: `903019b` (`implement phase 5 security hardening`).

6. **Auditar arquitectura** — Pendiente.
   - Core framework vs demo/app ejemplo.
   - Rutas sin duenio, clases duplicadas, controladores grandes.
   - Docs desalineadas y deuda DevTools.

7. **Flujo futuro sin zips directos** — Pendiente.
   - Checklist para revisar zips/parches en carpeta temporal.
   - Setup de primer arranque para otro desarrollador.
   - Instalacion reproducible como base para otros proyectos.

---

## Phase 4: Fortalecer Checks Automaticos

### Objective

Crear un flujo local reproducible que ejecute los checks principales con una sola orden y documente claramente que fallos bloquean commit/push.

### Files

- Inspect before editing:
  - `app/Framework/Cli/Commands/`
  - `app/Framework/Cli/CommandRegistry.php` or equivalent command registration point.
  - `public/cli.php`
  - `docs/helpers-config.md`
  - `README.md`
- Candidate create:
  - `app/Framework/Cli/Commands/QualityCheckCommand.php`
  - `docs/quality-gate.md`
- Candidate modify:
  - CLI registry file that registers commands.
  - `README.md`
  - `docs/harness-context-map.md` if docs routing changes.
  - `STRUCTURE.md` if a new command class is added.

### Required Checks

The quality gate should include, at minimum:

```powershell
composer validate --strict
composer audit
php public/cli.php route:lint
php public/cli.php inspect:lint
php public/cli.php security:check
php public/cli.php status
```

### Local Warning Policy

Document expected local warnings separately from blockers:

- `php public/cli.php status` may warn from host about Docker-only DNS such as `WSDD-MySql-Server`.
- Security or routing failures are blockers.
- Composer validation/audit failures are blockers unless explicitly documented with a temporary exception.

### Execution Tasks

- [x] **Task 4.1: Close phase 2+3 Git checkpoint**

Run:

```powershell
composer dump-autoload
composer validate --strict
php public/cli.php route:lint
php public/cli.php inspect:lint
php public/cli.php status
git diff --check
git add -A
git diff --cached --check
git commit -m "finalize source operational cleanup"
git push
```

Expected:

- Composer and Catalyst CLI checks pass.
- `status` may show environment warnings only.
- Commit is created without AI co-author metadata.
- Push updates the private GitHub repository.

- [x] **Task 4.2: Discover existing CLI command patterns**

Run:

```powershell
rg -n "class .*Command|security:check|route:lint|inspect:lint|status|help" app public boot-core Repository
```

Expected:

- Identify the CLI command interface, registration mechanism, and output conventions before adding anything.

- [x] **Task 4.3: Decide whether `quality:check` should be code or docs-only**

Decision rule:

- If commands are easy to compose from the current CLI command framework without invoking shell-specific behavior, implement `quality:check`.
- If the CLI framework has no clean way to compose commands, create a docs-only quality gate first and defer command implementation.

- [x] **Task 4.4: Implement or document the quality gate**

Implementation path:

- Create `QualityCheckCommand` following existing command conventions.
- Register it in the existing CLI registry.
- Ensure it exits non-zero when a blocker check fails.

Docs-only path:

- Create `docs/quality-gate.md`.
- Add exact PowerShell commands and expected blocker/warning policy.

- [x] **Task 4.5: Verify phase 4**

Run:

```powershell
composer dump-autoload
composer validate --strict
composer audit
php public/cli.php route:lint
php public/cli.php inspect:lint
php public/cli.php security:check
php public/cli.php status
php public/cli.php help
```

If `quality:check` is implemented, also run:

```powershell
php public/cli.php quality:check
```

Expected:

- All blocker checks pass.
- Any local warning is explicitly documented.

- [x] **Task 4.6: Close phase 4 checkpoint**

Run:

```powershell
git diff --check
git status --short
```

Expected:

- Reviewable diff focused on quality gate.
- Ready for user review before commit/push unless the user explicitly asks to commit immediately.
