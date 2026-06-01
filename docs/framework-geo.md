# `Catalyst\Framework\Geo`

## Overview

`PA-07` closes the reusable geo primitive without introducing external providers or a parallel mapping subsystem.

The canonical surface lives in `app/Framework/Geo/` and provides:

- coordinate normalization and validation
- distance calculations from latitude/longitude pairs
- radius-driven bounding boxes for repository or query reuse

## Core classes

### `Coordinate`

**File**: `app/Framework/Geo/Coordinate.php`

Live helpers:

- `fromArray(array $payload): self`
- `toArray(): array`
- `normalizeLatitude(float $latitude): float`
- `normalizeLongitude(float $longitude): float`

Purpose:

- validate numeric latitude/longitude input
- clamp latitude and wrap longitude consistently

### `BoundingBox`

**File**: `app/Framework/Geo/BoundingBox.php`

Live helpers:

- `toArray(): array`
- `contains(Coordinate $coordinate): bool`

Purpose:

- expose a reusable north/south/east/west envelope
- support simple containment checks without a GIS dependency

### `GeoManager`

**File**: `app/Framework/Geo/GeoManager.php`

Live helpers:

- `coordinate(float|int|string $latitude, float|int|string $longitude): Coordinate`
- `distanceMeters(Coordinate $from, Coordinate $to): float`
- `distanceKilometers(Coordinate $from, Coordinate $to, int $precision = 3): float`
- `distanceMiles(Coordinate $from, Coordinate $to, int $precision = 3): float`
- `boundingBox(Coordinate $center, float $radiusMeters): BoundingBox`

Runtime notes:

- distance uses the Haversine formula
- bounding boxes are provider-agnostic and radius-based
- no SQL helper or provider adapter was introduced here

## CLI surface

- `php public/cli.php geo:smoke`

`geo:smoke` is the canonical probe for:

- coordinate normalization
- distance calculations
- bounding-box containment semantics

## Related docs

- `D:/OpsZone/DevWorkspace/Projects/Web/catalyst/STRUCTURE.md`
- `D:/OpsZone/DevWorkspace/Projects/Web/catalyst/docs/framework-concurrency.md`
