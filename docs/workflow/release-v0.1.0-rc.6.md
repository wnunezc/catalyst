# Catalyst v0.1.0-rc.6 Upgrade Notes

## Scope

`v0.1.0-rc.6` supersedes `v0.1.0-rc.5` and closes GitHub issue #9:

- adds horizontal organization unit selection to the Roles create/edit form;
- preselects existing `role_organization_units` links when editing a role;
- synchronizes submitted unit ids on create and update through `RoleRepository`;
- clears role-unit links when the administrator removes every selected unit;
- adds shared `FormBuilder` support for safe `select multiple` rendering;
- keeps organization metadata classification-only; RBAC permissions and role
  membership behavior are unchanged.

## Local Upgrade

From a derived project that tracks Catalyst as `upstream`:

```powershell
git status --short --branch
git fetch upstream --tags
git merge v0.1.0-rc.6
composer install
php public/cli.php config:sync
php public/cli.php migrate:status
php public/cli.php migrate
php public/cli.php roles:mvc-regression
php public/cli.php organization:smoke --json
php public/cli.php quality:check
```

No seed data is introduced. Administrators or developers must populate
organization data from `/users/organization-hierarchy`, then assign scope,
level and horizontal units from `/users/roles/create` or the role edit form.

## Developer Impact

Derived applications that customized role forms should keep their local changes
but include the `organization_unit_ids` multi-select and submit it as an array.
Submitting no selected units is supported and clears `role_organization_units`.

The shared form builder now supports fields declared as:

```php
[
    'type' => 'select',
    'multiple' => true,
    'name' => 'organization_unit_ids',
]
```

The template renders the control as `organization_unit_ids[]` and emits a hidden
sentinel so clearing all selections reaches the request layer.

## Verification

Run:

```powershell
php public/cli.php roles:mvc-regression
php public/cli.php organization:smoke --json
php public/cli.php quality:check
```

Manual browser smoke:

1. Open `/users/organization-hierarchy`.
2. Create an organization, scope, level and unit.
3. Open `/users/roles/create`.
4. Create a role with scope, level and one or more units.
5. Edit the role and confirm units are preselected.
6. Remove every unit, save, and confirm the role no longer has
   `role_organization_units` rows.
