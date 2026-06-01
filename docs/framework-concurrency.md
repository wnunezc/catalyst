# `Catalyst\Framework\Concurrency`

## Overview

`PA-01` is now closed in runtime through two canonical layers plus one controller/view adoption layer:

1. optimistic locking on ORM models through `HasOptimisticLockingTrait`
2. expirables claims by `resource_key + record_id` through `RecordClaimManager`
3. canonical controller/view integration through `InteractsWithRecordClaimsTrait` and `_record-claim-banner.phtml`

This does not replace workflow, audit, CRUD, or WebSocket. It extends them from the existing framework core.

## Optimistic locking

### Trait: `HasOptimisticLockingTrait`

**File**: `app/Framework/Traits/HasOptimisticLockingTrait.php`

### Purpose

Opt-in model trait that activates a `lock_version` compare-and-swap update contract.

### Contract

- requires a `lock_version` integer column
- seeds `lock_version=1` on insert when empty
- `Model::save()` updates by `primary_key + lock_version`
- successful updates increment the version automatically
- stale writes throw `OptimisticLockException`

### Notes

- this is model-level protection, not a second lock manager
- existing modules only get it when their entity adopts the trait
- `make:crud` now supports `--optimistic-locking=1` for generated modules

## Claiming

### Entity: `RecordClaim`

**File**: `app/Entities/RecordClaim.php`

Backs the `record_claims` table with:

- `resource_key`
- `record_id`
- `claim_token`
- `claimed_by`
- `claimed_by_label`
- `claimed_at`
- `expires_at`
- `released_at`
- `release_reason`
- `metadata`
- `lock_version`

### Repository: `RecordClaimRepository`

**File**: `app/Framework/Concurrency/RecordClaimRepository.php`

### Live public API

- `findByResource(string $resourceKey, int $recordId): ?RecordClaim`
- `lockByResource(string $resourceKey, int $recordId): ?RecordClaim`
- `search(array $filters = []): array`
- `decorateRow(array $row): array`

### Runtime behavior

- `lockByResource()` uses `FOR UPDATE` on the canonical row
- `search()` can filter by `resource_key`, `record_id`, `actor_id` and `active`
- rows are normalized with computed `status`:
  - `active`
  - `expired`
  - `released`

### Manager: `RecordClaimManager`

**File**: `app/Framework/Concurrency/RecordClaimManager.php`

### Live public API

- `acquire(string $resourceKey, int $recordId, ?int $actorId = null, ?string $actorLabel = null, int $ttlSeconds = 900, array $metadata = []): array`
- `release(string $resourceKey, int $recordId, ?int $actorId = null, ?string $reason = null, ?string $claimToken = null, bool $force = false): bool`
- `snapshot(string $resourceKey, int $recordId): ?array`
- `actor(?int $actorId = null, ?string $actorLabel = null): array`
- `owns(array $snapshot, ?int $actorId = null, ?string $actorLabel = null): bool`
- `assertAvailable(string $resourceKey, int $recordId, ?int $actorId = null, ?string $actorLabel = null, ?string $claimToken = null): ?array`

### Runtime behavior

- claims are unique per `resource_key + record_id`
- active claims block another actor until expiry or explicit release
- expired claims can be reclaimed without opening another ownership subsystem
- semantic claim events are audited through `AuditLogManager` on channel `concurrency`
- the backing row also keeps regular model audit/version increments

## Controller adoption

### Trait: `InteractsWithRecordClaimsTrait`

**File**: `app/Framework/Traits/InteractsWithRecordClaimsTrait.php`

### Purpose

Standardizes claim acquire/check/release flows for framework controllers without creating a second ownership subsystem.

### Live helper API

- `acquireRecordClaim(string $resourceKey, int $recordId, array $metadata = []): array`
- `assertRecordClaimAvailable(string $resourceKey, int $recordId, Request $request): ?array`
- `releaseRecordClaim(string $resourceKey, int $recordId, Request $request, ?string $reason = null): void`
- `buildRecordClaimContext(?array $claim): ?array`
- `concurrencyHiddenFields(?array $claim, ?int $lockVersion = null): array`
- `rememberConcurrencyConflict(Request $request, RuntimeException $e, string $bag = 'default'): void`

### Shared view primitive

- `boot-core/template/components/_record-claim-banner.phtml`

The banner exposes current owner, status and expiry while hidden fields keep `claim_token` and `lock_version` on canonical admin forms.

`buildRecordClaimContext()` is now tenant-aware and exposes:

- `tenant_id`
- `tenant_key`
- `claimed_by`
- `seconds_to_expiry`

This keeps shared-db tenancy and claim-derived presence on the same canonical record boundary.

## Presence over claims

`PA-08` extends the same claim row instead of opening a parallel presence table or a second realtime transport.

### Manager: `PresenceManager`

**File**: `app/Framework/Presence/PresenceManager.php`

Live helpers:

- `snapshot(string $resourceKey, int $recordId): ?array`
- `heartbeat(string $resourceKey, int $recordId, ?int $ttlSeconds = null): ?array`
- `publishClaimSnapshot(?array $claim): void`

Runtime behavior:

- owner pages keep the claim warm through heartbeat
- follower pages render conflict from the same claim snapshot
- reclaim still happens on the same canonical claim row after release or expiry
- the browser fallback stays server render + owner heartbeat when WS is unavailable

### Browser-facing adoption

- banner partial: `boot-core/template/components/_record-claim-banner.phtml`
- owner heartbeat route: `POST /api/presence/{resourceKey}/{recordId}/heartbeat`
- runtime modules:
  - `public/assets/js/catalyst/modules/status-bar.js`
  - `public/assets/js/catalyst/modules/record-presence.js`

## Live adopted surfaces

`PA-01` is no longer only scaffold-ready. The canonical framework runtime now applies claims and optimistic locking to:

- `Repository/Framework/Documents/`
- `Repository/Framework/Automation/`
- `Repository/Framework/Media/`
- `Repository/Framework/Roles/`

This covers document templates, automation rules, media items, metadata field definitions, roles and permissions.

## CLI surface

Canonical operational commands:

- `php public/cli.php claims:list`
- `php public/cli.php claims:release --resource=<key> --record-id=<id>`
- `php public/cli.php concurrency:smoke`
- `php public/cli.php presence:smoke`

`concurrency:smoke` is the canonical DB-backed probe for:

- stale write detection
- claim expiry + reclaim
- cleanup release

`presence:smoke` is the canonical DB-backed probe for:

- owner-visible claim snapshots
- conflict snapshots for a second actor
- heartbeat-driven refresh
- release / reclaim on the same canonical row

## Related docs

- `D:/OpsZone/DevWorkspace/Projects/Web/catalyst/docs/framework-database.md`
- `D:/OpsZone/DevWorkspace/Projects/Web/catalyst/docs/framework-geo.md`
- `D:/OpsZone/DevWorkspace/Projects/Web/catalyst/docs/framework-traits.md`
- `D:/OpsZone/DevWorkspace/Projects/Web/catalyst/docs/framework-websocket.md`
- `D:/OpsZone/DevWorkspace/Projects/Web/catalyst/TERMINAL.md`
