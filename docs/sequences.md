# Scoped Sequences

## Purpose

Provide transactional sequence numbers per tenant, scope and sequence key.

## Contract

Use `Catalyst\Framework\Sequence\SequenceManager` to allocate numbers. The default `DatabaseSequenceStore` persists counters in `framework_sequences` and advances rows inside a database transaction with `SELECT ... FOR UPDATE`.

The canonical storage key is:

- `tenant_id`
- `scope_key`
- `sequence_key`

## Happy Path

1. Choose a stable `scope_key`, for example `radio-session:123`.
2. Choose a `sequence_key`, for example `certificate` or `entry`.
3. Call `SequenceManager::next($scopeKey, $sequenceKey)`.
4. Store the returned number in the domain table with a unique index appropriate for that domain.

## Sad Path

- Invalid scope or sequence keys throw `InvalidArgumentException`.
- `startAt` must be zero or greater.
- `step` must be greater than zero.
- If the database is unavailable, allocation fails before a number is issued.

## Verification

```powershell
php public/cli.php sequences:smoke --json
```

The smoke uses `InMemorySequenceStore` so it can validate scope isolation without a live database.
