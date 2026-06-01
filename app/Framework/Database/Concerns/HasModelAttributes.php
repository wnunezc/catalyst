<?php

declare(strict_types=1);

namespace Catalyst\Framework\Database\Concerns;

use Catalyst\Framework\Database\Collection;
use Catalyst\Framework\Database\Model;
use DateTimeImmutable;

trait HasModelAttributes
{
    public function fill(array $attributes): static
    {
        foreach ($attributes as $key => $value) {
            if ($this->isFillable($key)) {
                $this->setAttribute($key, $value);
            }
        }

        return $this;
    }

    public function forceFill(array $attributes): static
    {
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }

        return $this;
    }

    public function getAttribute(string $key): mixed
    {
        if (!array_key_exists($key, $this->attributes)) {
            return null;
        }

        return $this->castAttribute($key, $this->attributes[$key]);
    }

    public function setAttribute(string $key, mixed $value): void
    {
        $this->attributes[$key] = $this->castForStorage($key, $value);
    }

    public function getRawAttribute(string $key): mixed
    {
        return $this->attributes[$key] ?? null;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getKey(): int|string|null
    {
        return $this->attributes[static::$primaryKey] ?? null;
    }

    public function isDirty(?string $key = null): bool
    {
        if ($key !== null) {
            return ($this->attributes[$key] ?? null) !== ($this->original[$key] ?? null);
        }

        return $this->getDirty() !== [];
    }

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

    public function wasChanged(?string $key = null): bool
    {
        return $this->isDirty($key);
    }

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

    public function toJson(int $flags = 0): string
    {
        return (string) json_encode($this->toArray(), $flags | JSON_THROW_ON_ERROR);
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

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

    protected function castToDate(mixed $value): ?DateTimeImmutable
    {
        $dt = $this->castToDatetime($value);
        return $dt?->setTime(0, 0);
    }

    protected function isFillable(string $key): bool
    {
        if (!empty(static::$fillable)) {
            return in_array($key, static::$fillable, true);
        }

        return !in_array($key, static::$guarded, true);
    }
}
