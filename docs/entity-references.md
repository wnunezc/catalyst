# Entity References

## Purpose

Formalize generic references between framework or app resources without allowing free-form type strings.

## Contract

`Catalyst\Framework\Reference\EntityReference` carries:

- `resource_key`
- `record_id`
- `label`
- `metadata`

`Catalyst\Framework\Reference\EntityReferenceRegistry` registers allowed `resource_key` values and validates references before they are used by attachments, metadata, workflow, reports, audit or app services.

## Happy Path

1. A module or app service registers referenceable resource types during bootstrap.
2. Incoming payloads are converted with `EntityReference::fromArray()`.
3. The registry validates that the `resource_key` is allowed.
4. The app stores `resource_key` and `record_id` in its own repository table or passes the reference to framework services.

## Sad Path

- Invalid resource keys throw `InvalidArgumentException`.
- Blank or unsafe record ids throw `InvalidArgumentException`.
- Unregistered resource keys validate as `false`.
- Ownership and visibility checks stay in app services or resource policies; the reference contract only carries the metadata needed to perform those checks.

## Naming

Use `resource_key` and `record_id` for new tables. Legacy names such as `related_entity_type` and `related_entity_id` should be normalized into `EntityReference` at request/service boundaries.

## Verification

```powershell
php public/cli.php references:smoke --json
```
