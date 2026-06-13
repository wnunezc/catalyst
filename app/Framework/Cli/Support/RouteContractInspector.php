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

namespace Catalyst\Framework\Cli\Support;

use Catalyst\Framework\Cli\CliRouteLoader;
use Catalyst\Framework\Module\ModuleRegistry;
use Catalyst\Framework\Route\CanonicalPathRedirector;
use Catalyst\Framework\Route\Route;
use Catalyst\Framework\Route\Router;
use Catalyst\Helpers\Config\AppEntryCatalog;
use Catalyst\Helpers\Config\ConfigManager;

/**
 * Inspector for route contract metadata.
 *
 * Responsibility: Collects route, middleware and asset-contract metadata for CLI inspection commands.
 *
 * @package Catalyst\Framework\Cli\Support
 */
final class RouteContractInspector
{
    private const APP_SURFACE_NAMESPACE_PREFIX = 'App\\Surface\\';
    private const LEGACY_APP_NAMESPACE_PREFIX = 'App\\';
    private const REPOSITORY_NAMESPACE_PREFIX = 'Catalyst\\Repository\\';

    /**
     * Describes the inspect helper responsibility inside the CLI component.
     *
     * Responsibility: Supports the inspect helper workflow used by this CLI component.
     */
    public function inspect(): array
    {
        CliRouteLoader::loadAll();

        /** @var Route[] $routes */
        $routes = Router::getInstance()->getRoutes()->all();
        $issues = [];

        $checks = [
            'entries' => $this->inspectEntryTargets($routes, $issues),
            'aliases' => $this->inspectLegacyAliases($routes, $issues),
            'casing' => $this->inspectUnexpectedUppercaseRoutes($routes, $issues),
            'work_assets' => $this->inspectWorkAssets($issues),
            'module_html' => $this->inspectModuleHtmlOwnership($routes, $issues),
            'public_json' => $this->inspectPublicModuleJsonRoutes($routes, $issues),
        ];

        return [
            'ok' => $issues === [],
            'issue_count' => count($issues),
            'checks' => $checks,
            'issues' => $issues,
        ];
    }

    /**
     * Describes the inspect entry targets helper responsibility inside the CLI component.
     *
     * Responsibility: Supports the inspect entry targets helper workflow used by this CLI component.
     */
    private function inspectEntryTargets(array $routes, array &$issues): array
    {
        $environment = ConfigManager::getInstance()->getEnvironment();
        $paths = $this->routePatterns($routes);
        $checked = 0;

        foreach (AppEntryCatalog::primaryKeys($environment === 'development') as $entry) {
            if ($entry === AppEntryCatalog::USER_ACCESS) {
                continue;
            }

            $path = AppEntryCatalog::resolvePath($entry);
            if ($path === null) {
                continue;
            }

            $checked++;

            if (!isset($paths[$path])) {
                $issues[] = [
                    'type' => 'missing-entry-route',
                    'message' => sprintf('Entry point "%s" is missing its canonical route "%s".', $entry, $path),
                ];
            }
        }

        return [
            'ok' => $checked > 0 && !$this->hasIssueType($issues, 'missing-entry-route'),
            'checked' => $checked,
        ];
    }

    /**
     * Describes the inspect legacy aliases helper responsibility inside the CLI component.
     *
     * Responsibility: Supports the inspect legacy aliases helper workflow used by this CLI component.
     */
    private function inspectLegacyAliases(array $routes, array &$issues): array
    {
        $paths = $this->routePatterns($routes);
        $redirector = new CanonicalPathRedirector();
        $checked = 0;

        foreach ($redirector->legacyPrefixes() as $alias => $target) {
            $checked++;

            if (!isset($paths[$target])) {
                $issues[] = [
                    'type' => 'missing-legacy-target',
                    'message' => sprintf('Legacy redirect prefix "%s" points to missing canonical target "%s".', $alias, $target),
                ];
            }
        }

        return [
            'ok' => !$this->hasIssueType($issues, 'missing-legacy-target'),
            'checked' => $checked,
        ];
    }

    /**
     * Describes the inspect unexpected uppercase routes helper responsibility inside the CLI component.
     *
     * Responsibility: Supports the inspect unexpected uppercase routes helper workflow used by this CLI component.
     */
    private function inspectUnexpectedUppercaseRoutes(array $routes, array &$issues): array
    {
        $checked = 0;

        foreach ($routes as $route) {
            $pattern = $route->getPattern();
            $normalizedPattern = preg_replace('/\{[^}]+\}/', '{}', $pattern) ?? $pattern;

            if (!preg_match('/[A-Z]/', $normalizedPattern)) {
                continue;
            }

            $checked++;

            $issues[] = [
                'type' => 'unexpected-uppercase-route',
                'message' => sprintf('Route "%s" contains uppercase characters and should be normalized through canonical redirects.', $pattern),
            ];
        }

        return [
            'ok' => !$this->hasIssueType($issues, 'unexpected-uppercase-route'),
            'checked' => $checked,
        ];
    }

    /**
     * Describes the inspect work assets helper responsibility inside the CLI component.
     *
     * Responsibility: Supports the inspect work assets helper workflow used by this CLI component.
     */
    private function inspectWorkAssets(array &$issues): array
    {
        $checked = 0;

        foreach ($this->discoverModules() as $module) {
            if (!$module['has_views']) {
                continue;
            }

            $checked++;
            $moduleName = $module['name'];
            $slug = $module['slug'];

            foreach ([
                ['source' => $module['path'] . DS . 'front' . DS . 'style.css', 'published' => PD . DS . 'public' . DS . 'assets' . DS . 'css' . DS . 'work' . DS . $slug . DS . 'style.css'],
                ['source' => $module['path'] . DS . 'front' . DS . 'script.js', 'published' => PD . DS . 'public' . DS . 'assets' . DS . 'js' . DS . 'work' . DS . $slug . DS . 'script.js'],
            ] as $asset) {
                if (!is_file($asset['source'])) {
                    $issues[] = [
                        'type' => 'missing-front-asset',
                        'message' => sprintf('Module "%s" is missing source asset "%s".', $moduleName, $this->relativePath($asset['source'])),
                    ];
                    continue;
                }

                if (!is_file($asset['published'])) {
                    $issues[] = [
                        'type' => 'missing-published-asset',
                        'message' => sprintf('Module "%s" is missing published asset "%s".', $moduleName, $this->relativePath($asset['published'])),
                    ];
                }
            }
        }

        return [
            'ok' => !$this->hasIssueType($issues, 'missing-front-asset')
                && !$this->hasIssueType($issues, 'missing-published-asset'),
            'checked' => $checked,
        ];
    }

    /**
     * Describes the inspect module html ownership helper responsibility inside the CLI component.
     *
     * Responsibility: Supports the inspect module html ownership helper workflow used by this CLI component.
     */
    private function inspectModuleHtmlOwnership(array $routes, array &$issues): array
    {
        $checked = 0;
        $htmlOwners = [];

        foreach ($routes as $route) {
            if (!$this->isHtmlRoute($route)) {
                continue;
            }

            $owner = $this->resolveModuleOwner($route);
            if ($owner === null) {
                continue;
            }

            $htmlOwners[$owner['scope'] . ':' . $owner['module']] = true;
        }

        foreach ($this->discoverModules() as $module) {
            if (!($module['runtime_enabled'] ?? true)) {
                continue;
            }

            if (!$module['has_views']) {
                continue;
            }

            $checked++;
            $key = $module['scope'] . ':' . $module['name'];

            if (!isset($htmlOwners[$key])) {
                $issues[] = [
                    'type' => 'missing-module-html-route',
                    'message' => sprintf(
                        'Module "%s/%s" has views and work assets but no owned HTML route mapped from its controllers.',
                        $module['scope'],
                        $module['name']
                    ),
                ];
            }
        }

        return [
            'ok' => !$this->hasIssueType($issues, 'missing-module-html-route'),
            'checked' => $checked,
        ];
    }

    /**
     * Describes the inspect public module json routes helper responsibility inside the CLI component.
     *
     * Responsibility: Supports the inspect public module json routes helper workflow used by this CLI component.
     */
    private function inspectPublicModuleJsonRoutes(array $routes, array &$issues): array
    {
        $checked = 0;
        $paths = $this->routePatterns($routes);

        foreach ($this->discoverModules('App') as $module) {
            if (!($module['runtime_enabled'] ?? true)) {
                continue;
            }

            if (!$this->isPublicAppModule($module)) {
                continue;
            }

            $checked++;
            $moduleName = $module['name'];
            $slug = $module['slug'];
            $hasHtml = false;

            foreach (array_keys($paths) as $pattern) {
                if ($pattern === '/' . $slug || str_starts_with($pattern, '/' . $slug . '/')) {
                    $hasHtml = true;
                }

            }

            if (!$hasHtml) {
                $issues[] = [
                    'type' => 'missing-public-html-route',
                    'message' => sprintf('Public module "%s" has views but no canonical HTML route for slug "%s".', $moduleName, $slug),
                ];
            }

        }

        return [
            'ok' => !$this->hasIssueType($issues, 'missing-public-html-route'),
            'checked' => $checked,
        ];
    }

    /**
     * Describes the route patterns helper responsibility inside the CLI component.
     *
     * Responsibility: Supports the route patterns helper workflow used by this CLI component.
     */
    private function routePatterns(array $routes): array
    {
        $patterns = [];

        foreach ($routes as $route) {
            $patterns[$route->getPattern()] = true;
        }

        return $patterns;
    }

    /**
     * Describes the discover modules helper responsibility inside the CLI component.
     *
     * Responsibility: Supports the discover modules helper workflow used by this CLI component.
     */
    private function discoverModules(?string $scope = null): array
    {
        $modules = $scope === null
            ? ModuleRegistry::getInstance()->all()
            : ModuleRegistry::getInstance()->forScope($scope);

        return array_map(static function (array $module): array {
            return [
                'scope' => (string)($module['scope'] ?? ''),
                'name' => (string)($module['name'] ?? ''),
                'slug' => (string)($module['slug'] ?? ''),
                'path' => (string)($module['path'] ?? ''),
                'has_views' => (bool)($module['views']['has_views'] ?? false),
                'runtime_enabled' => (bool)($module['runtime_enabled'] ?? true),
                'route_guards' => array_values((array) ($module['route_guards'] ?? [])),
                'navigation_shell' => array_values((array) ($module['navigation']['shell'] ?? [])),
                'navigation_public' => array_values((array) ($module['navigation']['public'] ?? [])),
            ];
        }, $modules);
    }

    /**
     * Describes the is html route helper responsibility inside the CLI component.
     *
     * Responsibility: Supports the is html route helper workflow used by this CLI component.
     */
    private function isHtmlRoute(Route $route): bool
    {
        foreach ($route->getMethods() as $method) {
            if (in_array($method, ['GET', 'HEAD'], true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Describes the is public app module helper responsibility inside the CLI component.
     *
     * Responsibility: Supports the is public app module helper workflow used by this CLI component.
     */
    private function isPublicAppModule(array $module): bool
    {
        return ($module['scope'] ?? null) === 'App'
            && ($module['has_views'] ?? false)
            && (array) ($module['route_guards'] ?? []) === []
            && (array) ($module['navigation_public'] ?? []) !== [];
    }

    /**
     * Describes the resolve module owner helper responsibility inside the CLI component.
     *
     * Responsibility: Supports the resolve module owner helper workflow used by this CLI component.
     */
    private function resolveModuleOwner(Route $route): ?array
    {
        $handler = $route->getHandler();
        $controllerClass = null;

        if (is_array($handler) && isset($handler[0]) && is_string($handler[0])) {
            $controllerClass = $handler[0];
        } elseif (is_string($handler) && str_contains($handler, '@')) {
            [$controllerClass] = explode('@', $handler, 2);
        }

        if (!is_string($controllerClass) || $controllerClass === '') {
            return null;
        }

        foreach ([
            self::APP_SURFACE_NAMESPACE_PREFIX,
            self::LEGACY_APP_NAMESPACE_PREFIX,
            self::REPOSITORY_NAMESPACE_PREFIX,
        ] as $prefix) {
            if (!str_starts_with($controllerClass, $prefix)) {
                continue;
            }

            $relative = substr($controllerClass, strlen($prefix));
            $parts = explode('\\', $relative);
            $module = $parts[0] ?? '';

            if ($module === '') {
                return null;
            }

            $scope = $prefix === self::REPOSITORY_NAMESPACE_PREFIX ? 'Framework' : 'App';
            return [
                'scope' => $scope,
                'module' => $module,
            ];
        }

        return null;
    }

    /**
     * Describes the has issue type helper responsibility inside the CLI component.
     *
     * Responsibility: Supports the has issue type helper workflow used by this CLI component.
     */
    private function hasIssueType(array $issues, string $type): bool
    {
        foreach ($issues as $issue) {
            if (($issue['type'] ?? '') === $type) {
                return true;
            }
        }

        return false;
    }

    /**
     * Describes the relative path helper responsibility inside the CLI component.
     *
     * Responsibility: Supports the relative path helper workflow used by this CLI component.
     */
    private function relativePath(string $path): string
    {
        $prefix = rtrim(PD, '\\/') . DS;

        return str_starts_with($path, $prefix)
            ? substr($path, strlen($prefix))
            : $path;
    }
}
