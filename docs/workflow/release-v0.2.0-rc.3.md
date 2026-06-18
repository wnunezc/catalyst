# Catalyst v0.2.0-rc.3 Corrective Release Notes

## Purpose

`v0.2.0-rc.3` is a corrective follow-up to `v0.2.0-rc.2`. It hardens the
bootstrap error surface, clarifies expired form-state responses, improves AJAX
form contract diagnostics and restores the shared mobile/tablet sidebar
behavior without changing the existing bootstrap or `.htaccess` error flow.

## Resolved Issues

### Issue #17: Missing Bootstrap Error Templates And Unsafe Fallback

Catalyst now provides dedicated dependency-safe development and production
bootstrap error templates:

- `boot-core/template/errors/handler_error.phtml`
- `boot-core/template/errors/handler_error_no.phtml`

`ErrorOutput` retains the existing bootstrap and logging sequence while adding
a bounded internal fallback, recursive-render protection and size limits for
descriptions, traces and source context. Development responses expose bounded
diagnostics; production responses expose only a safe summary, correlation
ticket and occurrence timestamp. The unbounded `print_r()` fallback was
removed.

### Issue #18: AJAX Form Contract Mismatch Diagnostics

When a form marked with `data-catalyst="form"` receives HTML or a followed
redirect instead of the required JSON response, the shared HTTP and form
runtimes now report an explicit `Catalyst form contract mismatch` diagnostic.
Traditional forms without the Catalyst AJAX marker continue to support normal
HTML redirects.

### Issue #19: Expired Form State Was Reported As Session Expiration

Invalid CSRF state for an active form is now classified as
`form_state_expired` instead of incorrectly claiming that the authenticated
session expired.

AJAX responses include:

```json
{
  "success": false,
  "code": "form_state_expired",
  "refresh_required": true,
  "new_token": "..."
}
```

Traditional HTML submissions receive a clear flash message explaining that the
form expired due to inactivity. CSRF validation remains fail-closed.

### Issue #20: Error Rendering Masked The Original Roles Edit Failure

The hardened bootstrap error output no longer expands arbitrary error payloads
or recursively crashes when handling a failure from
`/users/roles/{id}/edit`. Missing-template and large-payload regression
coverage verifies that the original error remains bounded and loggable.

## Additional Mobile Shell Correction

The shared shell now restores Inspinia offcanvas behavior on mobile and tablet:

- the sidebar is visible during initial boot and closes when the UI runtime is
  ready;
- the small Catalyst topbar logo is a semantic button that opens and closes the
  sidebar without triggering navigation or the global activity overlay;
- edge swipe opens the sidebar and reverse swipe closes it;
- tapping the exterior backdrop closes it;
- desktop retains the fixed/default sidebar;
- theme initialization can no longer overwrite the mobile offcanvas state.

The behavior remains centralized in
`public/assets/js/catalyst/shell/navigation.js` and
`public/assets/css/catalyst/inspinia-runtime-compat.css`.

## Upgrade From v0.2.0-rc.2

```powershell
git fetch upstream --tags
git diff --stat v0.2.0-rc.2..v0.2.0-rc.3
git merge v0.2.0-rc.3
composer install
php public/cli.php inspect:lint
php public/cli.php route:lint
php public/cli.php security:check
php public/cli.php quality:check
```

No database migration is introduced by this release candidate.

## Focused Verification

- Bootstrap error missing-template and large-payload contracts.
- CSRF JSON/HTML `form_state_expired` contracts.
- AJAX form HTML/JSON mismatch architecture contract.
- Mobile/tablet sidebar automatic close, toggle, backdrop and swipe behavior.
- Desktop fixed sidebar regression.
- Anonymous error surface layout.
- Composer validation/audit, structural lint, route lint, security checks and
  the Catalyst quality gate.

The complete framework unit suite remains dependent on the local PDO SQLite
driver. In the release environment where that driver is unavailable, nine
database-backed tests cannot start; focused tests for this release pass.

## Release Contract

- `catalyst.json` reports `0.2.0-rc.3` with channel `rc`.
- Publish as a signed tag and GitHub pre-release through
  `.github/workflows/release.yml`.
- ROADMAP files, backups, local secrets, uploads, runtime state and ad-hoc
  archives remain outside the release commit.
- Verify the public ZIP and SHA-256 asset before closing issues #17, #18, #19
  and #20.
