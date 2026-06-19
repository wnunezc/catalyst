# Catalyst v0.2.0-rc.4 Corrective Framework Release Notes

## Purpose

`v0.2.0-rc.4` is a corrective follow-up to `v0.2.0-rc.3`. It resolves the
framework usability and management gaps reported in issues #22 through #29 and
adds authenticated user profile photo management.

## Resolved Issues

### Issue #22: Role Permission Checkboxes Rendered Outside Cards

`/users/roles/{id}/permissions` now uses Bootstrap-compatible option markup.
Checkbox inputs remain inside the bordered option card, labels stay clickable
and the surface keeps the global Bootstrap/Inspinia geometry contract without
surface-specific CSS.

### Issue #23: Role Edit Multiple Select Array Values

`FormBuilderViewModel` now computes checkbox/radio checked state only for field
types that need it. Multiple-select values remain arrays, including
`organization_unit_ids`, and `/users/roles/{id}/edit` loads without array to
string conversion warnings.

### Issue #24 and #27: Foreground Toasts, Wait Overlays and Environment Setup

Foreground form responses now release activity before showing response
notifications, and the toaster stack is layered above long-running activity
overlays. Environment setup settings saves return the canonical JSON redirect
contract, close the active wait state and leave visible feedback instead of a
stuck modal overlay.

### Issue #25: Secure Privileged User Enrollment

Privileged enrollment no longer asks administrators to set a user password.
The flow creates a secure onboarding/reset token, sends the appropriate
localized onboarding email and reports delivery status without exposing tokens,
temporary credentials or transport secrets.

### Issue #26: Organization Hierarchy Management

The organization hierarchy surface now supports controlled update and delete
operations for organizations, units, scopes and levels. Delete operations are
blocked when related data exists and the UI shows the blocking reason.

### Issue #28: Module Designer Management

Module Designer now lists inspected modules, shows state and allows safe
management actions. Framework/core modules and modules with dependencies remain
protected from destructive operations.

### Issue #29: Framework Mail Templates And Multi-Language Email Pipeline

Framework mail now owns a central template manager at
`/workspaces/mail-templates`, visible after Locale Tools. It provides
versionable `system` and `managed` sources under `Repository/Framework/Mail`,
Translator-backed catalogs, locale fallback, safe preview, test delivery,
managed image assets and override restoration. `Repository/App/Mail` is not an
extension point; applications consume stable template keys through
`OutboundEmailService::sendTemplate()`.

Safe preview now follows the `data-catalyst="form"` contract: POST renders the
preview into one-time session state and returns JSON with a redirect target, so
the runtime never receives a full HTML page for a Catalyst AJAX form.

### User Profile Photo Management

`/account/profile` now includes a guarded avatar upload form. JPEG, PNG and
WebP images up to 2 MB are validated by MIME type, stored as framework-managed
user avatar files, persisted in `user_profiles.avatar_path` and projected into
the authenticated shell.

## Upgrade From v0.2.0-rc.3

```powershell
git fetch upstream --tags
git diff --stat v0.2.0-rc.3..v0.2.0-rc.4
git merge v0.2.0-rc.4
composer install
php public/cli.php inspect:lint
php public/cli.php route:lint
php public/cli.php quality:check
```

This release introduces one database migration:

```text
boot-core/database/migrations/20260619090000_add_avatar_path_to_user_profiles.php
```

Run the project migration flow before expecting profile photos to persist.

## Focused Verification

- Mail template manager unit contracts, including system/managed precedence,
  locale fallback, placeholder validation, assets, delivery failures and safe
  preview redirect behavior.
- FormBuilder multiple-select regression.
- Role permission checkbox geometry.
- Environment setup settings response handling.
- Privileged enrollment request and onboarding contracts.
- Organization hierarchy dependency guards.
- Module Designer management guards.
- Account profile avatar upload contract.
- MySQL/MariaDB integration coverage for setup account provisioning and RBAC
  permission migrators previously covered by SQLite-only unit harnesses.
- `php public/cli.php inspect:lint`
- `php public/cli.php route:lint`
- `php public/cli.php shell-navigation:smoke --json`
- `php public/cli.php docs:inventory --json`
- `php public/cli.php docs:sync-runtime --stdout`
- `php public/cli.php quality:check`
- `git diff --check`

Playwright authenticated tests remain dependent on the local MFA-Forge agent
session. In this release environment, MFA-Forge reported `agent session is not
running`, so the authenticated browser cases could not prepare state. The
stateful retry discovered the focused tests but skipped them because no valid
authenticated state was available.

The complete framework unit suite no longer depends on SQLite and passes in
this release environment. Database-backed migrator/provisioner coverage runs
through `test/framework/IntegrationTest` against MySQL/MariaDB using a temporary
database per test.

## Release Contract

- `catalyst.json` reports `0.2.0-rc.4` with channel `rc`.
- Publish as a signed tag and GitHub pre-release through
  `.github/workflows/release.yml`.
- ROADMAP files, backups, local secrets, uploads, runtime state and ad-hoc
  archives remain outside the release commit.
- Verify the public ZIP and SHA-256 asset before considering issues #22
  through #29 closed.
