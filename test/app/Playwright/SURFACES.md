# App Playwright Surface Registry

Application-owned functional coverage belongs to the `app` suite.

| Surface | Representative routes | Coverage |
|---|---|---|
| Account | Dashboard, profile, activity | `surface-account-layout.spec.cjs` |
| Public | Home, Landing, Store | `surface-public-layout.spec.cjs` |
| ROADMAP-7 complete app inventory | `/`, Dashboard, Home, Landing and Store | `roadmap7-app-inventory.parallel.spec.cjs` |

Run these specs through the workspace Playwright engine with `--suite app`.
They are prepared here but are not executed by the roadmap implementation.
