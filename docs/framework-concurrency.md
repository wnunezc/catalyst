# Catalyst\Framework\Concurrency

## Purpose

Document reusable optimistic locking and record-claim primitives.

## Runtime Owners

| Concern | Owner |
|---|---|
| Acquires, renews, releases, validates, audits, and broadcasts record claim state. | `Catalyst\Framework\Concurrency\RecordClaimManager` |
| Reads, locks, searches, and decorates record claims for concurrency workflows. | `Catalyst\Framework\Concurrency\RecordClaimRepository` |
| Projects claim snapshots to heartbeat and realtime clients. | `Catalyst\Framework\Presence\RecordPresenceManager` |

## Current Behavior

This file is regenerated from current PHP docblocks and the runtime inventory scope for `Catalyst\Framework\Concurrency`. It intentionally replaces stale historical API notes with the classes and methods that exist in code now.

## API From Docblocks

### `Catalyst\Framework\Concurrency\RecordClaimManager`

- File: `app/Framework/Concurrency/RecordClaimManager.php`
- Kind: `class`
- Summary: Manager for concurrent record ownership claims.
- Responsibility: Acquires, renews, releases, validates, audits, and broadcasts record claim state.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `__construct()` | `protected` | Initializes claim persistence and database transaction collaborators. | Initializes claim persistence and database transaction collaborators. |
| `acquire()` | `public` | Acquires or renews a claim for a tenant resource record. | Acquires or renews a claim for a tenant resource record. |
| `release()` | `public` | Releases an active claim when owned by the actor or forced. | Releases an active claim when owned by the actor or forced. |
| `snapshot()` | `public` | Returns the decorated claim snapshot for a resource record. | Returns the decorated claim snapshot for a resource record. |
| `actor()` | `public` | Resolves the current claim actor identity. | Resolves the current claim actor identity. |
| `owns()` | `public` | Determines whether a claim snapshot belongs to the resolved actor. | Determines whether a claim snapshot belongs to the resolved actor. |
| `assertAvailable()` | `public` | Asserts that a resource is unclaimed or claimed by the current actor. | Asserts that a resource is unclaimed or claimed by the current actor. |
| `resolveActor()` | `private` | Resolves actor id and label from explicit input, session user, or runtime fallback. | Resolves actor id and label from explicit input, session user, or runtime fallback. |
| `createOrRecoverClaim()` | `private` | Creates a new claim row or recovers the row created by a concurrent transaction. | Creates a new claim row or recovers the row created by a concurrent transaction. |
| `isOwnedBy()` | `private` | Determines whether a claim entity belongs to the supplied actor. | Determines whether a claim entity belongs to the supplied actor. |
| `audit()` | `private` | Writes an audit record for claim lifecycle changes. | Writes an audit record for claim lifecycle changes. |
| `normalizeClaim()` | `private` | Decorates a claim entity as the public claim snapshot shape. | Decorates a claim entity as the public claim snapshot shape. |
| `now()` | `private` | Returns the current timestamp for claim calculations. | Returns the current timestamp for claim calculations. |
| `secondsBetween()` | `private` | Calculates the non-negative number of seconds between timestamps. | Calculates the non-negative number of seconds between timestamps. |

### `Catalyst\Framework\Concurrency\RecordClaimRepository`

- File: `app/Framework/Concurrency/RecordClaimRepository.php`
- Kind: `class`
- Summary: Repository for tenant-scoped record claim rows.
- Responsibility: Reads, locks, searches, and decorates record claims for concurrency workflows.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `__construct()` | `protected` | Initializes database and logging collaborators for claim persistence. | Initializes database and logging collaborators for claim persistence. |
| `findByResource()` | `public` | Finds the claim for a tenant resource record. | Finds the claim for a tenant resource record. |
| `lockByResource()` | `public` | Locks and returns the claim row for a resource inside an active transaction. | Locks and returns the claim row for a resource inside an active transaction. |
| `search()` | `public` | Searches claim rows using tenant, resource, record, actor, and active filters. | Searches claim rows using tenant, resource, record, actor, and active filters. |
| `decorateRow()` | `public` | Adds status and expiry metadata to a raw claim row. | Adds status and expiry metadata to a raw claim row. |
| `parseDateTime()` | `private` | Parses a database datetime string into an immutable timestamp. | Parses a database datetime string into an immutable timestamp. |
| `now()` | `private` | Returns the current timestamp for claim status decoration. | Returns the current timestamp for claim status decoration. |
| `currentTenantId()` | `private` | Resolves the active tenant id for all claim queries. | Resolves the active tenant id for all claim queries. |

## Operational Notes

When PHP symbols or method contracts in this namespace change, refresh this document from docblocks and run `php public/cli.php docs:inventory --json`.

## Related Documentation

- `docs/runtime-inventory.md`
- `docs/runtime-module-catalog.md`
- `docs/framework-record-presence.md`
- `docs/harness-context-map.md`
