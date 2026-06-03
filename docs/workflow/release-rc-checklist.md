# Release Candidate Checklist

Use this checklist before publishing a Catalyst release candidate. Publishing an RC means a real tag, workflow run, GitHub pre-release, release assets, checksums and public verification.

## Preconditions

- `catalyst.json` has the intended version and channel.
- `README.md`, `docs/architecture.md`, `docs/harness-context-map.md`, runtime inventory and module catalog are current.
- Closed planning artifacts are archived outside the repo; `/docs` contains only current product/framework documentation.
- Changed PHP classes and methods have docblocks with meaningful `Responsibility:` lines that are not copied from the summary.
- No RTM Hub modules, tables or screens are present in Catalyst.
- No secrets, DKIM keys, uploads, logs, local storage or ad-hoc archives are included.
- `vendor/` is not modified by release preparation.

## Local Verification

```powershell
composer validate --strict
composer audit
php public/cli.php docs:inventory --json
php public/cli.php docs:sync-runtime --stdout
php public/cli.php route:list --json
php public/cli.php inspect:lint
php public/cli.php route:lint
php public/cli.php security:check
php public/cli.php quality:check
git diff --check
```

Run feature smokes relevant to the RC scope:

```powershell
php public/cli.php deletion:smoke --json
php public/cli.php references:smoke --json
php public/cli.php sequences:smoke --json
php public/cli.php workflow:smoke --json
php public/cli.php attachments:policy-smoke --json
php public/cli.php calendar:smoke --json
php public/cli.php reports:contract-smoke --json
php public/cli.php scaffold:app-smoke --json
```

Database-backed smokes may require the workspace database host to resolve from the current shell. MFA-Forge is only needed when validating an MFA-protected real session flow.

If host Windows cannot resolve a Docker-only DB host such as
`WSDD-MySql-Server`, rerun the same DB-backed smoke inside the WSDD PHP
container instead of changing Catalyst configuration:

```powershell
docker exec -w /var/www/html/catalyst.dock WSDD-Web-Server-PHP8.4 php public/cli.php reporting:smoke --json
```

The verified `reporting:smoke` Happy Path for this environment is:
first run persists a failed report, retry completes it, XLS-compatible output
matches the requested format and unsupported formats are rejected. Treat the
host DNS failure as a Sad Path of the local shell boundary, not as a release
regression, when the Docker rerun passes.

## Artifact Preparation

1. Confirm the working tree scope and keep unrelated changes out of the release commit.
2. Commit intentionally after review.
3. Tag the exact commit with `v{version}` from `catalyst.json`.
4. Push the tag so `.github/workflows/release.yml` can build the archive and checksum from the committed tree.
5. Exclude runtime/private artifacts by relying on `git archive` output, not a working directory zip.
6. If the release already exists, rerun `Catalyst Release` with `workflow_dispatch` and the tag to upload assets with `--clobber`.

## GitHub Release

1. Push the tag or run `.github/workflows/release.yml` manually with `workflow_dispatch`.
2. Confirm the GitHub Release is marked as pre-release.
3. Confirm release assets and checksums are uploaded.
4. Include:
   - installation contract;
   - upgrade/update notes;
   - known warnings;
   - verification commands;
   - dependency decisions and deferred items.
5. Verify public asset downloads and checksums from the release page.

## Cloud Repository Configuration

- `.github/workflows/quality.yml` runs Composer validation/audit, PHP lint,
  route lint, structural lint, security check, i18n usage lint, Settings
  localization smoke and `quality:check` on `main` pushes and pull requests.
- `.github/workflows/release.yml` builds `catalyst-{version}.zip`, generates
  `sha256sum`, creates a pre-release for `v*` tags or updates assets when run
  manually with `workflow_dispatch`.
- The GitHub repository should keep issues and discussions enabled, wiki and
  projects disabled, secret scanning with push protection enabled, and `main`
  protected against force-pushes/deletions.

## Known RC Constraints

- Host Windows may not resolve Docker-only database hostnames such as `WSDD-MySql-Server`; treat this as an environment warning only when the quality gate still passes.
- XLSX real and advanced HTML-to-PDF are extension-driver decisions, not mandatory RC dependencies.
- FullCalendar asset bundling and Google Calendar sync remain provider/asset decisions unless explicitly approved for the release.
