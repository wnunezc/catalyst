# Framework API Owner

## Purpose

Document the independent owner of transversal public framework APIs.

## Contract

`Repository/Framework/Api` and `Catalyst\Repository\Api` own six stable `/api/v1/*` routes:

| Method | Path |
|---|---|
| GET | `/api/v1/catalog` |
| GET | `/api/v1/calendar/events` |
| GET | `/api/v1/workflows` |
| POST | `/api/v1/workflows/{id}/transition` |
| GET | `/api/v1/versions/{resourceKey}/{recordId}` |
| POST | `/api/v1/versions/{id}/restore` |

These endpoints preserve `ApiTokenMiddleware`, abilities, throttling, payloads and error contracts. API Management remains a separate privileged surface under Operations.
