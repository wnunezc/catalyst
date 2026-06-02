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
 * BelongsToMany — many-to-many relationship via a pivot (junction) table.
 *
 * Example:
 *   class User extends Model {
 *       public function roles(): BelongsToMany {
 *           return $this->belongsToMany(
 *               Role::class,    // related model
 *               'user_roles',   // pivot table
 *               'user_id',      // FK in pivot → parent (user)
 *               'role_id',      // FK in pivot → related (role)
 *               'id',           // parent local key (users.id)
 *               'id'            // related local key (roles.id)
 *           );
 *       }
 *   }
 *
 *   $user->roles                         // Collection<Role> — lazy loaded
 *   User::query()->with('roles')->get()  // eager loaded — 2 queries total
 *
 * SQL (lazy, 2 queries):
 *   SELECT role_id FROM user_roles WHERE user_id = :userId
 *   SELECT * FROM roles WHERE id IN (:rid1, :rid2, …)
 *
 * SQL (eager, 2 queries):
 *   SELECT user_id, role_id FROM user_roles WHERE user_id IN (…)
 *   SELECT * FROM roles WHERE id IN (all collected role_ids)
 *
 * The two-query strategy is used instead of a JOIN to avoid column
 * name collisions (both tables may share column names like 'id',
 * 'created_at', etc.) and to keep the soft-delete scope unambiguous.
 *
 * @package Catalyst\Framework\Database\Relations
 */
class BelongsToMany extends Relation
{
    /**
     * @param Model  $parent
     * @param string $related          class-string<Model> for the related side
     * @param string $pivotTable       junction / pivot table name
     * @param string $foreignKey       pivot column pointing to parent (e.g. 'user_id')
     * @param string $relatedKey       pivot column pointing to related (e.g. 'role_id')
     * @param string $localKey         parent PK column (default 'id')
     * @param string $relatedLocalKey  related PK column (default 'id')
     */
    public function __construct(
        Model $parent,
        string $related,
        protected string $pivotTable,
        string $foreignKey,
        protected string $relatedKey,
        string $localKey = 'id',
        protected string $relatedLocalKey = 'id',
    ) {
        parent::__construct($parent, $related, $foreignKey, $localKey);
    }

    // -------------------------------------------------------------------------
    // Lazy load
    // -------------------------------------------------------------------------

    /**
     * Return all related models for the current parent via a 2-step query.
     *
     * @return Collection<Model>
     */
    public function getResults(): Collection
    {
        $parentKeyVal = $this->parent->getAttribute($this->localKey);

        if ($parentKeyVal === null) {
            return new Collection([]);
        }

        // Step 1: fetch pivot rows for this parent
        $pivotRows = $this->getConnection()
            ->table($this->pivotTable)
            ->whereEqual($this->foreignKey, $parentKeyVal)
            ->get([$this->relatedKey]);

        if (empty($pivotRows)) {
            return new Collection([]);
        }

        $relatedKeys = array_unique(array_column($pivotRows, $this->relatedKey));

        // Step 2: fetch related models by their PKs
        return ($this->related)::query()
            ->whereIn($this->relatedLocalKey, array_values($relatedKeys))
            ->get();
    }

    // -------------------------------------------------------------------------
    // Eager load
    // -------------------------------------------------------------------------

    /**
     * Batch-load all pivot + related data for a set of parent models.
     * Uses 2 queries regardless of parent count — no N+1.
     *
     * @param Model[] $models
     */
    public function matchEager(array $models, string $relation): void
    {
        // Pre-fill all parents with empty Collections
        foreach ($models as $model) {
            $model->setRelation($relation, new Collection([]));
        }

        $parentKeys = array_values(array_unique(array_filter(
            array_map(fn(Model $m) => $m->getAttribute($this->localKey), $models)
        )));

        if (empty($parentKeys)) {
            return;
        }

        // Step 1: batch-load pivot rows for all parents
        $pivotRows = $this->getConnection()
            ->table($this->pivotTable)
            ->whereIn($this->foreignKey, $parentKeys)
            ->get([$this->foreignKey, $this->relatedKey]);

        if (empty($pivotRows)) {
            return;
        }

        // Build: parentKey → [relatedKey, ...]
        $pivotMap    = [];
        $relatedKeys = [];
        foreach ($pivotRows as $row) {
            $pKey = $row[$this->foreignKey];
            $rKey = $row[$this->relatedKey];
            $pivotMap[$pKey][] = $rKey;
            $relatedKeys[]     = $rKey;
        }

        $relatedKeys = array_values(array_unique($relatedKeys));

        if (empty($relatedKeys)) {
            return;
        }

        // Step 2: load all related models in one query
        $relatedModels = ($this->related)::query()
            ->whereIn($this->relatedLocalKey, $relatedKeys)
            ->get();

        // Index related models by their PK
        $relatedMap = [];
        foreach ($relatedModels->all() as $rel) {
            $pk = $rel->getAttribute($this->relatedLocalKey);
            if ($pk !== null) {
                $relatedMap[$pk] = $rel;
            }
        }

        // Distribute to parent models
        foreach ($models as $model) {
            $pKey  = $model->getAttribute($this->localKey);
            $rKeys = $pivotMap[$pKey] ?? [];
            $items = [];
            foreach ($rKeys as $rKey) {
                if (isset($relatedMap[$rKey])) {
                    $items[] = $relatedMap[$rKey];
                }
            }
            $model->setRelation($relation, new Collection($items));
        }
    }
}
