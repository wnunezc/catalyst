# Catalyst v0.1.0-rc.4 Upgrade Notes

## Scope

`v0.1.0-rc.4` supersedes `v0.1.0-rc.3` and closes the admin sidebar regression found after issue #6:

- restores the curated sidebar taxonomy: `Configuration`, `Workspaces`, `Operations`, `Users`, `Devtools`;
- keeps `NavigationRegistry::adminShell()` as the discovery source without letting incomplete manifest contexts reorder the visual menu;
- keeps `Organization Hierarchy` under `Users`;
- moves `Account Recovery` under `Users`;
- keeps `Test Features`, `UI Showcase`, `UML / Architecture` and `Demo UI` under `Devtools`;
- prevents nested `Users`, `Operations` inside `Configuration`, `Security` as a separate admin group and duplicate visible `Devtools` sections.

## Local Upgrade

From a derived project that tracks Catalyst as `upstream`:

```powershell
git status --short --branch
git fetch upstream --tags
git merge v0.1.0-rc.4
composer install
php public/cli.php migrate:status
php public/cli.php admin-navigation:smoke --json
php public/cli.php quality:check
```

No database migration is introduced by this RC. Resolve conflicts by preserving application work in `Repository/App/` and accepting framework-owned sidebar/navigation changes in `app/Framework/Navigation/`, `app/Framework/Cli/Commands/AdminNavigationSmokeCommand.php`, `boot-core/template/scope/layouts/_demo-product-shell.php`, `docs/` and `public/cli.php`.

## Verification

Run:

```powershell
php public/cli.php admin-navigation:smoke --json
php public/cli.php inspect:lint
php public/cli.php quality:check
```

For browser verification, sign in as an admin and open `/users/organization-hierarchy`. The rendered sidebar must contain:

- `Configuration`: Environment Setup, Application Health, Platform Appearance, Feature Flags, Plugins Management;
- `Workspaces`: Catalogs, Module Designer, Media and Documents Fields, Media and Documents Library, Document Template, Locale Tools;
- `Operations`: Deployments, Tenancy, Audit Log, API Platform, Automation Rules;
- `Users`: User Management, User Role, User Permissions, User Enroll, Organization Hierarchy, Account Recovery;
- `Devtools`: Test Features, UI Showcase, UML / Architecture, Demo UI.
