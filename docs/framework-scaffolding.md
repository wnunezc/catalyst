# Catalyst Framework Scaffolding

Catalyst scaffolding generates framework-compatible module structure without putting application domain code inside framework folders.

## Commands

```powershell
php public/cli.php make:module Intake --space=App --surface=administration --permission=manage-intake
php public/cli.php make:module Intake --space=App --surface=administration --permission=manage-intake --preset=complex
php public/cli.php make:crud Catalog CatalogItem --fields="name:text!,slug:text!"
php public/cli.php scaffold:app-smoke --json
```

## Basic Module

`make:module` creates:

- `Repository/App/Surface/{Module}/Controllers/{Module}Controller.php`
- `Repository/App/Surface/{Module}/Views/pages/index.phtml`
- `Repository/App/Surface/{Module}/front/script.js`
- `Repository/App/Surface/{Module}/front/style.css`
- `Repository/App/Surface/{Module}/lang/en/{slug}.json`
- `Repository/App/Surface/{Module}/lang/es/{slug}.json`
- `Repository/App/Surface/{Module}/routes.php`
- `Repository/App/Surface/{Module}/module.php`

## Complex Preset

`--preset=complex` adds app-owned extension points for larger systems:

- centralized request validation;
- policy authorization;
- repository persistence boundary;
- service business boundary;
- report provider skeleton;
- calendar provider skeleton;
- workflow definition skeleton;
- reverse cascade delete plan factory;
- migration baseline;
- placeholders for attachments, references, sequences, DataGrid and FormBuilder usage.

The preset stays domain-neutral. It does not create RTM Hub, RTM tables, RTM routes or RTM screens inside Catalyst.

## Capabilities

Capabilities can be added explicitly:

```powershell
php public/cli.php make:module Intake --surface=workspace --permission=manage-intake --capabilities=request,policy,service,repository,reports
```

Allowed capabilities:

- `attachments`
- `calendar`
- `datagrid`
- `delete-policy`
- `form`
- `migration`
- `policy`
- `references`
- `repository`
- `reports`
- `request`
- `sequence`
- `service`
- `workflow`

## Happy Path

1. Developer chooses `Repository/App` module name, surface and permission.
2. Scaffold validates the surface/permission combination.
3. Basic files, language files and front assets are generated.
4. Complex preset adds app-owned service, repository and framework provider extension points.
5. Generated routes and manifest remain compatible with module lint and route lint.

## Sad Path

Scaffolding rejects:

- unknown presets;
- unknown capabilities;
- multi-segment module names;
- permission slugs on `public` or `none` surfaces;
- invalid table names;
- existing module directories or generated target files.

## Verification

```powershell
php public/cli.php scaffold:app-smoke --json
php public/cli.php inspect:lint
php public/cli.php route:lint
```
