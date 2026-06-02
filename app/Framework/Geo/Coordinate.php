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
 * Defines the Coordinate class contract.
 *
 * @package Catalyst\Framework\Geo
 * Responsibility: Coordinates the coordinate behavior within its module boundary.
 */
final class Coordinate
{
    private float $latitude;
    private float $longitude;

    /**
     * Initializes the Coordinate instance.
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
     * Handles the latitude workflow.
     */
    public function latitude(): float
    {
        return $this->latitude;
    }

    /**
     * Handles the longitude workflow.
     */
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

    /**
     * Handles the latitude radians workflow.
     */
    public function latitudeRadians(): float
    {
        return deg2rad($this->latitude);
    }

    /**
     * Handles the longitude radians workflow.
     */
    public function longitudeRadians(): float
    {
        return deg2rad($this->longitude);
    }

    /**
     * Normalizes the provided value.
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
