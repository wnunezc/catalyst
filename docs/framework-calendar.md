# Catalyst Framework Calendar

`Catalyst\Framework\Calendar` provides a provider-based calendar contract and a FullCalendar-compatible JSON shape. It is framework infrastructure only; apps own their calendar screens and may use FullCalendar or another renderer.

## Runtime Pieces

- `CalendarProviderInterface`: implemented by modules that contribute events.
- `CalendarQuery`: normalized date range, actor and filter context.
- `CalendarEvent`: immutable event DTO with timing, resource metadata and permission hints.
- `CalendarManager`: aggregates providers, filters by date overlap and permission hints, and renders FullCalendar payloads.
- `/api/v1/calendar/events`: API Platform endpoint protected by `ApiTokenMiddleware`.

## Provider Contract

```php
CalendarManager::getInstance()->register('training', new TrainingCalendarProvider());
```

Providers return `CalendarEvent[]`:

```php
new CalendarEvent(
    id: 'training-123',
    title: 'External formation',
    start: '2026-06-03T10:00:00+00:00',
    end: '2026-06-03T11:00:00+00:00',
    resourceKey: 'training-records',
    recordId: 123,
    permissionsAny: ['view-training-calendar']
);
```

## API Feed

Request:

```powershell
GET /api/v1/calendar/events?start=2026-06-01&end=2026-07-01
Authorization: Bearer <token>
```

Response data is compatible with FullCalendar event arrays:

```json
[
  {
    "id": "training-123",
    "title": "External formation",
    "start": "2026-06-03T10:00:00+00:00",
    "end": "2026-06-03T11:00:00+00:00",
    "allDay": false,
    "extendedProps": {
      "resource_key": "training-records",
      "record_id": 123
    }
  }
]
```

## Happy Path

1. Module registers one or more providers during bootstrap.
2. API request supplies `start` and `end`.
3. `CalendarQuery` validates the date window.
4. `CalendarManager` asks each provider for events.
5. Events outside the range are removed.
6. Events with `permissionsAny` are kept only when the actor has at least one declared permission.
7. Payload is returned in FullCalendar shape.

## Sad Path

The contract rejects or hides data when:

- `start` or `end` is missing;
- end date is not after start date;
- provider returns values that are not `CalendarEvent`;
- event is outside the requested range;
- event declares permissions and the actor lacks all of them.

## FullCalendar Asset

FullCalendar is not vendorized in this block. The framework now guarantees the compatible JSON contract and endpoint. A later asset decision can add FullCalendar as a distributable framework asset without changing provider APIs.

## Smoke

Run:

```powershell
php public/cli.php calendar:smoke --json
```

The smoke validates provider registration, range filtering, permission filtering, invalid date rejection and FullCalendar payload shape without DB, session or MFA.
