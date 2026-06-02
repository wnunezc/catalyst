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
 * Defines the Bounding Box class contract.
 *
 * @package Catalyst\Framework\Geo
 * Responsibility: Coordinates the bounding box behavior within its module boundary.
 */
final class BoundingBox
{
    /**
     * Initializes the Bounding Box instance.
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
     * Handles the north workflow.
     */
    public function north(): float
    {
        return $this->north;
    }

    /**
     * Handles the south workflow.
     */
    public function south(): float
    {
        return $this->south;
    }

    /**
     * Handles the east workflow.
     */
    public function east(): float
    {
        return $this->east;
    }

    /**
     * Handles the west workflow.
     */
    public function west(): float
    {
        return $this->west;
    }

    /**
     * Handles the contains workflow.
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
