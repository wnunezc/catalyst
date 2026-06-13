# Test Features JavaScript Inventory

## Scope

This document records the active `/test-features` rendering and JavaScript
contract. The route uses the same canonical Catalyst document, shell, theme
support and centralized UI runtime as `/demo-ui`.

## Rendering Stack

```text
TestFeaturesController::index()
  -> Controller::view(...)
  -> View::render(...)
  -> boot-core/template/document.phtml
  -> boot-core/template/shell.phtml
     -> boot-core/template/_topbar.phtml
     -> boot-core/template/_sidebar.phtml
     -> boot-core/template/_content.phtml
        -> Repository/Framework/DevTools/Views/pages/test-features.phtml
     -> boot-core/template/_status-bar.phtml
```

The page template and all `_tf-*.phtml` files are fragments. They do not own
`html`, `head`, `body`, shell markup or runtime scripts.

## Shared Shell Contract

`TestFeaturesController` declares the surface identity and enabled shell
capabilities:

- `surface_context=devtools`
- `surface_page=test-features`
- topbar, sidebar, status bar and theme customizer enabled
- shared wrapper, content and Inspinia-compatible shell classes
- DevTools navigation context and Test Features status label

Theme values and common assets come from `DocumentScope`; the controller does
not initialize a separate theme runtime.

## JavaScript Ownership

`public/assets/js/catalyst/runtime/ui-runtime.js` is the only governor. The
DevTools work script registers capability adapters through
`registration-queue.js` and never starts from `DOMContentLoaded`.

| Adapter | DOM capability | Responsibility |
|---|---|---|
| `devtools.actions` | `[data-devtools-action]` | Delegated DevTools actions, API calls and dialogs |
| `devtools.form-submit` | DevTools form submissions | HTTP submission through the shared client |
| `devtools.form-response` | `catalyst:form:response` | Response rendering and form-specific cleanup |
| `devtools.upload` | `#u17-form` | Upload result and reset handling |
| `devtools.uml` | `.uml-showcase` | UML-only Mermaid loading and rendering |

Each adapter is idempotent. Bootstrap components, modals, toasts, navigation,
theme controls and status bar behavior remain owned by the central runtime.

The visible Global Activity Overlay card exercises five non-destructive
lifecycles through the existing partial-refresh endpoint and one GET form:
foreground, background, concurrent foreground, expected error and native
submit navigation. The DevTools work script does not open a local wait modal;
the global `ActivityManager` owns request activity.

## Assets

The canonical document loads:

1. shared head assets and Catalyst theme CSS;
2. `/assets/css/work/devtools/style.css`;
3. the local Bootstrap bundle and shared Catalyst modules;
4. `/assets/js/catalyst/runtime/ui-runtime.js`;
5. `/assets/js/work/devtools/script.js`.

The work assets are published from `Repository/Framework/DevTools/front/` by
`FrontResourceTrait`. No second shell or UI runtime is loaded.

## Playwright Coverage

Focused specs under `test/framework/Playwright/specs/`:

- `test-features-runtime.spec.cjs`: document, shell, runtime and asset contract.
- `test-features-actions.spec.cjs`: direct toast and partial-refresh actions.
- `flash-runtime.spec.cjs`: one-shot and persistent shared flash behavior.
- `devtools-modals.spec.cjs`: modal interactions and cleanup.
- `activity-overlay.spec.cjs`: boot, visible request diagnostics and navigation activity.

Run them only through the workspace Playwright runner with
`--suite framework`. The specs must be executed individually when validation
is authorized.
