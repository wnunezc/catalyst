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
use Catalyst\Framework\Database\Connection;
use Catalyst\Framework\Database\Model;
use Catalyst\Framework\Database\ModelQueryBuilder;

/**
 * Base contract for ORM relation objects backed by model query builders.
 *
 * @package Catalyst\Framework\Database\Relations
 * Responsibility: Store shared relation metadata and require lazy-load and eager-load implementations for concrete relations.
 */
abstract class Relation
{
    /**
     * Captures parent model, related model class, and key metadata shared by concrete relations.
     *
     * Responsibility: Captures parent model, related model class, and key metadata shared by concrete relations.
     */
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
     * Loads relation results for the current parent model.
     *
     * Responsibility: Loads relation results for the current parent model.
     */
    abstract public function getResults(): mixed;

    /**
     * Batch-loads relation results and stores them in each parent model relation cache.
     *
     * Responsibility: Batch-loads relation results and stores them in each parent model relation cache.
     * @param Model[] $models   All parent model instances to hydrate.
     * @param string  $relation The relation name (used as cache key).
     */
    abstract public function matchEager(array $models, string $relation): void;

    // -------------------------------------------------------------------------
    // Shared helpers
    // -------------------------------------------------------------------------

    /**
     * Creates a fresh ORM query builder for the related model class.
     *
     * Responsibility: Creates a fresh ORM query builder for the related model class.
     * @return ModelQueryBuilder<Model>
     */
    protected function newQuery(): ModelQueryBuilder
    {
        return ($this->related)::query();
    }

    /**
     * Resolves the database connection configured by the related model class.
     *
     * Responsibility: Resolves the database connection configured by the related model class.
     */
    protected function getConnection(): Connection
    {
        return ($this->related)::resolveConnection();
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    /**
     * Returns the related model class handled by the relation.
     *
     * Responsibility: Returns the related model class handled by the relation.
     * @return class-string<Model>
     */
    public function getRelated(): string
    {
        return $this->related;
    }

    /**
     * Returns the relation foreign key column name.
     *
     * Responsibility: Returns the relation foreign key column name.
     */
    public function getForeignKey(): string
    {
        return $this->foreignKey;
    }

    /**
     * Returns the relation local key column name.
     *
     * Responsibility: Returns the relation local key column name.
     */
    public function getLocalKey(): string
    {
        return $this->localKey;
    }
}
