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

use Catalyst\Framework\Traits\SingletonTrait;

/**
 * Provides geographic coordinate and distance utilities.
 *
 * @package Catalyst\Framework\Geo
 * Responsibility: Creates validated coordinates, calculates great-circle distances and derives radius-based bounding boxes.
 */
final class GeoManager
{
    use SingletonTrait;

    private const EARTH_RADIUS_METERS = 6371008.8;

    /**
     * Creates a validated coordinate from scalar latitude and longitude values.
     *
     * Responsibility: Creates a validated coordinate from scalar latitude and longitude values.
     */
    public function coordinate(float|int|string $latitude, float|int|string $longitude): Coordinate
    {
        return new Coordinate((float) $latitude, (float) $longitude);
    }

    /**
     * Calculates the great-circle distance in meters between two coordinates.
     *
     * Responsibility: Calculates the great-circle distance in meters between two coordinates.
     */
    public function distanceMeters(Coordinate $from, Coordinate $to): float
    {
        $latitudeDelta = $to->latitudeRadians() - $from->latitudeRadians();
        $longitudeDelta = $to->longitudeRadians() - $from->longitudeRadians();

        $a = sin($latitudeDelta / 2) ** 2
            + cos($from->latitudeRadians()) * cos($to->latitudeRadians()) * sin($longitudeDelta / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(max(0.0, 1 - $a)));

        return self::EARTH_RADIUS_METERS * $c;
    }

    /**
     * Calculates the rounded distance in kilometers between two coordinates.
     *
     * Responsibility: Calculates the rounded distance in kilometers between two coordinates.
     */
    public function distanceKilometers(Coordinate $from, Coordinate $to, int $precision = 3): float
    {
        return round($this->distanceMeters($from, $to) / 1000, $precision);
    }

    /**
     * Calculates the rounded distance in miles between two coordinates.
     *
     * Responsibility: Calculates the rounded distance in miles between two coordinates.
     */
    public function distanceMiles(Coordinate $from, Coordinate $to, int $precision = 3): float
    {
        return round($this->distanceMeters($from, $to) / 1609.344, $precision);
    }

    /**
     * Builds a geographic bounding box around a center coordinate and radius.
     *
     * Responsibility: Builds a geographic bounding box around a center coordinate and radius.
     */
    public function boundingBox(Coordinate $center, float $radiusMeters): BoundingBox
    {
        $radiusMeters = max(0.0, $radiusMeters);
        $angularDistance = $radiusMeters / self::EARTH_RADIUS_METERS;
        $latitude = $center->latitudeRadians();
        $longitude = $center->longitudeRadians();

        $minLatitude = max(-M_PI / 2, $latitude - $angularDistance);
        $maxLatitude = min(M_PI / 2, $latitude + $angularDistance);

        if ($minLatitude <= -M_PI / 2 || $maxLatitude >= M_PI / 2) {
            $minLongitude = -M_PI;
            $maxLongitude = M_PI;
        } else {
            $longitudeDelta = asin(min(1.0, sin($angularDistance) / max(0.0000001, cos($latitude))));
            $minLongitude = $longitude - $longitudeDelta;
            $maxLongitude = $longitude + $longitudeDelta;
        }

        return new BoundingBox(
            north: rad2deg($maxLatitude),
            south: rad2deg($minLatitude),
            east: Coordinate::normalizeLongitude(rad2deg($maxLongitude)),
            west: Coordinate::normalizeLongitude(rad2deg($minLongitude))
        );
    }
}
