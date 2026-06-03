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
| `docs/architecture.md` | # Catalyst Framework - Architecture | Canonical | `docs/runtime-inventory.md`, `docs/runtime-module-catalog.md`, `route:list --json`, class responsibilities | Keep and update |
| `docs/audit-remediation-promptd.md` | # Promptd — histórico / obsoleto | Historical | `docs/runtime-module-catalog.md` and `php public/cli.php route:list --json` supersede this snapshot or cutover evidence | Candidate remove |
| `docs/audit-remediation-roadmap.md` | # Catalyst Audit Remediation Roadmap | Historical | `docs/runtime-module-catalog.md` and `php public/cli.php route:list --json` supersede this snapshot or cutover evidence | Candidate remove |
| `docs/audits/architecture-first/2026-06-01-6a1-bootstrap-routing-middleware.md` | # Phase 6A.1 - Bootstrap, Routing And Middleware Audit | Historical | Closed audit/spec/plan evidence; current truth comes from runtime commands and canonical docs | Candidate remove |
| `docs/audits/architecture-first/2026-06-01-6a2-framework-modules.md` | # Phase 6A.2 - Framework Modules Audit | Historical | Closed audit/spec/plan evidence; current truth comes from runtime commands and canonical docs | Candidate remove |
| `docs/audits/architecture-first/2026-06-01-6a3-app-surfaces.md` | # Phase 6A.3 - App Surfaces And Shared Support Audit | Historical | Closed audit/spec/plan evidence; current truth comes from runtime commands and canonical docs | Candidate remove |
| `docs/audits/architecture-first/2026-06-01-6a4-documentation-debt.md` | # Phase 6A.4 - Inline And Repository Documentation Audit | Historical | Closed audit/spec/plan evidence; current truth comes from runtime commands and canonical docs | Candidate remove |
| `docs/audits/architecture-first/2026-06-01-6b-architecture-normalization.md` | # Phase 6B - Architecture Normalization | Historical | Closed audit/spec/plan evidence; current truth comes from runtime commands and canonical docs | Candidate remove |
| `docs/audits/architecture-first/2026-06-01-architecture-first-audit.md` | # Auditoria inicial de arquitectura - Fase 6 | Historical | Closed audit/spec/plan evidence; current truth comes from runtime commands and canonical docs | Candidate remove |
| `docs/audits/architecture-first/2026-06-02-6c1-automation-mvc-refactor.md` | # Task 6C.1 - Automation MVC Refactor | Historical | Closed audit/spec/plan evidence; current truth comes from runtime commands and canonical docs | Candidate remove |
| `docs/audits/architecture-first/2026-06-02-6c2-documents-mvc-refactor.md` | # Task 6C.2 - Documents MVC Refactor | Historical | Closed audit/spec/plan evidence; current truth comes from runtime commands and canonical docs | Candidate remove |
| `docs/audits/architecture-first/2026-06-02-6c3-residual-batch-plan.md` | # Task 6C.3 - Residual Architecture Batch Plan | Historical | Closed audit/spec/plan evidence; current truth comes from runtime commands and canonical docs | Candidate remove |
| `docs/audits/architecture-first/2026-06-02-6c4-6c7-runtime-normalization.md` | # Tasks 6C.4-6C.7 - Residual Runtime Normalization | Historical | Closed audit/spec/plan evidence; current truth comes from runtime commands and canonical docs | Candidate remove |
| `docs/audits/architecture-first/2026-06-02-6d1-documentation-contract.md` | # Task 6D.1 - Documentation Contract And Inventory | Historical | Closed audit/spec/plan evidence; current truth comes from runtime commands and canonical docs | Candidate remove |
| `docs/audits/architecture-first/2026-06-02-6d2-js-contracts.md` | # Task 6D.2 - Canonical JavaScript Contracts | Historical | Closed audit/spec/plan evidence; current truth comes from runtime commands and canonical docs | Candidate remove |
| `docs/audits/architecture-first/2026-06-02-6d3-template-migration-plan.md` | # Task 6D.3 - Executable Template Migration Plan | Historical | Closed audit/spec/plan evidence; current truth comes from runtime commands and canonical docs | Candidate remove |
| `docs/audits/architecture-first/2026-06-02-6d4-inline-asset-extraction-plan.md` | # Task 6D.4 - Inline Asset Extraction Plan | Historical | Closed audit/spec/plan evidence; current truth comes from runtime commands and canonical docs | Candidate remove |
| `docs/audits/architecture-first/2026-06-02-6e1-phase-6-verification.md` | # Task 6E.1 - Phase 6 Verification | Historical | Closed audit/spec/plan evidence; current truth comes from runtime commands and canonical docs | Candidate remove |
| `docs/audits/architecture-first/2026-06-02-7a-future-workflow.md` | # Phase 7A - Future Workflow Without Direct Zips | Historical | Closed audit/spec/plan evidence; current truth comes from runtime commands and canonical docs | Candidate remove |
| `docs/audits/architecture-first/2026-06-02-phase-6-closeout-phase-7-kickoff.md` | # Phase 6 Closeout And Phase 7 Kickoff | Historical | Closed audit/spec/plan evidence; current truth comes from runtime commands and canonical docs | Candidate remove |
| `docs/audits/architecture-first/2026-06-02-phase-7-closeout.md` | # Phase 7 Closeout | Historical | Closed audit/spec/plan evidence; current truth comes from runtime commands and canonical docs | Candidate remove |
| `docs/audits/documentation/2026-06-02-duplication-candidates.md` | # Duplication Candidates Audit | Canonical | Active deliverable for `docs/superpowers/plans/2026-06-02-framework-responsibility-docs-audit.md` | Keep as active audit deliverable |
| `docs/audits/documentation/2026-06-02-responsibility-map.md` | # Responsibility Map Audit | Canonical | Active deliverable for `docs/superpowers/plans/2026-06-02-framework-responsibility-docs-audit.md` | Keep as active audit deliverable |
| `docs/audits/security-first/2026-06-01-security-first-audit.md` | # Security First Audit - 2026-06-01 | Historical | Closed audit/spec/plan evidence; current truth comes from runtime commands and canonical docs | Candidate remove |
| `docs/audits/source-operational-cleanup/demo-ui-generated-snapshots.md` | # Demo UI Generated Snapshots | Historical | Closed audit/spec/plan evidence; current truth comes from runtime commands and canonical docs | Candidate remove |
| `docs/audits/source-operational-cleanup/devtools-deferred.md` | # DevTools Deferred Debt | Historical | Closed audit/spec/plan evidence; current truth comes from runtime commands and canonical docs | Candidate remove |
| `docs/auth.md` | # Auth Index | Split index | `docs/harness-context-map.md` routes broad topics to split docs | Keep |
| `docs/checklists/setup-completion-e2e.md` | # Checklist E2E — `/configuration/environment-setup` admin + finalización | Canonical | `docs/runtime-inventory.md` and package responsibilities | Keep |
| `docs/composer.md` | # Composer Configuration | Canonical | `docs/runtime-inventory.md` and package responsibilities | Keep |
| `docs/database.md` | # Database Index | Split index | `docs/harness-context-map.md` routes broad topics to split docs | Keep and update |
| `docs/deployment.md` | # Deployment Guide | Canonical | `docs/runtime-inventory.md`, `docs/runtime-module-catalog.md`, `route:list --json`, class responsibilities | Keep and update |
| `docs/documentation-contract.md` | # Documentation Contract | Canonical | `docs/runtime-inventory.md`, `docs/runtime-module-catalog.md`, `route:list --json`, class responsibilities | Keep and update |
| `docs/entry-points.md` | # Entry Points | Canonical | `docs/runtime-inventory.md`, `docs/runtime-module-catalog.md`, `route:list --json`, class responsibilities | Keep and update |
| `docs/framework-appearance.md` | # Catalyst Framework Appearance | Canonical | `docs/runtime-inventory.md`, `docs/runtime-module-catalog.md`, `route:list --json`, class responsibilities | Keep and update |
| `docs/framework-argument.md` | # Catalyst\Framework\Argument | Canonical | `docs/runtime-inventory.md` and package responsibilities | Keep |
| `docs/framework-auth.md` | # Catalyst\Framework\Auth | Canonical | `docs/runtime-inventory.md`, `docs/runtime-module-catalog.md`, `route:list --json`, class responsibilities | Keep and update |
| `docs/framework-concurrency.md` | # `Catalyst\Framework\Concurrency` | Canonical | `docs/runtime-inventory.md`, `docs/runtime-module-catalog.md`, `route:list --json`, class responsibilities | Keep and update |
| `docs/framework-controllers.md` | # Catalyst\Framework\Controllers | Canonical | `docs/runtime-inventory.md` and package responsibilities | Keep |
| `docs/framework-database.md` | # `Catalyst\Framework\Database` | Canonical | `docs/runtime-inventory.md`, `docs/runtime-module-catalog.md`, `route:list --json`, class responsibilities | Keep and update |
| `docs/framework-datagrid.md` | # Refactor de DataGrid — Catalyst Framework | Canonical | `docs/runtime-inventory.md`, `docs/runtime-module-catalog.md`, `route:list --json`, class responsibilities | Keep and update |
| `docs/framework-enums.md` | # Catalyst\Framework\Enums | Canonical | `docs/runtime-inventory.md` and package responsibilities | Keep |
| `docs/framework-event.md` | # Catalyst\Framework\Event | Canonical | `docs/runtime-inventory.md`, `docs/runtime-module-catalog.md`, `route:list --json`, class responsibilities | Keep and update |
| `docs/framework-geo.md` | # `Catalyst\Framework\Geo` | Canonical | `docs/runtime-inventory.md`, `docs/runtime-module-catalog.md`, `route:list --json`, class responsibilities | Keep and update |
| `docs/framework-mail.md` | # Catalyst\Framework\Mail | Canonical | `docs/runtime-inventory.md` and package responsibilities | Keep |
| `docs/framework-notification.md` | # Catalyst\Framework\Notification | Canonical | `docs/runtime-inventory.md`, `docs/runtime-module-catalog.md`, `route:list --json`, class responsibilities | Keep and update |
| `docs/framework-queue.md` | # Catalyst\Framework\Queue | Canonical | `docs/runtime-inventory.md`, `docs/runtime-module-catalog.md`, `route:list --json`, class responsibilities | Keep and update |
| `docs/framework-schedule.md` | # Catalyst\Framework\Schedule | Canonical | `docs/runtime-inventory.md`, `docs/runtime-module-catalog.md`, `route:list --json`, class responsibilities | Keep and update |
| `docs/framework-session.md` | # Catalyst\Framework\Session | Canonical | `docs/runtime-inventory.md` and package responsibilities | Keep |
| `docs/framework-traits.md` | # `Catalyst\Framework\Traits` | Canonical | `docs/runtime-inventory.md` and package responsibilities | Keep |
| `docs/framework-view.md` | # Catalyst\Framework\View | Canonical | `docs/runtime-inventory.md`, `docs/runtime-module-catalog.md`, `route:list --json`, class responsibilities | Keep and update |
| `docs/framework-websocket.md` | # Catalyst\Framework\WebSocket | Canonical | `docs/runtime-inventory.md` and package responsibilities | Keep |
| `docs/harness-context-map.md` | # Catalyst Harness Context Map | Canonical | `docs/runtime-inventory.md` and package responsibilities | Keep |
| `docs/helpers-config.md` | # Catalyst\Helpers\Config | Canonical | `docs/runtime-inventory.md` and package responsibilities | Keep |
| `docs/helpers-debug.md` | # Catalyst\Helpers\Debug | Canonical | `docs/runtime-inventory.md` and package responsibilities | Keep |
| `docs/helpers-error.md` | # `Catalyst\Helpers\Error` | Canonical | `docs/runtime-inventory.md` and package responsibilities | Keep |
| `docs/helpers-exceptions.md` | # Catalyst\Helpers\Exceptions | Canonical | `docs/runtime-inventory.md` and package responsibilities | Keep |
| `docs/helpers-i18n.md` | # Catalyst i18n Runtime | Canonical | `docs/runtime-inventory.md`, `docs/runtime-module-catalog.md`, `route:list --json`, class responsibilities | Keep and update |
| `docs/helpers-log.md` | # Catalyst\Helpers\Log | Canonical | `docs/runtime-inventory.md` and package responsibilities | Keep |
| `docs/helpers-toolbox.md` | # Catalyst\Helpers\ToolBox | Canonical | `docs/runtime-inventory.md` and package responsibilities | Keep |
| `docs/helpers-validation.md` | # Catalyst\Helpers\Validation | Canonical | `docs/runtime-inventory.md` and package responsibilities | Keep |
| `docs/kernel.md` | # Kernel | Canonical | `docs/runtime-inventory.md`, `docs/runtime-module-catalog.md`, `route:list --json`, class responsibilities | Keep and update |
| `docs/middleware.md` | # Middleware Index | Split index | `docs/harness-context-map.md` routes broad topics to split docs | Keep and update |
| `docs/modules.md` | # Module Index | Split index | `docs/harness-context-map.md` routes broad topics to split docs | Keep and update |
| `docs/navigation-route-matrix-222.md` | # Matriz completa de rutas runtime | Superseded | `docs/runtime-module-catalog.md` and `php public/cli.php route:list --json` supersede this snapshot or cutover evidence | Candidate remove |
| `docs/navigation-route-refactor-plan.md` | # Plan de refactor de navegación y rutas | Superseded | `docs/runtime-module-catalog.md` and `php public/cli.php route:list --json` supersede this snapshot or cutover evidence | Candidate remove |
| `docs/quality-gate.md` | # Quality Gate | Canonical | `docs/runtime-inventory.md`, `docs/runtime-module-catalog.md`, `route:list --json`, class responsibilities | Keep and update |
| `docs/repository-auth.md` | # Catalyst\Repository\Auth\Controllers | Canonical | `docs/runtime-inventory.md`, `docs/runtime-module-catalog.md`, `route:list --json`, class responsibilities | Keep and update |
| `docs/repository-devtools.md` | # `Catalyst\Repository\DevTools` | Canonical | `docs/runtime-inventory.md` and package responsibilities | Keep |
| `docs/repository-notification.md` | # Catalyst\Repository\Notification | Canonical | `docs/runtime-inventory.md`, `docs/runtime-module-catalog.md`, `route:list --json`, class responsibilities | Keep and update |
| `docs/routing.md` | # Routing Index | Split index | `docs/harness-context-map.md` routes broad topics to split docs | Keep and update |
| `docs/runtime-inventory.md` | # Runtime Inventory | Runtime generated | `php public/cli.php docs:inventory --json` / `php public/cli.php docs:sync-runtime --stdout` | Keep synchronized |
| `docs/runtime-model.md` | # Runtime Model | Split index | `docs/harness-context-map.md` routes broad topics to split docs | Keep |
| `docs/runtime-module-catalog.md` | # Runtime Module Catalog | Runtime generated | `php public/cli.php docs:inventory --json` / `php public/cli.php docs:sync-runtime --stdout` | Keep synchronized |
| `docs/security-conventions.md` | # Security Conventions | Canonical | `docs/runtime-inventory.md`, `docs/runtime-module-catalog.md`, `route:list --json`, class responsibilities | Keep and update |
| `docs/SecurityTest/ATTACK_SURFACE_MAP.md` | # Attack Surface Map | Historical | Closed audit/spec/plan evidence; current truth comes from runtime commands and canonical docs | Candidate remove |
| `docs/SecurityTest/AUTHORIZATION_MATRIX.md` | # Authorization Matrix | Historical | Closed audit/spec/plan evidence; current truth comes from runtime commands and canonical docs | Candidate remove |
| `docs/SecurityTest/BUSINESS_LOGIC_REVIEW.md` | # Business Logic Review | Historical | Closed audit/spec/plan evidence; current truth comes from runtime commands and canonical docs | Candidate remove |
| `docs/SecurityTest/DATABASE_SECURITY_REVIEW.md` | # Database Security Review | Historical | Closed audit/spec/plan evidence; current truth comes from runtime commands and canonical docs | Candidate remove |
| `docs/SecurityTest/EXECUTIVE_DECISION_NOTES.md` | # Executive Decision Notes | Historical | Closed audit/spec/plan evidence; current truth comes from runtime commands and canonical docs | Candidate remove |
| `docs/SecurityTest/REMEDIATION_ROADMAP.md` | # Remediation Roadmap | Historical | Closed audit/spec/plan evidence; current truth comes from runtime commands and canonical docs | Candidate remove |
| `docs/SecurityTest/SECURITY_AUDIT_SUMMARY.md` | # Security Audit Summary | Historical | Closed audit/spec/plan evidence; current truth comes from runtime commands and canonical docs | Candidate remove |
| `docs/SecurityTest/SECURITY_BACKLOG.md` | # Security Backlog | Historical | Closed audit/spec/plan evidence; current truth comes from runtime commands and canonical docs | Candidate remove |
| `docs/SecurityTest/SECURITY_REMEDIATION_EXECUTION_PLAN.md` | # Security Remediation Execution Plan | Historical | Closed audit/spec/plan evidence; current truth comes from runtime commands and canonical docs | Candidate remove |
| `docs/SecurityTest/SECURITY_TEST_PLAN.md` | # Security Test Plan | Historical | Closed audit/spec/plan evidence; current truth comes from runtime commands and canonical docs | Candidate remove |
| `docs/SecurityTest/VULNERABILITY_REGISTER.md` | # Vulnerability Register | Historical | Closed audit/spec/plan evidence; current truth comes from runtime commands and canonical docs | Candidate remove |
| `docs/superpowers/plans/2026-06-01-catalyst-stabilization-roadmap.md` | # Catalyst Stabilization Roadmap Implementation Plan | Historical | Closed audit/spec/plan evidence; current truth comes from runtime commands and canonical docs | Candidate remove |
| `docs/superpowers/plans/2026-06-01-phase-2-source-operational-cleanup.md` | # Phase 2 Source Operational Cleanup Implementation Plan | Historical | Closed audit/spec/plan evidence; current truth comes from runtime commands and canonical docs | Candidate remove |
| `docs/superpowers/plans/2026-06-01-phase-5-security-hardening.md` | # Phase 5 Security Hardening Implementation Plan | Historical | Closed audit/spec/plan evidence; current truth comes from runtime commands and canonical docs | Candidate remove |
| `docs/superpowers/plans/2026-06-02-documentation-debt-grounding.md` | # Documentation Debt Grounding Implementation Plan | Historical | Closed audit/spec/plan evidence; current truth comes from runtime commands and canonical docs | Candidate remove |
| `docs/superpowers/plans/2026-06-02-framework-responsibility-docs-audit.md` | # Framework Responsibility And Documentation Audit Implementation Plan | Historical | Closed audit/spec/plan evidence; current truth comes from runtime commands and canonical docs | Candidate remove |
| `docs/superpowers/specs/2026-06-01-phase-5-security-hardening.md` | # Phase 5 Security Hardening Specification | Historical | Closed audit/spec/plan evidence; current truth comes from runtime commands and canonical docs | Candidate remove |
| `docs/testing.md` | # Testing Guide | Split index | `docs/harness-context-map.md` routes broad topics to split docs | Keep |
| `docs/ui/admin-surface-contract.md` | # Admin surface contract | Canonical | Runtime module catalog, work assets and security conventions | Keep |
| `docs/ui/datagrid-visual-guidelines.md` | # Lineamientos visuales del DataGrid | Canonical | Runtime module catalog, work assets and security conventions | Keep |
| `docs/ui/institutional-themes.md` | # Response skins and neutral branding | Canonical | Runtime module catalog, work assets and security conventions | Keep |
| `docs/ui/migration-ui-100-cutover.md` | # Migration UI cutover aplicado | Historical | `docs/runtime-module-catalog.md` and `php public/cli.php route:list --json` supersede this snapshot or cutover evidence | Candidate remove |
| `docs/ui/migration-ui-actual-cutover.md` | # Migration UI actual cutover | Historical | `docs/runtime-module-catalog.md` and `php public/cli.php route:list --json` supersede this snapshot or cutover evidence | Candidate remove |
| `docs/ui/migration-ui-framework-realignment-plan.md` | # `/demo-ui` Framework Realignment Plan | Historical | `docs/runtime-module-catalog.md` and `php public/cli.php route:list --json` supersede this snapshot or cutover evidence | Candidate remove |
| `docs/ui/migration-ui-refactor-cutover.md` | # Migration UI Refactor — Cutover Patch | Historical | `docs/runtime-module-catalog.md` and `php public/cli.php route:list --json` supersede this snapshot or cutover evidence | Candidate remove |
| `docs/ui/public-surface-contract.md` | # Public surface contract | Canonical | Runtime module catalog, work assets and security conventions | Keep |
| `docs/ui/route-inventory-99.md` | # Inventario de 99 rutas HTML auditadas | Superseded | `docs/runtime-module-catalog.md` and `php public/cli.php route:list --json` supersede this snapshot or cutover evidence | Candidate remove |
| `docs/ui/sidebar-navigation.md` | # Sidebar y navegacion administrativa | Canonical | Runtime module catalog, work assets and security conventions | Keep |
| `docs/ui/validation-checklist.md` | # Checklist de validacion del parche visual | Canonical | Runtime module catalog, work assets and security conventions | Keep |
| `docs/ui/visual-refactor-files.md` | # Manifiesto tecnico de archivos del refactor visual v2 | Historical | `docs/runtime-module-catalog.md` and `php public/cli.php route:list --json` supersede this snapshot or cutover evidence | Candidate remove |
| `docs/ui/visual-refactor-v2.md` | # Refactor visual v2 del framework Catalyst | Canonical | Runtime module catalog, work assets and security conventions | Keep |
| `docs/update-log.md` | # Update Log | Historical | Technical history; current state lives in canonical docs and generated inventories | Candidate remove |
| `docs/views.md` | # Views Index | Split index | `docs/harness-context-map.md` routes broad topics to split docs | Keep |
| `docs/workflow/first-run.md` | # First Run Workflow | Canonical | `docs/runtime-inventory.md`, `docs/runtime-module-catalog.md`, `route:list --json`, class responsibilities | Keep and update |
| `docs/workflow/patch-intake.md` | # Patch Intake Workflow | Canonical | `docs/runtime-inventory.md`, `docs/runtime-module-catalog.md`, `route:list --json`, class responsibilities | Keep and update |
| `docs/workflow/reusable-base-install.md` | # Reusable Base Install Workflow | Canonical | `docs/runtime-inventory.md`, `docs/runtime-module-catalog.md`, `route:list --json`, class responsibilities | Keep and update |
| `docs/audits/documentation/2026-06-02-docs-classification.md` | # Docs Classification Audit | Canonical | Active deliverable for `docs/superpowers/plans/2026-06-02-framework-responsibility-docs-audit.md` | Keep as active audit deliverable |
