# `Catalyst\Framework\Database`

## Overview

The live database stack is split into four layers:

1. `DatabaseManager` resolves named connections from `ConfigManager::section('db')`.
2. `Connection` wraps PDO and exposes prepared-query helpers.
3. `QueryBuilder` compiles fluent SQL for table-level work.
4. `Model` + `ModelQueryBuilder` provide the ORM layer, relations, pagination, and lifecycle hooks.

For `PA-01`, the ORM layer now also supports opt-in optimistic locking through `HasOptimisticLockingTrait`.

The runtime is JSON-first and `.env`-fallback second:

- configured app: `boot-core/config/{env}/db.json`
- first boot / setup pending: `DB_*` constants from `.env`

`DatabaseManager` still accepts legacy config aliases `db_name` / `db_user`, but the canonical keys are `db_database` / `db_username`.

## Class: DatabaseManager

**File**: `app/Framework/Database/DatabaseManager.php`  
**Pattern**: singleton via `SingletonTrait`

### Purpose

Creates named `Connection` instances lazily and exposes a shorthand entry point for `QueryBuilder`.

### Live public API

- `connection(?string $name = null): Connection`
- `table(string $table, ?string $connection = null): QueryBuilder`
- `setDefaultConnection(string $name): self`
- `getConnectionNames(): array`
- `hasConnection(string $name): bool`

### Runtime behavior

- When `ConfigManager::section('db')` is non-empty, every top-level JSON key becomes a named connection.
- When no JSON DB config exists, `loadFromEnvFallback()` creates a single `default` connection from `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`.
- If `DB_DATABASE` is empty, no fallback connection is registered.
- Passwords prefixed with `enc:` are not decrypted yet. The manager logs a warning and passes the raw value through unchanged.

### Internal helpers actually present

- `loadConfigurations(): void`
- `loadFromEnvFallback(): void`
- `buildConnection(string $name, array $config): Connection`
- `resolvePassword(string $password, string $connectionName): string`

`loadFromJsonFile()` is not part of the current class anymore.

## Class: Connection

**File**: `app/Framework/Database/Connection.php`

### Purpose

PDO wrapper with lazy connect, prepared statements, and transaction helpers.

### Public API

- `getPdo(): PDO`
- `table(string $table): QueryBuilder`
- `query(string $query, array $params = []): PDOStatement`
- `select(string $query, array $params = [], int $fetchMode = PDO::FETCH_ASSOC): array`
- `selectOne(string $query, array $params = [], int $fetchMode = PDO::FETCH_ASSOC): ?array`
- `execute(string $query, array $params = []): int`
- `insert(string $table, array $data): int`
- `transaction(Closure $callback): mixed`
- `test(): bool`
- `getConnectionInfo(): array`
- `getName(): string`

### Notes

- `transaction()` is the preferred API for scoped work; explicit begin/commit/rollback lives in `Transaction`.
- `getConnectionInfo()` masks the password before returning metadata.

## Class: QueryBuilder

**File**: `app/Framework/Database/QueryBuilder.php`

### Purpose

Fluent SQL builder for raw table work. It compiles named bindings and executes through `Connection`.

### Fluent query methods

- `select(array|string $columns = ['*']): self`
- `where(string $column, string $operator, mixed $value, string $boolean = 'AND'): self`
- `orWhere(string $column, string $operator, mixed $value): self`
- `whereEqual(string $column, mixed $value): self`
- `whereIn(string $column, array $values, string $boolean = 'AND'): self`
- `orWhereIn(string $column, array $values): self`
- `whereNull(string $column, string $boolean = 'AND', bool $not = false): self`
- `whereNotNull(string $column, string $boolean = 'AND'): self`
- `orderBy(string $column, string $direction = 'ASC'): self`
- `groupBy(array|string $columns): self`
- `having(string $column, string $operator, mixed $value, string $boolean = 'AND'): self`
- `join(string $table, string $first, string $operator, string $second, string $type = 'INNER'): self`
- `leftJoin(string $table, string $first, string $operator, string $second): self`
- `rightJoin(string $table, string $first, string $operator, string $second): self`
- `limit(int $limit): self`
- `offset(int $offset): self`
- `forPage(int $page, int $perPage): self`

### Execution methods

- `first(array $columns = ['*']): mixed`
- `get(array $columns = ['*']): mixed`
- `all(array $columns = ['*']): mixed`
- `count(): int`
- `insert(array $values): int`
- `update(array $values): int`
- `delete(): int`
- `getConnection(): Connection`

### Notes

- Public aggregate support is limited to `count()`.
- The generic `aggregate()` helper exists, but it is `protected`, not public API.
- Return types stay intentionally loose here because `ModelQueryBuilder` overrides `get()` / `first()` with ORM-aware types.

## ORM Layer

### Class: Model

**File**: `app/Framework/Database/Model.php`

The current ORM contract is property-based, not constant-based.

### Static configuration actually used

- `protected static string $table = ''`
- `protected static string $primaryKey = 'id'`
- `protected static array $fillable = []`
- `protected static array $guarded = ['id']`
- `protected static array $casts = []`
- `protected static array $hidden = []`
- `protected static ?string $connection = null`

### Core public API

- factories:
  - `newInstance(array $attributes = [], bool $exists = false): static`
  - `fromRow(array $row): static`
- querying:
  - `query(): ModelQueryBuilder`
  - `all(): Collection`
  - `find(int|string $id): ?static`
  - `findOrFail(int|string $id): static`
  - `where(string $column, string $operator, mixed $value): ModelQueryBuilder`
- persistence:
  - `create(array $attributes): static`
  - `save(): bool`
  - `update(array $attributes): bool`
  - `delete(): bool`
  - `fresh(): ?static`
  - `refresh(): static`
- attribute/state helpers:
  - `fill(array $attributes): static`
  - `forceFill(array $attributes): static`
  - `getAttribute(string $key): mixed`
  - `setAttribute(string $key, mixed $value): void`
  - `getRawAttribute(string $key): mixed`
  - `getAttributes(): array`
  - `getKey(): int|string|null`
  - `isDirty(?string $key = null): bool`
  - `getDirty(): array`
  - `wasChanged(?string $key = null): bool`
  - `toArray(): array`
  - `toJson(int $flags = 0): string`
  - `exists(): bool`
- relation cache:
  - `setRelation(string $name, mixed $value): static`
  - `getRelation(string $name): mixed`
  - `relationLoaded(string $name): bool`
- metadata:
  - `getTable(): string`
  - `getPrimaryKey(): string`
  - `resolveConnection(): Connection`
  - `registerHook(string $event, Closure $callback): void`

### Important corrections

- `TABLE` / `PRIMARY_KEY` are not live constants in the current runtime.
- `Model::delete()` hard-deletes by default.
- Soft delete behavior only appears when the model uses `HasSoftDeletesTrait`, which overrides `delete()`.
- `Model::save()` may throw `OptimisticLockException` when the model uses `HasOptimisticLockingTrait` and the submitted `lock_version` is stale.
- Relationship factory helpers inside `Model` are `protected`:
  - `hasOne(...)`
  - `hasMany(...)`
  - `belongsTo(...)`
  - `belongsToMany(...)`
  Subclasses expose public relationship methods that call those helpers.

### Boot and hook system

`Model::bootIfNeeded()` discovers trait boot methods once per concrete model class.

Supported events:

- `inserting`
- `inserted`
- `updating`
- `updated`
- `deleting`
- `deleted`

### Optimistic locking

Models can now opt into compare-and-swap updates through `HasOptimisticLockingTrait`.

Contract:

- declare/use the trait on the model
- provide a `lock_version` column
- inserts default to `1`
- updates compare against the in-memory or submitted version and then increment it
- stale writes raise `OptimisticLockException`

### Current live model usage

Confirmed repository models:

- `Repository/Framework/Auth/Models/User.php`
- `Repository/Framework/DevTools/Models/DemoEmail.php`

Neither currently uses `HasTimestampsTrait`, `HasSoftDeletesTrait`, or `HasAuditLogTrait`; those remain supported framework extension points rather than required runtime behavior.

### Class: ModelQueryBuilder

**File**: `app/Framework/Database/ModelQueryBuilder.php`

### Purpose

Extends `QueryBuilder` to hydrate model instances, paginate them, eager-load relations, and apply soft-delete scope when the model declares it.

### Public API

- `with(string ...$relations): static`
- `get(array $columns = ['*']): Collection`
- `first(array $columns = ['*']): ?Model`
- `firstOrFail(array $columns = ['*']): Model`
- `find(int|string $id): ?Model`
- `findOrFail(int|string $id): Model`
- `paginate(int $perPage = 15, ?int $page = null): Pagination`
- `withTrashed(): static`
- `onlyTrashed(): static`

### Internal behavior worth knowing

- Soft-delete scope is auto-applied only when the model class defines `SOFT_DELETES = true`, which currently comes from `HasSoftDeletesTrait`.
- Eager loading is implemented internally via `eagerLoadRelations()`.
- The generic `aggregate()` helper is `protected`, not public API.

## Relations

**Directory**: `app/Framework/Database/Relations/`

Live relation classes:

- `Relation` - abstract base
- `HasOne`
- `HasMany`
- `BelongsTo`
- `BelongsToMany`

They are consumed through model relationship methods and by `ModelQueryBuilder::with(...)`.

## Supporting DTO/Collections

### Class: Collection

**File**: `app/Framework/Database/Collection.php`

Iterable wrapper used by ORM results and some relation APIs.

### Public API

- `all(): array`
- `toArray(): array`
- `toJson(int $flags = 0): string`
- `jsonSerialize(): array`
- `map(callable $callback): self`
- `filter(callable $callback): self`
- `first(?callable $callback = null): mixed`
- `last(?callable $callback = null): mixed`
- `pluck(string $key, ?string $keyBy = null): self`
- `keyBy(string $key): self`
- `chunk(int $size): self`
- `merge(self $other): self`
- `where(string $key, mixed $value): self`
- `each(callable $callback): self`
- `contains(callable|string $key, mixed $value = null): bool`
- `count(): int`
- `isEmpty(): bool`
- `isNotEmpty(): bool`
- `getIterator(): Traversable`

### Class: Pagination

**File**: `app/Framework/Database/Pagination.php`

DTO returned by `ModelQueryBuilder::paginate()`.

### Public API

- `hasMorePages(): bool`
- `onFirstPage(): bool`
- `onLastPage(): bool`
- `toArray(): array`
- `toJson(int $flags = 0): string`

## Migrations

### Class: Migration

**File**: `app/Framework/Database/Migration.php`

Abstract base for files under `boot-core/database/migrations/*.php`.

### Required public methods

- `getVersion(): string`
- `up(): void`
- `down(): void`
- `setConnection(Connection $connection): static`

### Protected helper methods

- `connection(): Connection`
- `statement(string $sql): void`
- `execute(string $sql, array $params = []): int`
- `select(string $sql, array $params = []): array`
- `selectOne(string $sql, array $params = []): ?array`
- `tableExists(string $table): bool`
- `foreignKeyExists(string $table, string $constraint): bool`
- `foreignKeyDeleteRule(string $table, string $constraint): ?string`
- `dropForeignKey(string $table, string $constraint): void`
- `addForeignKey(...)`
- `quoteIdentifier(string $identifier): string`

Those SQL helpers are intentionally `protected`; they are for migration subclasses, not general runtime callers.

### Class: MigrationRunner

**File**: `app/Framework/Database/MigrationRunner.php`

### Public API

- `runPending(): array`
- `rollbackLastBatch(): array`
- `status(): array`
- `getMigrationPath(): string`

### Runtime behavior

- tracks versions in the `migrations` table
- expects each migration file to `return` an instance of `Migration`
- executes pending versions in ascending order
- rolls back the latest batch in descending order

## Related docs

- `D:/OpsZone/DevWorkspace/Projects/Web/catalyst/docs/framework-traits.md`
- `D:/OpsZone/DevWorkspace/Projects/Web/catalyst/docs/framework-concurrency.md`
- `D:/OpsZone/DevWorkspace/Projects/Web/catalyst/STRUCTURE.md`
