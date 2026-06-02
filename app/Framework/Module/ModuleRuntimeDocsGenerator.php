<?php

declare(strict_types=1);

/**
 * Catalyst PHP Framework
 *
 * A modern PHP 8.4 framework for building
 * robust and scalable web applications.
 *
 * PHP Version 8.4 (Required).
 *
 * @package    Catalyst
 *
 * @author     Walter Nuñez (arcanisgk/original founder)
 * @email      <wnunez@lh-2.net>
 * @email      <icarosnet@gmail.com>
 * @copyright  2024-2026 Walter Francisco Nuñez Cruz and Icaros Net
 * @license    Proprietary - https://catalyst.lh-2.net/license
 *
 * @version    GIT: See repository tags
 *
 * @category   Framework
 * @filesource
 *
 * @link       https://catalyst.lh-2.net Project homepage
 * @see        https://catalyst.lh-2.net/docs Documentation
 *
 */

namespace Catalyst\Framework\Module;

/**
 * Generates the runtime module catalog document.
 *
 * @package Catalyst\Framework\Module
 * Responsibility: Renders inspection, harness, and lint results into a Markdown module inventory.
 */
final class ModuleRuntimeDocsGenerator
{
    /**
     * Generates the complete runtime module catalog in Markdown.
     *
     * Responsibility: Generates the complete runtime module catalog in Markdown.
     */
    public function generate(): string
    {
        $inspection = (new ModuleInspector())->inspect();
        $harness = (new ModuleHarnessInspector())->inspect();
        $lint = (new ModuleLinter())->lint();
        $harnessMap = [];

        foreach ((array) ($harness['modules'] ?? []) as $module) {
            $harnessMap[(string) ($module['key'] ?? '')] = $module;
        }

        $lines = [
            '# Runtime Module Catalog',
            '',
            '> Auto-generated from `ModuleRegistry`, `PermissionRegistry`, `NavigationRegistry`, `ModuleInspector`, `ModuleHarnessInspector` and `ModuleLinter`.',
            '> Last generated: ' . date('Y-m-d H:i:s'),
            '',
            '## Runtime Summary',
            '',
            '- Modules: ' . (int) ($inspection['module_count'] ?? 0),
            '- Structural lint: ' . (($lint['ok'] ?? false) ? 'OK' : 'ISSUES (' . (int) ($lint['issue_count'] ?? 0) . ')'),
            '',
            '| Key | Surface | HTML | JSON | Mutations | Assets | Permissions | Settings | Seeds |',
            '|---|---|---:|---:|---:|---|---|---|---|',
        ];

        foreach ((array) ($inspection['modules'] ?? []) as $module) {
            $key = (string) ($module['key'] ?? '');
            $harnessModule = $harnessMap[$key] ?? [];
            $assetsOk = $this->assetStatusLabel((array) ($harnessModule['assets'] ?? []));

            $lines[] = sprintf(
                '| `%s` | `%s` | %d | %d | %d | `%s` | %s | %s | %s |',
                $key,
                (string) ($harnessModule['surface'] ?? 'unknown')
                    . (($harnessModule['runtime_enabled'] ?? true) ? '' : ' (disabled)'),
                (int) (($harnessModule['counts'] ?? [])['html'] ?? 0),
                (int) (($harnessModule['counts'] ?? [])['json'] ?? 0),
                (int) (($harnessModule['counts'] ?? [])['mutation'] ?? 0),
                $assetsOk,
                $this->inlineList(array_map(
                    static fn (array $permission): string => (string) ($permission['slug'] ?? ''),
                    (array) ($module['permissions'] ?? [])
                )),
                $this->inlineList((array) ($module['settings'] ?? [])),
                $this->inlineList((array) ($module['seeds'] ?? []))
            );
        }

        $lines[] = '';
        $lines[] = '## Module Detail';
        $lines[] = '';

        foreach ((array) ($inspection['modules'] ?? []) as $module) {
            $key = (string) ($module['key'] ?? '');
            $harnessModule = $harnessMap[$key] ?? [];
            $routes = (array) (($harnessModule['routes'] ?? [])['html'] ?? []);
            $jsonRoutes = (array) (($harnessModule['routes'] ?? [])['json'] ?? []);
            $mutationRoutes = (array) (($harnessModule['routes'] ?? [])['mutation'] ?? []);

            $lines[] = '### ' . $key;
            $lines[] = '';
            $lines[] = '- Scope: `' . (string) ($module['scope'] ?? '') . '`';
            $lines[] = '- Surface: `' . (string) ($harnessModule['surface'] ?? 'unknown') . '`';
            $lines[] = '- Runtime enabled: ' . (($harnessModule['runtime_enabled'] ?? true) ? '`yes`' : '`no`');
            $lines[] = '- Slug: `' . (string) ($module['slug'] ?? '') . '`';
            $lines[] = '- Description: ' . $this->plainValue((string) ($module['description'] ?? ''));
            $lines[] = '- Plugin: ' . $this->inlineScalar((string) (($module['plugin']['key'] ?? '') ?: 'standalone'));
            $lines[] = '- Views: ' . (($module['views']['has_views'] ?? false) ? '`yes`' : '`no`');
            $lines[] = '- Assets: `' . $this->assetStatusLabel((array) ($harnessModule['assets'] ?? [])) . '`';
            $lines[] = '- Settings: ' . $this->inlineList((array) ($module['settings'] ?? []));
            $lines[] = '- Permissions: ' . $this->inlineList(array_map(
                static fn (array $permission): string => (string) ($permission['slug'] ?? ''),
                (array) ($module['permissions'] ?? [])
            ));
            $lines[] = '- Seeds: ' . $this->inlineList((array) ($module['seeds'] ?? []));
            $lines[] = '- Feature flags: ' . $this->inlineList((array) ($module['feature_flags'] ?? []));
            $lines[] = '- Module flag key: ' . $this->inlineScalar((string) ($harnessModule['module_flag_key'] ?? ''));
            $lines[] = '- Representative HTML: ' . $this->inlineScalar($harnessModule['representative']['html'] ?? null);
            $lines[] = '- Representative JSON: ' . $this->inlineScalar($harnessModule['representative']['json'] ?? null);
            $lines[] = '';
            $lines[] = '#### HTML routes';
            $lines[] = '';
            $lines[] = $routes === []
                ? '_No HTML routes declared for harness._'
                : $this->renderRouteTable($routes);
            $lines[] = '';
            $lines[] = '#### JSON routes';
            $lines[] = '';
            $lines[] = $jsonRoutes === []
                ? '_No JSON routes declared for harness._'
                : $this->renderRouteTable($jsonRoutes);
            $lines[] = '';
            $lines[] = '#### Mutations';
            $lines[] = '';
            $lines[] = $mutationRoutes === []
                ? '_No mutation routes declared for harness._'
                : $this->renderRouteTable($mutationRoutes);
            $lines[] = '';
        }

        if (!(bool) ($lint['ok'] ?? false)) {
            $lines[] = '## Active Lint Issues';
            $lines[] = '';

            foreach ((array) ($lint['issues'] ?? []) as $issue) {
                $module = $issue['module'] ?? null;
                $prefix = $module !== null ? '[' . $module . '] ' : '';
                $lines[] = '- `' . (string) ($issue['type'] ?? 'issue') . '` ' . $prefix . (string) ($issue['message'] ?? '');
            }

            $lines[] = '';
        }

        return implode(PHP_EOL, $lines) . PHP_EOL;
    }

    /**
     * Renders a Markdown table for inspected routes.
     *
     * Responsibility: Renders a Markdown table for inspected routes.
     * @param array<int, array<string, mixed>> $routes
     */
    private function renderRouteTable(array $routes): string
    {
        $lines = [
            '| Pattern | Methods | Guest | User | Admin | State Profiles | Permissions | Middleware |',
            '|---|---|---|---|---|---|---|---|',
        ];

        foreach ($routes as $route) {
            $expectations = (array) ($route['expectations'] ?? []);
            $stateExpectations = (array) ($route['state_expectations'] ?? []);
            $lines[] = sprintf(
                '| `%s` | `%s` | `%s` | `%s` | `%s` | %s | %s | %s |',
                (string) ($route['pattern'] ?? ''),
                implode(',', (array) ($route['methods'] ?? [])),
                (string) ($expectations['guest'] ?? 'n/a'),
                (string) ($expectations['user'] ?? 'n/a'),
                (string) ($expectations['admin'] ?? 'n/a'),
                $this->inlineStateExpectations($stateExpectations),
                $this->inlineList((array) ($route['required_permissions'] ?? [])),
                $this->inlineList((array) ($route['middleware_classes'] ?? []))
            );
        }

        return implode(PHP_EOL, $lines);
    }

    /**
     * Formats route state expectations for inline Markdown output.
     *
     * Responsibility: Formats route state expectations for inline Markdown output.
     * @param array<string, string> $states
     */
    private function inlineStateExpectations(array $states): string
    {
        if ($states === []) {
            return '`n/a`';
        }

        ksort($states);

        $items = [];
        foreach ($states as $state => $expectation) {
            $items[] = $this->inlineScalar($state . '=' . $expectation);
        }

        return implode(', ', $items);
    }

    /**
     * Formats a list of scalar values for inline Markdown output.
     *
     * Responsibility: Formats a list of scalar values for inline Markdown output.
     * @param array<int, string> $items
     */
    private function inlineList(array $items): string
    {
        $items = array_values(array_filter(array_map(
            static fn (mixed $item): string => trim((string) $item),
            $items
        ), static fn (string $item): bool => $item !== ''));

        if ($items === []) {
            return '`n/a`';
        }

        return implode(', ', array_map(fn (string $item): string => $this->inlineScalar($item), $items));
    }

    /**
     * Formats a scalar value as inline Markdown or an unavailable marker.
     *
     * Responsibility: Formats a scalar value as inline Markdown or an unavailable marker.
     */
    private function inlineScalar(mixed $value): string
    {
        $text = trim((string) $value);

        if ($text === '') {
            return '`n/a`';
        }

        return '`' . str_replace('`', '\`', $text) . '`';
    }

    /**
     * Formats a plain text value or an unavailable marker.
     *
     * Responsibility: Formats a plain text value or an unavailable marker.
     */
    private function plainValue(string $value): string
    {
        $value = trim($value);

        return $value !== '' ? $value : '`n/a`';
    }

    /**
     * Returns the rendered asset status label for a harness module.
     *
     * Responsibility: Returns the rendered asset status label for a harness module.
     * @param array<string, mixed> $assets
     */
    private function assetStatusLabel(array $assets): string
    {
        if (!(bool) ($assets['expected'] ?? false)) {
            return 'n/a';
        }

        return (bool) ($assets['ok'] ?? false) ? 'ok' : 'issues';
    }
}
