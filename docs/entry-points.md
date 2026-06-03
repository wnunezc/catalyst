# Entry Points

## Purpose

Document the two supported process entry points for Catalyst: HTTP and CLI.

## Runtime Owners

| Concern | Owner |
|---|---|
| HTTP front controller | `public/index.php` |
| CLI front controller | `public/cli.php` |
| HTTP lifecycle | `Catalyst\Kernel` |
| CLI lifecycle | `Catalyst\Framework\Cli\CliKernel` |
| Command discovery | `Catalyst\Framework\Cli\CommandRegistry` |

## Current Behavior

`public/index.php` is the web front controller and delegates request handling to `Catalyst\Kernel`. `public/cli.php` is the command front controller and exposes framework commands such as `docs:inventory`, `docs:sync-runtime`, `route:list`, `inspect:lint`, `route:lint` and `quality:check`.

The CLI command catalog is discoverable with `php public/cli.php help`. Runtime documentation should prefer CLI outputs over manually maintained inventories when the two disagree.

## Operational Notes

Use PowerShell in this workspace. For documentation reconciliation, the minimum relevant entry-point checks are `php public/cli.php help`, `php public/cli.php docs:inventory --json`, `php public/cli.php docs:sync-runtime --stdout`, and `php public/cli.php route:list --json`.

## Related Documentation

- `docs/kernel.md`
- `docs/runtime-model.md`
- `docs/quality-gate.md`