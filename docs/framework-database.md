# Catalyst\Framework\Database

## Purpose

Document database connection, query, model, migration and relation primitives.

## Runtime Owners

| Concern | Owner |
|---|---|
| Provides iterable, serializable transformation helpers for query results. | `Catalyst\Framework\Database\Collection` |
| Manage in-memory ORM attributes, fill rules, type casts, dirty tracking, and array/JSON output. | `Catalyst\Framework\Database\Concerns\HasModelAttributes` |
| Boot model traits once per concrete class and dispatch registered ORM lifecycle callbacks. | `Catalyst\Framework\Database\Concerns\HasModelLifecycleHooks` |
| Build ORM relationship objects, cache loaded relations, and route property access to attributes or relations. | `Catalyst\Framework\Database\Concerns\HasModelRelationships` |
| Persist ORM model state with lifecycle hooks, tenant filters, and optimistic locking safeguards. | `Catalyst\Framework\Database\Concerns\PersistsModelState` |
| Wraps PDO connection lifecycle, queries and transaction callbacks. | `Catalyst\Framework\Database\Connection` |
| Loads database configuration and lazily resolves named connections. | `Catalyst\Framework\Database\DatabaseManager` |
| Provides migration versioning, connection access, SQL execution, table checks, and foreign key helpers. | `Catalyst\Framework\Database\Migration` |
| Discovers migration files, tracks applied versions, executes batches, and maintains migration history. | `Catalyst\Framework\Database\MigrationRunner` |
| Provides Active Record persistence, querying, casting and relationship entry points. | `Catalyst\Framework\Database\Model` |
| Hydrates query results into models and applies ORM scopes, eager loading and pagination. | `Catalyst\Framework\Database\ModelQueryBuilder` |
| Carries page items and pagination metadata for APIs and views. | `Catalyst\Framework\Database\Pagination` |
| Builds PDO option arrays without requiring unavailable driver constants. | `Catalyst\Framework\Database\PdoOptionsFactory` |
| Builds validated SQL clauses, bindings and aggregate statements for a table. | `Catalyst\Framework\Database\QueryBuilder` |
| Load and eager-match one owner model for each parent model by comparing parent foreign keys to related local keys. | `Catalyst\Framework\Database\Relations\BelongsTo` |
| Load pivot rows, query related models, and distribute related collections to parent model relation caches. | `Catalyst\Framework\Database\Relations\BelongsToMany` |
| Load and eager-match collections of related models for each parent model key. | `Catalyst\Framework\Database\Relations\HasMany` |
| Load and eager-match one related model for each parent model key. | `Catalyst\Framework\Database\Relations\HasOne` |
| Store shared relation metadata and require lazy-load and eager-load implementations for concrete relations. | `Catalyst\Framework\Database\Relations\Relation` |
| Guards table, column, alias, operator, and join fragments before they are interpolated into SQL. | `Catalyst\Framework\Database\SqlReference` |
| Provides explicit begin, commit and rollback operations over one connection. | `Catalyst\Framework\Database\Transaction` |

## Current Behavior

This file is regenerated from current PHP docblocks and the runtime inventory scope for `Catalyst\Framework\Database`. It intentionally replaces stale historical API notes with the classes and methods that exist in code now.

## API From Docblocks

### `Catalyst\Framework\Database\Collection`

- File: `app/Framework/Database/Collection.php`
- Kind: `class`
- Summary: Typed iterable collection for Model instances and plain arrays.
- Responsibility: Provides iterable, serializable transformation helpers for query results.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `__construct()` | `public` | Initializes the Collection instance. | Initializes the Collection instance. |
| `all()` | `public` | Returns the underlying item array. | Returns the underlying item array. |
| `toArray()` | `public` | Convert items to plain arrays. Model instances are serialized via toArray(); other values are cast. | Convert items to plain arrays. Model instances are serialized via toArray(); other values are cast. |
| `toJson()` | `public` | Encodes the collection items as JSON. | Encodes the collection items as JSON. |
| `jsonSerialize()` | `public` | JsonSerializable — allows json_encode($collection) to produce a clean JSON array directly, without a ->toArray() or ->all() call at the call site. pluck(), where(), map(), etc. all return Collection, so they can be passed directly to a JsonResponse data array. | JsonSerializable — allows json_encode($collection) to produce a clean JSON array directly, without a ->toArray() or ->all() call at the call site. pluck(), where(), map(), etc. all return Collection, so they can be passed directly to a JsonResponse data array. |
| `map()` | `public` | Applies a callback to every collection item and returns a new collection. | Applies a callback to every collection item and returns a new collection. |
| `filter()` | `public` | Filters collection items with an optional predicate and returns a new collection. | Filters collection items with an optional predicate and returns a new collection. |
| `first()` | `public` | Return first item, optionally matching a predicate. | Return first item, optionally matching a predicate. |
| `last()` | `public` | Return last item, optionally matching a predicate. | Return last item, optionally matching a predicate. |
| `pluck()` | `public` | Pluck a single field from each item. | Pluck a single field from each item. |
| `keyBy()` | `public` | Re-index the collection by the value of a field. | Re-index the collection by the value of a field. |
| `chunk()` | `public` | Split the collection into chunks of the given size. Returns a Collection of arrays (not a Collection of Collections). | Split the collection into chunks of the given size. Returns a Collection of arrays (not a Collection of Collections). |
| `merge()` | `public` | Merge another collection into this one. | Merge another collection into this one. |
| `where()` | `public` | Filter items by a field value. | Filter items by a field value. |
| `each()` | `public` | Execute a callback for each item. Return false from the callback to break the loop. | Execute a callback for each item. Return false from the callback to break the loop. |
| `contains()` | `public` | Check if any item matches. Usage: $collection->contains(fn($u) => $u->email === 'x@x.com') $collection->contains('email', 'x@x.com'). | Check if any item matches. Usage: $collection->contains(fn($u) => $u->email === 'x@x.com') $collection->contains('email', 'x@x.com'). |
| `count()` | `public` | Counts the collection items. | Counts the collection items. |
| `isEmpty()` | `public` | Determines whether the collection has no items. | Determines whether the collection has no items. |
| `isNotEmpty()` | `public` | Determines whether the collection contains at least one item. | Determines whether the collection contains at least one item. |
| `getIterator()` | `public` | Returns an iterator over the collection items. | Returns an iterator over the collection items. |
| `resolveField()` | `private` | Read a field from either an array or an object (Model magic getter). | Read a field from either an array or an object (Model magic getter). |

### `Catalyst\Framework\Database\Concerns\HasModelAttributes`

- File: `app/Framework/Database/Concerns/HasModelAttributes.php`
- Kind: `trait`
- Summary: Splits model attribute mass-assignment, casting, dirty state, and serialization behavior out of Model.
- Responsibility: Manage in-memory ORM attributes, fill rules, type casts, dirty tracking, and array/JSON output.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `fill()` | `public` | Assigns only fillable attributes through the normal casting pipeline. | Assigns only fillable attributes through the normal casting pipeline. |
| `forceFill()` | `public` | Assigns every provided attribute through the normal casting pipeline. | Assigns every provided attribute through the normal casting pipeline. |
| `getAttribute()` | `public` | Returns a casted attribute value or null when the attribute is absent. | Returns a casted attribute value or null when the attribute is absent. |
| `setAttribute()` | `public` | Stores an attribute after converting supported cast types for persistence. | Stores an attribute after converting supported cast types for persistence. |
| `getRawAttribute()` | `public` | Returns the stored attribute value without read-time casting. | Returns the stored attribute value without read-time casting. |
| `getAttributes()` | `public` | Returns all stored attributes in their persistence-ready form. | Returns all stored attributes in their persistence-ready form. |
| `getKey()` | `public` | Returns the current model primary key value. | Returns the current model primary key value. |
| `isDirty()` | `public` | Reports whether one attribute or the whole model differs from the original state. | Reports whether one attribute or the whole model differs from the original state. |
| `getDirty()` | `public` | Returns all attributes whose stored values differ from the original state. | Returns all attributes whose stored values differ from the original state. |
| `wasChanged()` | `public` | Reports whether an attribute or the model state has changed since hydration or save. | Reports whether an attribute or the model state has changed since hydration or save. |
| `toArray()` | `public` | Converts visible attributes and loaded relations into array output. | Converts visible attributes and loaded relations into array output. |
| `toJson()` | `public` | Encodes the array representation as JSON with caller-provided flags. | Encodes the array representation as JSON with caller-provided flags. |
| `jsonSerialize()` | `public` | Provides the array representation for JsonSerializable consumers. | Provides the array representation for JsonSerializable consumers. |
| `castAttribute()` | `protected` | Converts stored values to their configured runtime types. | Converts stored values to their configured runtime types. |
| `castForStorage()` | `protected` | Converts runtime values to their configured storage representation. | Converts runtime values to their configured storage representation. |
| `castToDatetime()` | `protected` | Normalizes supported date and timestamp inputs into DateTimeImmutable values. | Normalizes supported date and timestamp inputs into DateTimeImmutable values. |
| `castToDate()` | `protected` | Normalizes supported date inputs into DateTimeImmutable values at midnight. | Normalizes supported date inputs into DateTimeImmutable values at midnight. |
| `isFillable()` | `protected` | Applies the model fillable and guarded assignment rules for one attribute. | Applies the model fillable and guarded assignment rules for one attribute. |

### `Catalyst\Framework\Database\Concerns\HasModelLifecycleHooks`

- File: `app/Framework/Database/Concerns/HasModelLifecycleHooks.php`
- Kind: `trait`
- Summary: Splits model boot and lifecycle hook registration behavior out of Model.
- Responsibility: Boot model traits once per concrete class and dispatch registered ORM lifecycle callbacks.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `bootIfNeeded()` | `protected` | Boots the concrete model class once and invokes discovered trait boot methods. | n/a |
| `registerHook()` | `public` | Registers a callback for one ORM lifecycle event on the concrete model class. | n/a |
| `fireHook()` | `protected` | Dispatches registered callbacks for the given lifecycle event on the current model. | Dispatches registered callbacks for the given lifecycle event on the current model. |

### `Catalyst\Framework\Database\Concerns\HasModelRelationships`

- File: `app/Framework/Database/Concerns/HasModelRelationships.php`
- Kind: `trait`
- Summary: Splits relation factories, relation cache access, and model magic accessors out of Model.
- Responsibility: Build ORM relationship objects, cache loaded relations, and route property access to attributes or relations.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `setRelation()` | `public` | Stores a loaded relation value in the model relation cache. | Stores a loaded relation value in the model relation cache. |
| `getRelation()` | `public` | Returns a cached relation value or null when it has not been loaded. | Returns a cached relation value or null when it has not been loaded. |
| `relationLoaded()` | `public` | Reports whether a relation key exists in the model relation cache. | Reports whether a relation key exists in the model relation cache. |
| `hasOne()` | `protected` | Creates a HasOne relation from this model to a single related model. | Creates a HasOne relation from this model to a single related model. |
| `hasMany()` | `protected` | Creates a HasMany relation from this model to a related model collection. | Creates a HasMany relation from this model to a related model collection. |
| `belongsTo()` | `protected` | Creates a BelongsTo relation where this model stores the foreign key. | Creates a BelongsTo relation where this model stores the foreign key. |
| `belongsToMany()` | `protected` | Creates a BelongsToMany relation through a pivot table. | Creates a BelongsToMany relation through a pivot table. |
| `__get()` | `public` | Reads attributes, cached relations, or lazily loads relation methods as properties. | Reads attributes, cached relations, or lazily loads relation methods as properties. |
| `__set()` | `public` | Writes dynamic property assignments into model attributes. | Writes dynamic property assignments into model attributes. |
| `__isset()` | `public` | Checks dynamic property existence across attributes and cached relations. | Checks dynamic property existence across attributes and cached relations. |
| `__unset()` | `public` | Removes a dynamic property from model attributes. | Removes a dynamic property from model attributes. |
| `deriveKey()` | `private` | Derives a conventional snake_case foreign key name from a model class name. | n/a |

### `Catalyst\Framework\Database\Concerns\PersistsModelState`

- File: `app/Framework/Database/Concerns/PersistsModelState.php`
- Kind: `trait`
- Summary: Splits model insert, update, delete, refresh, and connection persistence behavior out of Model.
- Responsibility: Persist ORM model state with lifecycle hooks, tenant filters, and optimistic locking safeguards.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `save()` | `public` | Persists a new model or dirty existing model through insert or update operations. | Persists a new model or dirty existing model through insert or update operations. |
| `update()` | `public` | Mass-assigns attributes and persists the resulting model state. | Mass-assigns attributes and persists the resulting model state. |
| `delete()` | `public` | Deletes an existing model row using primary key and tenant scope constraints. | Deletes an existing model row using primary key and tenant scope constraints. |
| `fresh()` | `public` | Returns a newly queried copy of the current model or null when it is not persisted. | Returns a newly queried copy of the current model or null when it is not persisted. |
| `refresh()` | `public` | Replaces current attributes and original state with a freshly queried copy. | Replaces current attributes and original state with a freshly queried copy. |
| `exists()` | `public` | Reports whether the model currently represents a persisted row. | Reports whether the model currently represents a persisted row. |
| `resolveConnection()` | `public` | Resolves the database connection configured for the concrete model. | n/a |
| `performInsert()` | `protected` | Inserts the current attributes, initializes optimistic lock state, and marks the model as persisted. | Inserts the current attributes, initializes optimistic lock state, and marks the model as persisted. |
| `performUpdate()` | `protected` | Updates dirty attributes with tenant scope and optimistic lock constraints when enabled. | Updates dirty attributes with tenant scope and optimistic lock constraints when enabled. |
| `getConnectionInstance()` | `protected` | Returns the DatabaseManager connection selected by the concrete model configuration. | n/a |
| `usesOptimisticLocking()` | `protected` | Reports whether the concrete model opted into optimistic locking. | Reports whether the concrete model opted into optimistic locking. |
| `optimisticLockColumn()` | `protected` | Returns the optimistic lock column configured by the concrete model. | Returns the optimistic lock column configured by the concrete model. |
| `usesTenantScoping()` | `protected` | Reports whether the concrete model requires tenant-scoped persistence queries. | Reports whether the concrete model requires tenant-scoped persistence queries. |
| `tenantScopeColumn()` | `protected` | Returns the tenant scope column configured by the concrete model. | Returns the tenant scope column configured by the concrete model. |
| `expectedLockVersion()` | `protected` | Reads the expected optimistic lock version from current or original attributes. | Reads the expected optimistic lock version from current or original attributes. |
| `currentPersistedLockVersion()` | `protected` | Queries the currently persisted optimistic lock version for conflict reporting. | Queries the currently persisted optimistic lock version for conflict reporting. |

### `Catalyst\Framework\Database\Connection`

- File: `app/Framework/Database/Connection.php`
- Kind: `class`
- Summary: Database connection wrapper
- Responsibility: Wraps PDO connection lifecycle, queries and transaction callbacks.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `__construct()` | `public` | Initializes the Connection instance. | Initializes the Connection instance. |
| `getPdo()` | `public` | Get the PDO instance, connecting lazily if necessary. | Provides the active PDO connection, opening it lazily when first requested. |
| `connect()` | `protected` | Establish PDO connection. | Establish PDO connection. |

### `Catalyst\Framework\Database\DatabaseManager`

- File: `app/Framework/Database/DatabaseManager.php`
- Kind: `class`
- Summary: Database connection manager
- Responsibility: Loads database configuration and lazily resolves named connections.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `__construct()` | `protected` | Initializes the Database Manager instance. | Initializes the Database Manager instance. |
| `connection()` | `public` | Get a connection by name, or the default connection when $name is null. Connections are lazy-created: the PDO handshake happens on first use. | Get a connection by name, or the default connection when $name is null. Connections are lazy-created: the PDO handshake happens on first use. |

### `Catalyst\Framework\Database\Migration`

- File: `app/Framework/Database/Migration.php`
- Kind: `class`
- Summary: Base class for database migration definitions.
- Responsibility: Provides migration versioning, connection access, SQL execution, table checks, and foreign key helpers.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `getVersion()` | `public` | Returns the migration version identifier. | Returns the migration version identifier. |
| `up()` | `public` | Applies the schema or data changes for the migration. | Applies the schema or data changes for the migration. |
| `down()` | `public` | Reverts the schema or data changes for the migration. | Reverts the schema or data changes for the migration. |
| `setConnection()` | `public` | Sets the database connection used by this migration. | Sets the database connection used by this migration. |
| `connection()` | `protected` | Returns the active database connection for this migration. | Returns the active database connection for this migration. |
| `statement()` | `protected` | Executes a SQL statement against the migration connection. | Executes a SQL statement against the migration connection. |
| `execute()` | `protected` | Executes a prepared SQL statement and returns affected rows. | Executes a prepared SQL statement and returns affected rows. |
| `select()` | `protected` | Executes a SQL select query and returns all rows. | Executes a SQL select query and returns all rows. |
| `selectOne()` | `protected` | Executes a SQL select query and returns the first row. | Executes a SQL select query and returns the first row. |
| `tableExists()` | `protected` | Determines whether a table exists in the current database. | Determines whether a table exists in the current database. |
| `foreignKeyExists()` | `protected` | Determines whether a named foreign key exists on a table. | Determines whether a named foreign key exists on a table. |
| `foreignKeyDeleteRule()` | `protected` | Resolves the delete rule configured for a named foreign key. | Resolves the delete rule configured for a named foreign key. |
| `dropForeignKey()` | `protected` | Drops a foreign key when it exists on the target table. | Drops a foreign key when it exists on the target table. |
| `addForeignKey()` | `protected` | Adds a foreign key constraint to a table. | Adds a foreign key constraint to a table. |
| `quoteIdentifier()` | `protected` | Quotes a SQL identifier for safe DDL composition. | Quotes a SQL identifier for safe DDL composition. |

### `Catalyst\Framework\Database\MigrationRunner`

- File: `app/Framework/Database/MigrationRunner.php`
- Kind: `class`
- Summary: Runner for applying and rolling back database migrations.
- Responsibility: Discovers migration files, tracks applied versions, executes batches, and maintains migration history.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `__construct()` | `public` | Initializes the runner with a database connection and migration directory. | Initializes the runner with a database connection and migration directory. |
| `runPending()` | `public` | Coordinates the run pending method responsibility within its owning class. | Coordinates the run pending method responsibility within its owning class. |
| `rollbackLastBatch()` | `public` | Coordinates the rollback last batch method responsibility within its owning class. | Coordinates the rollback last batch method responsibility within its owning class. |

### `Catalyst\Framework\Database\Model`

- File: `app/Framework/Database/Model.php`
- Kind: `class`
- Summary: Abstract ORM base — Active Record with Data Mapper hooks.
- Responsibility: Provides Active Record persistence, querying, casting and relationship entry points.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `__construct()` | `public` | Initializes the Model instance. | Initializes the Model instance. |
| `newInstance()` | `public` | Create a new (optionally persisted) instance without triggering fill(). Used internally; prefer Model::create() for public persisted creation. | n/a |
| `fromRow()` | `public` | Hydrate an instance from a raw DB row. Sets exists = true and snapshots original attributes. | n/a |
| `query()` | `public` | Start a fluent ModelQueryBuilder for this model. | n/a |
| `all()` | `public` | Return all rows as a Collection. | n/a |
| `find()` | `public` | Find by primary key, returning null when not found. | n/a |
| `findOrFail()` | `public` | Find by primary key or throw ModelNotFoundException. | n/a |
| `where()` | `public` | Start a query with a WHERE clause. | n/a |
| `create()` | `public` | Create and persist a new instance with the given attributes. | n/a |
| `getTable()` | `public` | Resolve the DB table name. Auto-derives snake_case plural from the class name when $table is empty. Override $table in the subclass to use a custom name. | n/a |
| `getPrimaryKey()` | `public` | Returns the configured primary key column name. | n/a |

### `Catalyst\Framework\Database\ModelQueryBuilder`

- File: `app/Framework/Database/ModelQueryBuilder.php`
- Kind: `class`
- Summary: Query builder that hydrates results into Model instances.
- Responsibility: Hydrates query results into models and applies ORM scopes, eager loading and pagination.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `__construct()` | `public` | Initializes the Model Query Builder instance. | Initializes the Model Query Builder instance. |
| `with()` | `public` | Specify relations to eager-load with this query. Prevents N+1 — each named relation is resolved in at most 2 extra queries regardless of how many parent models the query returns. Example: User::query()->with('posts', 'profile')->get(); User::query()->with('roles')->paginate(20);. | Specify relations to eager-load with this query. Prevents N+1 — each named relation is resolved in at most 2 extra queries regardless of how many parent models the query returns. Example: User::query()->with('posts', 'profile')->get(); User::query()->with('roles')->paginate(20);. |
| `get()` | `public` | Execute and return a Collection of Model instances. If with() was called, eager-loads all specified relations. | Execute and return a Collection of Model instances. If with() was called, eager-loads all specified relations. |
| `first()` | `public` | Execute and return the first matching Model, or null. | Execute and return the first matching Model, or null. |
| `firstOrFail()` | `public` | Like first() but throws ModelNotFoundException when no record is found. | Like first() but throws ModelNotFoundException when no record is found. |
| `find()` | `public` | Find by primary key, returning null when not found. | Find by primary key, returning null when not found. |
| `findOrFail()` | `public` | Find by primary key or throw ModelNotFoundException. | Find by primary key or throw ModelNotFoundException. |
| `paginate()` | `public` | Paginate results. Reads the current page from $_GET['page'] when $page is null. | Paginate results. Reads the current page from $_GET['page'] when $page is null. |
| `withTrashed()` | `public` | Include soft-deleted rows in the query results. | Include soft-deleted rows in the query results. |
| `onlyTrashed()` | `public` | Return only soft-deleted rows. | Return only soft-deleted rows. |
| `eagerLoadRelations()` | `protected` | Resolve each eager relation and batch-load results into all parent models. A "template" instance of the model class is created with no attributes so we can call the relation factory method and obtain a configured Relation object (with the correct FK / local-key column names). The template's own attribute values are irrelevant — matchEager() only uses the column names stored on the Relation object and the $models array it receives. | Resolve each eager relation and batch-load results into all parent models. A "template" instance of the model class is created with no attributes so we can call the relation factory method and obtain a configured Relation object (with the correct FK / local-key column names). The template's own attribute values are irrelevant — matchEager() only uses the column names stored on the Relation object and the $models array it receives. |
| `applySoftDeleteScope()` | `protected` | Automatically add WHERE deleted_at IS NULL when model uses HasSoftDeletesTrait. | Automatically add WHERE deleted_at IS NULL when model uses HasSoftDeletesTrait. |
| `applyTenantScope()` | `protected` | Applies tenant filtering to model queries when the model uses tenant scoping. | Applies tenant filtering to model queries when the model uses tenant scoping. |
| `hasSoftDeletes()` | `protected` | Check whether the bound model class declares soft-delete support. | Check whether the bound model class declares soft-delete support. |

### `Catalyst\Framework\Database\Pagination`

- File: `app/Framework/Database/Pagination.php`
- Kind: `class`
- Summary: Immutable pagination result DTO.
- Responsibility: Carries page items and pagination metadata for APIs and views.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `__construct()` | `public` | Initializes the Pagination instance. | Initializes the Pagination instance. |
| `hasMorePages()` | `public` | Determines whether another page exists after the current page. | Determines whether another page exists after the current page. |
| `onFirstPage()` | `public` | Determines whether the paginator is positioned on the first page. | Determines whether the paginator is positioned on the first page. |
| `onLastPage()` | `public` | Determines whether the paginator is positioned on the last page. | Determines whether the paginator is positioned on the last page. |
| `toArray()` | `public` | Serialize to array — suitable for JSON API responses. Example response envelope: { "data": [...], "meta": { "total": 100, "per_page": 15, ... } }. | Serialize to array — suitable for JSON API responses. Example response envelope: { "data": [...], "meta": { "total": 100, "per_page": 15, ... } }. |
| `toJson()` | `public` | Encodes the paginator payload as JSON. | Encodes the paginator payload as JSON. |

### `Catalyst\Framework\Database\PdoOptionsFactory`

- File: `app/Framework/Database/PdoOptionsFactory.php`
- Kind: `class`
- Summary: Builds safe PDO options for Catalyst database connections.
- Responsibility: Builds PDO option arrays without requiring unavailable driver constants.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `mysql()` | `public` | Default PDO options used by Catalyst. | n/a |
| `hasMysqlDriver()` | `public` | Whether the current PHP runtime has the MySQL PDO driver loaded. | n/a |
| `mysqlInitCommandConstant()` | `private` | Resolve PDO::MYSQL_ATTR_INIT_COMMAND safely. | n/a |

### `Catalyst\Framework\Database\QueryBuilder`

- File: `app/Framework/Database/QueryBuilder.php`
- Kind: `class`
- Summary: Fluent SQL query builder
- Responsibility: Builds validated SQL clauses, bindings and aggregate statements for a table.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `__construct()` | `public` | Initializes the Query Builder instance. | Initializes the Query Builder instance. |
| `select()` | `public` | Sets the columns or expressions selected by the query. | Sets the columns or expressions selected by the query. |
| `where()` | `public` | Adds a basic WHERE condition with a validated column and operator. | Adds a basic WHERE condition with a validated column and operator. |
| `orWhere()` | `public` | Adds a basic OR WHERE condition. | Adds a basic OR WHERE condition. |
| `whereEqual()` | `public` | Adds an equality WHERE condition. | Adds an equality WHERE condition. |
| `whereIn()` | `public` | Adds a WHERE IN condition with bound values. | Adds a WHERE IN condition with bound values. |
| `orWhereIn()` | `public` | Adds an OR WHERE IN condition. | Adds an OR WHERE IN condition. |
| `whereNull()` | `public` | Adds a NULL or NOT NULL WHERE condition. | Adds a NULL or NOT NULL WHERE condition. |
| `whereNotNull()` | `public` | Adds a NOT NULL WHERE condition. | Adds a NOT NULL WHERE condition. |
| `orderBy()` | `public` | Adds an ORDER BY clause with a validated direction. | Adds an ORDER BY clause with a validated direction. |
| `groupBy()` | `public` | Adds one or more GROUP BY columns. | Adds one or more GROUP BY columns. |
| `having()` | `public` | Adds a HAVING condition with a validated column and operator. | Adds a HAVING condition with a validated column and operator. |
| `join()` | `public` | Adds a validated JOIN clause. | Adds a validated JOIN clause. |
| `leftJoin()` | `public` | Adds a LEFT JOIN clause. | Adds a LEFT JOIN clause. |
| `rightJoin()` | `public` | Adds a RIGHT JOIN clause. | Adds a RIGHT JOIN clause. |
| `limit()` | `public` | Sets the maximum number of rows returned by the query. | Sets the maximum number of rows returned by the query. |
| `offset()` | `public` | Sets the row offset used by the query. | Sets the row offset used by the query. |
| `forPage()` | `public` | Applies offset and limit values for page-based pagination. | Applies offset and limit values for page-based pagination. |
| `first()` | `public` | Execute and return the first matching row, or null. Return type is `mixed` to allow ModelQueryBuilder to narrow the return type to `?Model` via PHP 8 covariant return types without violating the Liskov Substitution Principle at the PHP engine level. Direct callers of QueryBuilder (not ModelQueryBuilder) always receive `?array` at runtime; the broadened signature is a type-system necessity. | Execute and return the first matching row, or null. Return type is `mixed` to allow ModelQueryBuilder to narrow the return type to `?Model` via PHP 8 covariant return types without violating the Liskov Substitution Principle at the PHP engine level. Direct callers of QueryBuilder (not ModelQueryBuilder) always receive `?array` at runtime; the broadened signature is a type-system necessity. |
| `get()` | `public` | Execute and return all matching rows. Return type is `mixed` to allow ModelQueryBuilder to narrow the return type to `Collection` via PHP 8 covariant return types. Direct callers of QueryBuilder always receive `array` at runtime. | Execute and return all matching rows. Return type is `mixed` to allow ModelQueryBuilder to narrow the return type to `Collection` via PHP 8 covariant return types. Direct callers of QueryBuilder always receive `array` at runtime. |
| `all()` | `public` | Fetches all matching rows through the query builder get() execution path. | Provides the all() alias while preserving query execution and exception behavior from get(). |
| `count()` | `public` | Return the count of matching rows. | Return the count of matching rows. |
| `insert()` | `public` | Insert a new row and return the last insert ID. | Insert a new row and return the last insert ID. |
| `update()` | `public` | Update matching rows and return the number of affected rows. | Update matching rows and return the number of affected rows. |
| `delete()` | `public` | Delete matching rows and return the number of affected rows. | Delete matching rows and return the number of affected rows. |
| `getConnection()` | `public` | Returns the database connection used by this builder. | Returns the database connection used by this builder. |
| `compileSelect()` | `protected` | Compiles the current SELECT query and bindings. | Compiles the current SELECT query and bindings. |

### `Catalyst\Framework\Database\Relations\BelongsTo`

- File: `app/Framework/Database/Relations/BelongsTo.php`
- Kind: `class`
- Summary: Resolves an inverse ORM relation where the parent model stores the foreign key.
- Responsibility: Load and eager-match one owner model for each parent model by comparing parent foreign keys to related local keys.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `getResults()` | `public` | Loads the related owner model referenced by the parent foreign key. | Loads the related owner model referenced by the parent foreign key. |
| `matchEager()` | `public` | Batch-loads owner models for parent foreign keys and stores matched owners in each relation cache. | Batch-loads owner models for parent foreign keys and stores matched owners in each relation cache. |

### `Catalyst\Framework\Database\Relations\BelongsToMany`

- File: `app/Framework/Database/Relations/BelongsToMany.php`
- Kind: `class`
- Summary: Resolves a many-to-many ORM relation through a pivot table.
- Responsibility: Load pivot rows, query related models, and distribute related collections to parent model relation caches.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `__construct()` | `public` | Captures parent, related model, pivot table, and key metadata for the relation. | Captures parent, related model, pivot table, and key metadata for the relation. |
| `getResults()` | `public` | Loads all related models for the current parent through pivot keys. | Loads all related models for the current parent through pivot keys. |
| `matchEager()` | `public` | Batch-loads pivot and related records, then stores related collections on each parent model. | Batch-loads pivot and related records, then stores related collections on each parent model. |

### `Catalyst\Framework\Database\Relations\HasMany`

- File: `app/Framework/Database/Relations/HasMany.php`
- Kind: `class`
- Summary: Resolves a one-to-many ORM relation where related rows store the foreign key.
- Responsibility: Load and eager-match collections of related models for each parent model key.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `getResults()` | `public` | Loads all related models whose foreign key points to the current parent. | Loads all related models whose foreign key points to the current parent. |
| `matchEager()` | `public` | Batch-loads related models for parent keys and stores a collection on each parent relation cache. | Batch-loads related models for parent keys and stores a collection on each parent relation cache. |

### `Catalyst\Framework\Database\Relations\HasOne`

- File: `app/Framework/Database/Relations/HasOne.php`
- Kind: `class`
- Summary: Resolves a one-to-one ORM relation where the related row stores the foreign key.
- Responsibility: Load and eager-match one related model for each parent model key.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `getResults()` | `public` | Loads the first related model whose foreign key points to the current parent. | Loads the first related model whose foreign key points to the current parent. |
| `matchEager()` | `public` | Batch-loads related models for parent keys and stores one matched model in each relation cache. | Batch-loads related models for parent keys and stores one matched model in each relation cache. |

### `Catalyst\Framework\Database\Relations\Relation`

- File: `app/Framework/Database/Relations/Relation.php`
- Kind: `class`
- Summary: Base contract for ORM relation objects backed by model query builders.
- Responsibility: Store shared relation metadata and require lazy-load and eager-load implementations for concrete relations.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `__construct()` | `public` | Captures parent model, related model class, and key metadata shared by concrete relations. | Captures parent model, related model class, and key metadata shared by concrete relations. |
| `getResults()` | `public` | Loads relation results for the current parent model. | Loads relation results for the current parent model. |
| `matchEager()` | `public` | Batch-loads relation results and stores them in each parent model relation cache. | Batch-loads relation results and stores them in each parent model relation cache. |
| `newQuery()` | `protected` | Creates a fresh ORM query builder for the related model class. | Creates a fresh ORM query builder for the related model class. |
| `getConnection()` | `protected` | Resolves the database connection configured by the related model class. | Resolves the database connection configured by the related model class. |
| `getRelated()` | `public` | Returns the related model class handled by the relation. | Returns the related model class handled by the relation. |
| `getForeignKey()` | `public` | Returns the relation foreign key column name. | Returns the relation foreign key column name. |
| `getLocalKey()` | `public` | Returns the relation local key column name. | Returns the relation local key column name. |

### `Catalyst\Framework\Database\SqlReference`

- File: `app/Framework/Database/SqlReference.php`
- Kind: `class`
- Summary: Validator for SQL identifiers and operators used by query builders.
- Responsibility: Guards table, column, alias, operator, and join fragments before they are interpolated into SQL.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `assertTable()` | `public` | Validates a table reference and returns it unchanged. | n/a |
| `assertColumn()` | `public` | Validates a column reference and returns it unchanged. | n/a |

### `Catalyst\Framework\Database\Transaction`

- File: `app/Framework/Database/Transaction.php`
- Kind: `class`
- Summary: Database transaction handler
- Responsibility: Provides explicit begin, commit and rollback operations over one connection.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `__construct()` | `public` | Initializes the Transaction instance. | Initializes the Transaction instance. |
| `begin()` | `public` | Begin a new transaction. | Begin a new transaction. |

## Operational Notes

When PHP symbols or method contracts in this namespace change, refresh this document from docblocks and run `php public/cli.php docs:inventory --json`.

## Related Documentation

- `docs/runtime-inventory.md`
- `docs/runtime-module-catalog.md`
- `docs/harness-context-map.md`
