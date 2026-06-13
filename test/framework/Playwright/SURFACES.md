# Playwright Surface Registry

## Purpose

Track progressive Playwright coverage without bulk-migrating legacy specs.

## Modal Inventory

| Surface | Route | Modal modes | Coverage |
|---|---|---|---|
| Settings setup | `/configuration/environment-setup` | Inventory contract plus every visible settings modal trigger | `settings-modals.spec.cjs` |
| DevTools | `/test-features` | confirm, alert, dynamic HTML, dynamic form, API-triggered modal, wait modal | `devtools-modals.spec.cjs` |
| Demo UI modal reference | `/demo-ui/modals` | Inventory contract, every direct trigger, chained transitions and varying-content triggers | `demo-ui-modals.spec.cjs` |

## Test Features Inventory

| Surface | Route | Coverage |
|---|---|---|
| Test Features runtime | `/test-features` | Canonical document, shell, theme controls, central runtime and DevTools work asset | `test-features-runtime.spec.cjs` |
| Test Features actions | `/test-features` | Direct shared toast and non-destructive partial refresh through shared HTTP response actions | `test-features-actions.spec.cjs` |

## Demo UI Inventory

The active Demo UI catalog contains 85 URLs backed by 84 unique generated
preview documents. `/demo-ui` is an alias of the Alerts preview.

| Family | URLs | Coverage |
|---|---:|---|
| All routes | 85 | `demo-ui-routes.spec.cjs` |
| Interactive Base UI components | 7 focused behaviors | `demo-ui-components.spec.cjs` |
| Form runtime/plugin groups | 7 focused contracts | `demo-ui-forms.spec.cjs` |
| Apex and ECharts | 31 | `demo-ui-charts.spec.cjs` |
| Static, custom and DataTables | 17 | `demo-ui-tables.spec.cjs` |
| Modals | 23 declared triggers | `demo-ui-modals.spec.cjs` |

The canonical route and asset data lives in
`fixtures/demo-ui-catalog.cjs`. The detailed inventory is maintained in
`docs/ui/demo-ui-javascript-inventory.md`.

The route, component, form, chart and table cases were executed individually.
After removal of the legacy parallel governor, every modal case was executed
three times independently: 69/69 passed.

## Canonical Document Coverage

Every complete framework surface now uses `document.phtml` and the shared
shell. The following focused specs are prepared for individual execution:

| Surface | Representative routes | Coverage |
|---|---|---|
| Application shell | Workspaces, Operations, Roles, Settings, DevTools | `shell-layout.spec.cjs` |
| Canonical owners | Six Workspaces and five Operations representative routes | `canonical-owners.spec.cjs` |
| Auth | Login, forgot password, email verification | `surface-auth-layout.spec.cjs` |
| Error | Missing route / 404 | `surface-error-layout.spec.cjs` |
| Demo UI runtime | `/demo-ui` | `demo-ui-runtime.spec.cjs` |
| Dynamic runtime rescan | Runtime-managed inserted DOM | `ui-runtime-dynamic.spec.cjs` |
| Theme skins | 7 Inspinia skins plus 4 institutional skins | `theme-skins.spec.cjs` |
| Global flash notifications | Shared flash projection and runtime dismissal | `flash-runtime.spec.cjs` |
| Global DataGrid | `/users` | `datagrid-runtime.spec.cjs` |
| Global FormBuilder | `/workspaces/media-fields/create` | `form-builder-runtime.spec.cjs` |
| Global RecordPresence | First available `/operations/automation-rules/{id}` | `record-presence-runtime.spec.cjs` |
| Demo UI recursive model | `/demo-ui/charts/apex/line` | `navigation-models.spec.cjs` |
| Framework model | `/configuration/application-health` | `navigation-models.spec.cjs` |
| Application model | `/account/profile` | `navigation-models.spec.cjs` |
| ROADMAP-4 route ownership | Retired aliases/companions and canonical Users, Account, runtime, UML and Demo UI routes | `roadmap4-routes.spec.cjs` |

## Configuration Ownership Coverage

| Surface | Route | Coverage |
|---|---|---|
| Environment Setup | `/configuration/environment-setup` | `configuration-surfaces.spec.cjs`, `settings-modals.spec.cjs` |
| Application Health panel | `/configuration/application-health` | `configuration-surfaces.spec.cjs` |
| Public liveness/readiness | `/configuration/application-health/live`, `/configuration/application-health/ready` | `configuration-surfaces.spec.cjs` |
| Platform Appearance | `/configuration/platform-appearance` | `configuration-surfaces.spec.cjs` |
| Feature Flags | `/configuration/feature-flags` | `configuration-surfaces.spec.cjs` |
| Plugins | `/configuration/plugins` | `configuration-surfaces.spec.cjs` |

The model tests assert the single recursive sidebar renderer, active propagation,
Framework/App composition and zero `Disconnected` debt. The Configuration
tests assert the shared shell/runtime and reject legacy Operations work assets.
They are prepared for manual execution and are not executed as part of
ROADMAP-2 implementation.

These specs verify one canonical runtime script, expected shell capabilities and
the absence of the removed `shell-dropdowns.js` governor. They must be executed
through the workspace runner with `--suite framework`, one surface spec at a
time.

Account, Dashboard, Home, Landing and Store are application-owned consumers.
Their functional specs live under `test/app/Playwright` and use `--suite app`.

`datagrid-runtime.spec.cjs` additionally verifies the neutral DataGrid template,
shared stylesheet, runtime-owned print action, selection state and bulk-action
enablement without executing destructive actions.

`form-builder-runtime.spec.cjs` verifies the neutral FormBuilder template,
dependency state and opt-in autosave against a real metadata form without
submitting mutations.

`record-presence-runtime.spec.cjs` discovers a real automation record, verifies
the neutral banner and shared assets, and observes owner heartbeat traffic
without performing a record mutation.

## Individual Modal Coverage

Settings covers the 11 active triggers:

`modal-app`, `modal-db`, `modal-mail`, `modal-ftp`, `modal-session`,
`modal-security`, `modal-features`, `modal-logging`, `modal-websocket`,
`modal-devtools` and `modal-cors`.

`modal-cache` is rendered but intentionally has no active trigger outside
production. Its inactive contract is asserted separately: modal present, no
visible trigger and save disabled.

Demo UI asserts all 23 declared modal triggers. This includes 17 direct
single-target examples, the three `#exampleModal` varying-content triggers,
`#multiple-one` to `#multiple-two`, and
`#exampleModalToggle` to `#exampleModalToggle2` and back.

Each executable case navigates independently to its owning route, opens from a
visible trigger, validates visible content and layering, closes through visible
UI, and verifies that no modal, backdrop or body-state residue remains.

## Non-Surfaces

| Source | Classification | Action |
|---|---|---|
| `boot-core/template/components/_page-header.phtml` modal target support | Reusable capability without current runtime consumer | Document and cover when a real consuming surface is added |
| `Repository/Framework/DemoUi/generated/theme-previews/form-layout.html` | Generated orphan without active route/controller mapping | Do not add E2E coverage |
| Settings `modal-cache` in non-production | Rendered but intentionally inactive and without a visible trigger | Assert inactive contract; execute opening flow in a production-profile harness when one exists |
| Playwright engine legacy Catalyst specs | Historical migration source | Migrate individual still-valid cases only |

## Progressive Migration Rule

Before adding a spec:

1. Confirm the route and surface exist in the current runtime.
2. Confirm the behavior is not already covered.
3. Extract only the minimal reusable helper needed.
4. Add one short independent regression or surface contract.
5. Run the new spec independently before adding another surface.

## ROADMAP-3 Manual Commands

Run from PowerShell through the authorized workspace runner. These commands are
prepared for the user and were not executed by the implementation agent.

```powershell
Push-Location D:\OpsZone\DevWorkspace\Engines\Playwright; node .\scripts\run-project-tests.js D:\OpsZone\DevWorkspace\Projects\Web\catalyst --suite framework --grep "@navigation-models"; Pop-Location
Push-Location D:\OpsZone\DevWorkspace\Engines\Playwright; node .\scripts\run-project-tests.js D:\OpsZone\DevWorkspace\Projects\Web\catalyst --suite framework --grep "@configuration-surfaces"; Pop-Location
Push-Location D:\OpsZone\DevWorkspace\Engines\Playwright; node .\scripts\run-project-tests.js D:\OpsZone\DevWorkspace\Projects\Web\catalyst --suite framework --grep "@canonical-owners"; Pop-Location
Push-Location D:\OpsZone\DevWorkspace\Engines\Playwright; node .\scripts\run-project-tests.js D:\OpsZone\DevWorkspace\Projects\Web\catalyst --suite framework --grep "@record-presence"; Pop-Location
Push-Location D:\OpsZone\DevWorkspace\Engines\Playwright; node .\scripts\run-project-tests.js D:\OpsZone\DevWorkspace\Projects\Web\catalyst --suite framework --grep "@roadmap4-routes"; Pop-Location
Push-Location D:\OpsZone\DevWorkspace\Engines\Playwright; node .\scripts\run-project-tests.js D:\OpsZone\DevWorkspace\Projects\Web\catalyst --suite framework; Pop-Location
```
