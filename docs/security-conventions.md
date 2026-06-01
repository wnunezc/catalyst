# Security Conventions

Hard rules for writing CSP-safe views, templates and JavaScript in Catalyst.
Deviating from these conventions breaks the browser's Content Security Policy
and produces runtime errors in the console (`Refused to execute inline script`,
`Refused to apply inline event handler`, etc.).

Read this before touching any `.php` view/template or any file under
`public/assets/js/catalyst/`.

---

## 0. Framework Security Baseline

The current Catalyst runtime already ships with these active security
capabilities:

- CSP enforcement with per-request nonce handling via
  `SecurityHeadersMiddleware`.
- Safe server-to-JS data contract through `InlineJson`.
- Safe server-owned HTML fragment contract through `TrustedHtml`,
  `JsonResponse::withHtml()` and the browser-side `trusted-html` policy check.
- CSRF enforcement for mutating requests.
- MFA/TOTP, password reset, remember-me invalidation and auth throttling.
- RBAC + resource authorization through middleware, policies and registries.
- Tenant-aware API token ownership enforcement and FK-backed integrity.
- Signed cache / route-cache serialized payloads to reduce local
  deserialization exposure.
- DevTools / Setup operational boundaries enforced by dedicated middleware.

This document focuses on the view, template and browser-side conventions that
must be followed so those guarantees remain intact.

---

## 1. Content Security Policy (summary)

CSP is enforced by `app/Framework/Middleware/SecurityHeadersMiddleware.php` on
every non-static response that is not treated as AJAX / JSON by the middleware.

Effective directives:

| Directive      | Policy                                                                      |
|----------------|------------------------------------------------------------------------------|
| `default-src`  | `'self'`                                                                     |
| `script-src`   | `'self'` + `cdn.jsdelivr.net` + `'nonce-{request}'` — **no** `unsafe-inline` |
| `style-src`    | `'self'` + `cdn.jsdelivr.net` + `cdnjs.cloudflare.com` + `'nonce-{request}'` |
| `font-src`     | `'self'` + `cdn.jsdelivr.net` + `cdnjs.cloudflare.com` + `data:`             |
| `img-src`      | `'self'` + `data:`                                                           |
| `connect-src`  | `'self'` + optional `ws://`/`wss://` browser endpoint derived from effective websocket config when `ws_host` is browser-usable |
| `form-action`  | `'self'`                                                                     |
| `base-uri`     | `'self'`                                                                     |
| `frame-ancestors` | `'self'`                                                                  |
| `script-src-attr` | `'none'`                                                                  |
| `style-src-attr`  | `'none'`                                                                  |
| `object-src`      | `'none'`                                                                  |

Key consequences:

- **Every inline `<script>` must carry a nonce.** Scripts without a nonce (or
  with a stale/wrong nonce) are blocked.
- **Inline event handlers (`onclick=`, `onsubmit=`, …) are always blocked.**
  The nonce does not apply to attribute handlers.
- `object`, `embed` and similar plugin surfaces are disabled by `object-src 'none'`.
- Inline `<style>` blocks must carry a nonce, exactly igual que los scripts
  inline. `style="…"` attributes are blocked by `style-src-attr 'none'`.
- The `trusted-renderer` profile is the only current exception: it relaxes
  style handling narrowly for repository-owned Mermaid rendering on `/uml`.
- Prefer external CSS regardless; inline `<style>` should stay reserved for
  early fallback pages or dump/error surfaces that cannot rely on a normal
  asset pipeline.
- External scripts/styles must come from the whitelisted origins above, or be
  served from `/assets/...` (same-origin).

---

## 2. Nonces — how and when

### 2.1 Obtain the per-request nonce

```php
use Catalyst\Helpers\Security\CspNonce;

$nonce = CspNonce::get(); // one value per request, regenerated on every request
```

The nonce is the same value the middleware injected into the CSP header for
this request; reusing it on a `<script>` is what unlocks that script.

### 2.2 Inline `<script>` pattern

Whenever an inline `<script>` is truly necessary (bootstrapping data, tiny
delegation glue, etc.):

```php
<?php use Catalyst\Helpers\Security\CspNonce; ?>
<script nonce="<?= CspNonce::get() ?>">
    // …
</script>
```

If the view may render with or without `CspNonce` loaded (fallback/error
pages), guard the class:

```php
<script<?php
    $n = class_exists(\Catalyst\Helpers\Security\CspNonce::class)
        ? \Catalyst\Helpers\Security\CspNonce::get()
        : '';
    if ($n !== '') {
        echo ' nonce="' . htmlspecialchars($n, ENT_QUOTES, 'UTF-8') . '"';
    }
?>>
    // …
</script>
```

### 2.3 Prefer external files

Default choice is always an external `src="…"` script loaded from
`/assets/js/catalyst/…`. Inline scripts should be the exception, reserved for:

- Bootstrapping per-request state into the DOM (prefer JSON islands — see §5).
- Very small glue that needs server-side data (e.g., a route/URL).
- Fallback error pages that cannot rely on the asset pipeline.

### 2.4 Data passed to JavaScript (JSON island)

For server → JS data, emit a `<script type="application/json">` block. It does
**not** execute, so it needs no nonce:

```php
<script type="application/json" id="flash-data">
<?= json_encode($flashes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>
</script>
```

The consumer JS then reads it with `document.getElementById('flash-data').textContent`
and `JSON.parse(...)`. See `public/assets/js/catalyst/modules/flash-client.js`.

When the payload must be emitted directly into executable inline JavaScript,
use the shared helper instead of raw `json_encode(...)`:

```php
use Catalyst\Framework\View\InlineJson;

<script nonce="<?= \Catalyst\Helpers\Security\CspNonce::get() ?>">
    window.__catalystWs = <?= InlineJson::encode($payload) ?>;
</script>
```

`InlineJson` exists specifically to keep the inline context hardened with the
required `JSON_HEX_*` flags.

### 2.5 Inline `<style>` pattern

Whenever an inline `<style>` is truly necessary:

```php
<?php use Catalyst\Helpers\Security\CspNonce; ?>
<style nonce="<?= CspNonce::get() ?>">
    /* ... */
</style>
```

For early fallback pages where `CspNonce` may not be guaranteed yet, use the
same guarded pattern as inline scripts:

```php
<style<?php
    $n = class_exists(\Catalyst\Helpers\Security\CspNonce::class)
        ? \Catalyst\Helpers\Security\CspNonce::get()
        : '';
    if ($n !== '') {
        echo ' nonce="' . htmlspecialchars($n, ENT_QUOTES, 'UTF-8') . '"';
    }
?>>
    /* ... */
</style>
```

### 2.6 Third-party renderers that emit inline SVG styles

Some browser-side renderers (for example Mermaid) emit SVG markup with inline
`style=""` attributes or embedded `<style>` tags. Under Catalyst CSP, that
markup must **not** be inserted directly into the page DOM.

Preferred pattern:

- render the SVG string off-DOM
- expose it through a safe container such as `img src="data:image/svg+xml,..."`
  or another isolation boundary that does not introduce page-level
  `style=""` attributes

Do not treat "it only comes from a library" as an exception to
`style-src-attr 'none'`.

### 2.7 CSP profiles for trusted client renderers

This is not hypothetical anymore: `/uml` uses the `trusted-renderer` profile
from `Repository/Framework/DevTools/Controllers/UmlController.php`. For any
future page that truly needs a browser-side renderer such as Mermaid, do not
relax CSP ad hoc in the view or by editing headers route by route without a
shared rule. The intended pattern is a small set of explicit CSP profiles:

- `strict`
  Default for every HTML response. Keep `style-src-attr 'none'`.
- `trusted-renderer`
  Reserved for internal pages that render static, repository-owned diagrams in
  the browser or embed controlled vendor media and therefore need narrowly
  scoped style-attribute / frame-source exceptions.

Important constraints:

- The profile must be declared by the server, never inferred in the browser.
- The page must be internal/controlled; do not use this profile for user-supplied
  or user-editable diagram source.
- Do not relax the global default policy just because one page needs Mermaid.
- Prefer prerendered SVG over a relaxed profile whenever the diagrams are static.

Recommended decision order:

1. Static diagrams or rarely changing architecture pages:
   prerender SVG and serve it as `<img>`.
2. Internal DevTools/docs pages with controlled Mermaid source:
   use a dedicated `trusted-renderer` profile.
3. Any flow involving user-controlled diagram input:
   isolate rendering in a separate boundary instead of relaxing the main page CSP.

Example declaration pattern from the backend:

```php
return $this->view('uml', [
    'title' => 'Catalyst — Framework Architecture',
    'csp_profile' => 'trusted-renderer',
]);
```

Or as route metadata / response metadata, as long as the middleware remains the
single place that decides the final header:

```php
Route::get('/uml', [UmlController::class, 'index'])
    ->defaults(['csp_profile' => 'trusted-renderer']);
```

The middleware contract should remain:

- if no profile is declared: apply `strict`
- if `trusted-renderer` is declared: relax only the minimum needed for the renderer
- never let the frontend choose or upgrade its own CSP profile

Operational note for Mermaid:

- If console output says `Applying inline style violates ... style-src-attr 'none'`,
  the problem is the renderer behavior itself, not only the final injected DOM.
- Wrapping the final SVG in `<img>` can remove page-level `style=""` residue, but
  it does not guarantee that the renderer stopped touching inline styles during
  generation.
- Treat that console signal as a real CSP mismatch until proven otherwise.

---

## 3. No inline event handlers — use delegation

The following are **banned** everywhere (views, layouts, components, error
pages, email templates rendered as HTML, etc.):

- `onclick="…"`, `onsubmit="…"`, `onchange="…"`, `onkeyup="…"`, `onload="…"`,
  `onmouseover="…"`, and any other `on*=` attribute.
- Inline `href="javascript:…"`.

Replace them with **data attributes + a delegated listener**. Delegation lives
in a small module under `public/assets/js/catalyst/modules/`, loaded once by
the layout. Each module is idempotent (guarded by a `window.__catalyst…Bound`
flag) so it is safe to include multiple times.

### 3.1 Canonical `data-*` attributes

| Attribute                     | Purpose                                       | Module / template                                         |
|-------------------------------|-----------------------------------------------|-----------------------------------------------------------|
| `data-confirm="message"`      | `confirm()` before form submit                | `modules/ui-actions.js`                                   |
| `data-history-back`           | Button triggers `history.back()`              | `modules/ui-actions.js` (+ inline fallback on error pages)|
| `data-theme-toggle`           | Cycle light/dark theme                        | `modules/theme-toggle.js`                                 |
| `data-password-toggle`        | Toggle password-field visibility inside `.input-group` | `modules/ui-actions.js`                             |
| `data-flash-dismiss`          | Dismiss a flash card                          | `modules/flash-client.js`                                 |
| `data-dumper-toggle="modal"`  | Open/close a debug dumper modal               | `template/debug/dumper-scripts.tpl.phtml`                   |
| `data-dumper-collapse="id"`   | Expand/collapse a debug dumper section        | `Helpers/Debug/DumperCollapsible.php`                     |

Do **not** invent ad-hoc `data-` attributes for one-off view-local behavior.
If a view needs a new behavior, add a tiny module under
`public/assets/js/catalyst/modules/` and register it in the relevant layout.

### 3.2 Delegation pattern

```javascript
(function () {
    if (window.__catalystMyFeatureBound) return;
    window.__catalystMyFeatureBound = true;

    document.addEventListener('click', function (e) {
        var el = e.target;
        while (el && el !== document.body) {
            if (el.hasAttribute && el.hasAttribute('data-my-feature')) {
                // handle
                return;
            }
            el = el.parentElement;
        }
    });
})();
```

Use capture-phase (`true` as 3rd arg of `addEventListener`) only when the
event must be intercepted before bubbling — e.g., `submit` on a form that also
has its own listener.

### 3.3 Examples — before / after

**Before (CSP-blocked):**

```html
<form method="post" onsubmit="return confirm('Delete?')">
<button onclick="history.back()">Go Back</button>
```

**After (CSP-safe):**

```html
<form method="post" data-confirm="Delete?">
<button type="button" data-history-back>Go Back</button>
```

The matching handlers live in `modules/ui-actions.js` and are loaded through
the shared body-script partial used by `admin.php`, `auth.php` and `base.php`.

---

## 4. Layouts and module loading

Every HTML page should use one of these layouts instead of hand-rolling
`<html>` unless the page is an early fallback:

- `boot-core/template/layouts/admin.phtml` — authenticated admin UI
- `boot-core/template/layouts/auth.phtml` — auth pages (`/login`, `/register`, MFA, password reset)
- `boot-core/template/layouts/base.phtml` — public/dev/shared chrome (`/setup`, DevTools, generic public pages)
- `boot-core/template/layouts/blank.phtml` — shell without status bar, flash, toaster or modal chrome

`auth.php`, `base.php` and `admin.php` share `_head-assets.phtml`,
`_body-scripts.phtml` y `_catalyst-init.phtml`; `blank.php` solo monta el shell
mínimo y omite el chrome compartido.

When creating a new view, **choose the right layout** rather than emitting raw
`<html>`. If neither fits, replicate the script loading order:

1. Bootstrap JS bundle (CDN, whitelisted)
2. `/assets/js/catalyst/modules/ui-actions.js`
3. `/assets/js/catalyst/modules/theme-toggle.js`
4. `/assets/js/catalyst/modules/flash-client.js`
5. Page-specific modules

All `<script src="…">` tags should be same-origin (`/assets/...`) or from
cdn.jsdelivr.net. Nonces are **not** required on external scripts.

---

## 5. CSRF

CSRF is enforced server-side by `app/Framework/Middleware/CsrfMiddleware.php`.
`CsrfProtection` is the token provider used by views/layouts.

For every form that performs a state change:

```php
use Catalyst\Helpers\Security\CsrfProtection;

<form method="post" action="…">
    <?= CsrfProtection::getInstance()->getTokenField() ?>
    <!-- other fields -->
</form>
```

For AJAX requests, the centralized transport layer in
`public/assets/js/catalyst/modules/http.js` reads the token from
`<meta name="csrf-token" content="…">` (emitted by the layout via
`CsrfProtection::getMetaTag()`), injects `X-CSRF-TOKEN` automatically, and
mirrors refreshed tokens back into the DOM when the server returns `new_token`.
Do **not** add the token manually in framework fetch calls; do **not** remove
the meta tag from layouts.

---

## 6. Flash messages and toasts

Flash delivery is split between a server-side queue and client-side rendering.
The bridge is JSON, not server-rendered dismiss logic.

1. Controllers enqueue messages through the session-backed flash API
   (`$this->flash()->success(...)`, `$this->flash()->error(...)`, etc.).
2. HTML flows that need toast semantics use `Controller::toast(...)`, which
   still writes to the framework flash queue.
3. The layout component `boot-core/template/components/_flash-messages.phtml`
   emits the JSON bridge:
   ```html
   <script type="application/json" id="catalyst-flash-data">…</script>
   ```
4. `modules/flash-client.js` parses that payload. Regular flashes are consumed
   once; persistent flashes render dismissable cards with
   `data-flash-dismiss`.

Do not inline flash HTML with `onclick="dismiss(this)"`. Do not bypass the
bridge by rendering dismiss handlers directly in a view.

---

## 7. Debug Dumper (development only)

The `dd()` / `ddj()` helpers emit per-dump HTML through templates in
`boot-core/template/debug/`. The templates are CSP-safe:

- `dumper-button.tpl.php`, `dumper-modal.tpl.php`: use `data-dumper-toggle`.
- `dumper-scripts.tpl.php`: single delegated listener guarded by
  `window.__catalystDumperToggleBound`; nonce added when `CspNonce` is
  available.
- `DumperCollapsible::getJavaScript()`: returns a function + a delegated
  listener guarded by `window.__catalystDumperCollapseBound`.

When adding new debug output, route it through these helpers; do not emit
custom inline `<script>` / `onclick` in view code to render debug info.

---

## 8. Error pages

`boot-core/template/errors/{404,405,handler_error,handler_error_no}.phtml` and
`app/Kernel.php` (500 fallback) may be rendered very early — before middleware
or even before the autoloader is fully initialized.

Convention:

- Keep inline `<style>` simple and small, and always add a nonce using the
  guarded pattern when the page may render before the full layout pipeline.
- Any inline `<script>` must use the **guarded nonce pattern** from §2.2 so
  the page still degrades when `CspNonce` is unavailable.
- Buttons that would normally use `onclick="history.back()"` use
  `data-history-back` + a tiny inline script in the page itself (these pages
  do not load the asset pipeline). See `error/404.php`, `error/405.php`.

---

## 9. Checklist when creating or editing a view

1. No `on*=` attributes anywhere in the markup.
2. Every inline `<script>` has `nonce="<?= CspNonce::get() ?>"`.
3. Every inline `<style>` has `nonce="<?= CspNonce::get() ?>"` or the guarded fallback.
4. Any server → JS data uses a `<script type="application/json">` island, not
   inline `var x = <?= … ?>;`.
5. Any new repeated behavior has a module under
   `public/assets/js/catalyst/modules/` and is referenced via `data-*`.
6. External scripts/styles are on the whitelisted CDN origins or same-origin.
7. Forms performing state change include `CsrfProtection::getInstance()->getTokenField()`.
8. Pages relying on layouts use `layouts/admin.phtml`, `layouts/auth.phtml`,
   `layouts/base.phtml` or `layouts/blank.phtml` según corresponda; no emitir
   `<html>`/`<body>` directo salvo fallback temprano de error.

---

## 10. Verification

Before committing, run these scans. They must return **zero hits on code**
(comments in this doc and historical notes are acceptable):

```bash
# Inline event handlers in PHP / HTML
rg -n --glob '*.php' '\son(click|submit|change|keyup|keydown|load|mouseover|focus|blur)='

# Inline <script> without nonce and not JSON island and not src=
rg -n --glob '*.php' '<script(?![^>]*\b(src|nonce|type="application/json")=)'
```

CLI shortcut:

```bash
php public/cli.php security:check
```

If either produces a real hit, fix it before merging.

### Runtime caveat: local browser / antivirus injection

On this workspace's Windows environment, browser security products or
antivirus web shields may inject their own `<style>` blocks into the page.
One observed example during runtime validation was `style.abn_style` without a
nonce.

Treat those console violations as **environment noise first**, not as an app
regression, when all of the following hold:

1. `php public/cli.php security:check` is clean.
2. The project DOM does not contain `style="..."` attributes or inline
   `<style>` blocks without nonce.
3. The reported nodes are clearly foreign/injected (for example,
   `style.abn_style`) and do not come from Catalyst templates/assets.

If a CSP/runtime warning only reproduces in a browser with local security
injection enabled, document it as external interference before changing
server-side CSP or view code.

---

## References

- `app/Framework/Middleware/SecurityHeadersMiddleware.php` — CSP header source of truth
- `app/Helpers/Security/CspNonce.php` — per-request nonce provider
- `app/Helpers/Security/CsrfProtection.php` — CSRF token lifecycle
- `app/Framework/Middleware/CsrfMiddleware.php` — CSRF enforcement
- `public/assets/js/catalyst/modules/ui-actions.js` — `data-confirm`, `data-history-back`
- `public/assets/js/catalyst/modules/theme-toggle.js` — theme switching
- `public/assets/js/catalyst/modules/flash-client.js` — flash JSON island consumer
- `public/assets/js/catalyst/modules/password.js` — password visibility toggle
- `boot-core/template/debug/dumper-scripts.tpl.phtml` — debug dumper delegation
- `app/Helpers/Debug/DumperCollapsible.php` — collapsible section delegation
