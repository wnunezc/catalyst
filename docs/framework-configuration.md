# Catalyst Framework Configuration

## Ownership

`Repository/Framework/Configuration` is the only physical owner of the 29
active `/configuration/*` routes. Its PHP namespace is
`Catalyst\Repository\Configuration` and protected privileged uses
`manage-platform-configuration` with the existing privileged role fallback.

Configuration owns Environment Setup, Application Health, Platform Appearance,
Feature Flags and Plugins. There are no active Settings or Operations route
owners, aliases, proxies or legacy namespaces for these routes.

Global managers remain framework infrastructure when they have consumers
outside Configuration:

- `PlatformAppearanceManager` serves the shared document, shell and PDF runtime.
- `FeatureFlagManager` and `FeatureFlagOverrideRepository` serve runtime and CLI
  consumers.
- `PluginRegistry` and `PluginManager` serve discovery, CLI and runtime
  consumers.

Internal classes named `Settings*` are active setup presentation helpers. Their
name does not create a Settings module or route owner.

## Routes And Access

| Surface | Route family | Access |
|---|---|---|
| Environment Setup | `/configuration/environment-setup*` | Public during first run; authenticated privileged role after configuration |
| Application Health panel | `/configuration/application-health` | Authenticated `manage-platform-configuration` |
| Public health probes | `/configuration/application-health/live`, `/configuration/application-health/ready` | Public minimal JSON |
| Platform Appearance | `/configuration/platform-appearance*` | Authenticated `manage-platform-configuration` |
| Feature Flags | `/configuration/feature-flags*` | Authenticated `manage-platform-configuration` |
| Plugins | `/configuration/plugins*` | Authenticated `manage-platform-configuration` |

The public liveness/readiness probes expose only `ok` and `status`, preserving
HTTP `200`/`503` semantics. Detailed runtime, route and readiness diagnostics
remain in the protected Application Health panel.

## Navigation Models

Catalyst has one document, one shell, one frontend runtime and one recursive
sidebar renderer: `boot-core/template/_sidebar.phtml` delegates recursive nodes
to `_sidebar-node.phtml`.

`NavigationModelSelector` selects exactly three virtual models:

- `demo-ui`: recursive catalog navigation supplied by
  `DemoUiNavigationProvider`; it does not alter Demo UI previews or components.
- `framework`: authorized Framework taxonomy composed by
  `FrameworkNavigationProvider`.
- `application`: Framework account capabilities plus registered App module
  contributions, composed by `ApplicationNavigationProvider`.

These virtual models are not profiles, layouts, shells, themes or alternate
runtimes. `NavigationTreeNormalizer` accepts arbitrary depth; trees shown in
documentation are examples, never depth limits.

The final navigation models contain no `Disconnected` ownership debt. Every
canonical destination is projected from its current framework or application
owner.

## Verification

```powershell
php public/cli.php inspect:lint
php public/cli.php route:lint
php public/cli.php route:bootstrap-regression
php public/cli.php configuration:requests-regression
php public/cli.php configuration:localization-smoke
php public/cli.php configuration:feature-flags-smoke
php public/cli.php shell-navigation:smoke --json
```
