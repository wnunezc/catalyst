# Catalyst\Framework\Geo

## Purpose

Document coordinate normalization, distance and bounding-box primitives.

## Runtime Owners

| Concern | Owner |
|---|---|
| Stores box edges, validates north-south bounds and tests whether coordinates fall inside the box. | `Catalyst\Framework\Geo\BoundingBox` |
| Validates latitude, normalizes longitude and exposes degree/radian values for distance calculations. | `Catalyst\Framework\Geo\Coordinate` |
| Creates validated coordinates, calculates great-circle distances and derives radius-based bounding boxes. | `Catalyst\Framework\Geo\GeoManager` |

## Current Behavior

This file is regenerated from current PHP docblocks and the runtime inventory scope for `Catalyst\Framework\Geo`. It intentionally replaces stale historical API notes with the classes and methods that exist in code now.

## API From Docblocks

### `Catalyst\Framework\Geo\BoundingBox`

- File: `app/Framework/Geo/BoundingBox.php`
- Kind: `class`
- Summary: Represents a latitude and longitude bounding box.
- Responsibility: Stores box edges, validates north-south bounds and tests whether coordinates fall inside the box.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `__construct()` | `public` | Creates a bounding box from normalized geographic edges. | Creates a bounding box from normalized geographic edges. |
| `north()` | `public` | Returns the northern latitude edge. | Returns the northern latitude edge. |
| `south()` | `public` | Returns the southern latitude edge. | Returns the southern latitude edge. |
| `east()` | `public` | Returns the eastern longitude edge. | Returns the eastern longitude edge. |
| `west()` | `public` | Returns the western longitude edge. | Returns the western longitude edge. |
| `contains()` | `public` | Checks whether a coordinate is inside the box, including antimeridian spans. | Checks whether a coordinate is inside the box, including antimeridian spans. |
| `toArray()` | `public` | Exports the bounding box edges as an array. | Exports the bounding box edges as an array. |

### `Catalyst\Framework\Geo\Coordinate`

- File: `app/Framework/Geo/Coordinate.php`
- Kind: `class`
- Summary: Represents a validated geographic coordinate.
- Responsibility: Validates latitude, normalizes longitude and exposes degree/radian values for distance calculations.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `__construct()` | `public` | Creates a coordinate from latitude and longitude degrees. | Creates a coordinate from latitude and longitude degrees. |
| `fromArray()` | `public` | Creates a coordinate from latitude and longitude payload aliases. | n/a |
| `latitude()` | `public` | Returns the latitude in degrees. | Returns the latitude in degrees. |
| `longitude()` | `public` | Returns the normalized longitude in degrees. | Returns the normalized longitude in degrees. |
| `toArray()` | `public` | Exports the coordinate as latitude and longitude degrees. | Exports the coordinate as latitude and longitude degrees. |
| `latitudeRadians()` | `public` | Returns the latitude in radians. | Returns the latitude in radians. |
| `longitudeRadians()` | `public` | Returns the longitude in radians. | Returns the longitude in radians. |
| `normalizeLongitude()` | `public` | Normalizes longitude degrees into the -180 to 180 range. | n/a |

### `Catalyst\Framework\Geo\GeoManager`

- File: `app/Framework/Geo/GeoManager.php`
- Kind: `class`
- Summary: Provides geographic coordinate and distance utilities.
- Responsibility: Creates validated coordinates, calculates great-circle distances and derives radius-based bounding boxes.

| Method | Visibility | Summary | Responsibility |
|---|---|---|---|
| `coordinate()` | `public` | Creates a validated coordinate from scalar latitude and longitude values. | Creates a validated coordinate from scalar latitude and longitude values. |
| `distanceMeters()` | `public` | Calculates the great-circle distance in meters between two coordinates. | Calculates the great-circle distance in meters between two coordinates. |
| `distanceKilometers()` | `public` | Calculates the rounded distance in kilometers between two coordinates. | Calculates the rounded distance in kilometers between two coordinates. |
| `distanceMiles()` | `public` | Calculates the rounded distance in miles between two coordinates. | Calculates the rounded distance in miles between two coordinates. |
| `boundingBox()` | `public` | Builds a geographic bounding box around a center coordinate and radius. | Builds a geographic bounding box around a center coordinate and radius. |

## Operational Notes

When PHP symbols or method contracts in this namespace change, refresh this document from docblocks and run `php public/cli.php docs:inventory --json`.

## Related Documentation

- `docs/runtime-inventory.md`
- `docs/runtime-module-catalog.md`
- `docs/harness-context-map.md`
