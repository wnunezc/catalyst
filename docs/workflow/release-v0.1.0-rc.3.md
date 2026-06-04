# Catalyst v0.1.0-rc.3 Upgrade Notes

## Scope

`v0.1.0-rc.3` closes GitHub issue #6:

- the admin sidebar now renders module-declared `navigation.admin` entries through `NavigationRegistry::adminShell()`;
- `_demo-product-shell.php` no longer owns hardcoded framework/admin route lists;
- `AdminShellNavigationPresenter` adapts registry groups into the existing sidebar template model;
- `/users/organization-hierarchy` is visible under `Users` for administrators with `manage-roles`;
- `inspect:lint` and `admin-navigation:smoke` guard against sidebar/manifest drift.

## Local Upgrade

From a derived project that tracks Catalyst as `upstream`:

```powershell
git status --short --branch
git fetch upstream --tags
git merge v0.1.0-rc.3
composer install
php public/cli.php migrate:status
php public/cli.php migrate
php public/cli.php admin-navigation:smoke --json
php public/cli.php quality:check
```

No database migration is introduced by this RC. Resolve merge conflicts by keeping application-specific work in `Repository/App/` and accepting framework-owned changes in `app/Framework/Navigation/`, `app/Framework/Module/ModuleLinter.php`, `boot-core/template/scope/layouts/_demo-product-shell.php`, `public/cli.php` and generated runtime docs when appropriate.

## Sidebar Contract

Future framework or application modules should expose primary admin surfaces in `module.php` under `navigation.admin`. Do not add framework/admin links by editing `_demo-product-shell.php`.

For a local browser check, sign in as an admin and open:

```text
/users/organization-hierarchy
```

The `Users` sidebar group must be expanded, the organization hierarchy item must be present and the item must be active.

## Verification

Run:

```powershell
php public/cli.php admin-navigation:smoke --json
php public/cli.php inspect:lint
php public/cli.php route:lint
php public/cli.php quality:check
```
