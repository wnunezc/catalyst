# Catalyst Framework Modals

## Purpose

Define the safe modal contract for Catalyst and for applications built on top of
Catalyst.

## Runtime Owners

| Concern | Owner |
|---|---|
| Programmatic modals | `public/assets/js/catalyst/notifications/modal.js` |
| Shared UI component lifecycle | `public/assets/js/catalyst/runtime/ui-runtime.js` |
| Declarative modal actions | `public/assets/js/catalyst/core/declarative-actions.js` |
| Bootstrap component activation | `public/assets/js/catalyst/bootstrap/components.js` |
| Notification modal payloads | `Catalyst\Framework\Notification\NotificationBag` |
| DevTools modal test surface | `Catalyst\Repository\DevTools\Controllers\ModalTestController` |
| Environment Setup modal forms | `Catalyst\Repository\Configuration\Support\SettingsModalFactory` |

## Safe Modal Contract

Modal markup may be declared inside a view, partial, generated component or app
surface, but runtime activation must place active modal elements at document body
level. This avoids stacking-context bugs where a shell, account layout, sidebar,
status bar or transformed container renders the backdrop above the dialog.

Within Demo UI, declarative modal activation is owned by the common UI runtime.
The runtime registers `bootstrap-components.js`, prevents a second shell
governor in the Demo UI work script, hoists modal elements when required and
destroys owned instances before trusted DOM replacement, then rescans the new
content. Demo UI modules must not call
`initBootstrapComponents()` or another UI runtime themselves.

Third-party Catalyst modules must follow these rules:

- Use Bootstrap modal markup with a stable `id`.
- Trigger with `data-bs-toggle="modal"` and `data-bs-target="#modal-id"`.
- Do not assign custom z-index values to `.modal`, `.modal-dialog` or
  `.modal-backdrop` unless the framework shell contract is being changed.
- Do not wrap active modals in containers that create stacking contexts via
  `transform`, `filter`, `opacity`, `position` plus z-index, or fixed shell
  overlays.
- On a runtime-owned surface, use declarative Bootstrap markup or
  `Catalyst.modal`; do not start another component initializer.
- Runtime-owned programmatic triggers use `data-catalyst-modal-action`; surface
  scripts must not instantiate, import or invoke a modal manager.
- Dynamic modal HTML must be trusted HTML produced by Catalyst controllers and
  validated by the trusted DOM contract.
- Closing a modal must leave no `.modal.show`, `.modal-backdrop`, `modal-open`
  body class, body overflow or body padding residue.

The global activity overlay is not a Bootstrap modal and does not participate
in this component lifecycle. It is a single non-interactive document-level
capability owned by `ActivityManager`; surfaces must not open, clone or style it
as a business dialog. Foreground activity is released before a Bootstrap modal
or toaster communicates the result.

## Test Contract

Every modal E2E must:

1. Navigate to the owning route.
2. Confirm the real URL.
3. Detect login/MFA and complete it only through approved helpers.
4. Confirm a page title, heading or unique surface signal.
5. Inspect visible modal triggers.
6. Open a modal from a visible trigger.
7. Assert the dialog is above the backdrop.
8. Close through visible UI or Escape only when the modal allows keyboard close.
9. Assert no modal/backdrop/body residue remains.

Canonical modal surfaces:

- `/configuration/environment-setup`
- `/test-features`
- `/demo-ui/modals`

Current exhaustive active-surface coverage:

- Configuration setup inventory plus every visible setup modal trigger.
- DevTools confirm, alert, dynamic HTML, dynamic form, API-triggered and wait
  modals.
- Demo UI inventory plus every direct modal trigger, chained modal transition
  and varying-content trigger.

The suite guarantees the active runtime inventory it asserts. A new modal
trigger added to one of these surfaces must update the corresponding inventory
contract and add an independent interaction case. It must not silently expand
the runtime inventory without test coverage.

`boot-core/template/components/_page-header.phtml` exposes reusable
`modal_target` support for actions and the common-layout PageHeader help modal.
Every PageHeader producer outside Demo UI supplies surface-specific help
content through its description. Generated
`DemoUi/form-layout.html` modal markup has no active route/controller mapping.
Settings `modal-cache` is rendered but intentionally has no visible trigger
outside production; the development suite asserts that inactive contract
instead of opening it by bypassing the UI.

Run the current modal suite from the Playwright engine:

```powershell
$env:CATALYST_PLAYWRIGHT_ENGINE = 'D:\OpsZone\DevWorkspace\Engines\Playwright'
Push-Location $env:CATALYST_PLAYWRIGHT_ENGINE
node .\scripts\run-project-tests.js D:\OpsZone\DevWorkspace\Projects\Web\catalyst --suite framework --grep "@modals"
Pop-Location
```

## Environment Interruptions

If a local dependency is missing, such as WSDD, Docker, MFA-Forge, the
Playwright engine or an OS-level application used by a test, report the test as
interrupted by environment. Do not report that as a Catalyst functional failure
unless the dependency is part of Catalyst's runtime contract.

Do not place MFA secrets, account identifiers, browser storage state or local
credentials in the Catalyst repo. Keep them in the engine environment outside
the project.

## Related Documentation

- `docs/testing.md`
- `docs/framework-notification.md`
- `docs/framework-view.md`
- `docs/security-conventions.md`
- `docs/ui/activity-overlay.md`
