# Catalyst v0.1.0-rc.2 Upgrade Notes

## Scope

`v0.1.0-rc.2` closes the GitHub issue batch #1-#5:

- reusable-base distribution now ships development config in first-run mode;
- migration discovery contract is fixed for framework sequences;
- empty database migration failures include setup bootstrap guidance;
- anonymous shell registration links honor `auth.registration_enabled`;
- organization hierarchy primitives and administration UI are available for role classification.

## Local Upgrade

From a derived project that tracks Catalyst as `upstream`:

```powershell
git status --short --branch
git fetch upstream --tags
git merge v0.1.0-rc.2
composer install
php public/cli.php migrate:status
php public/cli.php migrate
php public/cli.php quality:check
```

Resolve merge conflicts by keeping application-specific work in `Repository/App/` and accepting framework-owned changes in `app/`, `boot-core/`, `Repository/Framework/`, `public/cli.php` and generated runtime docs when appropriate.

## Organization Hierarchy Setup

This release does not seed organization data. After migration, an administrator or developer with `manage-roles` must configure hierarchy metadata from:

```text
/users/organization-hierarchy
```

Recommended order:

1. Create the organization.
2. Create hierarchy scopes.
3. Create levels under those scopes.
4. Create organization units if the application needs them.
5. Create or edit roles and assign scope/level metadata.

RBAC behavior does not change. Role and permission checks still decide access; hierarchy metadata is used for classification, display and future catalog/reporting features.

## Verification

Run:

```powershell
php public/cli.php distribution:smoke --json
php public/cli.php organization:smoke --json
php public/cli.php roles:mvc-regression
php public/cli.php i18n:usage-lint
php public/cli.php route:lint
php public/cli.php inspect:lint
php public/cli.php security:check
php public/cli.php quality:check
```

For browser/E2E verification, sign in as an admin, configure hierarchy metadata from `/users/organization-hierarchy`, then create a role from `/users/roles/create` using the configured scope and level.
