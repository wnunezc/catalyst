<?php

declare(strict_types=1);

/**
 * Catalyst PHP Framework
 *
 * A modern PHP 8.4 framework for building
 * robust and scalable web applications.
 *
 * PHP Version 8.4 (Required).
 *
 * @package    Catalyst
 *
 * @author     Walter Nuñez (arcanisgk/original founder)
 * @email      <wnunez@lh-2.net>
 * @email      <icarosnet@gmail.com>
 * @copyright  2024-2026 Walter Francisco Nuñez Cruz and Icaros Net
 * @license    Proprietary - https://catalyst.lh-2.net/license
 *
 * @version    GIT: See repository tags
 *
 * @category   Framework
 * @filesource
 *
 * @link       https://catalyst.lh-2.net Project homepage
 * @see        https://catalyst.lh-2.net/docs Documentation
 *
 */

namespace Catalyst\Framework\Database\Relations;

use Catalyst\Framework\Database\Collection;
use Catalyst\Framework\Database\Model;

/**
 * HasMany — one-to-many relationship where the FK lives on the related table.
 *
 * Example:
 *   class User extends Model {
 *       public function posts(): HasMany {
 *           return $this->hasMany(Post::class, 'user_id', 'id');
 *       }
 *   }
 *
 *   $user->posts                           // Collection<Post> — lazy loaded
 *   User::query()->with('posts')->get()    // eager loaded (no N+1)
 *
 * SQL (lazy):
 *   SELECT * FROM posts WHERE user_id = :parentId
 *
 * SQL (eager):
 *   SELECT * FROM posts WHERE user_id IN (:id1, :id2, …)
 *   → grouped by user_id and distributed to each parent
 *
 * @package Catalyst\Framework\Database\Relations
 */
class HasMany extends Relation
{
    /**
     * Lazy-load: return all related models for this parent.
     *
     * @return Collection<Model>
     */
    public function getResults(): Collection
    {
        $parentKeyVal = $this->parent->getAttribute($this->localKey);

        if ($parentKeyVal === null) {
            return new Collection([]);
        }

        return $this->newQuery()
            ->whereEqual($this->foreignKey, $parentKeyVal)
            ->get();
    }

    /**
     * Eager-load: batch-query all related rows for the given parents,
     * then distribute a Collection to each parent's relation cache.
     *
     * @param Model[] $models
     */
    public function matchEager(array $models, string $relation): void
    {
        // Pre-fill every parent with an empty Collection
        foreach ($models as $model) {
            $model->setRelation($relation, new Collection([]));
        }

        $keys = array_values(array_unique(array_filter(
            array_map(fn(Model $m) => $m->getAttribute($this->localKey), $models)
        )));

        if (empty($keys)) {
            return;
        }

        $related = $this->newQuery()
            ->whereIn($this->foreignKey, $keys)
            ->get();

        // Group by FK value
        $map = [];
        foreach ($related->all() as $rel) {
            $fkVal = $rel->getAttribute($this->foreignKey);
            if ($fkVal !== null) {
                $map[$fkVal][] = $rel;
            }
        }

        foreach ($models as $model) {
            $key = $model->getAttribute($this->localKey);
            $model->setRelation($relation, new Collection($map[$key] ?? []));
        }
    }
}
