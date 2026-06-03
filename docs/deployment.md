# Deployment Guide

## Purpose

Document current deployment and packaging boundaries for Catalyst without preserving historical incident notes as product documentation.

## Runtime Owners

| Concern | Owner |
|---|---|
| Deployment pipeline | `Catalyst\Framework\Deployment\DeploymentManager` |
| Deployment runs | `Catalyst\Framework\Deployment\DeploymentRunRepository` |
| Deployment CLI | `deploy:list`, `deploy:run` |
| Secret configuration | `Catalyst\Helpers\Config\ConfigSecretStore` |

## Current Behavior

Deployment must not package local secrets, DKIM private keys, runtime storage, logs, throttle state, PID/stamp files or DevTools uploads. CLI deployment support is available through `deploy:list` and `deploy:run`; environment readiness is validated through the quality gate and status checks.

Catalyst is distributed as a project base. The project root may be installed as
the target site root, but the effective web root is `public/`. Production hosts
should point the web server document root directly to `public/` when possible.
The root `.htaccess` exists only as an Apache fallback for local/shared hosts
that serve the project root directly.

## Operational Notes

Before deployment, run:

```powershell
php public/cli.php quality:check
php public/cli.php route:list --json
php public/cli.php inspect:lint
php public/cli.php route:lint
```

If secrets or runtime artifacts are exposed, rotate affected values. Do not store incident narratives or old audit notes in this guide.

Do not treat maintainer-local URLs such as `https://catalyst.dock/` as
deployment defaults. Project URL, database, mail, DKIM, OAuth and storage values
belong to the target environment and must be configured by the developer or
operator.

## Related Documentation

- `docs/quality-gate.md`
- `docs/workflow/reusable-base-install.md`
- `docs/security-conventions.md`
