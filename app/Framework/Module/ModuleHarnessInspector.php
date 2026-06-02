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

use Catalyst\Framework\Middleware\ApiTokenMiddleware;
use Catalyst\Framework\Middleware\AuthMiddleware;
use Catalyst\Framework\Middleware\DevToolsGuardMiddleware;
use Catalyst\Framework\Middleware\GuestMiddleware;
use Catalyst\Framework\Middleware\RoleMiddleware;
use Catalyst\Framework\Middleware\SetupGuardMiddleware;
use Catalyst\Helpers\Config\ConfigManager;

/**
 * Defines the Module Harness Inspector class contract.
 *
 * @package Catalyst\Framework\Module
 * Responsibility: Coordinates the module harness inspector behavior within its module boundary.
 */
final class ModuleHarnessInspector
{
    /**
     * @return array<string, mixed>
     */
    public function inspect(): array
    {
        $inspection = (new ModuleInspector())->inspect();
        $modules = [];

        foreach ((array) ($inspection['modules'] ?? []) as $module) {
            $htmlRoutes = $this->collectRoutes($module, 'html');
            $jsonRoutes = $this->collectRoutes($module, 'json');
            $mutationRoutes = $this->collectRoutes($module, 'mutation');
            $surface = $this->resolveSurface($module, $htmlRoutes, $jsonRoutes);
            $adminNavigation = array_values((array) (($module['navigation'] ?? [])['admin'] ?? []));
            $publicNavigation = array_values((array) (($module['navigation'] ?? [])['public'] ?? []));
            $breadcrumbs = array_values((array) (($module['navigation'] ?? [])['breadcrumbs'] ?? []));
            $assets = (array) ($module['assets'] ?? []);
            $published = (array) ($assets['published'] ?? []);
            $permissions = array_values(array_map(
                static fn (array $permission): string => (string) ($permission['slug'] ?? ''),
                (array) ($module['permissions'] ?? [])
            ));

            $modules[] = [
                'key' => (string) ($module['key'] ?? ''),
                'scope' => (string) ($module['scope'] ?? ''),
                'slug' => (string) ($module['slug'] ?? ''),
                'label' => (string) ($module['label'] ?? ''),
                'runtime_enabled' => (bool) (($module['runtime'] ?? [])['enabled'] ?? ($module['runtime_enabled'] ?? true)),
                'plugin' => (array) ($module['plugin'] ?? []),
                'module_flag_key' => (string) (($module['runtime'] ?? [])['module_flag_key'] ?? ($module['module_flag_key'] ?? '')),
                'module_flag_enabled' => (bool) (($module['runtime'] ?? [])['module_flag_enabled'] ?? ($module['module_flag_enabled'] ?? true)),
                'surface' => $surface,
                'description' => (string) ($module['description'] ?? ''),
                'has_views' => (bool) (($module['views'] ?? [])['has_views'] ?? false),
                'settings' => array_values((array) ($module['settings'] ?? [])),
                'permissions' => $permissions,
                'seeds' => array_values((array) ($module['seeds'] ?? [])),
                'feature_flags' => array_values((array) ($module['feature_flags'] ?? [])),
                'assets' => [
                    'slug' => (string) ($assets['slug'] ?? ''),
                    'expected' => (bool) (($module['views'] ?? [])['has_views'] ?? false),
                    'ok' => ($published['style_exists'] ?? false) && ($published['script_exists'] ?? false),
                    'published_style' => (string) ($published['style'] ?? ''),
                    'published_script' => (string) ($published['script'] ?? ''),
                ],
                'navigation' => [
                    'admin_count' => count($adminNavigation),
                    'public_count' => count($publicNavigation),
                    'breadcrumbs_count' => count($breadcrumbs),
                    'admin_contexts' => array_values(array_unique(array_filter(array_map(
                        static fn (array $item): string => (string) ($item['context'] ?? ''),
                        $adminNavigation
                    )))),
                    'primary_admin_href' => $adminNavigation[0]['href'] ?? null,
                    'primary_public_href' => $publicNavigation[0]['href'] ?? null,
                ],
                'routes' => [
                    'html' => $htmlRoutes,
                    'json' => $jsonRoutes,
                    'mutation' => $mutationRoutes,
                ],
                'representative' => [
                    'html' => $this->resolveRepresentativeRoute($module, $htmlRoutes, 'html'),
                    'json' => $this->resolveRepresentativeRoute($module, $jsonRoutes, 'json'),
                ],
                'counts' => [
                    'html' => count($htmlRoutes),
                    'json' => count($jsonRoutes),
                    'mutation' => count($mutationRoutes),
                ],
            ];
        }

        usort(
            $modules,
            static fn (array $left, array $right): int =>
                [$left['scope'], $left['key']] <=> [$right['scope'], $right['key']]
        );

        return [
            'module_count' => count($modules),
            'modules' => $modules,
        ];
    }

    /**
     * @param array<string, mixed> $module
     * @param array<int, array<string, mixed>> $routes
     * @param 'html'|'json' $kind
     */
    private function resolveRepresentativeRoute(array $module, array $routes, string $kind): ?string
    {
        if ($routes === []) {
            return null;
        }

        $declared = (array) (($module['routes'] ?? [])['declared'] ?? []);
        $preferred = array_values((array) ($kind === 'html' ? ($declared['web'] ?? []) : ($declared['api'] ?? [])));

        foreach ($preferred as $pattern) {
            if ($pattern === '/' && count($routes) > 1) {
                continue;
            }

            if (str_contains((string) $pattern, '{')) {
                continue;
            }

            foreach ($routes as $route) {
                if (($route['pattern'] ?? null) === $pattern) {
                    return (string) $pattern;
                }
            }
        }

        return (string) ($routes[0]['pattern'] ?? null);
    }

    /**
     * @param array<string, mixed> $module
     * @param 'html'|'json'|'mutation' $kind
     * @return array<int, array<string, mixed>>
     */
    private function collectRoutes(array $module, string $kind): array
    {
        $routes = [];
        $ownedRoutes = array_values((array) (($module['routes'] ?? [])['owned'] ?? []));
        $declared = (array) (($module['routes'] ?? [])['declared'] ?? []);
        $declaredWeb = array_values((array) ($declared['web'] ?? []));
        $declaredApi = array_values((array) ($declared['api'] ?? []));
        $declaredAliases = array_values((array) ($declared['aliases'] ?? []));

        foreach ($ownedRoutes as $route) {
            $methods = array_values((array) ($route['methods'] ?? []));
            $pattern = (string) ($route['pattern'] ?? '');
            $isJson = str_starts_with($pattern, '/api/')
                || in_array($pattern, $declaredApi, true);
            $isMutation = $this->hasMutationMethod($methods);
            $isReadable = $this->hasReadMethod($methods);

            if ($kind === 'html' && (!$isReadable || $isJson)) {
                continue;
            }

            if ($kind === 'json' && (!$isReadable || !$isJson)) {
                continue;
            }

            if ($kind === 'mutation' && !$isMutation) {
                continue;
            }

            $expectationSet = $this->routeExpectations($module, $route, $kind !== 'html');
            $routes[] = [
                'pattern' => $pattern,
                'methods' => $methods,
                'kind' => $isJson ? 'json' : (in_array($pattern, $declaredAliases, true) ? 'alias' : 'canonical'),
                'declared' => in_array($pattern, $declaredWeb, true)
                    || in_array($pattern, $declaredApi, true)
                    || in_array($pattern, $declaredAliases, true),
                'required_roles' => array_values((array) ($route['required_roles'] ?? [])),
                'required_permissions' => array_values((array) ($route['required_permissions'] ?? [])),
                'middleware_classes' => array_values((array) ($route['middleware_classes'] ?? [])),
                'expectations' => (array) ($expectationSet['defaults'] ?? []),
                'state_expectations' => (array) ($expectationSet['states'] ?? []),
            ];
        }

        usort(
            $routes,
            static fn (array $left, array $right): int =>
                [$left['pattern'], implode(',', (array) $left['methods'])]
                <=> [$right['pattern'], implode(',', (array) $right['methods'])]
        );

        return $routes;
    }

    /**
     * @param array<string, mixed> $module
     * @param array<int, array<string, mixed>> $htmlRoutes
     * @param array<int, array<string, mixed>> $jsonRoutes
     */
    private function resolveSurface(array $module, array $htmlRoutes, array $jsonRoutes): string
    {
        $moduleKey = (string) ($module['key'] ?? '');
        $adminNavigation = array_values((array) (($module['navigation'] ?? [])['admin'] ?? []));
        $publicNavigation = array_values((array) (($module['navigation'] ?? [])['public'] ?? []));
        $contexts = array_values(array_unique(array_filter(array_map(
            static fn (array $item): string => (string) ($item['context'] ?? ''),
            $adminNavigation
        ))));
        $permissionLists = array_map(
            static fn (array $route): array => array_values((array) ($route['required_permissions'] ?? [])),
            array_merge($htmlRoutes, $jsonRoutes)
        );
        $permissions = $permissionLists === []
            ? []
            : array_values(array_unique(array_merge(...$permissionLists)));

        if ($moduleKey === 'framework.settings' || in_array('workspace', $contexts, true)) {
            return 'workspace';
        }

        if ($moduleKey === 'framework.auth') {
            return 'auth-flow';
        }

        if ($moduleKey === 'framework.devtools' || in_array('devtools', $contexts, true) || in_array('access-devtools', $permissions, true)) {
            return 'devtools';
        }

        if ($moduleKey === 'framework.roles' || in_array('administration', $contexts, true)) {
            return 'administration';
        }

        if ($publicNavigation !== [] && $adminNavigation === []) {
            return 'public';
        }

        if ($htmlRoutes === [] && $jsonRoutes !== []) {
            return 'authenticated-api';
        }

        return 'authenticated';
    }

    /**
     * @param array<string, mixed> $module
     * @param array<string, mixed> $route
     * @return array{defaults: array<string, string>, states: array<string, string>}
     */
    private function routeExpectations(array $module, array $route, bool $json): array
    {
        $middleware = array_values((array) ($route['middleware_classes'] ?? []));
        $usesApiToken = in_array(ApiTokenMiddleware::class, $middleware, true);
        $guestOnly = in_array(GuestMiddleware::class, $middleware, true);
        $usesSetupGuard = in_array(SetupGuardMiddleware::class, $middleware, true);
        $usesDevToolsGuard = in_array(DevToolsGuardMiddleware::class, $middleware, true);
        $usesRoleGuard = in_array(RoleMiddleware::class, $middleware, true)
            || (array) ($route['required_permissions'] ?? []) !== []
            || (array) ($route['required_roles'] ?? []) !== [];
        $requiresPrivilege = $usesRoleGuard || $usesDevToolsGuard || $usesSetupGuard;
        $requiresAuth = $requiresPrivilege
            || in_array(AuthMiddleware::class, $middleware, true)
            || $usesApiToken
            || $usesSetupGuard;

        if ($json && $usesApiToken) {
            $defaults = [
                'guest' => '401',
                'user' => '401',
                'admin' => '401',
            ];
        } else {
            $defaults = $json
                ? [
                    'guest' => $requiresAuth ? '401' : '200',
                    'user' => $guestOnly ? '409' : ($requiresPrivilege ? '403' : '200'),
                    'admin' => $guestOnly ? '409' : '200',
                ]
                : [
                'guest' => $requiresAuth ? 'login' : '200',
                'user' => $guestOnly ? 'root' : (($usesDevToolsGuard || $usesSetupGuard) ? '403' : ($usesRoleGuard ? 'root' : '200')),
                'admin' => $guestOnly ? 'root' : '200',
                ];
        }

        $states = [];
        $moduleKey = (string) ($module['key'] ?? '');
        $pattern = (string) ($route['pattern'] ?? '');

        if ($json && $usesApiToken) {
            $states['api_token'] = '200';
        }

        if ($moduleKey === 'framework.auth' && !$json) {
            $mfaEnabled = $this->isMfaGloballyEnabled();

            if ($pattern === '/mfa/setup') {
                if ($mfaEnabled) {
                    $defaults = [
                        'guest' => 'login',
                        'user' => '200',
                        'admin' => '200',
                    ];
                    $states['pending_setup'] = '200';
                } else {
                    $defaults = [
                        'guest' => 'root',
                        'user' => 'root',
                        'admin' => 'root',
                    ];
                }
            }

            if ($pattern === '/mfa/challenge') {
                $defaults = [
                    'guest' => 'login',
                    'user' => 'root',
                    'admin' => 'root',
                ];
                $states['pending_mfa'] = '200';
            }
        }

        if ($moduleKey === 'framework.devtools') {
            if ($pattern === '/test-features/json-error') {
                $defaults['admin'] = '400';
            }

            if ($pattern === '/test-features/validation-error') {
                $defaults['admin'] = '422';
            }

            if ($pattern === '/test-features/api/toaster-error') {
                $defaults['admin'] = '400';
            }
        }

        return [
            'defaults' => $defaults,
            'states' => $states,
        ];
    }

    /**
     * Determines whether is Mfa Globally Enabled.
     */
    private function isMfaGloballyEnabled(): bool
    {
        try {
            return (bool) ConfigManager::getInstance()->get('security.security.mfa_enabled', false);
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * @param string[] $methods
     */
    private function hasReadMethod(array $methods): bool
    {
        foreach ($methods as $method) {
            if (in_array($method, ['GET', 'HEAD'], true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string[] $methods
     */
    private function hasMutationMethod(array $methods): bool
    {
        foreach ($methods as $method) {
            if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
                return true;
            }
        }

        return false;
    }
}
