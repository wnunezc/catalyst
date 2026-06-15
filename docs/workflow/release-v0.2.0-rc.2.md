# Catalyst v0.2.0-rc.2 Corrective Upgrade And Release Notes

## Purpose

`v0.2.0-rc.2` is a corrective and security-hardening follow-up to
`v0.2.0-rc.1`. It resolves GitHub issues #14, #15 and #16 without restoring
retired surface classes or weakening framework ownership boundaries.

Projects upgrading directly from `v0.1.0-rc.8` must first read
`docs/workflow/release-v0.2.0-rc.1.md` for the structural migration, then apply
the RC2 corrections documented here.

## Resolved Issues

### Issue #14: Account App Test Contract

The app-owned Account architecture assertion now follows the canonical
`catalyst-shell-body` contract and explicitly rejects the retired
`account-page-body` and `account-guest-body` classes.

### Issue #15: Feature-Flag Rollback Journals

Documents, Audit and ApiPlatform owner migrations now use independent rollback
journals:

| Legacy owner | RC2 rollback journal |
|---|---|
| Documents | `workspaces_documents_feature_flag_migration` |
| Audit | `operations_audit_feature_flag_migration` |
| ApiPlatform | `operations_api_platform_feature_flag_migration` |

Concurrent overrides can now migrate and roll back without being restored under
the wrong source owner or losing another migration's journal.

The RC1 table `documents_feature_flag_migration` did not record source
ownership. When an already-applied RC1 installation has rows in that shared
table, RC2 fails rollback explicitly because Audit and ApiPlatform rows cannot
be classified safely after both were migrated to Operations. Restore the
pre-migration database backup or classify those rows manually before rollback.
An empty RC1 shared journal requires no data repair.

Audit before rollback:

```sql
SELECT COUNT(*) AS rows_to_classify
FROM documents_feature_flag_migration;
```

### Issue #16: Portable Framework Unit Tests

Frozen route and PageHeader totals now scan only framework-owned paths.
Additional valid modules, routes and PageHeader producers under
`Repository/App` remain owned by the derived project's `test/app` suite and no
longer invalidate inherited framework tests.

## Security Hardening Included

- Default password policy now requires 12 characters, uppercase, lowercase,
  number and symbol content, and blocks common passwords.
- TOTP login challenges persist and atomically consume the accepted timestep to
  prevent replay of the same or an older code per tenant/account.
- CSP nonce generation fails closed when secure randomness is unavailable.
- Media uploads deny executable, HTML and SVG active content by default. SVG
  requires explicit configuration opt-in.
- Composer resolution rejects `guzzlehttp/psr7 <2.10.2`, preventing installation
  of versions affected by CVE-2026-48998 and CVE-2026-49214.

Run migrations after merging so `users.mfa_last_totp_step` is available.

## Upgrade From v0.2.0-rc.1

```powershell
git fetch upstream --tags
git diff --stat v0.2.0-rc.1..v0.2.0-rc.2
git merge v0.2.0-rc.2
composer install
php public/cli.php config:sync
php public/cli.php migrate:status
php public/cli.php migrate
php public/cli.php inspect:lint
php public/cli.php route:lint
php public/cli.php security:check
php public/cli.php quality:check
php test/app/UnitTest/run.php
```

Derived projects retaining `test/framework` may run that suite after resolving
framework conflicts. Product-specific assertions remain under `test/app`.

## Focused Verification Performed

- App Account shell contract.
- Framework-only route and PageHeader inventories.
- Independent feature-flag journal architecture contract.
- Temporary-database integration covering simultaneous Documents, Audit and
  ApiPlatform overrides, a preexisting target collision and exact reverse
  rollback restoration.
- Password policy, TOTP replay-protection wiring, CSP nonce fail-closed behavior
  and media active-content defaults.

## Release Contract

- `catalyst.json` reports `0.2.0-rc.2` with channel `rc`.
- Publish as a GitHub pre-release through `.github/workflows/release.yml`.
- ROADMAP files, backups, local secrets, uploads, runtime state and ad-hoc
  archives remain outside the release commit.
- Verify the public ZIP and SHA-256 asset before closing issues #14, #15 and
  #16.
