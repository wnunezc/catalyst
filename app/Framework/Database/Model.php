<?php

declare(strict_types=1);

namespace Catalyst\Framework\Database;

use Catalyst\Framework\Database\Concerns\HasModelAttributes;
use Catalyst\Framework\Database\Concerns\HasModelLifecycleHooks;
use Catalyst\Framework\Database\Concerns\HasModelRelationships;
use Catalyst\Framework\Database\Concerns\PersistsModelState;
use Catalyst\Helpers\Exceptions\ModelNotFoundException;
use JsonSerializable;

/**
 * Abstract ORM base — Active Record with Data Mapper hooks.
 *
 * ## Quick usage
 *
 *   class User extends Model {
 *       protected static string $table    = 'users';
 *       protected static array  $fillable = ['name', 'email', 'password'];
 *       protected static array  $hidden   = ['password'];
 *       protected static array  $casts    = ['created_at' => 'datetime', 'is_active' => 'bool'];
 *       use HasTimestampsTrait, HasAuditLogTrait;
 *   }
 *
 *   // Read
 *   $user  = User::find(1);                           // ?User
 *   $user  = User::findOrFail(1);                     // User | throws ModelNotFoundException
 *   $users = User::all();                             // Collection<User>
 *   $users = User::where('active', '=', true)->get(); // Collection<User>
 *   $page  = User::query()->paginate(20);             // Pagination
 *
 *   // Write
 *   $user  = User::create(['name' => 'Walter', ...]);
 *   $user->name = 'New';
 *   $user->save();
 *   $user->delete();
 *
 * ## Relationships
 *
 *   class User extends Model {
 *       public function posts(): HasMany   { return $this->hasMany(Post::class, 'user_id'); }
 *       public function profile(): HasOne  { return $this->hasOne(UserProfile::class, 'user_id'); }
 *       public function roles(): BelongsToMany {
 *           return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id');
 *       }
 *   }
 *   class Post extends Model {
 *       public function author(): BelongsTo { return $this->belongsTo(User::class, 'user_id'); }
 *   }
 *
 *   $user->posts                            // Collection<Post>   lazy loaded
 *   $user->roles                            // Collection<Role>   lazy loaded
 *   User::query()->with('posts')->get()     // eager loaded — no N+1
 *
 * ## Boot system
 *
 * Traits can register lifecycle hooks via static boot{TraitName}() methods:
 *
 *   protected static function bootHasTimestampsTrait(): void
 *   {
 *       static::registerHook('inserting', fn(Model $m) => $m->touchCreatedAt());
 *       static::registerHook('updating',  fn(Model $m) => $m->touchUpdatedAt());
 *   }
 *
 * Events: inserting | inserted | updating | updated | deleting | deleted
 *
 * @package Catalyst\Framework\Database
 */
abstract class Model implements JsonSerializable
{
    use HasModelAttributes;
    use HasModelLifecycleHooks;
    use HasModelRelationships;
    use PersistsModelState;

    // -------------------------------------------------------------------------
    // Schema configuration — override in subclass
    // -------------------------------------------------------------------------

    /** DB table name. Auto-derived from class name when empty. */
    protected static string $table = '';

    /** Primary key column. */
    protected static string $primaryKey = 'id';

    /**
     * Attribute whitelist for mass assignment.
     * When non-empty, only keys listed here are accepted by fill().
     */
    protected static array $fillable = [];

    /**
     * Attribute blacklist for mass assignment.
     * Applied only when $fillable is empty (acts as inverse whitelist).
     */
    protected static array $guarded = ['id'];

    /**
     * Attribute type casts applied on read and write.
     *
     * Supported: int, float, bool, string, array, json, datetime, date
     *
     * Example:
     *   protected static array $casts = [
     *       'is_active'  => 'bool',
     *       'settings'   => 'json',
     *       'created_at' => 'datetime',
     *   ];
     */
    protected static array $casts = [];

    /**
     * Attributes excluded from toArray() / toJson() output.
     * Use this to hide sensitive fields (passwords, secrets, etc.).
     */
    protected static array $hidden = [];

    /**
     * Named connection to use for this model, or null for the default.
     * Maps to keys in boot-core/config/{env}/db.json.
     */
    protected static ?string $connection = null;

    // -------------------------------------------------------------------------
    // Boot system — static, shared per-class
    // -------------------------------------------------------------------------

    /** @var array<string, bool> Classes that have already been booted. */
    private static array $booted = [];

    /**
     * Registered lifecycle hooks.
     * Shape: [class => [event => [Closure, ...]]]
     */
    private static array $hooks = [];

    // -------------------------------------------------------------------------
    // Instance state
    // -------------------------------------------------------------------------

    /** Raw attribute values as stored in / fetched from the database. */
    protected array $attributes = [];

    /**
     * Snapshot taken at load time (fromRow) or after each successful save.
     * Used by isDirty() / getDirty().
     */
    protected array $original = [];

    /** Whether this instance represents a persisted DB row. */
    protected bool $exists = false;

    /**
     * Loaded relation results, keyed by relation name.
     * Populated by lazy loading (via __get) and eager loading (via with()).
     * Included in toArray() / toJson() output when non-empty.
     */
    protected array $relations = [];

    // -------------------------------------------------------------------------
    // Constructor
    // -------------------------------------------------------------------------

    public function __construct(array $attributes = [])
    {
        static::bootIfNeeded();
        $this->fill($attributes);
    }

    // -------------------------------------------------------------------------
    // Static factories
    // -------------------------------------------------------------------------

    /**
     * Create a new (optionally persisted) instance without triggering fill().
     * Used internally; prefer Model::create() for public persisted creation.
     */
    public static function newInstance(array $attributes = [], bool $exists = false): static
    {
        $instance = new static();
        $instance->attributes = $attributes;
        $instance->exists     = $exists;

        if ($exists) {
            $instance->original = $attributes;
        }

        return $instance;
    }

    /**
     * Hydrate an instance from a raw DB row.
     * Sets exists = true and snapshots original attributes.
     */
    public static function fromRow(array $row): static
    {
        $instance             = new static();
        $instance->attributes = $row;
        $instance->original   = $row;
        $instance->exists     = true;

        return $instance;
    }

    // -------------------------------------------------------------------------
    // Static query API
    // -------------------------------------------------------------------------

    /**
     * Start a fluent ModelQueryBuilder for this model.
     *
     * @return ModelQueryBuilder<static>
     */
    public static function query(): ModelQueryBuilder
    {
        static::bootIfNeeded();

        return new ModelQueryBuilder(
            static::getConnectionInstance(),
            static::getTable(),
            static::class
        );
    }

    /**
     * Return all rows as a Collection.
     *
     * @return Collection<static>
     */
    public static function all(): Collection
    {
        return static::query()->get();
    }

    /**
     * Find by primary key, returning null when not found.
     */
    public static function find(int|string $id): ?static
    {
        /** @var static|null */
        return static::query()->whereEqual(static::$primaryKey, $id)->first();
    }

    /**
     * Find by primary key or throw ModelNotFoundException.
     *
     * @throws ModelNotFoundException
     */
    public static function findOrFail(int|string $id): static
    {
        $model = static::find($id);

        if ($model === null) {
            throw ModelNotFoundException::forModel(static::class, $id);
        }

        return $model;
    }

    /**
     * Start a query with a WHERE clause.
     *
     * @return ModelQueryBuilder<static>
     */
    public static function where(string $column, string $operator, mixed $value): ModelQueryBuilder
    {
        return static::query()->where($column, $operator, $value);
    }

    /**
     * Create and persist a new instance with the given attributes.
     *
     * @throws \Throwable
     */
    public static function create(array $attributes): static
    {
        $instance = new static($attributes);
        $instance->save();

        return $instance;
    }

    // -------------------------------------------------------------------------
    // Schema helpers (static)
    // -------------------------------------------------------------------------

    /**
     * Resolve the DB table name.
     * Auto-derives snake_case plural from the class name when $table is empty.
     * Override $table in the subclass to use a custom name.
     */
    public static function getTable(): string
    {
        if (static::$table !== '') {
            return static::$table;
        }

        // ClassName → class_names
        $short = basename(str_replace('\\', '/', static::class));
        return strtolower((string) preg_replace('/(?<!^)[A-Z]/', '_$0', $short)) . 's';
    }

    public static function getPrimaryKey(): string
    {
        return static::$primaryKey;
    }
}
