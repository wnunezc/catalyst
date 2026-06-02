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

use Catalyst\Framework\Database\Collection;
use Catalyst\Framework\Database\Model;
use DateTimeImmutable;

/**
 * Defines the Has Model Attributes trait contract.
 *
 * @package Catalyst\Framework\Database\Concerns
 * Responsibility: Coordinates the has model attributes behavior within its module boundary.
 */
trait HasModelAttributes
{
    /**
     * Handles the fill workflow.
     */
    public function fill(array $attributes): static
    {
        foreach ($attributes as $key => $value) {
            if ($this->isFillable($key)) {
                $this->setAttribute($key, $value);
            }
        }

        return $this;
    }

    /**
     * Handles the force fill workflow.
     */
    public function forceFill(array $attributes): static
    {
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }

        return $this;
    }

    /**
     * Returns the attribute value.
     */
    public function getAttribute(string $key): mixed
    {
        if (!array_key_exists($key, $this->attributes)) {
            return null;
        }

        return $this->castAttribute($key, $this->attributes[$key]);
    }

    /**
     * Updates the attribute value.
     */
    public function setAttribute(string $key, mixed $value): void
    {
        $this->attributes[$key] = $this->castForStorage($key, $value);
    }

    /**
     * Returns the raw attribute value.
     */
    public function getRawAttribute(string $key): mixed
    {
        return $this->attributes[$key] ?? null;
    }

    /**
     * Returns the attributes value.
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Returns the key value.
     */
    public function getKey(): int|string|null
    {
        return $this->attributes[static::$primaryKey] ?? null;
    }

    /**
     * Determines whether is Dirty.
     */
    public function isDirty(?string $key = null): bool
    {
        if ($key !== null) {
            return ($this->attributes[$key] ?? null) !== ($this->original[$key] ?? null);
        }

        return $this->getDirty() !== [];
    }

    /**
     * Returns the dirty value.
     */
    public function getDirty(): array
    {
        $dirty = [];

        foreach ($this->attributes as $key => $value) {
            if (!array_key_exists($key, $this->original) || $this->original[$key] !== $value) {
                $dirty[$key] = $value;
            }
        }

        return $dirty;
    }

    /**
     * Handles the was changed workflow.
     */
    public function wasChanged(?string $key = null): bool
    {
        return $this->isDirty($key);
    }

    /**
     * Handles the to array workflow.
     */
    public function toArray(): array
    {
        $result = [];

        foreach ($this->attributes as $key => $value) {
            if (in_array($key, static::$hidden, true)) {
                continue;
            }

            $cast = $this->castAttribute($key, $value);
            $result[$key] = $cast instanceof DateTimeImmutable
                ? $cast->format('Y-m-d H:i:s')
                : $cast;
        }

        foreach ($this->relations as $name => $value) {
            if ($value instanceof Collection) {
                $result[$name] = $value->toArray();
            } elseif ($value instanceof Model) {
                $result[$name] = $value->toArray();
            } else {
                $result[$name] = $value;
            }
        }

        return $result;
    }

    /**
     * Handles the to json workflow.
     */
    public function toJson(int $flags = 0): string
    {
        return (string) json_encode($this->toArray(), $flags | JSON_THROW_ON_ERROR);
    }

    /**
     * Handles the json serialize workflow.
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Handles the cast attribute workflow.
     */
    protected function castAttribute(string $key, mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        $type = static::$casts[$key] ?? null;

        if ($type === null) {
            return $value;
        }

        return match ($type) {
            'int', 'integer'  => (int) $value,
            'float', 'double' => (float) $value,
            'bool', 'boolean' => (bool) $value,
            'string'          => (string) $value,
            'array'           => is_string($value)
                ? (json_decode($value, true) ?? [])
                : (array) $value,
            'json'            => is_string($value)
                ? json_decode($value, true)
                : $value,
            'datetime'        => $this->castToDatetime($value),
            'date'            => $this->castToDate($value),
            default           => $value,
        };
    }

    /**
     * Handles the cast for storage workflow.
     */
    protected function castForStorage(string $key, mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        $type = static::$casts[$key] ?? null;

        if ($type === null) {
            return $value;
        }

        return match ($type) {
            'array', 'json'   => is_string($value)
                ? $value
                : (string) json_encode($value),
            'bool', 'boolean' => $value ? 1 : 0,
            'datetime'        => $value instanceof DateTimeImmutable
                ? $value->format('Y-m-d H:i:s')
                : (string) $value,
            'date'            => $value instanceof DateTimeImmutable
                ? $value->format('Y-m-d')
                : (string) $value,
            default           => $value,
        };
    }

    /**
     * Handles the cast to datetime workflow.
     */
    protected function castToDatetime(mixed $value): ?DateTimeImmutable
    {
        if ($value instanceof DateTimeImmutable) {
            return $value;
        }

        if (is_string($value)) {
            foreach (['Y-m-d H:i:s', 'Y-m-d H:i:s.u', 'Y-m-d'] as $format) {
                $dt = DateTimeImmutable::createFromFormat($format, $value);
                if ($dt !== false) {
                    return $dt;
                }
            }
        }

        if (is_int($value)) {
            return (new DateTimeImmutable())->setTimestamp($value);
        }

        return null;
    }

    /**
     * Handles the cast to date workflow.
     */
    protected function castToDate(mixed $value): ?DateTimeImmutable
    {
        $dt = $this->castToDatetime($value);
        return $dt?->setTime(0, 0);
    }

    /**
     * Determines whether is Fillable.
     */
    protected function isFillable(string $key): bool
    {
        if (!empty(static::$fillable)) {
            return in_array($key, static::$fillable, true);
        }

        return !in_array($key, static::$guarded, true);
    }
}
