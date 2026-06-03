# Responsibility Map Audit

Date: 2026-06-02
Scope: PHP runtime responsibilities and framework documentation alignment.

## Inputs

- Inline PHP class and method docblocks.
- Runtime module catalog.
- Runtime inventory.
- CLI command catalog.
- Route list.
- Existing `/docs` Markdown.

## Verification Commands

- `php public/cli.php docs:inventory --json`
- `php public/cli.php docs:sync-runtime --stdout`
- `php public/cli.php route:list --json`
- `php public/cli.php inspect:lint`
- `php public/cli.php route:lint`

## Scope Rules

- Runtime logic is read-only.
- Documentation may be updated after classification.
- Obsolete docs are classified before removal.

## Output Files

- `docs/audits/documentation/2026-06-02-duplication-candidates.md`
- `docs/audits/documentation/2026-06-02-docs-classification.md`
- `docs/audits/documentation/2026-06-02-docs-reconciliation-report.md`
