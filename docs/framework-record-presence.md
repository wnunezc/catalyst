# Catalyst Framework RecordPresence

## Purpose

RecordPresence is the global realtime projection of tenant-scoped record
claims. `RecordClaimManager` remains the concurrency primitive; RecordPresence
converts claim snapshots into browser state, heartbeat renewal and WebSocket
updates.

## Runtime Owners

| Concern | Owner |
|---|---|
| Presence payloads, heartbeat and publication | `Catalyst\Framework\Presence\RecordPresenceManager` |
| View contract and i18n state | `Catalyst\Framework\Presence\RecordPresenceViewModel` |
| Shared template | `boot-core/template/components/_record-presence.phtml` |
| Heartbeat and realtime projection | `public/assets/js/catalyst/presence/record-presence.js` through the central UI runtime |
| Shared styles | `public/assets/css/catalyst/record-presence.css` |
| Authenticated API | `Catalyst\Repository\Notification\Controllers\PresenceController` |
| Claim persistence and ownership | `Catalyst\Framework\Concurrency\RecordClaimManager` |

## Security

The heartbeat route requires authentication, CSRF, tenant context, throttle and
resource-level `view` authorization before renewing a claim. Invalid or unknown
resources are denied through the shared resource policy.

Only the current owner starts heartbeat polling. The browser uses the shared
HTTP client and shared StatusBar WebSocket connection. Text updates use
`textContent`; no claim labels or actor values are injected as HTML.

## Consumption

Controllers expose `recordPresence` through
`buildRecordPresenceContext()`. Views render `components._record-presence`.
Concurrency mutation forms continue to submit the opaque `claim_token`; that
token is not part of the browser subscription contract.
