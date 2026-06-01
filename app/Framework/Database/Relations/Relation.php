<?php

declare(strict_types=1);

namespace Catalyst\Framework\Database\Relations;

use Catalyst\Framework\Database\Collection;
use Catalyst\Framework\Database\Connection;
use Catalyst\Framework\Database\Model;
use Catalyst\Framework\Database\ModelQueryBuilder;

/**
 * Abstract base for all ORM relationship types.
 *
 * A Relation encapsulates:
 *   - The parent model instance (the "left" side of the association)
 *   - The related model class (the "right" side)
 *   - The foreign key and local key columns that link the two sides
 *
 * Subclasses implement two methods:
 *   - getResults(): executes a lazy-load query for one parent instance
 *   - matchEager(): batch-loads results for a set of parent models
 *     and populates each model's relation cache to prevent N+1 queries
 *
 * Key naming convention (shared by all subclasses):
 *   - $foreignKey — the column that references the other table
 *   - $localKey  — the column on the "owner" table that $foreignKey points to
 *
 * Note: the semantics of "foreign" vs "local" differ per subclass:
 *   HasOne / HasMany  — $foreignKey is on the RELATED table; $localKey on PARENT
 *   BelongsTo         — $foreignKey is on the PARENT table; $localKey on RELATED
 *   BelongsToMany     — both keys are in the PIVOT table
 *
 * @package Catalyst\Framework\Database\Relations
 */
abstract class Relation
{
    public function __construct(
        protected Model $parent,
        /** @var class-string<Model> */
        protected string $related,
        protected string $foreignKey,
        protected string $localKey,
    ) {}

    // -------------------------------------------------------------------------
    // Abstract interface
    // -------------------------------------------------------------------------

    /**
     * Execute a lazy-load query for the parent model and return the result.
     * Called by Model::__get() on first access when the relation is not cached.
     */
    abstract public function getResults(): mixed;

    /**
     * Batch-load results for a set of parent models and distribute them
     * into each model's relation cache. Used by ModelQueryBuilder::with().
     *
     * @param Model[] $models   All parent model instances to hydrate.
     * @param string  $relation The relation name (used as cache key).
     */
    abstract public function matchEager(array $models, string $relation): void;

    // -------------------------------------------------------------------------
    // Shared helpers
    // -------------------------------------------------------------------------

    /**
     * Create a fresh query builder for the related model class.
     *
     * @return ModelQueryBuilder<Model>
     */
    protected function newQuery(): ModelQueryBuilder
    {
        return ($this->related)::query();
    }

    /**
     * Resolve the DB connection from the related model.
     * Delegates to Model::resolveConnection() to avoid duplicating
     * the DatabaseManager lookup.
     */
    protected function getConnection(): Connection
    {
        return ($this->related)::resolveConnection();
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    /** @return class-string<Model> */
    public function getRelated(): string
    {
        return $this->related;
    }

    public function getForeignKey(): string
    {
        return $this->foreignKey;
    }

    public function getLocalKey(): string
    {
        return $this->localKey;
    }
}
