# Catalyst Distribution Roadmap

## Current Target

`0.1.0-rc.1` is the first release-candidate target for distributing Catalyst as
a reusable PHP project base.

Catalyst is not currently distributed as a Composer package and is not intended
to be installed into another project's `vendor/` directory. A developer should
clone, copy or unpack the project base, configure it locally and build the
application inside `Repository/App/`.

## 0.1.0-rc.1 Scope

- Portable installation contract documented for XAMPP, WSDD/Docker, Laragon,
  MAMP and equivalent PHP/Apache stacks.
- Project root can be the target site root; effective web root remains
  `public/`.
- Root `.htaccess` provides an Apache fallback for hosts that serve the project
  root directly.
- Maintainer-local values such as `https://catalyst.dock/`, WSDD database hosts,
  mail accounts, DKIM keys and secrets are not distribution defaults.
- First-run and reusable-base workflows use templates, `.env` and the setup
  wizard as the configuration path.
- Derived projects keep their own `origin` remote and add Catalyst as `upstream`
  for tagged release updates.
- `version` and `update:check` expose local release metadata and manual update
  guidance.
- Quality gate and runtime documentation generation remain the pre-release
  validation baseline.
- Framework/app boundary linting protects derived apps from placing application
  modules in framework-owned folders.
- Public registration can be configured without patching auth controllers.
- Permissions are strengthened independently from strong tenancy.
- Reusable contracts are available for safe reverse cascade deletes, generic
  references, transactional sequences, attachment policies, QR verification
  tokens, workflow approvals, calendar providers and report providers.
- `make:module --preset=complex` can start a large `Repository/App` module with
  request, policy, service, repository, provider and migration extension points.
- Large product specs can be mapped into Catalyst modules through
  `docs/spec-to-catalyst-guide.md`.

## Required Before Tagging 0.1.0-rc.1

1. Run the full local quality gate.
2. Validate a clean install from a fresh checkout or reviewed export.
3. Confirm no local secrets, DKIM keys, uploads, logs, runtime storage or ad-hoc
   archives are included in the release artifact.
4. Confirm setup wizard can configure app URL, database, mail, security and DKIM
   values without maintainer-local assumptions.
5. Produce release notes with known constraints and verification commands.
6. Produce release artifacts and checksums from a reviewed tree.
7. Publish a pre-release tag matching `catalyst.json`.
8. Verify the public GitHub Release, assets and checksum downloads.

## Expected 0.1.0 Feedback

- Fresh install friction on XAMPP/Laragon/MAMP/WSDD.
- Clarity of project-root versus `public/` document-root setup.
- Whether starter `Repository/App/Surface/*` modules are useful defaults.
- Whether additional CLI commands are needed for clean project initialization.
- Any runtime path assumptions that still depend on maintainer-local folders or
  URLs.
- Whether the `origin`/`upstream` update flow is clear enough for derived
  application repositories.

## Deferred

- Public Composer package distribution.
- Long-running HTTP runtime support such as Swoole or RoadRunner.
- Full public release automation with GitHub Release, checksums and signed
  artifacts.
- Formal external support policy beyond the current proprietary project
  contract.
- Optional XLSX exporter using `phpoffice/phpspreadsheet`, pending explicit
  dependency approval.
- Optional advanced HTML-to-PDF exporter using `dompdf/dompdf`, pending explicit
  dependency approval.
- FullCalendar asset distribution and Google Calendar sync drivers, pending
  asset/dependency/provider decisions.
