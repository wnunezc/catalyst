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
 * Resolves an inverse ORM relation where the parent model stores the foreign key.
 *
 * @package Catalyst\Framework\Database\Relations
 * Responsibility: Load and eager-match one owner model for each parent model by comparing parent foreign keys to related local keys.
 */
class BelongsTo extends Relation
{
    /**
     * Loads the related owner model referenced by the parent foreign key.
     *
     * Responsibility: Loads the related owner model referenced by the parent foreign key.
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
     * Batch-loads owner models for parent foreign keys and stores matched owners in each relation cache.
     *
     * Responsibility: Batch-loads owner models for parent foreign keys and stores matched owners in each relation cache.
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
