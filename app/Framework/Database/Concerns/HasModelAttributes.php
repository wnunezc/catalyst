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
 * Splits model attribute mass-assignment, casting, dirty state, and serialization behavior out of Model.
 *
 * @package Catalyst\Framework\Database\Concerns
 * Responsibility: Manage in-memory ORM attributes, fill rules, type casts, dirty tracking, and array/JSON output.
 */
trait HasModelAttributes
{
    /**
     * Assigns only fillable attributes through the normal casting pipeline.
     *
     * Responsibility: Assigns only fillable attributes through the normal casting pipeline.
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
     * Assigns every provided attribute through the normal casting pipeline.
     *
     * Responsibility: Assigns every provided attribute through the normal casting pipeline.
     */
    public function forceFill(array $attributes): static
    {
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }

        return $this;
    }

    /**
     * Returns a casted attribute value or null when the attribute is absent.
     *
     * Responsibility: Returns a casted attribute value or null when the attribute is absent.
     */
    public function getAttribute(string $key): mixed
    {
        if (!array_key_exists($key, $this->attributes)) {
            return null;
        }

        return $this->castAttribute($key, $this->attributes[$key]);
    }

    /**
     * Stores an attribute after converting supported cast types for persistence.
     *
     * Responsibility: Stores an attribute after converting supported cast types for persistence.
     */
    public function setAttribute(string $key, mixed $value): void
    {
        $this->attributes[$key] = $this->castForStorage($key, $value);
    }

    /**
     * Returns the stored attribute value without read-time casting.
     *
     * Responsibility: Returns the stored attribute value without read-time casting.
     */
    public function getRawAttribute(string $key): mixed
    {
        return $this->attributes[$key] ?? null;
    }

    /**
     * Returns all stored attributes in their persistence-ready form.
     *
     * Responsibility: Returns all stored attributes in their persistence-ready form.
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Returns the current model primary key value.
     *
     * Responsibility: Returns the current model primary key value.
     */
    public function getKey(): int|string|null
    {
        return $this->attributes[static::$primaryKey] ?? null;
    }

    /**
     * Reports whether one attribute or the whole model differs from the original state.
     *
     * Responsibility: Reports whether one attribute or the whole model differs from the original state.
     */
    public function isDirty(?string $key = null): bool
    {
        if ($key !== null) {
            return ($this->attributes[$key] ?? null) !== ($this->original[$key] ?? null);
        }

        return $this->getDirty() !== [];
    }

    /**
     * Returns all attributes whose stored values differ from the original state.
     *
     * Responsibility: Returns all attributes whose stored values differ from the original state.
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
     * Reports whether an attribute or the model state has changed since hydration or save.
     *
     * Responsibility: Reports whether an attribute or the model state has changed since hydration or save.
     */
    public function wasChanged(?string $key = null): bool
    {
        return $this->isDirty($key);
    }

    /**
     * Converts visible attributes and loaded relations into array output.
     *
     * Responsibility: Converts visible attributes and loaded relations into array output.
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
     * Encodes the array representation as JSON with caller-provided flags.
     *
     * Responsibility: Encodes the array representation as JSON with caller-provided flags.
     */
    public function toJson(int $flags = 0): string
    {
        return (string) json_encode($this->toArray(), $flags | JSON_THROW_ON_ERROR);
    }

    /**
     * Provides the array representation for JsonSerializable consumers.
     *
     * Responsibility: Provides the array representation for JsonSerializable consumers.
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Converts stored values to their configured runtime types.
     *
     * Responsibility: Converts stored values to their configured runtime types.
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
     * Converts runtime values to their configured storage representation.
     *
     * Responsibility: Converts runtime values to their configured storage representation.
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
     * Normalizes supported date and timestamp inputs into DateTimeImmutable values.
     *
     * Responsibility: Normalizes supported date and timestamp inputs into DateTimeImmutable values.
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
     * Normalizes supported date inputs into DateTimeImmutable values at midnight.
     *
     * Responsibility: Normalizes supported date inputs into DateTimeImmutable values at midnight.
     */
    protected function castToDate(mixed $value): ?DateTimeImmutable
    {
        $dt = $this->castToDatetime($value);
        return $dt?->setTime(0, 0);
    }

    /**
     * Applies the model fillable and guarded assignment rules for one attribute.
     *
     * Responsibility: Applies the model fillable and guarded assignment rules for one attribute.
     */
    protected function isFillable(string $key): bool
    {
        if (!empty(static::$fillable)) {
            return in_array($key, static::$fillable, true);
        }

        return !in_array($key, static::$guarded, true);
    }
}
