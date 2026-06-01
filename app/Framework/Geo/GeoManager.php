<?php

declare(strict_types=1);

namespace Catalyst\Framework\Geo;

use Catalyst\Framework\Traits\SingletonTrait;

final class GeoManager
{
    use SingletonTrait;

    private const EARTH_RADIUS_METERS = 6371008.8;

    public function coordinate(float|int|string $latitude, float|int|string $longitude): Coordinate
    {
        return new Coordinate((float) $latitude, (float) $longitude);
    }

    public function distanceMeters(Coordinate $from, Coordinate $to): float
    {
        $latitudeDelta = $to->latitudeRadians() - $from->latitudeRadians();
        $longitudeDelta = $to->longitudeRadians() - $from->longitudeRadians();

        $a = sin($latitudeDelta / 2) ** 2
            + cos($from->latitudeRadians()) * cos($to->latitudeRadians()) * sin($longitudeDelta / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(max(0.0, 1 - $a)));

        return self::EARTH_RADIUS_METERS * $c;
    }

    public function distanceKilometers(Coordinate $from, Coordinate $to, int $precision = 3): float
    {
        return round($this->distanceMeters($from, $to) / 1000, $precision);
    }

    public function distanceMiles(Coordinate $from, Coordinate $to, int $precision = 3): float
    {
        return round($this->distanceMeters($from, $to) / 1609.344, $precision);
    }

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
