# Framework Responsibility And Documentation Audit Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Detect duplicated responsibilities, features and functionality across the PHP framework, then reconcile `/docs` so public documentation matches the existing codebase and removes obsolete framework documentation.

**Architecture:** Treat inline PHP docblocks, runtime inventories, routes, modules and CLI registries as the source of truth. Produce machine-readable audit inventories first, then review candidates manually before editing or deleting documentation. Documentation changes must be separated from runtime code changes and committed in small verified batches.

**Tech Stack:** PHP 8.4, Catalyst CLI, PowerShell, Git, Markdown, existing `docs:inventory`, `docs:sync-runtime`, `inspect:lint`, `route:lint`, and `quality:check` commands.

---

## Guardrails

- Do not edit runtime PHP logic while executing this plan.
- Do not edit `vendor/`.
- Do not add Composer dependencies.
- Do not mark project phases as complete without explicit user confirmation.
- Do not delete documentation directly in the first pass; classify it first.
- Historical or obsolete documentation must be removed from the project after classification; do not keep historical archives inside `/docs`.
- Keep generated/public work assets out of documentation commits unless a task explicitly targets them.
- Use PowerShell commands.
- Commit after each completed task group.

## Source Inputs

- `AGENTS.md`
- `docs/harness-context-map.md`
- `STRUCTURE.md`
- `API.md`
- `docs/runtime-inventory.md`
- `docs/runtime-module-catalog.md`
- `docs/documentation-contract.md`
- `docs/quality-gate.md`
- `php public/cli.php docs:inventory --json`
- `php public/cli.php docs:sync-runtime --stdout`
- `php public/cli.php route:list --json`
- All PHP files excluding `vendor/` and `public/assets/`
- All Markdown files under `docs/`

## Deliverables

- `docs/audits/documentation/2026-06-02-responsibility-map.md`
- `docs/audits/documentation/2026-06-02-duplication-candidates.md`
- `docs/audits/documentation/2026-06-02-docs-classification.md`
- `docs/audits/documentation/2026-06-02-docs-reconciliation-report.md`
- Updated `/docs/*.md` files that describe existing framework features and workflows.
- Removed historical, obsolete, superseded or duplicate docs after their role is classified and their replacement or lack of runtime evidence is clear.
- Final verification evidence with `quality:check`, `git diff --check`, doc inventory checks and route/module checks.

---

### Task 1: Establish Baseline And Freeze Scope

**Files:**
- Create: `docs/audits/documentation/2026-06-02-responsibility-map.md`
- Read only: `AGENTS.md`
- Read only: `docs/harness-context-map.md`
- Read only: `docs/documentation-contract.md`
- Read only: `docs/runtime-inventory.md`
- Read only: `docs/runtime-module-catalog.md`

- [ ] **Step 1: Confirm branch and worktree scope**

Run:

```powershell
git status --short --branch
git log --oneline --decorate -8
```

Expected:

```text
Branch is main.
Any dirty files are identified before documentation work starts.
No untracked/generated assets are staged accidentally.
```

- [ ] **Step 2: Generate runtime evidence**

Run:

```powershell
php public/cli.php docs:inventory --json
php public/cli.php docs:sync-runtime --stdout
php public/cli.php route:list --json
php public/cli.php inspect:lint
php public/cli.php route:lint
```

Expected:

```text
Commands complete successfully.
If host Windows emits known DB DNS warnings only through status checks, record them as accepted environmental warnings.
```

- [ ] **Step 3: Create the responsibility-map audit shell**

Create `docs/audits/documentation/2026-06-02-responsibility-map.md` with this structure:

```markdown
# Responsibility Map Audit

Date: 2026-06-02
Scope: PHP runtime responsibilities and framework documentation alignment.

## Inputs

- Inline PHP class and method docblocks.
- Runtime module catalog.
- Runtime inventory.
- CLI command catalog.
- Route list.
- Existing `/docs` Markdown.

## Verification Commands

- `php public/cli.php docs:inventory --json`
- `php public/cli.php docs:sync-runtime --stdout`
- `php public/cli.php route:list --json`
- `php public/cli.php inspect:lint`
- `php public/cli.php route:lint`

## Scope Rules

- Runtime logic is read-only.
- Documentation may be updated after classification.
- Obsolete docs are classified before removal.

## Output Files

- `docs/audits/documentation/2026-06-02-duplication-candidates.md`
- `docs/audits/documentation/2026-06-02-docs-classification.md`
- `docs/audits/documentation/2026-06-02-docs-reconciliation-report.md`
```

- [ ] **Step 4: Commit baseline audit shell**

Run:

```powershell
git add docs/audits/documentation/2026-06-02-responsibility-map.md
git commit -m "start framework responsibility audit"
```

Expected:

```text
Commit created with only the baseline audit shell.
```

---

### Task 2: Build Responsibility Inventory From Code

**Files:**
- Create: `docs/audits/documentation/2026-06-02-responsibility-map.md`
- Modify: `docs/audits/documentation/2026-06-02-responsibility-map.md`

- [ ] **Step 1: Extract class-level responsibility groups**

Run:

```powershell
@'
<?php
$root = getcwd();
$files = [];
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
foreach ($it as $file) {
    $path = str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));
    if (!str_ends_with($path, '.php')) {
        continue;
    }
    if (str_starts_with($path, 'vendor/') || str_starts_with($path, 'public/assets/')) {
        continue;
    }
    $files[] = $path;
}

$rows = [];
foreach ($files as $path) {
    $tokens = token_get_all(file_get_contents($path));
    $lastDoc = null;
    for ($i = 0; $i < count($tokens); $i++) {
        $token = $tokens[$i];
        if (is_array($token) && $token[0] === T_DOC_COMMENT) {
            $lastDoc = $token[1];
            continue;
        }
        if (is_array($token) && in_array($token[0], [T_WHITESPACE, T_COMMENT, T_ATTRIBUTE, T_FINAL, T_ABSTRACT, T_READONLY], true)) {
            continue;
        }
        if (is_array($token) && in_array($token[0], [T_CLASS, T_INTERFACE, T_TRAIT, T_ENUM], true)) {
            $j = $i + 1;
            while ($j < count($tokens) && is_array($tokens[$j]) && $tokens[$j][0] === T_WHITESPACE) {
                $j++;
            }
            if ($j < count($tokens) && is_array($tokens[$j]) && $tokens[$j][0] === T_STRING) {
                $name = $tokens[$j][1];
                preg_match('/Responsibility:\s*(.+)/', (string) $lastDoc, $match);
                $responsibility = trim($match[1] ?? '');
                preg_match('/@package\s+([^\s]+)/', (string) $lastDoc, $packageMatch);
                $package = trim($packageMatch[1] ?? '');
                $rows[] = [$path, $name, $package, $responsibility];
            }
            $lastDoc = null;
            continue;
        }
        $lastDoc = null;
    }
}

usort($rows, fn ($a, $b) => [$a[2], $a[1], $a[0]] <=> [$b[2], $b[1], $b[0]]);
echo "| File | Symbol | Package | Responsibility |\n";
echo "|---|---|---|---|\n";
foreach ($rows as $row) {
    echo "| `{$row[0]}` | `{$row[1]}` | `{$row[2]}` | " . str_replace('|', '\|', $row[3]) . " |\n";
}
'@ | php > docs/audits/documentation/2026-06-02-responsibility-map.generated.md
```

Expected:

```text
Generated Markdown table contains every PHP class, interface, trait and enum responsibility.
```

- [ ] **Step 2: Merge generated table into audit document**

Append the generated table under this heading in `docs/audits/documentation/2026-06-02-responsibility-map.md`:

```markdown
## Class Responsibility Inventory

The table below was generated from PHP docblocks and is used as the source for duplicate responsibility review.
```

Then append the content of:

```text
docs/audits/documentation/2026-06-02-responsibility-map.generated.md
```

- [ ] **Step 3: Remove temporary generated file**

Run:

```powershell
Remove-Item -LiteralPath docs/audits/documentation/2026-06-02-responsibility-map.generated.md
```

Expected:

```text
Only the curated audit document remains.
```

- [ ] **Step 4: Commit responsibility inventory**

Run:

```powershell
git add docs/audits/documentation/2026-06-02-responsibility-map.md
git commit -m "map framework responsibilities"
```

Expected:

```text
Commit contains only the responsibility map update.
```

---

### Task 3: Identify Responsibility And Feature Duplication Candidates

**Files:**
- Create: `docs/audits/documentation/2026-06-02-duplication-candidates.md`
- Read only: all PHP files outside `vendor/` and `public/assets/`
- Read only: `docs/runtime-module-catalog.md`
- Read only: route and CLI outputs

- [ ] **Step 1: Create duplication candidate document**

Create `docs/audits/documentation/2026-06-02-duplication-candidates.md`:

```markdown
# Duplication Candidates Audit

Date: 2026-06-02
Scope: Candidate responsibility, feature and functionality duplication across Catalyst PHP runtime.

## Classification

- Confirmed duplicate: same responsibility and same functional boundary.
- Overlap: related responsibility but different owner or lifecycle.
- Intentional facade: repeated wording exists because one class delegates or exposes a simpler API.
- Naming drift: same concept appears under different names.
- No action: candidate was reviewed and rejected.

## Review Rules

- Do not rename or move code in this audit.
- Do not change runtime logic.
- Record exact files and symbols.
- Every confirmed duplicate needs a proposed owner.

## Candidates

| Status | Category | Primary Owner | Other Symbols | Evidence | Recommendation |
|---|---|---|---|---|---|
```

- [ ] **Step 2: Generate exact duplicate responsibility candidates**

Run:

```powershell
@'
<?php
$source = file_get_contents('docs/audits/documentation/2026-06-02-responsibility-map.md');
preg_match_all('/\| `([^`]+)` \| `([^`]+)` \| `([^`]+)` \| (.+?) \|/', $source, $matches, PREG_SET_ORDER);
$groups = [];
foreach ($matches as $m) {
    $responsibility = strtolower(trim(strip_tags($m[4])));
    $responsibility = preg_replace('/\s+/', ' ', $responsibility);
    $groups[$responsibility][] = [$m[1], $m[2], $m[3], $m[4]];
}
foreach ($groups as $responsibility => $rows) {
    if (count($rows) < 2) {
        continue;
    }
    echo "### Exact responsibility match\n\n";
    echo "Responsibility: {$rows[0][3]}\n\n";
    foreach ($rows as $row) {
        echo "- `{$row[1]}` in `{$row[0]}` (`{$row[2]}`)\n";
    }
    echo "\n";
}
'@ | php > docs/audits/documentation/2026-06-02-duplication-exact.generated.md
```

Expected:

```text
Generated output lists exact same-responsibility candidates, if any.
```

- [ ] **Step 3: Review candidates manually into the audit table**

For each exact match, add a row to `docs/audits/documentation/2026-06-02-duplication-candidates.md`:

```markdown
| Overlap | Responsibility | `PrimaryClass` | `OtherClass` | Same responsibility text found in inline PHP docblocks. | Review call graph and ownership before consolidation. |
```

If the exact generated file is empty, add:

```markdown
No exact class responsibility duplicates were found by text match. Near-duplicate review continues by domain.
```

- [ ] **Step 4: Review domain-level duplication**

Read class responsibility groups by package and fill table rows for these domains:

```text
Auth and authorization
Routes and middleware
Views and frontend resources
Modules and manifests
CLI and scaffolding
Database, ORM and migrations
Logging, errors and debug
Documents, media and attachments
Workflow, automation, queue and schedule
Notification, presence and timeline
Settings, operations and setup
```

For each domain, add rows using this format:

```markdown
| Status | Domain | Primary Owner | Other Symbols | Evidence | Recommendation |
|---|---|---|---|---|---|
| Overlap | Routes and middleware | `Catalyst\Framework\Route\Router` | `RouteDispatcher`, `RouteCollection`, `UrlGenerator` | Responsibilities share routing concerns but separate registration, matching and URL generation. | Keep split if each class retains a single boundary; document the boundary in `docs/routing.md`. |
```

- [ ] **Step 5: Delete temporary exact-match file**

Run:

```powershell
Remove-Item -LiteralPath docs/audits/documentation/2026-06-02-duplication-exact.generated.md
```

- [ ] **Step 6: Commit duplication audit**

Run:

```powershell
git add docs/audits/documentation/2026-06-02-duplication-candidates.md
git commit -m "audit framework responsibility duplication"
```

Expected:

```text
Commit contains only duplication audit documentation.
```

---

### Task 4: Classify Every `/docs` Markdown File

**Files:**
- Create: `docs/audits/documentation/2026-06-02-docs-classification.md`
- Read only: all `docs/**/*.md`

- [ ] **Step 1: Generate Markdown inventory**

Run:

```powershell
Get-ChildItem -Path docs -Recurse -Filter *.md |
    Sort-Object FullName |
    ForEach-Object {
        $relative = Resolve-Path -Relative $_.FullName
        $firstHeading = Select-String -LiteralPath $_.FullName -Pattern '^# ' | Select-Object -First 1
        if ($firstHeading) {
            "$relative`t$($firstHeading.Line)"
        } else {
            "$relative`t(no h1)"
        }
    } > docs/audits/documentation/2026-06-02-docs-inventory.generated.tsv
```

Expected:

```text
Generated TSV lists every Markdown file under docs with its first H1.
```

- [ ] **Step 2: Create classification document**

Create `docs/audits/documentation/2026-06-02-docs-classification.md`:

```markdown
# Docs Classification Audit

Date: 2026-06-02
Scope: All Markdown files under `/docs`.

## Classification Values

- Canonical: current user-facing or developer-facing documentation for existing framework behavior.
- Split index: broad index that routes to canonical detailed docs.
- Runtime generated: generated or runtime-derived inventory that must remain synchronized with commands.
- Historical: audit, closeout or decision evidence that is not part of current product documentation and must be removed from the project after classification.
- Superseded: old documentation replaced by newer canonical documentation.
- Duplicate: same content or same responsibility as another doc.
- Obsolete: describes behavior not present in the current codebase.
- Candidate remove: should be deleted from the project because it is historical, superseded, duplicate or obsolete.

## Inventory

| File | H1 | Classification | Code Evidence | Action |
|---|---|---|---|---|
```

- [ ] **Step 3: Fill classification rows**

For each row in `docs/audits/documentation/2026-06-02-docs-inventory.generated.tsv`, add a Markdown table row:

```markdown
| `docs/example.md` | Example H1 | Canonical | `app/Framework/...` or `php public/cli.php ...` | Keep and update |
```

Classification rules:

```text
Docs under docs/audits/ are Historical evidence unless they duplicate generated scratch files.
docs/runtime-inventory.md is Runtime generated.
docs/runtime-module-catalog.md is Runtime generated.
docs/harness-context-map.md is Canonical.
docs/quality-gate.md is Canonical.
Broad index docs such as auth/database/routing/modules/views/testing are Split index when they route to detailed docs.
Feature docs are Canonical only when the feature exists in PHP code, routes, modules or CLI commands.
```

- [ ] **Step 4: Remove temporary TSV**

Run:

```powershell
Remove-Item -LiteralPath docs/audits/documentation/2026-06-02-docs-inventory.generated.tsv
```

- [ ] **Step 5: Commit docs classification**

Run:

```powershell
git add docs/audits/documentation/2026-06-02-docs-classification.md
git commit -m "classify framework documentation"
```

Expected:

```text
Commit contains only the docs classification audit.
```

---

### Task 5: Reconcile Canonical Feature Documentation

**Files:**
- Modify: `docs/*.md`
- Modify: `docs/**/*.md`
- Read only: `docs/audits/documentation/2026-06-02-docs-classification.md`
- Read only: `docs/audits/documentation/2026-06-02-duplication-candidates.md`

- [ ] **Step 1: Select canonical docs to update**

From the classification audit, list all rows with:

```text
Classification = Canonical
Action = Keep and update
```

Group by domain:

```text
Runtime model and bootstrap
Routing and middleware
Views and security conventions
Modules and navigation
Database and ORM
Auth and authorization
Queue, schedule, events and automation
Documents, media, reporting and attachments
Settings, operations and appearance
Testing and quality gate
Deployment and reusable base workflow
```

- [ ] **Step 2: Update each canonical doc against code evidence**

For each canonical doc:

```text
1. Confirm the feature exists in PHP class responsibilities, runtime module catalog, routes or CLI output.
2. Remove descriptions of behavior not present in the code.
3. Add missing behavior that exists in the code.
4. Link to related canonical docs.
5. Avoid duplicating full content that belongs to another canonical doc.
6. Keep historical notes out of canonical docs unless they affect current operation.
```

Each edited doc must include:

```markdown
## Purpose

## Runtime Owners

## Current Behavior

## Operational Notes

## Related Documentation
```

Use this exact row format for runtime owners when appropriate:

```markdown
| Concern | Owner |
|---|---|
| Route registration | `Catalyst\Framework\Route\Router` |
| Dispatch | `Catalyst\Framework\Route\RouteDispatcher` |
```

- [ ] **Step 3: Run documentation-related checks after each domain**

Run after each domain batch:

```powershell
php public/cli.php docs:inventory --json
php public/cli.php docs:sync-runtime --stdout
php public/cli.php inspect:lint
git diff --check
```

Expected:

```text
All commands pass.
Only known host Windows DB DNS warnings may appear in broader status checks.
```

- [ ] **Step 4: Commit each domain batch**

Run:

```powershell
git add docs
git commit -m "sync <domain> documentation"
```

Use domain-specific messages:

```text
sync routing documentation
sync database documentation
sync auth documentation
sync module documentation
sync operations documentation
```

Expected:

```text
Each commit is scoped to one documentation domain.
```

---

### Task 6: Remove Historical, Superseded, Duplicate And Obsolete Docs

**Files:**
- Modify: `docs/audits/documentation/2026-06-02-docs-classification.md`
- Delete after classification: selected `docs/**/*.md`

- [ ] **Step 1: Prepare removal proposal**

In `docs/audits/documentation/2026-06-02-docs-reconciliation-report.md`, add:

```markdown
# Docs Reconciliation Report

Date: 2026-06-02

## Keep

| File | Reason |
|---|---|

## Update Completed

| File | Reason |
|---|---|

## Remove From Project

| File | Reason |
|---|---|

## No Runtime Evidence Found

| File | Claim | Search Evidence |
|---|---|---|
```

- [ ] **Step 2: Fill remove-from-project rows**

Use this format:

```markdown
| `docs/old-example.md` | Historical or superseded by `docs/new-example.md`; not part of current product documentation. |
```

- [ ] **Step 3: Fill obsolete remove rows**

Use this format:

```markdown
| `docs/obsolete-example.md` | Describes a feature not found in PHP classes, routes, modules, CLI commands or runtime docs. |
```

- [ ] **Step 4: Remove classified historical and obsolete documentation**

Remove only files already listed under `Remove From Project` in `docs/audits/documentation/2026-06-02-docs-reconciliation-report.md`.

Run:

```powershell
Remove-Item -LiteralPath docs/old-example.md
Remove-Item -LiteralPath docs/obsolete-example.md
```

Expected:

```text
Historical, superseded, duplicate and obsolete docs are removed from the repository.
Canonical, runtime-generated and current workflow docs remain.
```

- [ ] **Step 5: Verify removed docs were not referenced by canonical docs**

Run:

```powershell
$removed = @(
    'docs/old-example.md',
    'docs/obsolete-example.md'
)
foreach ($file in $removed) {
    rg -n ([regex]::Escape($file)) docs -g '*.md'
}
```

Expected:

```text
No canonical documentation references deleted files.
If references remain, update the referencing canonical doc before commit.
```

- [ ] **Step 6: Commit cleanup**

Run:

```powershell
git add docs
git commit -m "remove obsolete framework documentation"
```

Expected:

```text
Commit contains only classified documentation removals and reference fixes.
```

---

### Task 7: Final Cross-Checks For Product Readiness

**Files:**
- Modify: `docs/audits/documentation/2026-06-02-docs-reconciliation-report.md`
- Read only: all docs and PHP runtime files

- [ ] **Step 1: Run full verification**

Run:

```powershell
php public/cli.php docs:inventory --json
php public/cli.php docs:sync-runtime --stdout
php public/cli.php route:list --json
php public/cli.php inspect:lint
php public/cli.php route:lint
php public/cli.php quality:check
git diff --check
```

Expected:

```text
All commands pass.
Known acceptable warnings are documented explicitly.
```

- [ ] **Step 2: Add final evidence to reconciliation report**

Append:

```markdown
## Final Verification

| Command | Result | Notes |
|---|---|---|
| `php public/cli.php docs:inventory --json` | PASS | Runtime inventory generated successfully. |
| `php public/cli.php docs:sync-runtime --stdout` | PASS | Runtime module documentation synchronized. |
| `php public/cli.php route:list --json` | PASS | Route list available from runtime. |
| `php public/cli.php inspect:lint` | PASS | Structural lint is coherent. |
| `php public/cli.php route:lint` | PASS | Route contract is coherent. |
| `php public/cli.php quality:check` | PASS | Quality gate passed; record known warnings if present. |
| `git diff --check` | PASS | No whitespace errors. |

## Product Readiness Notes

- Inline PHP docblocks are aligned with responsibility-oriented review.
- `/docs` canonical files are aligned with current framework runtime behavior.
- Duplicated responsibilities are classified as confirmed duplicate, overlap, facade, naming drift or no action.
- Historical and obsolete documentation is removed from the project after classification.
```

- [ ] **Step 3: Commit final report**

Run:

```powershell
git add docs/audits/documentation/2026-06-02-docs-reconciliation-report.md
git commit -m "document framework documentation reconciliation"
```

- [ ] **Step 4: Push completed work**

Run:

```powershell
git push
git status --short --branch
git log --oneline --decorate -8
```

Expected:

```text
Branch is synchronized with origin after push.
Only unrelated user worktree changes remain, if any.
```

---

## Completion Criteria

- Every PHP class/interface/trait/enum remains documented with summary, `@package` and `Responsibility`.
- Every PHP method remains documented with summary and responsibility.
- Responsibility duplicates are classified with explicit recommendations.
- `/docs` has a complete classification record.
- Canonical docs describe only existing framework/runtime behavior.
- Historical and obsolete docs are removed from the project after classification.
- Removed docs have no remaining references from canonical documentation.
- Final verification commands pass.
- Work is committed in scoped commits and pushed.

## Self-Review

- Spec coverage: The plan covers duplicate responsibility/functionality analysis and `/docs` reconciliation.
- Placeholder scan: No task uses unresolved `TBD`, `TODO`, or unspecified implementation steps.
- Safety: Runtime code remains read-only; documentation deletion is limited to files classified as historical, superseded, duplicate or obsolete by this plan.
- Verification: Each major batch includes concrete commands and expected results.
