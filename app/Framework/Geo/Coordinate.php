<?php

declare(strict_types=1);

namespace Catalyst\Framework\Geo;

use InvalidArgumentException;

final class Coordinate
{
    private float $latitude;
    private float $longitude;

    public function __construct(float $latitude, float $longitude)
    {
        if ($latitude < -90.0 || $latitude > 90.0) {
            throw new InvalidArgumentException('Latitude must be between -90 and 90 degrees.');
        }

        $this->latitude = $latitude;
        $this->longitude = self::normalizeLongitude($longitude);
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromArray(array $payload): self
    {
        $latitude = $payload['latitude'] ?? $payload['lat'] ?? null;
        $longitude = $payload['longitude'] ?? $payload['lng'] ?? $payload['lon'] ?? null;

        if (!is_numeric($latitude) || !is_numeric($longitude)) {
            throw new InvalidArgumentException('Coordinate payload requires numeric latitude and longitude values.');
        }

        return new self((float) $latitude, (float) $longitude);
    }

    public function latitude(): float
    {
        return $this->latitude;
    }

    public function longitude(): float
    {
        return $this->longitude;
    }

    /**
     * @return array{latitude:float,longitude:float}
     */
    public function toArray(): array
    {
        return [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ];
    }

    public function latitudeRadians(): float
    {
        return deg2rad($this->latitude);
    }

    public function longitudeRadians(): float
    {
        return deg2rad($this->longitude);
    }

    public static function normalizeLongitude(float $longitude): float
    {
        $normalized = fmod($longitude + 180.0, 360.0);

        if ($normalized < 0) {
            $normalized += 360.0;
        }

        return $normalized - 180.0;
    }
}
