# App Boundary

## Purpose

Define the source boundary between Catalyst framework-owned code and application-owned code in derived projects.

Catalyst is distributed as a project base. A derived application may receive upstream updates from `wnunezc/catalyst`, so application work must stay in predictable extension points instead of modifying framework-owned runtime folders.

## Ownership Contract

| Owner | Paths | Rule |
|---|---|---|
| Framework | `app/`, `boot-core/`, `Repository/Framework/`, `public/assets/js/catalyst/`, `public/assets/css/catalyst/` | Updated by Catalyst releases. Derived apps should not add application modules here. |
| Application | `Repository/App/` | Primary home for app modules, routes, controllers, requests, policies, services, repositories, views, lang files and source front assets. |
| Generated work assets | `public/assets/{css,js}/work/{slug}/` | Published output from module `front/` assets. Do not edit as source. |
| Runtime/local | `boot-core/config/{environment}/`, `.env`, storage, uploads, logs | Environment-specific state. Do not treat as distributable source. |

## Application Module Layout

Application modules should use this shape:

```text
Repository/App/Surface/{Module}/
+-- Controllers/
+-- Requests/
+-- Policies/
+-- Services/
+-- Repositories/
+-- routes.php
+-- module.php
+-- lang/
+-- views/
+-- front/
    +-- style.css
    +-- script.js
```

The framework scaffolding commands should generate this shape. If a module requires migrations, entities, report providers, calendar providers, workflow declarations or delete policies, keep the app-specific implementation under `Repository/App` unless a reusable framework contract already exists.

## Lint Contract

`php public/cli.php inspect:lint` validates the boundary with the `app_boundary` check.

It currently fails when it detects:

- application source roots such as `app/App`, `app/Application`, `app/Module`, `app/Modules`, `app/Surface` or `app/Surfaces`;
- unsupported `Repository/*` roots outside `Repository/App` and `Repository/Framework`;
- application source asset roots under `public/assets/app`, `public/assets/css/app` or `public/assets/js/app`.

These checks are intentionally conservative. They protect common mistakes without blocking legitimate generated assets under `public/assets/{css,js}/work/{slug}/`.

## Update Safety Checklist

Before merging a Catalyst upstream release into a derived app:

1. Run `git status --short --branch` and keep unrelated app work out of the merge.
2. Review the release notes for touched folders, migrations, config changes and verification commands.
3. Run `php public/cli.php update:check`.
4. Run `php public/cli.php inspect:lint` before and after the merge.
5. Run `php public/cli.php route:lint`, `php public/cli.php security:check` and `php public/cli.php quality:check`.
6. Resolve conflicts by preserving app-specific work under `Repository/App` and framework updates under framework-owned paths.

If a derived app must change a framework-owned folder to support a reusable capability, treat it as a Catalyst framework patch first. Do not hide framework improvements inside an app module.
