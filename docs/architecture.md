# Catalyst Framework Architecture

## Purpose

Describe the current Catalyst architecture and act as the central index for the live Markdown documentation under `/docs`.

## Runtime Owners

| Concern | Owner |
|---|---|
| HTTP bootstrap and dispatch | `Catalyst\Kernel` |
| CLI bootstrap and commands | `Catalyst\Framework\Cli\CliKernel`, `Catalyst\Framework\Cli\CommandRegistry` |
| Route registration and dispatch | `Catalyst\Framework\Route\Router`, `Catalyst\Framework\Route\RouteDispatcher` |
| Module registration and inspection | `Catalyst\Framework\Module\ModuleRegistry`, `ModuleInspector`, `ModuleHarnessInspector`, `ModuleLinter` |
| View rendering and module assets | `Catalyst\Framework\View\View`, `FrontResourceTrait` |
| Generated documentation truth | `RuntimeInventoryGenerator`, `DocsSyncRuntimeCommand` |

## Current Behavior

Catalyst is a PHP 8.4 MVC framework with a small kernel, explicit HTTP/CLI entry points, framework-owned reusable modules under `Repository/Framework`, app-owned surfaces under `Repository/App/Surface`, and runtime-generated documentation inventories. The current module catalog reports 18 modules with structural lint OK.

The architecture uses separated owners instead of single large classes: routes are registered, compiled, collected and dispatched by different route classes; modules are declared, discovered, inspected and linted by different module classes; views render templates while trusted HTML, inline JSON and token rendering are separate security boundaries.

## Runtime Layers

| Layer | Current Source |
|---|---|
| Entry points | `public/index.php`, `public/cli.php` |
| Kernel | `app/Kernel.php` |
| Framework primitives | `app/Framework/*` |
| Helpers | `app/Helpers/*` |
| Shared entities | `app/Entities/*` |
| Framework modules | `Repository/Framework/*` |
| App surfaces | `Repository/App/Surface/*` |
| Shared templates | `boot-core/template/*` |
| Public frontend runtime | `public/assets/js/catalyst/*`, `public/assets/css/catalyst/*` |

## Documentation Index

| File | Topic |
|---|---|
| `docs/architecture.md` | Catalyst Framework - Architecture |
| `docs/auth.md` | Auth Index |
| `docs/checklists/setup-completion-e2e.md` | Checklist E2E — `/configuration/environment-setup` admin + finalización |
| `docs/composer.md` | Composer Configuration |
| `docs/database.md` | Database Index |
| `docs/deployment.md` | Deployment Guide |
| `docs/documentation-contract.md` | Documentation Contract |
| `docs/entry-points.md` | Entry Points |
| `docs/entity-references.md` | Entity References |
| `docs/framework-appearance.md` | Catalyst Framework Appearance |
| `docs/framework-argument.md` | Catalyst\Framework\Argument |
| `docs/framework-attachments.md` | Catalyst Framework Attachments |
| `docs/framework-auth.md` | Catalyst\Framework\Auth |
| `docs/framework-calendar.md` | Catalyst Framework Calendar |
| `docs/framework-concurrency.md` | Catalyst\Framework\Concurrency |
| `docs/framework-controllers.md` | Catalyst\Framework\Controllers |
| `docs/framework-database.md` | Catalyst\Framework\Database |
| `docs/framework-datagrid.md` | Catalyst Framework DataGrid |
| `docs/framework-enums.md` | Catalyst\Framework\Enums |
| `docs/framework-event.md` | Catalyst\Framework\Event |
| `docs/framework-geo.md` | Catalyst\Framework\Geo |
| `docs/framework-mail.md` | Catalyst\Framework\Mail |
| `docs/framework-notification.md` | Catalyst\Framework\Notification |
| `docs/framework-organization.md` | Catalyst Framework Organization |
| `docs/framework-queue.md` | Catalyst\Framework\Queue |
| `docs/framework-reporting.md` | Catalyst Framework Reporting |
| `docs/framework-schedule.md` | Catalyst\Framework\Schedule |
| `docs/framework-scaffolding.md` | Catalyst Framework Scaffolding |
| `docs/framework-session.md` | Catalyst\Framework\Session |
| `docs/framework-traits.md` | Catalyst\Framework\Traits |
| `docs/framework-view.md` | Catalyst\Framework\View |
| `docs/framework-websocket.md` | Catalyst\Framework\WebSocket |
| `docs/framework-workflow.md` | Catalyst Framework Workflow |
| `docs/harness-context-map.md` | Catalyst Harness Context Map |
| `docs/helpers-config.md` | Catalyst\Helpers\Config |
| `docs/helpers-debug.md` | Catalyst\Helpers\Debug |
| `docs/helpers-error.md` | Catalyst\Helpers\Error |
| `docs/helpers-exceptions.md` | Catalyst\Helpers\Exceptions |
| `docs/helpers-i18n.md` | Catalyst\Helpers\I18n |
| `docs/helpers-log.md` | Catalyst\Helpers\Log |
| `docs/helpers-toolbox.md` | Catalyst\Helpers\ToolBox |
| `docs/helpers-validation.md` | Catalyst\Helpers\Validation |
| `docs/kernel.md` | Kernel |
| `docs/middleware.md` | Middleware Index |
| `docs/modules.md` | Module Index |
| `docs/app-boundary.md` | App Boundary |
| `docs/quality-gate.md` | Quality Gate |
| `docs/repository-auth.md` | Catalyst\Repository\Auth |
| `docs/repository-devtools.md` | Catalyst\Repository\DevTools |
| `docs/repository-notification.md` | Catalyst\Repository\Notification |
| `docs/reverse-cascade-delete.md` | Reverse Cascade Delete |
| `docs/routing.md` | Routing Index |
| `docs/runtime-inventory.md` | Runtime Inventory |
| `docs/runtime-model.md` | Runtime Model |
| `docs/runtime-module-catalog.md` | Runtime Module Catalog |
| `docs/security-conventions.md` | Security Conventions |
| `docs/sequences.md` | Transactional Sequences |
| `docs/spec-to-catalyst-guide.md` | Spec To Catalyst Guide |
| `docs/testing.md` | Testing Guide |
| `docs/ui/admin-surface-contract.md` | Admin surface contract |
| `docs/ui/datagrid-visual-guidelines.md` | Lineamientos visuales del DataGrid |
| `docs/ui/institutional-themes.md` | Response skins and neutral branding |
| `docs/ui/public-surface-contract.md` | Public surface contract |
| `docs/ui/sidebar-navigation.md` | Sidebar y navegacion administrativa |
| `docs/ui/validation-checklist.md` | Checklist de validacion del parche visual |
| `docs/ui/visual-refactor-v2.md` | Refactor visual v2 del framework Catalyst |
| `docs/views.md` | Views Index |
| `docs/workflow/first-run.md` | First Run Workflow |
| `docs/workflow/patch-intake.md` | Patch Intake Workflow |
| `docs/workflow/release-v0.1.0-rc.3.md` | Catalyst v0.1.0-rc.3 Upgrade Notes |
| `docs/workflow/release-rc-checklist.md` | Release Candidate Checklist |
| `docs/workflow/reusable-base-install.md` | Reusable Base Install Workflow |

## Operational Notes

- Use `docs/runtime-inventory.md` for exhaustive class/template/script truth.
- Use `docs/runtime-module-catalog.md` and `php public/cli.php route:list --json` for live module and route truth.
- Historical development plans, audit notes and route snapshots are not kept in `/docs`; closed history belongs in Obsidian summaries or Git history.
- If a Markdown file is added under `/docs`, update this index or confirm it is generated by the runtime.

## Related Documentation

- `docs/harness-context-map.md`
- `docs/documentation-contract.md`
- `docs/runtime-inventory.md`
- `docs/runtime-module-catalog.md`
- `API.md`
- `STRUCTURE.md`
