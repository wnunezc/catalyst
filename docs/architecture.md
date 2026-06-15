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
| Frontend UI orchestration | `public/assets/js/catalyst/runtime/ui-runtime.js` |
| Generated documentation truth | `RuntimeInventoryGenerator`, `DocsSyncRuntimeCommand` |

## Current Behavior

Catalyst is a PHP 8.4 MVC framework with a small kernel, explicit HTTP/CLI entry points, framework-owned reusable modules under `Repository/Framework`, app-owned surfaces under `Repository/App/Surface`, and runtime-generated documentation inventories. The current module catalog reports 14 modules with structural lint OK.

Configuration, Users, Account, Workspaces and Operations are canonical
aggregate owners. Workspaces owns 49 routes, Operations owns 21 routes and the
independent Framework API owner holds six transversal routes. Together
Workspaces, Operations and API preserve the 76 ROADMAP-3 routes and 13 public
APIs under `/api/v1/*`.

The architecture uses separated owners instead of single large classes: routes are registered, compiled, collected and dispatched by different route classes; modules are declared, discovered, inspected and linted by different module classes; views render templates while trusted HTML, inline JSON and token rendering are separate security boundaries.

Catalyst renders every complete HTML response through
`boot-core/template/document.phtml`. `View::render()` uses that document by
default; insertable HTML must use the explicit `View::renderFragment()` /
`Controller::viewFragment()` contract. There are no layout profiles or
surface-specific document wrappers.

The document composes `_html-open.phtml`, `_head.phtml`, `shell.phtml` and
`_body-scripts.phtml`. Topbar, sidebar, status bar and the theme customizer are
capabilities of the shared shell. Exceptional surfaces such as Auth, Public,
Account guest and Error control visibility and CSS classes through explicit
scope values; they do not select another layout.

Catalyst uses one frontend governor. `_body-scripts.phtml` loads
`runtime/ui-runtime.js` directly at the end of the body. The runtime owns
ordered component registration, global runtime events, initial mounting,
targeted rescans after `catalyst:dom:updated`, destruction and module
extension. Inspinia and Bootstrap initializers are adapters of that runtime;
surface work scripts register extensions or remain inert and never start a
parallel governor.

The same governor mounts one global `ActivityManager`. The canonical document
owns one initially visible activity overlay; the manager releases it after
runtime mounting and coordinates internal navigation, native submits and
foreground `HttpClient` requests. Automatic background transports never block
the UI.

Modules may extend the active runtime through `Catalyst.ui.register()` and
`Catalyst.ui.registerEvent()`. They must register behavior, not start a second
runtime.

All participating shells use the common `Catalyst.ui.initRuntime()` entry
point. Surface-specific scripts extend that runtime and do not initialize a
parallel governor.

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
| `docs/checklists/setup-completion-e2e.md` | Checklist E2E — `/configuration/environment-setup` privileged + finalización |
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
| `docs/framework-configuration.md` | Catalyst Framework Configuration |
| `docs/framework-controllers.md` | Catalyst\Framework\Controllers |
| `docs/framework-database.md` | Catalyst\Framework\Database |
| `docs/framework-datagrid.md` | Catalyst Framework DataGrid |
| `docs/framework-enums.md` | Catalyst\Framework\Enums |
| `docs/framework-event.md` | Catalyst\Framework\Event |
| `docs/framework-form-builder.md` | Catalyst Framework FormBuilder |
| `docs/framework-geo.md` | Catalyst\Framework\Geo |
| `docs/framework-mail.md` | Catalyst\Framework\Mail |
| `docs/framework-modals.md` | Catalyst Framework Modals |
| `docs/framework-notification.md` | Catalyst\Framework\Notification |
| `docs/framework-organization.md` | Catalyst Framework Organization |
| `docs/framework-queue.md` | Catalyst\Framework\Queue |
| `docs/framework-record-presence.md` | Catalyst Framework RecordPresence |
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
| `docs/repository-workspaces.md` | Workspaces Owner |
| `docs/repository-operations.md` | Operations Owner |
| `docs/repository-api.md` | Framework API Owner |
| `docs/reverse-cascade-delete.md` | Reverse Cascade Delete |
| `docs/routing.md` | Routing Index |
| `docs/runtime-inventory.md` | Runtime Inventory |
| `docs/runtime-model.md` | Runtime Model |
| `docs/runtime-module-catalog.md` | Runtime Module Catalog |
| `docs/security-conventions.md` | Security Conventions |
| `docs/sequences.md` | Transactional Sequences |
| `docs/spec-to-catalyst-guide.md` | Spec To Catalyst Guide |
| `docs/testing.md` | Testing Guide |
| `docs/ui/page-header-contract.md` | Global PageHeader contract |
| `docs/ui/activity-overlay.md` | Global navigation and request activity contract |
| `docs/ui/css-ownership.md` | CSS ownership and common-layout inventory |
| `docs/ui/datagrid-visual-guidelines.md` | Lineamientos visuales del DataGrid |
| `docs/ui/demo-ui-javascript-inventory.md` | Demo UI JavaScript route, asset and Playwright inventory |
| `docs/ui/institutional-themes.md` | Response skins and neutral branding |
| `docs/ui/public-surface-contract.md` | Public surface contract |
| `docs/ui/sidebar-navigation.md` | Sidebar y navegacion privilegiada |
| `docs/ui/surface-architecture.md` | Canonical document, shell, runtime and surface ownership |
| `docs/ui/test-features-javascript-inventory.md` | Test Features document, JavaScript and Playwright inventory |
| `docs/ui/validation-checklist.md` | Checklist de validacion del parche visual |
| `docs/views.md` | Views Index |
| `docs/workflow/first-run.md` | First Run Workflow |
| `docs/workflow/patch-intake.md` | Patch Intake Workflow |
| `docs/workflow/release-v0.1.0-rc.2.md` | Catalyst v0.1.0-rc.2 Upgrade Notes |
| `docs/workflow/release-v0.1.0-rc.6.md` | Catalyst v0.1.0-rc.6 Upgrade Notes |
| `docs/workflow/release-v0.1.0-rc.7.md` | Catalyst v0.1.0-rc.7 Upgrade Notes |
| `docs/workflow/release-v0.1.0-rc.8.md` | Catalyst v0.1.0-rc.8 Upgrade Notes |
| `docs/workflow/release-v0.2.0-rc.1.md` | Catalyst v0.2.0-rc.1 migration and release notes |
| `docs/workflow/release-v0.2.0-rc.2.md` | Catalyst v0.2.0-rc.2 corrective upgrade and release notes |
| `docs/workflow/release-v0.1.0-rc.5.md` | Catalyst v0.1.0-rc.5 Upgrade Notes |
| `docs/workflow/release-v0.1.0-rc.4.md` | Catalyst v0.1.0-rc.4 Upgrade Notes |
| `docs/workflow/release-v0.1.0-rc.3.md` | Catalyst v0.1.0-rc.3 Upgrade Notes |
| `docs/workflow/release-rc-checklist.md` | Release Candidate Checklist |
| `docs/workflow/reusable-base-install.md` | Reusable Base Install Workflow |

## Operational Notes

- Use `docs/runtime-inventory.md` for exhaustive class/template/script truth.
- Use `docs/runtime-module-catalog.md` and `php public/cli.php route:list --json` for live module and route truth.
- Regenerate those inventories with `php public/cli.php docs:inventory` and `php public/cli.php docs:sync-runtime`; never edit generated inventory files manually.
- Historical development plans, audit notes and route snapshots are not kept in `/docs`; closed history belongs in Obsidian summaries or Git history.
- If a Markdown file is added under `/docs`, update this index or confirm it is generated by the runtime.

## Related Documentation

- `docs/harness-context-map.md`
- `docs/documentation-contract.md`
- `docs/runtime-inventory.md`
- `docs/runtime-module-catalog.md`
- `API.md`
- `STRUCTURE.md`
