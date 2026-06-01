<?php

declare(strict_types=1);

namespace Catalyst\Framework\Database\Relations;

use Catalyst\Framework\Database\Model;

/**
 * BelongsTo — inverse of HasOne / HasMany.
 * The FK lives on the PARENT (owning) model's table.
 *
 * Constructor semantics differ from HasOne / HasMany:
 *   $foreignKey = column on the PARENT table  (e.g. 'user_id' on posts)
 *   $localKey   = column on the RELATED table (e.g. 'id' on users)
 *
 * Example:
 *   class Post extends Model {
 *       public function author(): BelongsTo {
 *           return $this->belongsTo(User::class, 'user_id', 'id');
 *       }
 *   }
 *
 *   $post->author                          // ?User — lazy loaded
 *   Post::query()->with('author')->get()   // eager loaded (no N+1)
 *
 * SQL (lazy):
 *   SELECT * FROM users WHERE id = :post_user_id LIMIT 1
 *
 * SQL (eager):
 *   SELECT * FROM users WHERE id IN (:fk1, :fk2, …)
 *   → matched back to each post via its FK value
 *
 * @package Catalyst\Framework\Database\Relations
 */
class BelongsTo extends Relation
{
    /**
     * Lazy-load: look up the owner model by the FK stored on this instance.
     */
    public function getResults(): ?Model
    {
        $fkValue = $this->parent->getAttribute($this->foreignKey);

        if ($fkValue === null) {
            return null;
        }

        return $this->newQuery()
            ->whereEqual($this->localKey, $fkValue)
            ->first();
    }

    /**
     * Eager-load: collect all FK values from the parent models,
     * load the owners in one query, then distribute to each parent.
     *
     * @param Model[] $models
     */
    public function matchEager(array $models, string $relation): void
    {
        // Pre-fill with null
        foreach ($models as $model) {
            $model->setRelation($relation, null);
        }

        $keys = array_values(array_unique(array_filter(
            array_map(fn(Model $m) => $m->getAttribute($this->foreignKey), $models)
        )));

        if (empty($keys)) {
            return;
        }

        $related = $this->newQuery()
            ->whereIn($this->localKey, $keys)
            ->get();

        // Index related models by their owner key (localKey)
        $map = [];
        foreach ($related->all() as $rel) {
            $ownerVal = $rel->getAttribute($this->localKey);
            if ($ownerVal !== null) {
                $map[$ownerVal] = $rel;
            }
        }

        foreach ($models as $model) {
            $fkVal = $model->getAttribute($this->foreignKey);
            $model->setRelation($relation, $map[$fkVal] ?? null);
        }
    }
}
