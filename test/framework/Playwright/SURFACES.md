# Playwright Surface Registry

## Purpose

Track progressive Playwright coverage without bulk-migrating legacy specs.

## Modal Inventory

| Surface | Route | Modal modes | Coverage |
|---|---|---|---|
| Settings setup | `/configuration/environment-setup` | Inventory contract plus every visible settings modal trigger | `settings-modals.spec.cjs` |
| DevTools | `/test-features` | confirm, alert, dynamic HTML, dynamic form, API-triggered modal, wait modal | `devtools-modals.spec.cjs` |
| Demo UI modal reference | `/demo-ui/modals` | Inventory contract, every direct trigger, chained transitions and varying-content triggers | `demo-ui-modals.spec.cjs` |

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
| `boot-core/template/components/_admin-page-header.phtml` modal target support | Reusable capability without current runtime consumer | Document and cover when a real consuming surface is added |
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
