# Demo UI Generated Snapshots

Decision: keep `Repository/Framework/DemoUi/generated/theme-previews` for now.

Reason:

- `/demo-ui` is the canonical UI demo route.
- The generated HTML files are runtime inputs for the demo surface, not disposable screenshots.
- Deletion is deferred until a curated DemoUi implementation replaces the generated HTML.
- Asset references must use canonical vendor paths under `/assets/vendor/inspinia/`.

Cleanup rule:

- Do not add new generated snapshots without documenting the source template.
- Do not point new generated snapshots to `/assets/img/inspinia/`.
- Revisit after the internal `DemoUi` module name is renamed to `DemoUi`.
