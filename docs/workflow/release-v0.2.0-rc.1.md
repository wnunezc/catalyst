# Catalyst v0.2.0-rc.1 Migration And Release Notes

## Purpose

`v0.2.0-rc.1` supersedes `v0.1.0-rc.8` after the completed ROADMAP 1-7
migration sequence. The minor-version change is intentional: this release
changes framework ownership boundaries, removes legacy routes and owners,
replaces the document/shell/runtime architecture and establishes new global UI
contracts. It is not a patch-level RC9.

## Final Architecture

- Every complete HTML response uses `boot-core/template/document.phtml` and
  `boot-core/template/shell.phtml`.
- `public/assets/js/catalyst/runtime/ui-runtime.js` is the single frontend
  governor.
- Configuration, Users, Account, Workspaces, Operations and API are canonical
  framework owners.
- Public bearer-token APIs remain under `/api/v1/*`; session-authenticated
  transports live under `/runtime/*`.
- Bootstrap/Inspinia own base geometry; Catalyst supplies global capabilities
  and module-exclusive behavior.
- PageHeader, DataGrid, FormBuilder, RecordPresence, modal lifecycle and global
  activity are shared contracts.
- Framework and derived-app tests have separate `test/framework` and
  `test/app` ownership.

## Breaking Changes From v0.1.0-rc.8

| Previous contract | v0.2.0-rc.1 contract |
|---|---|
| `Repository/Framework/Settings` | `Repository/Framework/Configuration` |
| `Repository/Framework/Roles` | `Repository/Framework/Users` |
| App-owned Account implementation | `Repository/Framework/Account` |
| Separate Catalogs/Documents/Media owners | `Repository/Framework/Workspaces` |
| Separate Audit/ApiPlatform/Automation owners | Canonical `Repository/Framework/Operations` with API Management plus independent `Repository/Framework/Api` |
| `/admin/account-recovery*` | `/users/account-recovery*` |
| `/api/notifications*`, `/api/presence*`, `/api/ws-token` | `/runtime/*` |
| `/api/public/*` companions | Removed without aliases |
| `/index`, `/index.php`, `/test-features/ui-showcase` | Removed |
| Multiple layouts/shell governors | One document, one shell, one UI runtime |
| Local visual systems and surface profiles | Bootstrap/Inspinia plus global capability contracts |

## Derived Project Migration

1. Commit or stash application work and create a restorable backup.
2. Keep the application remote as `origin` and Catalyst as `upstream`.
3. Fetch tags and review the complete diff before merging.
4. Preserve app-owned work under `Repository/App`; do not restore legacy
   framework owners or route aliases during conflict resolution.
5. Accept canonical framework changes under `app`, `boot-core`,
   `Repository/Framework`, shared Catalyst assets and current documentation.
6. Replace references to old owners and routes with the canonical contracts
   listed above.
7. Run `composer install`, `config:sync`, migrations and the applicable
   verification commands after resolving conflicts.

```powershell
git fetch upstream --tags
git diff --stat v0.1.0-rc.8..v0.2.0-rc.1
git merge v0.2.0-rc.1
composer install
php public/cli.php config:sync
php public/cli.php migrate:status
php public/cli.php migrate
php public/cli.php inspect:lint
php public/cli.php route:lint
php public/cli.php security:check
php public/cli.php quality:check
```

## Modal Contract And Issue #13

The former `boot-core/template/layouts/account.phtml` no longer exists.
Canonical document consumers, including Account and derived app surfaces, use
the central UI runtime. They must not initialize a second shell runtime or call
`initBootstrapComponents()` themselves. Trusted dynamic insertion dispatches
`catalyst:dom:updated`; the central runtime rescans idempotently and owns modal
body placement, layering and cleanup.

The framework contract and prepared coverage are documented in
`docs/framework-modals.md`,
`test/framework/Playwright/specs/ui-runtime-dynamic.spec.cjs` and the shared
modal helper. The removed layout premise plus the shared runtime contract and
coverage close the integration ambiguity described in GitHub issue #13 without
reviving an account-specific runtime.

## Release Preparation

- `catalyst.json` must report `0.2.0-rc.1` and channel `rc`.
- ROADMAP files, backups, ZIP files, runtime state and local secrets stay out
  of the release commit.
- Publish as a GitHub pre-release.
- Build artifacts from the tag through `.github/workflows/release.yml`.
- Verify the public ZIP and checksum before announcing the release.

## Maintainer Publication Commands

Run only after reviewing the complete working tree. The staging pathspec
intentionally excludes every `ROADMAP*.md`, including the consolidated roadmap.

```powershell
Push-Location D:\OpsZone\DevWorkspace\Projects\Web\catalyst

git status --short --branch
git diff --check
git add -A -- . ':(exclude)ROADMAP*.md'
git status --short
git diff --cached --check
git diff --cached --stat
git commit -m "prepare v0.2.0-rc.1"

git tag -s v0.2.0-rc.1 -m "Catalyst v0.2.0-rc.1"
git push origin main
git push origin v0.2.0-rc.1

gh run list --workflow "Catalyst Release" --limit 5
gh release view v0.2.0-rc.1 --json tagName,name,isPrerelease,url,assets
gh release edit v0.2.0-rc.1 --notes-file docs/workflow/release-v0.2.0-rc.1.md --prerelease
gh release view v0.2.0-rc.1 --json tagName,name,isPrerelease,url,assets
gh issue close 13 --comment "Closed by v0.2.0-rc.1: the former account layout was removed, canonical document consumers now use the single central UI runtime, and the shared dynamic modal contract covers idempotent activation, body-level layering and residue cleanup."

Pop-Location
```

Do not close issue #13 until the public pre-release and its assets are visible.

## Known Verification State

ROADMAP-7 was closed by explicit user confirmation on June 14, 2026. Its
recorded evidence includes focused frontend/unit checks and manually executed
Playwright coverage. No fresh test execution is claimed by this documentation
consolidation.

Dependency advisories previously recorded for `guzzlehttp/psr7 <2.10.2` remain
a separate dependency decision and are not silently resolved by this release.
