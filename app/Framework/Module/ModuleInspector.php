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

use Catalyst\Framework\Middleware\DevToolsGuardMiddleware;
use Catalyst\Framework\Middleware\RoleMiddleware;
use Catalyst\Framework\Authorization\PermissionRegistry;
use Catalyst\Framework\Cli\CliRouteLoader;
use Catalyst\Framework\Cli\Support\RouteContractInspector;
use Catalyst\Framework\Middleware\RouteFeatureMiddleware;
use Catalyst\Framework\Navigation\NavigationRegistry;
use Catalyst\Framework\Route\Route;
use Catalyst\Framework\Route\Router;

/**
 * Inspects runtime module declarations and owned routes.
 *
 * @package Catalyst\Framework\Module
 * Responsibility: Produces module reports combining discovery, routing, permissions, navigation, and asset state.
 */
final class ModuleInspector
{
    /**
     * Builds the complete runtime module inspection report.
     *
     * Responsibility: Builds the complete runtime module inspection report.
     * @return array<string, mixed>
     */
    public function inspect(): array
    {
        $this->ensureRoutesLoaded();

        $modules = ModuleRegistry::getInstance()->all();
        $permissions = PermissionRegistry::getInstance();
        $navigation = NavigationRegistry::getInstance();
        $routes = Router::getInstance()->getRoutes()->all();

        $reportModules = [];

        foreach ($modules as $module) {
            $moduleKey = (string) ($module['key'] ?? '');
            $reportModules[] = [
                'key' => $moduleKey,
                'scope' => (string) ($module['scope'] ?? ''),
                'name' => (string) ($module['name'] ?? ''),
                'slug' => (string) ($module['slug'] ?? ''),
                'label' => (string) ($module['label'] ?? ''),
                'namespace' => (string) ($module['namespace'] ?? ''),
                'description' => (string) ($module['description'] ?? ''),
                'path' => (string) ($module['path'] ?? ''),
                'route_file' => $module['route_file'] ?? null,
                'manifest_file' => $module['manifest_file'] ?? null,
                'manifest_exists' => (bool) ($module['manifest_exists'] ?? false),
                'manifest_valid' => (bool) ($module['manifest_valid'] ?? true),
                'manifest_errors' => array_values((array) ($module['manifest_errors'] ?? [])),
                'plugin' => [
                    'key' => $module['plugin_key'] ?? null,
                    'label' => $module['plugin_label'] ?? null,
                    'required' => (bool) ($module['plugin_required'] ?? false),
                    'enabled' => (bool) ($module['plugin_enabled'] ?? true),
                ],
                'runtime' => [
                    'enabled' => (bool) ($module['runtime_enabled'] ?? true),
                    'module_flag_key' => (string) ($module['module_flag_key'] ?? ''),
                    'module_flag_enabled' => (bool) ($module['module_flag_enabled'] ?? true),
                ],
                'views' => [
                    'namespace' => (string) ($module['views']['namespace'] ?? ''),
                    'path' => (string) ($module['views']['path'] ?? ''),
                    'has_views' => (bool) ($module['views']['has_views'] ?? false),
                ],
                'assets' => [
                    'slug' => (string) ($module['assets']['slug'] ?? ''),
                    'source' => [
                        'style' => (string) ($module['assets']['source']['style'] ?? ''),
                        'script' => (string) ($module['assets']['source']['script'] ?? ''),
                        'style_exists' => is_file((string) ($module['assets']['source']['style'] ?? '')),
                        'script_exists' => is_file((string) ($module['assets']['source']['script'] ?? '')),
                    ],
                    'published' => [
                        'style' => (string) ($module['assets']['published']['style'] ?? ''),
                        'script' => (string) ($module['assets']['published']['script'] ?? ''),
                        'style_exists' => is_file((string) ($module['assets']['published']['style'] ?? '')),
                        'script_exists' => is_file((string) ($module['assets']['published']['script'] ?? '')),
                    ],
                ],
                'routes' => [
                    'declared' => [
                        'web' => array_values((array) ($module['routes']['web'] ?? [])),
                        'api' => array_values((array) ($module['routes']['api'] ?? [])),
                        'aliases' => array_values((array) ($module['routes']['aliases'] ?? [])),
                        'prefixes' => array_values((array) ($module['routes']['prefixes'] ?? [])),
                    ],
                    'owned' => $this->ownedRoutesForModule($routes, $module),
                ],
                'settings' => array_values((array) ($module['settings'] ?? [])),
                'permissions' => $permissions->forModule($moduleKey),
                'permission_migrations' => array_values((array) ($module['permission_migrations'] ?? [])),
                'health_checks' => array_values((array) ($module['health_checks'] ?? [])),
                'feature_flags' => array_values((array) ($module['feature_flags'] ?? [])),
                'route_guards' => array_values((array) ($module['route_guards'] ?? [])),
                'navigation' => $navigation->definitionsForModule($moduleKey),
            ];
        }

        usort(
            $reportModules,
            static fn (array $left, array $right): int =>
                [$left['scope'], $left['name']] <=> [$right['scope'], $right['name']]
        );

        return [
            'module_count' => count($reportModules),
            'modules' => $reportModules,
            'route_contract' => (new RouteContractInspector())->inspect(),
        ];
    }

    /**
     * Finds one inspected module by key, slug, name, or legacy alias.
     *
     * Responsibility: Finds one inspected module by key, slug, name, or legacy alias.
     * @return array<string, mixed>|null
     */
    public function inspectModule(string $identifier): ?array
    {
        $identifier = trim($identifier);

        if ($identifier === '') {
            return null;
        }

        foreach ((array) ($this->inspect()['modules'] ?? []) as $module) {
            if ($this->matchesIdentifier($module, $identifier)) {
                return $module;
            }
        }

        return null;
    }

    /**
     * Ensures CLI route definitions are loaded before inspection.
     *
     * Responsibility: Ensures CLI route definitions are loaded before inspection.
     */
    private function ensureRoutesLoaded(): void
    {
        if (class_exists(CliRouteLoader::class)) {
            CliRouteLoader::loadAll();
        }
    }

    /**
     * Extracts routes whose handlers belong to the given module.
     *
     * Responsibility: Extracts routes whose handlers belong to the given module.
     * @param Route[] $routes
     * @param array<string, mixed> $module
     * @return array<int, array<string, mixed>>
     */
    private function ownedRoutesForModule(array $routes, array $module): array
    {
        $owned = [];
        $scope = (string) ($module['scope'] ?? '');
        $name = (string) ($module['name'] ?? '');

        foreach ($routes as $route) {
            if (!$route instanceof Route) {
                continue;
            }

            $owner = $this->resolveRouteOwner($route);
            if ($owner === null || $owner['scope'] !== $scope || $owner['module'] !== $name) {
                continue;
            }

            $owned[] = [
                'pattern' => $route->getPattern(),
                'methods' => $route->getMethods(),
                'name' => $route->getName(),
                'handler' => $this->stringifyHandler($route->getHandler(), $route->getNamespace()),
                'middleware' => $this->stringifyMiddleware($route),
                'middleware_classes' => $this->middlewareClasses($route),
                'feature_flags' => $this->routeFeatureFlags($route),
                'required_roles' => $this->requiredRoles($route),
                'required_permissions' => $this->requiredPermissions($route),
            ];
        }

        usort(
            $owned,
            static fn (array $left, array $right): int =>
                [$left['pattern'], implode(',', $left['methods'])] <=> [$right['pattern'], implode(',', $right['methods'])]
        );

        return $owned;
    }

    /**
     * Resolves repository scope and module name from a route handler.
     *
     * Responsibility: Resolves repository scope and module name from a route handler.
     * @return array{scope: string, module: string}|null
     */
    private function resolveRouteOwner(Route $route): ?array
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

        if (str_starts_with($controllerClass, 'Catalyst\\Repository\\')) {
            $relative = substr($controllerClass, strlen('Catalyst\\Repository\\'));
            $parts = explode('\\', $relative);

            return [
                'scope' => 'Framework',
                'module' => $parts[0] ?? '',
            ];
        }

        if (str_starts_with($controllerClass, 'App\\Surface\\')) {
            $relative = substr($controllerClass, strlen('App\\Surface\\'));
            $parts = explode('\\', $relative);

            return [
                'scope' => 'App',
                'module' => $parts[0] ?? '',
            ];
        }

        if (str_starts_with($controllerClass, 'App\\')) {
            $relative = substr($controllerClass, strlen('App\\'));
            $parts = explode('\\', $relative);

            return [
                'scope' => 'App',
                'module' => $parts[0] ?? '',
            ];
        }

        return null;
    }

    /**
     * Converts route middleware definitions into class or type names.
     *
     * Responsibility: Converts route middleware definitions into class or type names.
     * @return string[]
     */
    private function stringifyMiddleware(Route $route): array
    {
        return array_map(
            static function (mixed $item): string {
                if (is_string($item)) {
                    return $item;
                }

                if (is_object($item)) {
                    return $item::class;
                }

                return gettype($item);
            },
            $route->getMiddleware()
        );
    }

    /**
     * Returns unique middleware classes attached to a route.
     *
     * Responsibility: Returns unique middleware classes attached to a route.
     * @return string[]
     */
    private function middlewareClasses(Route $route): array
    {
        return array_values(array_unique($this->stringifyMiddleware($route)));
    }

    /**
     * Returns feature flags attached through route middleware.
     *
     * Responsibility: Returns feature flags attached through route middleware.
     * @return string[]
     */
    private function routeFeatureFlags(Route $route): array
    {
        $flags = [];

        foreach ($route->getMiddleware() as $middleware) {
            if ($middleware instanceof RouteFeatureMiddleware) {
                $flags[] = $middleware->flagKey();
            }
        }

        return array_values(array_unique(array_filter($flags, 'is_string')));
    }

    /**
     * Returns role requirements attached through route middleware.
     *
     * Responsibility: Returns role requirements attached through route middleware.
     * @return string[]
     */
    private function requiredRoles(Route $route): array
    {
        $roles = [];

        foreach ($route->getMiddleware() as $middleware) {
            if ($middleware instanceof RoleMiddleware) {
                $roles = array_merge($roles, $middleware->getRequiredRoles());
            }
        }

        return array_values(array_unique(array_filter($roles, 'is_string')));
    }

    /**
     * Returns permission requirements attached through route middleware.
     *
     * Responsibility: Returns permission requirements attached through route middleware.
     * @return string[]
     */
    private function requiredPermissions(Route $route): array
    {
        $permissions = [];

        foreach ($route->getMiddleware() as $middleware) {
            if ($middleware instanceof RoleMiddleware) {
                $permissions = array_merge($permissions, $middleware->getRequiredPermissions());
                continue;
            }

            if ($middleware instanceof DevToolsGuardMiddleware) {
                $permissions = array_merge($permissions, $middleware->getRequiredPermissions());
            }
        }

        return array_values(array_unique(array_filter($permissions, 'is_string')));
    }

    /**
     * Converts a route handler definition into a readable identifier.
     *
     * Responsibility: Converts a route handler definition into a readable identifier.
     */
    private function stringifyHandler(mixed $handler, ?string $namespace): string
    {
        if (is_string($handler)) {
            if ($namespace !== null && str_contains($handler, '@') && !str_contains($handler, '\\')) {
                return $namespace . '\\' . $handler;
            }

            return $handler;
        }

        if (is_array($handler) && count($handler) === 2) {
            $class = is_object($handler[0]) ? $handler[0]::class : (string) $handler[0];

            return $class . '@' . (string) $handler[1];
        }

        if ($handler instanceof \Closure) {
            return 'Closure';
        }

        if (is_object($handler)) {
            return $handler::class;
        }

        return gettype($handler);
    }

    /**
     * Determines whether a module matches an inspection identifier.
     *
     * Responsibility: Determines whether a module matches an inspection identifier.
     * @param array<string, mixed> $module
     */
    private function matchesIdentifier(array $module, string $identifier): bool
    {
        $identifier = strtolower($identifier);
        $key = strtolower((string) ($module['key'] ?? ''));
        $legacyAlias = $this->legacyAppIdentifierForModule($key);

        return in_array($identifier, array_filter([
            $key,
            $legacyAlias,
            strtolower((string) ($module['slug'] ?? '')),
            strtolower((string) ($module['name'] ?? '')),
        ], static fn (mixed $value): bool => is_string($value) && $value !== ''), true);
    }

    /**
     * Converts a surface module key into its legacy application alias.
     *
     * Responsibility: Converts a surface module key into its legacy application alias.
     */
    private function legacyAppIdentifierForModule(string $moduleKey): ?string
    {
        if (!str_starts_with($moduleKey, 'app.surface.')) {
            return null;
        }

        return 'app.' . substr($moduleKey, strlen('app.surface.'));
    }
}
