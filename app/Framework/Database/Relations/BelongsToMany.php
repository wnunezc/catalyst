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
 * Resolves a many-to-many ORM relation through a pivot table.
 *
 * @package Catalyst\Framework\Database\Relations
 * Responsibility: Load pivot rows, query related models, and distribute related collections to parent model relation caches.
 */
class BelongsToMany extends Relation
{
    /**
     * Captures parent, related model, pivot table, and key metadata for the relation.
     *
     * Responsibility: Captures parent, related model, pivot table, and key metadata for the relation.
     * @param Model  $parent
     * @param string $related         class-string<Model> for the related side
     * @param string $pivotTable      Pivot table name.
     * @param string $foreignKey      Pivot column pointing to the parent model.
     * @param string $relatedKey      Pivot column pointing to the related model.
     * @param string $localKey        Parent key column.
     * @param string $relatedLocalKey Related key column.
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
     * Loads all related models for the current parent through pivot keys.
     *
     * Responsibility: Loads all related models for the current parent through pivot keys.
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
     * Batch-loads pivot and related records, then stores related collections on each parent model.
     *
     * Responsibility: Batch-loads pivot and related records, then stores related collections on each parent model.
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
