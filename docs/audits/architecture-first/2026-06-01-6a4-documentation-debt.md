# Phase 6A.4 - Inline And Repository Documentation Audit

Date: 2026-06-01

Status: initial documentation debt audit completed / remediation pending.

## Executive Summary

The documentation debt is real and broad. Catalyst has subsystem documents,
runtime catalogs and historical records, but they are mixed without a strict
hot/warm/cold contract. Inline PHP documentation is sparse, JavaScript source
contracts are mostly absent, `STRUCTURE.md` is not synchronized with the live
class inventory, and hot documents still reference superseded setup and health
routes.

This audit did not modify runtime or historical documentation.

## Inventory Results

### PHP Symbols

- Detected symbols under `app/`, `Repository/` and `boot-core/`: 573.
- Symbols with adjacent class-level docblock: 169.
- Symbols without adjacent class-level docblock: 404.
- Repository symbols: 140.
- Repository symbols without adjacent class-level docblock: 124.

### STRUCTURE Catalog

- Symbols compared under `app/` and `Repository/`: 580.
- Symbols mentioned in `STRUCTURE.md`: 369.
- Symbols not mentioned in `STRUCTURE.md`: 211.

The comparison is a coverage indicator, not a requirement to manually list
every private helper class inside `STRUCTURE.md`.

### JavaScript Source

Canonical `Repository/**/front/script.js` files excluding deferred DevTools:

- Source files: 16.
- Files with a header contract: 2.
- Files without a header contract: 14.

Several files are intentionally tiny loaders. Documentation effort should
prioritize non-trivial modules first.

### Template Separation

- Executable `.php` templates under Repository and `boot-core/template`: 89.
- Declarative `.phtml` templates: 140.
- Inline `<script>` blocks: 14.
- Inline `<style>` blocks: 6.

The reviewed inline blocks use CSP nonce attributes or JSON payload script
types where applicable. They are not classified as an immediate CSP bypass,
but they remain migration debt against the required PHP/HTML/CSS/JS
separation rule.

## Findings

### High - ARQ-25: Inline PHP contract documentation is incomplete

**Evidence**

404 of 573 detected PHP symbols have no adjacent class-level docblock.
Important omissions include entities, repositories, managers, module services,
queue jobs, CLI commands and module infrastructure.

**Recommendation**

Document by risk and contract boundary, not alphabetically:

1. Bootstrap, routing, cache and middleware invariants.
2. Public service and repository contracts.
3. Security-sensitive auth, setup, storage and export flows.
4. Jobs, events and payload schemas.
5. Remaining module classes as they are touched.

### High - ARQ-26: Hot documentation still describes superseded routes

**Files and examples**

- `API.md:77`
- `STRUCTURE.md:45`
- `STRUCTURE.md:345`
- `STRUCTURE.md:360`
- `docs/entry-points.md:28`
- `docs/checklists/setup-completion-e2e.md:1`
- `docs/harness-context-map.md:62`

**Evidence**

Hot documents retain `/setup` and `/health` as live contracts. The runtime
uses `/configuration/environment-setup` and
`/configuration/application-health`.

**Recommendation**

Correct hot documents during `6B.4`. Preserve historical route inventories and
cutover plans as cold evidence.

### High - ARQ-27: STRUCTURE is both incomplete and partially stale

**Files and examples**

- `STRUCTURE.md:46`
- `STRUCTURE.md:47`
- `STRUCTURE.md:48`
- `STRUCTURE.md:164`
- `STRUCTURE.md:458`

**Evidence**

`STRUCTURE.md` contains stale paths for Operations, Media, Documents, setup
and health. It also misses 211 detected symbols, including newer module,
queue, event and helper classes.

**Recommendation**

Keep `STRUCTURE.md` curated for architecture and public ownership boundaries.
Generate a machine-verifiable class inventory separately so the curated map
does not become an unsustainable handwritten class index.

### Medium - ARQ-28: Hot, warm and cold documentation are mixed

**Files**

- `docs/ui/route-inventory-99.md`
- `docs/ui/migration-ui-framework-realignment-plan.md`
- `docs/ui/migration-ui-refactor-cutover.md`
- `docs/update-log.md`
- `docs/SecurityTest/`
- `docs/audits/`

**Evidence**

Historical inventories, cutover plans and audit snapshots contain old names
and routes intentionally. They are useful evidence, but without explicit
classification they can be mistaken for current operating instructions.

**Recommendation**

Define a documentation index with hot, warm and cold sections. Do not rewrite
historical evidence merely to match current runtime.

### Medium - ARQ-29: Executable PHP templates remain a migration layer

**Files**

- `Repository/Framework/*/Views/scope/**/*.php`
- `boot-core/template/**/*.php`
- `app/Framework/View/View.php:31`
- `app/Framework/View/View.php:116`

**Evidence**

The renderer intentionally supports `.php` as a compatibility fallback while
`.phtml` is the declarative canonical format. There are still 89 executable
templates.

**Recommendation**

Migrate by surface in bounded batches. Do not remove PHP fallback until the
inventory reaches zero and regression coverage verifies the affected views.

### Medium - ARQ-30: Inline script and style blocks remain despite CSP compliance

**Files and examples**

- `boot-core/template/components/_catalyst-init.phtml:1`
- `boot-core/template/components/_status-bar.phtml:115`
- `boot-core/template/layouts/admin.phtml:21`
- `boot-core/template/layouts/base.phtml:21`
- `boot-core/template/errors/404.phtml:87`

**Evidence**

Nonce-backed inline blocks remain embedded in templates. CSP is respected in
the reviewed paths, but language separation remains incomplete.

**Recommendation**

Extract behavior to versioned JS assets and stylesheets incrementally. Retain
nonce-backed JSON payload blocks only where data transport is intentional and
documented.

### Medium - ARQ-31: JavaScript contracts are mostly undocumented

**Files and examples**

- `Repository/Framework/Auth/front/script.js`
- `Repository/Framework/Media/front/script.js`
- `Repository/Framework/Operations/front/script.js`
- `Repository/Framework/DemoUi/front/script.js`
- `Repository/App/Surface/Home/front/script.js`

**Evidence**

14 of 16 non-DevTools canonical module scripts lack a header contract. Some
are loaders, but the non-trivial scripts also omit event, DOM and payload
expectations.

**Recommendation**

Document only meaningful contracts: initialization trigger, DOM selectors,
events consumed/emitted, payload shape and CSP assumptions.

### Deferred - ARQ-32: DevTools UML remains ordered but untouched

**Files**

- `Repository/Framework/DevTools/lang/en/uml.json`
- `Repository/Framework/DevTools/lang/es/uml.json`

**Decision**

Keep the known stale UML debt ordered for a later DevTools batch. Do not enter
or modify that surface during the current audit sequence.

## Documentation Classification

### Hot - correct during stabilization

- `AGENTS.md`
- `docs/harness-context-map.md`
- `docs/architecture.md`
- `docs/entry-points.md`
- `docs/kernel.md`
- `docs/modules.md`
- `docs/routing.md`
- `docs/views.md`
- `docs/checklists/setup-completion-e2e.md`
- `STRUCTURE.md`
- `API.md`
- `TERMINAL.md`

### Warm - review when touching the owning subsystem

- `docs/framework-*.md`
- `docs/helpers-*.md`
- `docs/repository-*.md`
- `docs/testing.md`
- `docs/deployment.md`
- `docs/security-conventions.md`

### Cold - preserve as evidence

- `docs/audits/`
- `docs/superpowers/`
- `docs/update-log.md`
- `docs/SecurityTest/`
- Historical `docs/ui/*cutover*.md`
- `docs/ui/route-inventory-99.md`

## Commands Executed

```powershell
Get-ChildItem docs -Recurse -File
Get-ChildItem app,Repository,boot-core -Recurse -Filter *.php
Get-ChildItem Repository -Recurse -Filter *.js
rg -n ...
```

## Next Step

Close the consolidated `6A` audit sequence and present the remediation order.
Runtime changes remain pending.
