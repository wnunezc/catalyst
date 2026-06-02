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
 * Resolves a one-to-many ORM relation where related rows store the foreign key.
 *
 * @package Catalyst\Framework\Database\Relations
 * Responsibility: Load and eager-match collections of related models for each parent model key.
 */
class HasMany extends Relation
{
    /**
     * Loads all related models whose foreign key points to the current parent.
     *
     * Responsibility: Loads all related models whose foreign key points to the current parent.
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
     * Batch-loads related models for parent keys and stores a collection on each parent relation cache.
     *
     * Responsibility: Batch-loads related models for parent keys and stores a collection on each parent relation cache.
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
