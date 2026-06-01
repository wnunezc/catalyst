# Catalyst\Framework\View

## Class: View

File: `app/Framework/View/View.php`

Purpose: render Catalyst templates through the token pipeline, resolve layouts,
partials and components, and hydrate optional scope companions.

## Runtime Path Model

The runtime keeps one shared framework template root plus explicit per-module
view roots registered from module `routes.php` files.

Default shared root:

- `framework` -> `boot-core/template`

Typical module registration:

```php
View::getInstance()->addPath('auth', PD . DS . 'Repository' . DS . 'Framework' . DS . 'Auth' . DS . 'Views');
```

There is no implicit `project/Repository/Views` fallback.

## Live Directory Contract

Repository modules now use a structured `Views/` tree:

- `Views/pages/*.phtml`
- `Views/partials/*.phtml`
- `Views/components/*.phtml`
- `Views/scope/pages/*.php`
- `Views/scope/partials/*.php`
- `Views/scope/components/*.php`

Shared framework templates use:

- `boot-core/template/layouts/*.phtml`
- `boot-core/template/pages/*.phtml`
- `boot-core/template/components/*.phtml`
- `boot-core/template/errors/*.phtml`
- `boot-core/template/debug/*.phtml`
- `boot-core/template/scope/layouts/*.php`
- `boot-core/template/scope/pages/*.php`
- `boot-core/template/scope/components/*.php`
- `boot-core/template/scope/errors/*.php`
- `boot-core/template/scope/debug/*.php`

## Template Contract

Catalyst still resolves two file formats:

- `.phtml` -> canonical declarative token template
- `.php` -> legacy executable fallback when explicitly present

Search priority favors `.phtml` before `.php`.

For `.phtml`:

- inline `<?php` / `<?=` is invalid and rejected at render time
- output is rendered by `ViewTokenRenderer`
- optional scope preparation is resolved from `scope/.../*.php`

Examples:

- `Repository/Framework/Auth/Views/pages/login.phtml`
- `Repository/Framework/Auth/Views/scope/pages/login.php`
- `boot-core/template/layouts/admin.phtml`
- `boot-core/template/scope/layouts/admin.php`

Companions may return:

- `array`
- `callable` returning `array`
- `null`

Their responsibility is limited to presentation-scope preparation:

- CSRF fields
- derived labels
- structured collections for loops/cards/grids
- `TrustedHtml` values when raw output is explicitly intended

## Search Order

`findTemplate()` resolves templates in this order:

1. module paths registered via `addPath()`
2. shared framework fallback under `boot-core/template`

Namespaced module lookups default to `pages/`:

- `auth.login` -> `{auth path}/pages/login.phtml`
- `settings.index` -> `{settings path}/pages/index.phtml`

Explicit template subdirectories remain available:

- `devtools.partials._tf-header` -> `{devtools path}/partials/_tf-header.phtml`
- `framework.components._admin-datagrid` -> `boot-core/template/components/_admin-datagrid.phtml`
- `error.404` -> `boot-core/template/errors/404.phtml`

Relative partial references inside token templates stay valid through
`renderPartial()`:

- `{{> "../partials/_auth-social" }}`
- `{{> "./modal/_sample-content" }}`

## Layout Resolution

Layouts are resolved only from:

- `boot-core/template/layouts/{name}.phtml`

Example:

- `render('auth.login', $data, 200, 'base')`

## Companion Convention

Every token template may have one optional scope companion mirroring the same
relative path under a `scope/` subtree.

Examples:

- `boot-core/template/layouts/admin.phtml` -> `boot-core/template/scope/layouts/admin.php`
- `Repository/Framework/Settings/Views/pages/index.phtml` -> `Repository/Framework/Settings/Views/scope/pages/index.php`
- `Repository/Framework/DevTools/Views/partials/modal/_form-content.phtml` -> `Repository/Framework/DevTools/Views/scope/partials/modal/_form-content.php`

The old `_view/{basename}.view.php` convention is no longer part of the live
runtime contract.

## Public API

- `addPath(string $name, string $path): self`
- `share(string $key, mixed $value): self`
- `render(string $template, array $data = [], int $status = 200, ?string $layout = null): Response`
- `exists(string $template): bool`
- `renderPartial(string $reference, array $scope, ?string $fromTemplatePath = null): string`
- `renderTokenFragment(string $fragment, array $scope, ?string $templatePath = null): string`

## Summary

- module views are explicit and structured
- shared layouts/components/errors/debug stay under `boot-core/template/`
- `.phtml` is the canonical HTML surface format
- scope companions now live only under `scope/**/*.php`
- no `_view/*.view.php` lookup remains in the runtime
