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

namespace Catalyst\Framework\Database;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use Traversable;

/**
 * Typed iterable collection for Model instances and plain arrays.
 *
 * Returned by ModelQueryBuilder::get() and all ORM query methods.
 * Also usable as a general-purpose collection for any array of values.
 *
 * Implements JsonSerializable so json_encode($collection) produces a proper
 * JSON array without needing ->toArray() or ->all() at the call site.
 * pluck() still returns a Collection — callers may pass it directly to
 * json_encode() or a JsonResponse without an extra ->all() conversion.
 *
 * @package Catalyst\Framework\Database
 */
class Collection implements Countable, IteratorAggregate, JsonSerializable
{
    /**
     * Initializes the Collection instance.
     */
    public function __construct(protected array $items = []) {}

    // -------------------------------------------------------------------------
    // Basic accessors
    // -------------------------------------------------------------------------

    /** Return the underlying array. */
    public function all(): array
    {
        return $this->items;
    }

    /**
     * Convert items to plain arrays.
     * Model instances are serialized via toArray(); other values are cast.
     */
    public function toArray(): array
    {
        return array_map(
            fn (mixed $item): array => $item instanceof Model
                ? $item->toArray()
                : (array) $item,
            $this->items
        );
    }

    /**
     * Handles the to json workflow.
     */
    public function toJson(int $flags = 0): string
    {
        return (string) json_encode($this->toArray(), $flags | JSON_THROW_ON_ERROR);
    }

    /**
     * JsonSerializable — allows json_encode($collection) to produce a clean
     * JSON array directly, without a ->toArray() or ->all() call at the
     * call site. pluck(), where(), map(), etc. all return Collection, so
     * they can be passed directly to a JsonResponse data array.
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    // -------------------------------------------------------------------------
    // Transformation
    // -------------------------------------------------------------------------

    /**
     * Handles the map workflow.
     */
    public function map(callable $callback): self
    {
        return new self(array_values(array_map($callback, $this->items)));
    }

    /**
     * Handles the filter workflow.
     */
    public function filter(callable $callback): self
    {
        return new self(array_values(array_filter($this->items, $callback)));
    }

    /**
     * Return first item, optionally matching a predicate.
     */
    public function first(?callable $callback = null): mixed
    {
        if ($callback === null) {
            return $this->items[0] ?? null;
        }

        foreach ($this->items as $item) {
            if ($callback($item)) {
                return $item;
            }
        }

        return null;
    }

    /**
     * Return last item, optionally matching a predicate.
     */
    public function last(?callable $callback = null): mixed
    {
        if ($callback === null) {
            return empty($this->items) ? null : end($this->items);
        }

        $filtered = array_filter($this->items, $callback);
        return empty($filtered) ? null : end($filtered);
    }

    /**
     * Pluck a single field from each item.
     *
     * @param string      $key   Field to extract as value.
     * @param string|null $keyBy Optional field to use as array key.
     */
    public function pluck(string $key, ?string $keyBy = null): self
    {
        $results = [];

        foreach ($this->items as $item) {
            $value = $this->resolveField($item, $key);

            if ($keyBy !== null) {
                $results[$this->resolveField($item, $keyBy)] = $value;
            } else {
                $results[] = $value;
            }
        }

        return new self($results);
    }

    /**
     * Re-index the collection by the value of a field.
     */
    public function keyBy(string $key): self
    {
        $results = [];

        foreach ($this->items as $item) {
            $k = $this->resolveField($item, $key);
            if ($k !== null) {
                $results[$k] = $item;
            }
        }

        return new self($results);
    }

    /**
     * Split the collection into chunks of the given size.
     * Returns a Collection of arrays (not a Collection of Collections).
     */
    public function chunk(int $size): self
    {
        return new self(array_chunk($this->items, $size));
    }

    /**
     * Merge another collection into this one.
     */
    public function merge(self $other): self
    {
        return new self(array_merge($this->items, $other->all()));
    }

    /**
     * Filter items by a field value.
     */
    public function where(string $key, mixed $value): self
    {
        return $this->filter(
            fn (mixed $item): bool => $this->resolveField($item, $key) === $value
        );
    }

    // -------------------------------------------------------------------------
    // Iteration helpers
    // -------------------------------------------------------------------------

    /**
     * Execute a callback for each item.
     * Return false from the callback to break the loop.
     */
    public function each(callable $callback): self
    {
        foreach ($this->items as $key => $item) {
            if ($callback($item, $key) === false) {
                break;
            }
        }

        return $this;
    }

    // -------------------------------------------------------------------------
    // Search / existence
    // -------------------------------------------------------------------------

    /**
     * Check if any item matches.
     *
     * Usage:
     *   $collection->contains(fn($u) => $u->email === 'x@x.com')
     *   $collection->contains('email', 'x@x.com')
     */
    public function contains(callable|string $key, mixed $value = null): bool
    {
        if (is_callable($key)) {
            return $this->first($key) !== null;
        }

        return $this->first(
            fn (mixed $item): bool => $this->resolveField($item, $key) === $value
        ) !== null;
    }

    // -------------------------------------------------------------------------
    // Countable / IteratorAggregate
    // -------------------------------------------------------------------------

    /**
     * Handles the count workflow.
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * Determines whether is Empty.
     */
    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    /**
     * Determines whether is Not Empty.
     */
    public function isNotEmpty(): bool
    {
        return !empty($this->items);
    }

    /**
     * Returns the iterator value.
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    // -------------------------------------------------------------------------
    // Internal helpers
    // -------------------------------------------------------------------------

    /**
     * Read a field from either an array or an object (Model magic getter).
     */
    private function resolveField(mixed $item, string $key): mixed
    {
        if (is_array($item)) {
            return $item[$key] ?? null;
        }

        return $item->$key ?? null;
    }
}
