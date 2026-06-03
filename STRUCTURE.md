# Catalyst Framework - Technical Structure Index

## Purpose

Provide a compact technical dictionary for avoiding duplicate classes and locating current framework owners. Exhaustive symbol, template and script truth lives in `docs/runtime-inventory.md`.

## Runtime Owners

| Concern | Owner |
|---|---|
| Runtime symbol inventory | `docs/runtime-inventory.md` |
| Module/route/permission/asset catalog | `docs/runtime-module-catalog.md` |
| Architecture and docs index | `docs/architecture.md` |
| API index | `API.md` |

## Current Behavior

Catalyst is split into these active code roots:

| Root | Purpose |
|---|---|
| `app/Framework` | Framework primitives: routing, middleware, controllers, view, database, queue, schedule, event, modules, auth, authorization, storage, mail, reporting, retention and related managers. |
| `app/Helpers` | Config, security, logging, validation, i18n, debug, error handling and terminal formatting helpers. |
| `app/Entities` | Shared ORM entities used by framework and repository modules. |
| `Repository/Framework` | Framework-owned UI/API modules such as Auth, Roles, Settings, Operations, Documents, Media, Automation, Catalogs, ApiPlatform, Audit, Notification, DemoUi and DevTools. |
| `Repository/App/Surface` | App-owned public/authenticated surfaces: Home, Landing, Store, Dashboard and Account. |
| `boot-core/template` | Shared framework templates and layout/scope components. |
| `public/assets/js/catalyst` | Shared frontend runtime modules. |

## Key Namespaces

| Namespace | Directory | Documentation |
|---|---|---|
| `Catalyst\Kernel` | `app/Kernel.php` | `docs/kernel.md` |
| `Catalyst\Framework\Route` | `app/Framework/Route/` | `docs/routing.md` |
| `Catalyst\Framework\Middleware` | `app/Framework/Middleware/` | `docs/middleware.md` |
| `Catalyst\Framework\Controllers` | `app/Framework/Controllers/` | `docs/framework-controllers.md` |
| `Catalyst\Framework\View` | `app/Framework/View/` | `docs/framework-view.md` |
| `Catalyst\Framework\Database` | `app/Framework/Database/` | `docs/framework-database.md` |
| `Catalyst\Framework\Auth` | `app/Framework/Auth/` | `docs/framework-auth.md` |
| `Catalyst\Framework\Queue` | `app/Framework/Queue/` | `docs/framework-queue.md` |
| `Catalyst\Framework\Schedule` | `app/Framework/Schedule/` | `docs/framework-schedule.md` |
| `Catalyst\Framework\Event` | `app/Framework/Event/` | `docs/framework-event.md` |
| `Catalyst\Framework\Module` | `app/Framework/Module/` | `docs/modules.md`, `docs/runtime-module-catalog.md` |
| `Catalyst\Framework\Documentation` | `app/Framework/Documentation/` | `docs/documentation-contract.md`, `docs/runtime-inventory.md` |
| `Catalyst\Helpers\*` | `app/Helpers/` | `docs/helpers-*.md` |
| `Catalyst\Repository\*` | `Repository/Framework/` | `docs/repository-*.md`, `docs/runtime-module-catalog.md` |
| `App\Surface\*` | `Repository/App/Surface/` | `docs/runtime-module-catalog.md` |

## CLI Truth Sources

```powershell
php public/cli.php docs:inventory --json
php public/cli.php docs:sync-runtime --stdout
php public/cli.php route:list --json
php public/cli.php inspect:lint
php public/cli.php route:lint
php public/cli.php quality:check
```

## Operational Notes

- Check `docs/runtime-inventory.md` before creating new classes.
- Check `docs/runtime-module-catalog.md` before adding routes, permissions, navigation, module assets or settings.
- Keep process history outside `/docs`; use Obsidian summaries for closed development history.

## Related Documentation

- `docs/architecture.md`
- `API.md`
- `docs/runtime-inventory.md`
- `docs/runtime-module-catalog.md`
- `docs/harness-context-map.md`