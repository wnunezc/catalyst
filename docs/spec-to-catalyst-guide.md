# Spec To Catalyst Guide

Use this guide when a large product specification, institutional workflow or imported project brief needs to become a Catalyst application. The goal is to map the spec into framework extension points without creating a second framework inside the app.

## Non-Negotiable Boundaries

- Keep application modules under `Repository/App/Surface/{Module}`.
- Keep framework-owned reusable capabilities under `app/Framework` or `Repository/Framework`.
- Do not create app routers, kernels, service containers or `/app/Core` equivalents inside a derived app.
- Do not place product-specific modules, tables or screens inside Catalyst itself.
- Keep PHP, HTML, CSS and JavaScript separated.
- Use controllers for HTTP coordination, services for business logic, repositories/DAO for persistence and FormRequest classes for validation.
- Escape output by default and respect CSP.
- Keep language files current for visible module text.

## Intake Checklist

Before implementation, convert the source spec into these sections:

| Section | Decision |
|---|---|
| Product scope | What belongs to the derived app and what belongs to reusable Catalyst framework infrastructure. |
| Actors | Roles, permissions and approval responsibilities. |
| Data | Entities, tables, metadata fields, references, sequences and retention expectations. |
| Screens | App modules, views, layouts, DataGrid/FormBuilder needs and frontend assets. |
| Routes | HTML routes, JSON/API routes, mutations, auth guards and API token use. |
| Validation | FormRequest classes, accepted inputs, labels and normalization. |
| Persistence | Repositories, prepared statements/query builder use and migration ownership. |
| Services | Business workflows, side effects, queue jobs and scheduled tasks. |
| Permissions | Ability/resource map, role fallbacks and negative cases. |
| Workflows | State machines, approvals, transition guards and audit trail. |
| Attachments | Storage disk, allowed MIME/types, QR verification and private access. |
| Reports | Report providers, filters, permissions and CSV/XLS/PDF requirements. |
| Calendar | Calendar providers, event ranges, permission filtering and optional frontend renderer. |
| Deletes | Reverse cascade delete preview, blockers, confirmation token and handlers. |
| Integrations | OAuth/API providers, retries, credentials and future driver boundaries. |
| Harness | Happy Path, Sad Path, smoke command and lint commands per module. |

## Classification

Classify each spec requirement before writing code:

| Classification | Destination |
|---|---|
| Reusable framework capability | Catalyst patch under `app/Framework`, `Repository/Framework`, docs, CLI or migrations. |
| Product-specific app behavior | `Repository/App/Surface/{Module}` in the derived app. |
| Runtime configuration | `boot-core/config/{environment}`, `.env` or documented setup. |
| Generated/public work asset | Module `front/` source plus `public/assets/{css,js}/work/{slug}` output. |
| External integration | Provider/driver contract first, concrete app credentials/config outside source. |
| Out of scope | Record as a gap or future phase instead of hiding it in framework folders. |

## Module Mapping Template

Use one row per candidate module:

| Module | Surface | Routes | Requests | Services | Repositories | Permissions | Workflow | Attachments | Reports | Calendar | Delete Policy |
|---|---|---|---|---|---|---|---|---|---|---|---|
| `{Module}` | `public/workspace/privileged/devtools` | HTML/API/mutations | FormRequest list | Business actions | Tables/queries | Slugs | Definition key | Purposes/storage | Provider key | Provider key | Preview factory |

Start complex modules with:

```powershell
php public/cli.php make:module {Module} --space=App --surface=privileged --permission=manage-{module} --preset=complex
```

Use `make:crud` when the module is primarily a resource CRUD surface. Select
`workspace` or `privileged` explicitly and keep domain workflows outside
the generated controller.

## Happy Path Template

For each module, define:

1. Actor has the required permission.
2. Request passes FormRequest validation.
3. Controller delegates to service.
4. Service calls repository/provider contracts.
5. Persistence uses prepared statements/query builder.
6. View escapes output and uses language keys.
7. Module routes, assets and guards pass harness lint.
8. Smoke command proves the expected flow.

## Sad Path Template

For each module, define:

- unauthenticated request;
- authenticated but unauthorized actor;
- invalid payload;
- missing referenced record;
- duplicate sequence/reference;
- blocked workflow transition;
- forbidden attachment type/storage;
- report/calendar event hidden by permissions;
- reverse cascade delete blocked by dependencies;
- integration unavailable or credentials missing.

## RTM Hub As Conceptual Example

RTM Hub should be treated as one institutional derived app. Catalyst may add reusable capabilities needed by RTM-like systems, but Catalyst must not contain RTM modules, RTM tables or RTM screens.

Conceptual mapping:

| RTM-like concern | Catalyst destination |
|---|---|
| Institutional dashboard, training records, participants and providers | Derived app modules under `Repository/App/Surface/*`. |
| Permission primitives, report providers, workflow engine, calendar providers, attachment policy, sequence service | Reusable Catalyst framework contracts. |
| Google Calendar sync | Integration provider/driver boundary first; concrete credentials/config in derived app runtime. |
| Advanced PDFs/XLSX | Exporter drivers after dependency approval. |
| Strong tenancy | Not required for a single-institution RTM app; keep permission hardening separate from tenancy. |

## Verification

For each adapted spec section, run the narrow checks first and then the framework gate:

```powershell
php -l <modified PHP files>
php public/cli.php scaffold:app-smoke --json
php public/cli.php inspect:lint
php public/cli.php route:lint
php public/cli.php security:check
php public/cli.php quality:check
git diff --check
```

Use MFA-Forge only when a real local session or MFA-protected happy/sad path is required. Do not invent tokens or bypass the workspace session flow.
