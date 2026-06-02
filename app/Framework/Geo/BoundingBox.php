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

namespace Catalyst\Framework\Geo;

use InvalidArgumentException;

/**
 * Represents a latitude and longitude bounding box.
 *
 * @package Catalyst\Framework\Geo
 * Responsibility: Stores box edges, validates north-south bounds and tests whether coordinates fall inside the box.
 */
final class BoundingBox
{
    /**
     * Creates a bounding box from normalized geographic edges.
     *
     * Responsibility: Creates a bounding box from normalized geographic edges.
     */
    public function __construct(
        private readonly float $north,
        private readonly float $south,
        private readonly float $east,
        private readonly float $west
    ) {
        if ($north < $south) {
            throw new InvalidArgumentException('Bounding box north edge must be greater than or equal to south.');
        }
    }

    /**
     * Returns the northern latitude edge.
     *
     * Responsibility: Returns the northern latitude edge.
     */
    public function north(): float
    {
        return $this->north;
    }

    /**
     * Returns the southern latitude edge.
     *
     * Responsibility: Returns the southern latitude edge.
     */
    public function south(): float
    {
        return $this->south;
    }

    /**
     * Returns the eastern longitude edge.
     *
     * Responsibility: Returns the eastern longitude edge.
     */
    public function east(): float
    {
        return $this->east;
    }

    /**
     * Returns the western longitude edge.
     *
     * Responsibility: Returns the western longitude edge.
     */
    public function west(): float
    {
        return $this->west;
    }

    /**
     * Checks whether a coordinate is inside the box, including antimeridian spans.
     *
     * Responsibility: Checks whether a coordinate is inside the box, including antimeridian spans.
     */
    public function contains(Coordinate $coordinate): bool
    {
        $latitude = $coordinate->latitude();
        $longitude = $coordinate->longitude();

        if ($latitude < $this->south || $latitude > $this->north) {
            return false;
        }

        if ($this->west <= $this->east) {
            return $longitude >= $this->west && $longitude <= $this->east;
        }

        return $longitude >= $this->west || $longitude <= $this->east;
    }

    /**
     * Exports the bounding box edges as an array.
     *
     * Responsibility: Exports the bounding box edges as an array.
     * @return array{north:float,south:float,east:float,west:float}
     */
    public function toArray(): array
    {
        return [
            'north' => $this->north,
            'south' => $this->south,
            'east' => $this->east,
            'west' => $this->west,
        ];
    }
}
