# Global Activity Overlay

## Purpose

Define the single visual activity contract used while Catalyst loads a complete
document, navigates, submits native forms or waits for foreground JavaScript
requests.

## Runtime Owners

| Concern | Owner |
|---|---|
| Single overlay markup | `boot-core/template/document.phtml` |
| Overlay appearance | `public/assets/css/catalyst/activity-overlay.css` |
| Activity tokens and lifecycle | `public/assets/js/catalyst/runtime/activity-manager.js` |
| Runtime mounting | `public/assets/js/catalyst/runtime/ui-runtime.js` |
| JavaScript request lifecycle | `public/assets/js/catalyst/core/http.js` |

## Contract

The canonical document renders one overlay in the `booting` state before the
shared shell. The central runtime creates one `ActivityManager`, mounts the UI,
then releases the boot state. No module, surface or work asset may create a
second global loader.

The manager uses tokens instead of a global boolean. Concurrent foreground
operations therefore keep the overlay visible until every matching operation
finishes. The overlay blocks pointer interaction globally, so forms and links
cannot be activated repeatedly while Catalyst is waiting.

The capture listeners also cancel click and submit events while a token is
active. This closes the narrow interval where a browser can dispatch both
events of a fast double click before the newly rendered backdrop becomes the
pointer target.

The manager coordinates:

- internal same-document-context links;
- valid native form submits;
- declarative redirects and refreshes;
- foreground requests executed through `HttpClient` or the governed fetch
  interceptor.

External links, downloads, anchors, modified clicks and links targeting another
browsing context do not activate the local overlay.

## Foreground And Background Requests

Requests are foreground unless they explicitly pass `background: true`.
Foreground requests emit matching `catalyst:http:start` and
`catalyst:http:finish` events even when fetch or response processing fails.

Automatic transports must declare `background: true`. This includes presence
heartbeats, WebSocket token refresh, unread-count polling and best-effort flash
dismissal. User-triggered requests remain foreground.

Foreground notifications are deferred until the matching request has released
its activity token. Redirects and refreshes begin a navigation token that stays
visible until the replacement document loads.

## Recovery And Limits

Each activity token has a visual recovery timeout. The timeout releases the UI
and emits a console warning; it does not cancel a request or alter server data.

The overlay can appear only after the server has delivered enough HTML to parse
the document body. If JavaScript is completely disabled or the runtime module
cannot load at all, the initially visible overlay cannot self-release. Runtime
failures after `ActivityManager` mounts are covered by its recovery timeout.

## Related Documentation

- `docs/architecture.md`
- `docs/framework-view.md`
- `docs/framework-modals.md`
- `docs/testing.md`
- `docs/ui/surface-architecture.md`
