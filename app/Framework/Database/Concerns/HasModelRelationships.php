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

namespace Catalyst\Framework\Database\Concerns;

use Catalyst\Framework\Database\Model;
use Catalyst\Framework\Database\Relations\BelongsTo;
use Catalyst\Framework\Database\Relations\BelongsToMany;
use Catalyst\Framework\Database\Relations\HasMany;
use Catalyst\Framework\Database\Relations\HasOne;
use Catalyst\Framework\Database\Relations\Relation;

/**
 * Splits relation factories, relation cache access, and model magic accessors out of Model.
 *
 * @package Catalyst\Framework\Database\Concerns
 * Responsibility: Build ORM relationship objects, cache loaded relations, and route property access to attributes or relations.
 */
trait HasModelRelationships
{
    /**
     * Stores a loaded relation value in the model relation cache.
     *
     * Responsibility: Stores a loaded relation value in the model relation cache.
     */
    public function setRelation(string $name, mixed $value): static
    {
        $this->relations[$name] = $value;
        return $this;
    }

    /**
     * Returns a cached relation value or null when it has not been loaded.
     *
     * Responsibility: Returns a cached relation value or null when it has not been loaded.
     */
    public function getRelation(string $name): mixed
    {
        return $this->relations[$name] ?? null;
    }

    /**
     * Reports whether a relation key exists in the model relation cache.
     *
     * Responsibility: Reports whether a relation key exists in the model relation cache.
     */
    public function relationLoaded(string $name): bool
    {
        return array_key_exists($name, $this->relations);
    }

    /**
     * Creates a HasOne relation from this model to a single related model.
     *
     * Responsibility: Creates a HasOne relation from this model to a single related model.
     */
    protected function hasOne(
        string $related,
        ?string $foreignKey = null,
        string $localKey = 'id'
    ): HasOne {
        $foreignKey ??= self::deriveKey(static::class);
        return new HasOne($this, $related, $foreignKey, $localKey);
    }

    /**
     * Creates a HasMany relation from this model to a related model collection.
     *
     * Responsibility: Creates a HasMany relation from this model to a related model collection.
     */
    protected function hasMany(
        string $related,
        ?string $foreignKey = null,
        string $localKey = 'id'
    ): HasMany {
        $foreignKey ??= self::deriveKey(static::class);
        return new HasMany($this, $related, $foreignKey, $localKey);
    }

    /**
     * Creates a BelongsTo relation where this model stores the foreign key.
     *
     * Responsibility: Creates a BelongsTo relation where this model stores the foreign key.
     */
    protected function belongsTo(
        string $related,
        ?string $foreignKey = null,
        string $ownerKey = 'id'
    ): BelongsTo {
        $foreignKey ??= self::deriveKey($related);
        return new BelongsTo($this, $related, $foreignKey, $ownerKey);
    }

    /**
     * Creates a BelongsToMany relation through a pivot table.
     *
     * Responsibility: Creates a BelongsToMany relation through a pivot table.
     */
    protected function belongsToMany(
        string $related,
        string $pivotTable,
        string $foreignKey,
        string $relatedKey,
        string $localKey = 'id',
        string $relatedLocalKey = 'id'
    ): BelongsToMany {
        return new BelongsToMany(
            $this,
            $related,
            $pivotTable,
            $foreignKey,
            $relatedKey,
            $localKey,
            $relatedLocalKey
        );
    }

    /**
     * Reads attributes, cached relations, or lazily loads relation methods as properties.
     *
     * Responsibility: Reads attributes, cached relations, or lazily loads relation methods as properties.
     */
    public function __get(string $key): mixed
    {
        if (array_key_exists($key, $this->attributes)) {
            return $this->getAttribute($key);
        }

        if (array_key_exists($key, $this->relations)) {
            return $this->relations[$key];
        }

        if (method_exists($this, $key)) {
            $result = $this->$key();
            if ($result instanceof Relation) {
                $loaded = $result->getResults();
                $this->relations[$key] = $loaded;
                return $loaded;
            }
        }

        return null;
    }

    /**
     * Writes dynamic property assignments into model attributes.
     *
     * Responsibility: Writes dynamic property assignments into model attributes.
     */
    public function __set(string $key, mixed $value): void
    {
        $this->setAttribute($key, $value);
    }

    /**
     * Checks dynamic property existence across attributes and cached relations.
     *
     * Responsibility: Checks dynamic property existence across attributes and cached relations.
     */
    public function __isset(string $key): bool
    {
        return isset($this->attributes[$key]) || isset($this->relations[$key]);
    }

    /**
     * Removes a dynamic property from model attributes.
     *
     * Responsibility: Removes a dynamic property from model attributes.
     */
    public function __unset(string $key): void
    {
        unset($this->attributes[$key]);
    }

    /**
     * Derives a conventional snake_case foreign key name from a model class name.
     */
    private static function deriveKey(string $class): string
    {
        $short = basename(str_replace('\\', '/', $class));
        return strtolower((string) preg_replace('/(?<!^)[A-Z]/', '_$0', $short)) . '_id';
    }
}
