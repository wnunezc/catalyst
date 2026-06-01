# Quality Gate

## Purpose

The local quality gate is the standard pre-commit/pre-push check for Catalyst.
Run it after framework, routing, security, config or frontend asset changes.

```powershell
php public/cli.php quality:check
```

## Included Checks

`quality:check` runs these checks in order:

```powershell
composer validate --strict
composer audit
php public/cli.php route:lint
php public/cli.php inspect:lint
php public/cli.php security:check
php public/cli.php status
```

## Blockers

These checks block commit/push when they fail:

- `composer validate --strict`
- `composer audit`
- `php public/cli.php route:lint`
- `php public/cli.php inspect:lint`
- `php public/cli.php security:check`

## Local Warnings

`php public/cli.php status` is warning-only inside the quality gate. In local
WSDD host execution, queue and scheduler may warn when Docker-only DNS names
such as `WSDD-MySql-Server` are not resolvable from the host shell.

Warnings are acceptable only when `status` still reports `Overall: Ready` and
the warning is known to be environment-bound rather than a code regression.

## Manual Equivalent

Use the manual sequence when debugging one failing step:

```powershell
composer validate --strict
composer audit
php public/cli.php route:lint
php public/cli.php inspect:lint
php public/cli.php security:check
php public/cli.php status
```
