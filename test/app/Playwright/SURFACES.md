# App Playwright Surface Registry

Application-owned functional coverage belongs to the `app` suite.

| Surface | Representative routes | Coverage |
|---|---|---|
| Canonical document consumer integration | Dashboard plus representative Account profile/activity routes | `surface-account-layout.spec.cjs` |
| Public | Home, Landing, Store | `surface-public-layout.spec.cjs` |
| ROADMAP-7 complete app inventory | `/`, Dashboard, Home, Landing and Store | `roadmap7-app-inventory.parallel.spec.cjs` |

Run these specs through the workspace Playwright engine with `--suite app`.
They are prepared here but are not executed by the roadmap implementation.

Account is framework-owned in `v0.2.0-rc.1`. Its presence in this integration
spec demonstrates the derived-consumer boundary and does not transfer Account
ownership to `Repository/App`.
