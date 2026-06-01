# Entry Points

## Web: `public/index.php`
- Loads `boot-core/requirement-loader/error-catcher.php` when the server did not auto-prepend it.
- Blocks CLI execution through `IS_CLI`.
- Loads Composer autoload.
- Runs `Kernel::getInstance()->bootstrap()->run()`.

## CLI: `public/cli.php`
- Always ensures `error-catcher.php` is loaded first.
- Shares the same bootstrap constants and early config bootstrap as web mode.
- Dispatches the CLI kernel and built-in commands.
- Built-in operational commands now cover health, route inspection/cache, config inspection, key generation, storage cleanup, DevTools runtime disable, migrations and scaffold generation.

## Early Bootstrap Chain
1. `sys-constant.php`
2. `spl-autoload.php`
3. `env-constant.php`
4. Early `ConfigManager` bootstrap into `$GLOBALS['APP_CONFIGURATION']`
5. `ErrorCatcher.php`
6. Composer autoload
7. Web kernel or CLI kernel

## Notes
- `.env` remains the first source for environment resolution.
- JSON-backed runtime sections become available in the same bootstrap phase through `$GLOBALS['APP_CONFIGURATION']`, so consumers can read effective config consistently during the request lifecycle.
- App entry values are catalogued centrally in `app/Helpers/Config/AppEntryCatalog.php`.
- Canonical route style is lowercase for real web paths: `/setup`, `/home`, `/landing`, `/dashboard`, `/store`.
- Compatibility aliases may exist for historical/documented PascalCase entry names, but canonical redirects always normalize to lowercase paths.
- Public entry modules (`Home`, `Landing`, `Dashboard`, `Store`) should own their frontend assets through module-local `front/style.css` and `front/script.js`, published via `FrontResourceTrait` to `public/assets/*/work/{slug}/`.
## Runtime Entry Point Behavior

Framework Settings exposes two fields that intentionally control how `/` behaves:

- **Primary Entry Point**: the first destination resolved for the application root.
- **Secondary Entry Point**: the destination used when the primary entry point is
  `User-Access` and the visitor completes authentication.

The resolver is `Repository/App/Services/ApplicationEntryService.php`, backed by
`app/Helpers/Config/AppEntryCatalog.php`.

Important behavior:

- `Home`, `Landing`, `Store` and `Dashboard` are application/demo surfaces and
  render through the Public Shell.
- `User-Access` makes `/` require authentication before continuing to the
  configured secondary entry point.
- `Setup` remains a framework operations/setup destination and should not be
  linked from public demo navigation.
- Public/demo surfaces may be public or login-gated depending on the selected
  entry point, but they must not render the Admin Shell menu.

See also: `docs/ui/public-surface-contract.md`.

