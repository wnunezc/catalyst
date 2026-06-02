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
 * Defines the Has Model Relationships trait contract.
 *
 * @package Catalyst\Framework\Database\Concerns
 * Responsibility: Coordinates the has model relationships behavior within its module boundary.
 */
trait HasModelRelationships
{
    /**
     * Updates the relation value.
     */
    public function setRelation(string $name, mixed $value): static
    {
        $this->relations[$name] = $value;
        return $this;
    }

    /**
     * Returns the relation value.
     */
    public function getRelation(string $name): mixed
    {
        return $this->relations[$name] ?? null;
    }

    /**
     * Handles the relation loaded workflow.
     */
    public function relationLoaded(string $name): bool
    {
        return array_key_exists($name, $this->relations);
    }

    /**
     * Determines whether has One.
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
     * Determines whether has Many.
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
     * Handles the belongs to workflow.
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
     * Handles the belongs to many workflow.
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
     * Handles the get workflow.
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
     * Handles the set workflow.
     */
    public function __set(string $key, mixed $value): void
    {
        $this->setAttribute($key, $value);
    }

    /**
     * Handles the isset workflow.
     */
    public function __isset(string $key): bool
    {
        return isset($this->attributes[$key]) || isset($this->relations[$key]);
    }

    /**
     * Handles the unset workflow.
     */
    public function __unset(string $key): void
    {
        unset($this->attributes[$key]);
    }

    /**
     * Handles the derive key workflow.
     */
    private static function deriveKey(string $class): string
    {
        $short = basename(str_replace('\\', '/', $class));
        return strtolower((string) preg_replace('/(?<!^)[A-Z]/', '_$0', $short)) . '_id';
    }
}
