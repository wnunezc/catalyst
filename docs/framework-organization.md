# Catalyst Framework Organization

`Catalyst\Framework\Organization` owns configurable institutional hierarchy metadata for framework modules and derived applications. It is classification infrastructure only: permissions, policies and RBAC checks continue to decide access.

## Model

| Primitive | Purpose |
|---|---|
| `organizations` | Tenant-scoped organization identity and default organization marker. |
| `organization_units` | Horizontal divisions such as areas, departments, faculties, services, teams or regions. |
| `hierarchy_scopes` | Independent hierarchy axes such as authority, academic level, technical specialization or operational readiness. |
| `hierarchy_levels` | Ordered levels, ranks or grades under one scope. |
| `organization_classifications` | Generic assignments from `resource_key + record_id` to organization, scope, level and optional unit. |
| `role_organization_units` | Optional role-to-unit links for roles that operate in one or more divisions. |

Roles also expose nullable `hierarchy_scope_id` and `hierarchy_level_id` metadata. Existing roles without classification continue to work.

## Administration UI

The hierarchy engine is intentionally empty after migration. Catalyst does not seed institution-specific organization data because derived applications need to define their own institution model.

Administrators with `manage-roles` can configure the hierarchy from:

```text
/users/organization-hierarchy
```

Use the UI in this order:

1. Create an organization.
2. Create one or more hierarchy scopes for that organization.
3. Create hierarchy levels under each scope.
4. Optionally create organization units.
5. Create or edit roles and select the configured scope, level and one or more
   organization units from the role form.

The role form remains valid when no organization metadata exists. In that state
the hierarchy selectors render with empty/default choices, the organization unit
selector renders empty, and roles continue to behave normally. Clearing all
selected units in the role form synchronizes `role_organization_units` back to an
empty set.

## Runtime Owners

| Class | Responsibility |
|---|---|
| `OrganizationClassification` | Normalizes classification payloads for roles, users, courses, certifications and future catalogs. |
| `OrganizationClassificationPresenter` | Converts classification metadata into badge payloads using configured visual tokens and colors. |
| `OrganizationRepository` | Reads and writes tenant-scoped organizations, scopes, levels, units and classifications. |
| `OrganizationHierarchyController` | Provides the administrator UI used to populate organizations, scopes, levels and units. |

## RBAC Boundary

Organization hierarchy data does not grant or deny access. `RoleRepository`, `RoleMiddleware`, `PermissionRegistry` and policies continue to evaluate roles and permissions exactly as before. Hierarchy and units are used for display, reporting, profiles, catalogs and future education surfaces.

## Visual Tokens

`OrganizationClassificationPresenter::badge()` returns:

- `label`
- `title`
- `class`
- `style`
- `scope`
- `level`
- `unit`

The CSS modifier is derived from `visual_token` when configured, otherwise from scope and level. Inline style output is limited to a hex color custom property such as `--org-badge-color:#1f7a8c`.

## Verification

Run:

```powershell
php public/cli.php organization:smoke --json
```

This smoke validates the value object, presenter, migration presence and optional role integration without requiring a database connection.

For an end-to-end local verification, use the browser or HTTP session against the configured site:

1. Sign in as an admin.
2. Open `/users/organization-hierarchy`.
3. Create an organization, scope and level.
4. Open `/users/roles/create`.
5. Confirm the new scope and level appear as options.
6. Create a role with those values and one unit, then verify
   `roles.hierarchy_scope_id`, `roles.hierarchy_level_id` and one
   `role_organization_units` row are persisted.
7. Edit the same role and confirm the unit is preselected.
8. Remove every selected unit, save, and verify the role has no
   `role_organization_units` rows.
