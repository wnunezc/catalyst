<?php

declare(strict_types=1);

namespace Catalyst\Framework\Geo;

use InvalidArgumentException;

final class BoundingBox
{
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

    public function north(): float
    {
        return $this->north;
    }

    public function south(): float
    {
        return $this->south;
    }

    public function east(): float
    {
        return $this->east;
    }

    public function west(): float
    {
        return $this->west;
    }

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
