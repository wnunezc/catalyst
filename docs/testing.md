# Testing Guide

## Purpose

List the current verification commands that represent Catalyst runtime readiness.

## Runtime Owners

| Concern | Owner |
|---|---|
| Quality gate | `Catalyst\Framework\Cli\Commands\QualityCheckCommand` |
| Structural lint | `Catalyst\Framework\Cli\Commands\InspectLintCommand` |
| Route lint | `Catalyst\Framework\Cli\Commands\RouteLintCommand` |
| Runtime docs inventory | `Catalyst\Framework\Cli\Commands\DocsInventoryCommand` |
| Runtime module sync | `Catalyst\Framework\Cli\Commands\DocsSyncRuntimeCommand` |

## Current Behavior

The standard documentation/runtime verification set is:

```powershell
php public/cli.php docs:inventory --json
php public/cli.php docs:sync-runtime --stdout
php public/cli.php route:list --json
php public/cli.php settings:localization-smoke
php public/cli.php inspect:lint
php public/cli.php route:lint
php public/cli.php security:check
php public/cli.php quality:check
git diff --check
```

## Operational Notes

`quality:check` includes `status`, which may emit environment-bound DNS warnings from host Windows for Docker-only service names. Treat those as acceptable only when the gate reports ready and the failing check is known to be host-environment specific.

Release verification also includes a docblock responsibility review for changed
PHP classes and methods. Each class/method docblock must include a meaningful
`Responsibility:` line that is distinct from the summary and explains the
contract boundary, not just a restatement.

## Docker/WSDD DB-Backed Happy Path

When a DB-backed smoke fails from host Windows because `WSDD-MySql-Server`
does not resolve, run the same CLI command inside the WSDD PHP container. This
keeps the workaround outside Catalyst configuration and avoids committing
workspace-specific scripts or `.env` changes.

```powershell
docker exec -w /var/www/html/catalyst.dock WSDD-Web-Server-PHP8.4 php public/cli.php reporting:smoke --json
```

Verified Happy Path on 2026-06-03:

- `first-run-fails-and-persists`: ok.
- `retry-completes-report`: ok.
- `xls-output-matches-request`: ok.
- `unsupported-format-rejected`: ok.

Use the same pattern for other DB-backed CLI smokes only when the project is
mounted at `/var/www/html/catalyst.dock` inside `WSDD-Web-Server-PHP8.4`. This
is a local verification route, not a framework runtime requirement.

## RED And Sad Path Registry

During the `0.1.0-rc.1` adequations, the expected RED checks were command
absence or negative contract failures before implementation. The documented Sad
Paths remain part of the release evidence:

- `deletion:smoke --json`: RED was unknown command; Sad Path covers blockers,
  invalid confirmation token, unsupported action and rejecting handlers.
- `references:smoke --json`: RED was unknown command; Sad Path covers invalid
  resource keys, empty/unsafe record ids and unregistered reference types.
- `sequences:smoke --json`: RED was unknown command; Sad Path covers invalid
  keys, negative `startAt` and `step` lower than one.
- `workflow:smoke --json`: RED was unknown command; Sad Path covers invalid
  transitions, missing approvals and blocked guards.
- `attachments:policy-smoke --json`: RED was unknown command; Sad Path covers
  public storage rejection, size limit rejection, dangerous MIME/extension,
  disallowed purpose, tampered token and revoked token.
- `calendar:smoke --json`: RED was unknown command; Sad Path covers invalid
  ranges, out-of-range event exclusion and permission-hidden restricted events.
- `reports:contract-smoke --json`: RED was unknown command; Sad Path covers
  unknown providers, duplicate providers, unsupported formats and missing
  required criteria.
- `scaffold:app-smoke --json`: RED was unknown command; Sad Path covers unknown
  capabilities and permissions declared on public surfaces.
- `reporting:smoke --json`: host Windows DNS failure for `WSDD-MySql-Server`
  is an environment Sad Path. The Happy Path is to rerun the same command inside
  `WSDD-Web-Server-PHP8.4`.
- `settings:localization-smoke`: semantic i18n guard for
  `/configuration/environment-setup`. It verifies EN/ES card and group titles
  are not raw keys, repeated values or copies of the global Settings title.
  This complements key-count coverage because valid JSON can still contain
  wrong visible copy.
- Docblock responsibility review: RED is a class or method whose
  `Responsibility:` line is missing, empty or copied from the summary. Sad Path
  is publishing code where the docs do not explain ownership, side effects or
  validation boundaries.

## Related Documentation

- `docs/quality-gate.md`
- `docs/documentation-contract.md`
- `docs/runtime-module-catalog.md`
