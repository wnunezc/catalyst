# Phase 5 Security Hardening Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Enforce public artifact boundaries, move CLI smoke probes to private storage, block INI downloads and sanitize editable Documents previews.

**Architecture:** Keep the existing public `local` storage disk and add a private `runtime` disk rooted at `boot-core/storage/runtime/`. Normal Documents exports become PDF-only; reporting persists the requested CSV/XLS format. Trusted HTML remains an explicit contract, but editable Documents preview passes through a small allowlist sanitizer and unused DataGrid raw HTML support is removed.

**Tech Stack:** PHP 8.4, Catalyst CLI, Apache `.htaccess`, MySQL/MariaDB, WSDD + Docker, PowerShell.

---

## Scope Split

Implement as four reviewable blocks:

1. Apache INI protection.
2. Private runtime storage and smoke cleanup.
3. Documents PDF boundary and safe HTML preview.
4. Reporting CSV/XLS persistence plus final regression coverage.

No task deletes existing local artifacts.

### Task 1: Block INI HTTP downloads

**Files:**
- Modify: `public/.htaccess`
- Modify: `docs/deployment.md`

- [ ] Add a `FilesMatch` denial for INI files and sensitive dotfiles without moving
  `public/.user.ini` or `public/php.ini`.
- [ ] Verify Apache config syntax through the local WSDD runtime.
- [ ] Verify `Invoke-WebRequest https://catalyst.dock/php.ini` and
  `Invoke-WebRequest https://catalyst.dock/.user.ini` do not return file content.
- [ ] Run `php public/cli.php quality:check`.
- [ ] Commit with `git commit -m "block public ini downloads"`.

### Task 2: Add private runtime storage disk

**Files:**
- Modify: `app/Framework/Storage/LocalStorageAdapter.php`
- Modify: `app/Framework/Storage/StorageManager.php`
- Modify: `STRUCTURE.md`

- [ ] Extend `LocalStorageAdapter` so a disk can be private and return an empty
  public URL intentionally.
- [ ] Register disk `runtime` rooted at `boot-core/storage/runtime/`.
- [ ] Keep disk `local` rooted at `public/`.
- [ ] Add a focused CLI regression proving `runtime` writes outside `public/` and
  does not produce a downloadable URL.
- [ ] Run the regression and `php public/cli.php quality:check`.
- [ ] Commit with `git commit -m "add private runtime storage disk"`.

### Task 3: Move smoke probes to private storage and clean files

**Files:**
- Modify: `app/Framework/Cli/Commands/AttachmentsSmokeCommand.php`
- Modify: `app/Framework/Cli/Commands/CatalogsSmokeCommand.php`
- Modify: `app/Framework/Cli/Commands/ReportingSmokeCommand.php`
- Modify: `app/Framework/Cli/Commands/RetentionSmokeCommand.php`
- Modify: `TERMINAL.md`
- Modify: `STRUCTURE.md`

- [ ] Pass `disk => 'runtime'` for TXT smoke media.
- [ ] Replace direct SQL-only cleanup with cleanup that removes storage objects
  before deleting rows.
- [ ] Ensure Documents artifacts created by smoke are purged through
  `DocumentTemplateManager::purgeArtifact()`.
- [ ] Emit a warning when best-effort cleanup cannot remove a file.
- [ ] Run DB-backed smoke commands inside WSDD when host DNS cannot resolve
  `WSDD-MySql-Server`.
- [ ] Assert no new file appears under `public/smoke/`.
- [ ] Run `php public/cli.php quality:check`.
- [ ] Commit with `git commit -m "keep cli smoke artifacts private"`.

### Task 4: Sanitize editable Documents preview

**Files:**
- Create: `app/Framework/View/HtmlAllowlistSanitizer.php`
- Modify: `app/Framework/Document/DocumentTemplateManager.php`
- Modify: `app/Framework/Cli/Commands/SecurityRegressionCommand.php`
- Modify: `STRUCTURE.md`
- Modify: `docs/security-conventions.md`

- [ ] Implement a dependency-free allowlist sanitizer using DOM parsing.
- [ ] Allow basic structural tags and safe attributes only.
- [ ] Remove active tags, event handlers, inline styles and unsafe URL schemes.
- [ ] Sanitize rendered Documents preview before it reaches
  `TrustedHtml::fromString()`.
- [ ] Add negative regressions for `<script>`, `onclick`, `style` and
  `javascript:` plus a positive regression for safe markup.
- [ ] Run `php public/cli.php security:regression`.
- [ ] Run `php public/cli.php security:check`.
- [ ] Commit with `git commit -m "sanitize editable document previews"`.

### Task 5: Persist Documents exports as PDF only

**Files:**
- Modify: `app/Framework/Document/DocumentTemplateManager.php`
- Modify: `app/Framework/Cli/Commands/AttachmentsSmokeCommand.php`
- Modify: `app/Framework/Cli/Commands/RetentionSmokeCommand.php`
- Modify: `Repository/Framework/Documents/lang/en/documents.json`
- Modify: `Repository/Framework/Documents/lang/es/documents.json`
- Modify: `docs/framework-document.md` if present; otherwise update `STRUCTURE.md`

- [ ] Preserve internal template formats `html`, `text`, `pdf` for editing and preview.
- [ ] Change normal `export()` so stored artifact content, extension and format are PDF.
- [ ] Keep `rendered_content` as normalized source text for auditability.
- [ ] Update smoke expectations to assert generated Documents artifacts are PDF.
- [ ] Verify no new `.html` or `.txt` appears under `public/generated-documents/`.
- [ ] Run Documents smoke coverage and `php public/cli.php quality:check`.
- [ ] Commit with `git commit -m "persist document exports as pdf"`.

### Task 6: Persist Reporting output as requested CSV or XLS

**Files:**
- Modify: `app/Framework/Reporting/ReportingManager.php`
- Modify: `app/Framework/Cli/Commands/ReportingSmokeCommand.php`
- Modify: `STRUCTURE.md`

- [ ] Validate queued report format against `csv`, `xls`.
- [ ] Dispatch to `DataGrid::exportCsvRows()` or `DataGrid::exportXlsRows()`.
- [ ] Persist matching extension, MIME, filename and contents.
- [ ] Extend reporting smoke to cover CSV and XLS.
- [ ] Verify no unsupported extension appears under `public/generated-reports/`.
- [ ] Run `php public/cli.php reporting:smoke --json` in WSDD if required.
- [ ] Run `php public/cli.php quality:check`.
- [ ] Commit with `git commit -m "persist requested reporting format"`.

### Task 7: Remove unused DataGrid raw HTML bypass

**Files:**
- Modify: `app/Framework/Admin/Grid/DataGridRowNormalizer.php`
- Modify: `boot-core/template/scope/components/_admin-datagrid.php`
- Modify: `docs/framework-datagrid.md`
- Modify: `docs/security-conventions.md`

- [ ] Remove `html`/`raw` propagation from normalized cells.
- [ ] Remove `TrustedHtml::fromString()` for raw DataGrid values.
- [ ] Keep structured kinds `stack`, `code`, `badge`, `badges`.
- [ ] Run `rg` to verify no active DataGrid consumer requests raw HTML.
- [ ] Run `php public/cli.php security:check`.
- [ ] Run `php public/cli.php quality:check`.
- [ ] Commit with `git commit -m "remove datagrid raw html bypass"`.

### Task 8: Final documentation and verification

**Files:**
- Modify: `docs/audits/security-first/2026-06-01-security-first-audit.md`
- Modify: `docs/deployment.md`
- Modify: `docs/security-conventions.md`
- Modify: `D:/OpsZone/DevWorkspace/Knowledge/Obsidian-Vault/08-AI-Context/catalyst.md`
- Modify: `D:/OpsZone/DevWorkspace/Knowledge/Obsidian-Vault/08-AI-Context/catalyst-reentry.md`

- [ ] Record implemented decisions and remaining deferred items.
- [ ] Run:

```powershell
composer validate --strict
composer audit
php public/cli.php inspect:lint
php public/cli.php security:check
php public/cli.php security:regression
php public/cli.php quality:check
git diff --check
git status --short
```

- [ ] Verify new artifacts:
  - `public/generated-documents/`: PDF only.
  - `public/generated-reports/`: CSV/XLS only.
  - `public/smoke/`: no new files.
  - `boot-core/storage/runtime/smoke/`: cleanup leaves no new probes.
  - `public/uploads/devtools/`: still functional.
- [ ] Present evidence to the user. Do not mark phase 5 completed without explicit
  confirmation.

## Deferred Work

- License system and final `APP_KEY` contract.
- `display_errors Off` production profile before `v1.0.0`.
- Optional authorized-download controller for normal exported files.
