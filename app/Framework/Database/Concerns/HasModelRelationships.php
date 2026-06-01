<?php

declare(strict_types=1);

namespace Catalyst\Framework\Database\Concerns;

use Catalyst\Framework\Database\Model;
use Catalyst\Framework\Database\Relations\BelongsTo;
use Catalyst\Framework\Database\Relations\BelongsToMany;
use Catalyst\Framework\Database\Relations\HasMany;
use Catalyst\Framework\Database\Relations\HasOne;
use Catalyst\Framework\Database\Relations\Relation;

trait HasModelRelationships
{
    public function setRelation(string $name, mixed $value): static
    {
        $this->relations[$name] = $value;
        return $this;
    }

    public function getRelation(string $name): mixed
    {
        return $this->relations[$name] ?? null;
    }

    public function relationLoaded(string $name): bool
    {
        return array_key_exists($name, $this->relations);
    }

    protected function hasOne(
        string $related,
        ?string $foreignKey = null,
        string $localKey = 'id'
    ): HasOne {
        $foreignKey ??= self::deriveKey(static::class);
        return new HasOne($this, $related, $foreignKey, $localKey);
    }

    protected function hasMany(
        string $related,
        ?string $foreignKey = null,
        string $localKey = 'id'
    ): HasMany {
        $foreignKey ??= self::deriveKey(static::class);
        return new HasMany($this, $related, $foreignKey, $localKey);
    }

    protected function belongsTo(
        string $related,
        ?string $foreignKey = null,
        string $ownerKey = 'id'
    ): BelongsTo {
        $foreignKey ??= self::deriveKey($related);
        return new BelongsTo($this, $related, $foreignKey, $ownerKey);
    }

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

    public function __set(string $key, mixed $value): void
    {
        $this->setAttribute($key, $value);
    }

    public function __isset(string $key): bool
    {
        return isset($this->attributes[$key]) || isset($this->relations[$key]);
    }

    public function __unset(string $key): void
    {
        unset($this->attributes[$key]);
    }

    private static function deriveKey(string $class): string
    {
        $short = basename(str_replace('\\', '/', $class));
        return strtolower((string) preg_replace('/(?<!^)[A-Z]/', '_$0', $short)) . '_id';
    }
}
