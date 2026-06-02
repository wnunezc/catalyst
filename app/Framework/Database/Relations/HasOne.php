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

use Catalyst\Framework\Database\Model;

/**
 * HasOne — one-to-one relationship where the FK lives on the related table.
 *
 * Example:
 *   class User extends Model {
 *       public function profile(): HasOne {
 *           return $this->hasOne(UserProfile::class, 'user_id', 'id');
 *       }
 *   }
 *
 *   $user->profile   // ?UserProfile — lazy loaded on first access
 *
 * SQL (lazy):
 *   SELECT * FROM user_profiles WHERE user_id = :parentId LIMIT 1
 *
 * SQL (eager):
 *   SELECT * FROM user_profiles WHERE user_id IN (:id1, :id2, …)
 *   → distributed to each parent, first-match-wins per FK value
 *
 * @package Catalyst\Framework\Database\Relations
 */
class HasOne extends Relation
{
    /**
     * Lazy-load: return the single related model for this parent, or null.
     */
    public function getResults(): ?Model
    {
        $parentKeyVal = $this->parent->getAttribute($this->localKey);

        if ($parentKeyVal === null) {
            return null;
        }

        return $this->newQuery()
            ->whereEqual($this->foreignKey, $parentKeyVal)
            ->first();
    }

    /**
     * Eager-load: batch-query all related rows for the given parents,
     * then distribute one result per parent into their relation caches.
     *
     * @param Model[] $models
     */
    public function matchEager(array $models, string $relation): void
    {
        // Pre-fill every parent slot with null so unmatched parents get null
        foreach ($models as $model) {
            $model->setRelation($relation, null);
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

        // Index by FK value — HasOne takes only the first match per key
        $map = [];
        foreach ($related->all() as $rel) {
            $fkVal = $rel->getAttribute($this->foreignKey);
            if ($fkVal !== null && !isset($map[$fkVal])) {
                $map[$fkVal] = $rel;
            }
        }

        foreach ($models as $model) {
            $key = $model->getAttribute($this->localKey);
            $model->setRelation($relation, $map[$key] ?? null);
        }
    }
}
