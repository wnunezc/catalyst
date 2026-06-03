# Database Index

## Purpose

Serve as the broad entry point for Catalyst database, ORM, relation and migration documentation.

## Runtime Owners

| Concern | Owner |
|---|---|
| Connection management | `Catalyst\Framework\Database\DatabaseManager` |
| Low-level connection | `Catalyst\Framework\Database\Connection` |
| Active-record model primitive | `Catalyst\Framework\Database\Model` |
| Query builder | `Catalyst\Framework\Database\ModelQueryBuilder` |
| Migrations | `Catalyst\Framework\Database\MigrationRunner` |
| Relations | `Catalyst\Framework\Database\Relations\*` |
| Safe delete planning | `Catalyst\Framework\Deletion\ReverseCascadeDeleteService` |
| Scoped sequences | `Catalyst\Framework\Sequence\SequenceManager` |

## Current Behavior

Database primitives live under `Catalyst\Framework\Database`. Module repositories use those primitives but do not own the ORM contract. Current class and method details are generated in `docs/framework-database.md` from PHP docblocks.

Reverse cascade deletes are handled by `Catalyst\Framework\Deletion`, not by ORM relation magic. Apps should preview dependent records through repositories, inspect blockers and execute a confirmed plan through a service-level handler. See `docs/reverse-cascade-delete.md`.

Scoped counters are handled by `Catalyst\Framework\Sequence`. Apps should allocate numbers through `SequenceManager` and still enforce a domain-level unique index for the final persisted number. See `docs/sequences.md`.

## Operational Notes

Run `php public/cli.php migrate:status` for migration state and `php public/cli.php docs:inventory --json` after class changes. Keep module persistence details in module/repository docs instead of duplicating them here.

## Related Documentation

- `docs/framework-database.md`
- `docs/framework-concurrency.md`
- `docs/runtime-inventory.md`
