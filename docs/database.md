# Database Index

This file is a thin navigation index for Catalyst's database and ORM documentation.

It exists to satisfy the generic Phase 4 target while keeping the canonical detail in the split docs already reconciled against runtime.

## Canonical references

- Core database stack, ORM, relations, migrations: `docs/framework-database.md`
- Runtime configuration source and priority: `docs/helpers-config.md`
- Architecture placement and dependency map: `docs/architecture.md`
- Class dictionary and live model inventory: `STRUCTURE.md`
- CLI entry points for migrations and operational commands: `docs/entry-points.md`, `TERMINAL.md`

## Scope split

- `docs/framework-database.md` is the canonical deep dive for `DatabaseManager`, `Connection`, `QueryBuilder`, `Model`, relations, and migrations.
- This file is only the broad entry point for readers looking for `database.md`.

## Usage note

Use this file when a task starts from the broad label `database`.
Do not duplicate contracts here that already live in `docs/framework-database.md`.
