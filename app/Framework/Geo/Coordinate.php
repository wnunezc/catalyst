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
 * Represents a validated geographic coordinate.
 *
 * @package Catalyst\Framework\Geo
 * Responsibility: Validates latitude, normalizes longitude and exposes degree/radian values for distance calculations.
 */
final class Coordinate
{
    private float $latitude;
    private float $longitude;

    /**
     * Creates a coordinate from latitude and longitude degrees.
     *
     * Responsibility: Creates a coordinate from latitude and longitude degrees.
     */
    public function __construct(float $latitude, float $longitude)
    {
        if ($latitude < -90.0 || $latitude > 90.0) {
            throw new InvalidArgumentException('Latitude must be between -90 and 90 degrees.');
        }

        $this->latitude = $latitude;
        $this->longitude = self::normalizeLongitude($longitude);
    }

    /**
     * Creates a coordinate from latitude and longitude payload aliases.
     *
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

    /**
     * Returns the latitude in degrees.
     *
     * Responsibility: Returns the latitude in degrees.
     */
    public function latitude(): float
    {
        return $this->latitude;
    }

    /**
     * Returns the normalized longitude in degrees.
     *
     * Responsibility: Returns the normalized longitude in degrees.
     */
    public function longitude(): float
    {
        return $this->longitude;
    }

    /**
     * Exports the coordinate as latitude and longitude degrees.
     *
     * Responsibility: Exports the coordinate as latitude and longitude degrees.
     * @return array{latitude:float,longitude:float}
     */
    public function toArray(): array
    {
        return [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ];
    }

    /**
     * Returns the latitude in radians.
     *
     * Responsibility: Returns the latitude in radians.
     */
    public function latitudeRadians(): float
    {
        return deg2rad($this->latitude);
    }

    /**
     * Returns the longitude in radians.
     *
     * Responsibility: Returns the longitude in radians.
     */
    public function longitudeRadians(): float
    {
        return deg2rad($this->longitude);
    }

    /**
     * Normalizes longitude degrees into the -180 to 180 range.
     */
    public static function normalizeLongitude(float $longitude): float
    {
        $normalized = fmod($longitude + 180.0, 360.0);

        if ($normalized < 0) {
            $normalized += 360.0;
        }

        return $normalized - 180.0;
    }
}
