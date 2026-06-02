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

use Catalyst\Framework\Cli\CliRouteLoader;
use Catalyst\Framework\Plugin\PluginRegistry;
use Catalyst\Framework\Route\Route;
use Catalyst\Framework\Route\Router;
use Catalyst\Framework\Authorization\PermissionRegistry;
use Catalyst\Framework\Navigation\NavigationRegistry;

/**
 * Validates structural contracts across runtime modules.
 *
 * @package Catalyst\Framework\Module
 * Responsibility: Detects manifest, routing, asset, permission, navigation, and plugin inconsistencies.
 */
final class ModuleLinter
{
    /**
     * Runs every module consistency check and returns the aggregated report.
     *
     * Responsibility: Runs every module consistency check and returns the aggregated report.
     * @return array<string, mixed>
     */
    public function lint(): array
    {
        $this->ensureRoutesLoaded();

        $inspector = new ModuleInspector();
        $inspection = $inspector->inspect();
        $routeContract = (array) ($inspection['route_contract'] ?? []);
        $modules = (array) ($inspection['modules'] ?? []);
        $issues = [];

        $checks = [
            'module_registration' => $this->lintModuleRegistration($modules, $issues),
            'plugin_manifests' => $this->lintPluginManifests($modules, $issues),
            'registry_route_drift' => $this->lintRegistryRouteDrift($modules, $issues),
            'assets_contract' => $this->lintAssetsContract($modules, $issues),
            'slug_coherence' => $this->lintSlugCoherence($modules, $issues),
            'route_guards' => $this->lintRouteGuards($modules, $issues),
            'permission_bridge' => $this->lintPermissionBridge($modules, $issues),
            'navigation_registry' => $this->lintNavigationRegistry($modules, $issues),
            'route_contract' => [
                'ok' => (bool) ($routeContract['ok'] ?? false),
                'checked' => (int) ($routeContract['issue_count'] ?? 0),
            ],
        ];

        foreach ((array) ($routeContract['issues'] ?? []) as $issue) {
            $issues[] = [
                'type' => 'route-contract:' . ($issue['type'] ?? 'issue'),
                'module' => null,
                'message' => (string) ($issue['message'] ?? 'Route contract issue detected.'),
            ];
        }

        return [
            'ok' => $issues === [],
            'issue_count' => count($issues),
            'checks' => $checks,
            'issues' => $issues,
        ];
    }

    /**
     * Ensures CLI route definitions are loaded before linting.
     *
     * Responsibility: Ensures CLI route definitions are loaded before linting.
     */
    private function ensureRoutesLoaded(): void
    {
        if (class_exists(CliRouteLoader::class)) {
            CliRouteLoader::loadAll();
        }
    }

    /**
     * Checks module manifests and required route files.
     *
     * Responsibility: Checks module manifests and required route files.
     * @param array<int, array<string, mixed>> $modules
     * @param array<int, array<string, mixed>> $issues
     * @return array<string, int|bool>
     */
    private function lintModuleRegistration(array $modules, array &$issues): array
    {
        $checked = 0;

        foreach ($modules as $module) {
            $checked++;

            if (($module['manifest_exists'] ?? false) && !($module['manifest_valid'] ?? true)) {
                $issues[] = [
                    'type' => 'invalid-module-manifest',
                    'module' => $module['key'] ?? null,
                    'message' => sprintf(
                        'Module "%s" has an invalid module manifest: %s',
                        $module['key'] ?? 'unknown',
                        implode('; ', (array) ($module['manifest_errors'] ?? []))
                    ),
                ];
            }

            if (($module['route_file'] ?? null) === null) {
                $issues[] = [
                    'type' => 'missing-module-route-file',
                    'module' => $module['key'] ?? null,
                    'message' => sprintf('Module "%s" is missing routes.php.', $module['key'] ?? 'unknown'),
                ];
            }
        }

        return [
            'ok' => !$this->hasIssuePrefix($issues, 'invalid-module-manifest')
                && !$this->hasIssuePrefix($issues, 'missing-module-route-file'),
            'checked' => $checked,
        ];
    }

    /**
     * Detects drift between declared routes and runtime-owned routes.
     *
     * Responsibility: Detects drift between declared routes and runtime-owned routes.
     * @param array<int, array<string, mixed>> $modules
     * @param array<int, array<string, mixed>> $issues
     * @return array<string, int|bool>
     */
    private function lintRegistryRouteDrift(array $modules, array &$issues): array
    {
        $checked = 0;

        foreach ($modules as $module) {
            if (!($module['runtime']['enabled'] ?? $module['runtime_enabled'] ?? true)) {
                continue;
            }

            $checked++;

            $ownedRoutes = array_values((array) ($module['routes']['owned'] ?? []));
            $ownedPatterns = array_map(
                static fn (array $route): string => (string) ($route['pattern'] ?? ''),
                $ownedRoutes
            );
            $declaredExact = array_values(array_unique(array_merge(
                (array) ($module['routes']['declared']['web'] ?? []),
                (array) ($module['routes']['declared']['api'] ?? []),
                (array) ($module['routes']['declared']['aliases'] ?? [])
            )));
            $declaredPrefixes = array_values((array) ($module['routes']['declared']['prefixes'] ?? []));

            foreach ($declaredExact as $pattern) {
                if (!in_array($pattern, $ownedPatterns, true)) {
                    $issues[] = [
                        'type' => 'registry-route-missing',
                        'module' => $module['key'] ?? null,
                        'message' => sprintf(
                            'Module "%s" declares route "%s" but runtime ownership did not resolve it.',
                            $module['key'] ?? 'unknown',
                            $pattern
                        ),
                    ];
                }
            }

            foreach ($ownedPatterns as $pattern) {
                if (in_array($pattern, ['/index', '/index.php'], true)) {
                    continue;
                }

                if (in_array($pattern, $declaredExact, true)) {
                    continue;
                }

                $covered = false;
                foreach ($declaredPrefixes as $prefix) {
                    if (ModuleRegistry::pathMatches($pattern, (string) $prefix)) {
                        $covered = true;
                        break;
                    }
                }

                if (!$covered) {
                    $issues[] = [
                        'type' => 'runtime-route-outside-registry',
                        'module' => $module['key'] ?? null,
                        'message' => sprintf(
                            'Module "%s" owns route "%s" but it is not covered by declared exact routes or prefixes.',
                            $module['key'] ?? 'unknown',
                            $pattern
                        ),
                    ];
                }
            }
        }

        return [
            'ok' => !$this->hasIssuePrefix($issues, 'registry-route-missing')
                && !$this->hasIssuePrefix($issues, 'runtime-route-outside-registry'),
            'checked' => $checked,
        ];
    }

    /**
     * Checks generated asset availability for modules that expose views.
     *
     * Responsibility: Checks generated asset availability for modules that expose views.
     * @param array<int, array<string, mixed>> $modules
     * @param array<int, array<string, mixed>> $issues
     * @return array<string, int|bool>
     */
    private function lintAssetsContract(array $modules, array &$issues): array
    {
        $checked = 0;

        foreach ($modules as $module) {
            if (!($module['views']['has_views'] ?? false)) {
                continue;
            }

            $checked++;
            $slug = (string) ($module['slug'] ?? '');
            $source = (array) ($module['assets']['source'] ?? []);
            $published = (array) ($module['assets']['published'] ?? []);

            if (!($source['style_exists'] ?? false) || !($source['script_exists'] ?? false)) {
                $issues[] = [
                    'type' => 'missing-work-source-asset',
                    'module' => $module['key'] ?? null,
                    'message' => sprintf('Module "%s" is missing front/style.css or front/script.js.', $module['key'] ?? 'unknown'),
                ];
            }

            if (!($published['style_exists'] ?? false) || !($published['script_exists'] ?? false)) {
                $issues[] = [
                    'type' => 'missing-work-published-asset',
                    'module' => $module['key'] ?? null,
                    'message' => sprintf('Module "%s" is missing published work/%s assets.', $module['key'] ?? 'unknown', $slug),
                ];
            }
        }

        return [
            'ok' => !$this->hasIssuePrefix($issues, 'missing-work-source-asset')
                && !$this->hasIssuePrefix($issues, 'missing-work-published-asset'),
            'checked' => $checked,
        ];
    }

    /**
     * Checks consistency between module names, slugs, and view namespaces.
     *
     * Responsibility: Checks consistency between module names, slugs, and view namespaces.
     * @param array<int, array<string, mixed>> $modules
     * @param array<int, array<string, mixed>> $issues
     * @return array<string, int|bool>
     */
    private function lintSlugCoherence(array $modules, array &$issues): array
    {
        $checked = 0;

        foreach ($modules as $module) {
            $checked++;
            $name = (string) ($module['name'] ?? '');
            $slug = (string) ($module['slug'] ?? '');
            $expectedSlug = strtolower($name);
            $viewNamespace = (string) ($module['views']['namespace'] ?? '');
            $assetSlug = (string) ($module['assets']['slug'] ?? '');

            if ($slug !== $expectedSlug || $viewNamespace !== $slug || $assetSlug !== $slug) {
                $issues[] = [
                    'type' => 'inconsistent-module-slug',
                    'module' => $module['key'] ?? null,
                    'message' => sprintf(
                        'Module "%s" has inconsistent slug metadata (slug=%s, expected=%s, view=%s, asset=%s).',
                        $module['key'] ?? 'unknown',
                        $slug,
                        $expectedSlug,
                        $viewNamespace,
                        $assetSlug
                    ),
                ];
            }
        }

        return [
            'ok' => !$this->hasIssuePrefix($issues, 'inconsistent-module-slug'),
            'checked' => $checked,
        ];
    }

    /**
     * Checks that declared guarded routes expose their required middleware. Checks that permission declarations bridge into registries and navigation.
     *
     * Responsibility: Checks that declared guarded routes expose their required middleware. Checks that permission declarations bridge into registries and navigation.
     * @param array<int, array<string, mixed>> $modules
     * @param array<int, array<string, mixed>> $issues
     * @return array<string, int|bool>
     */
    private function lintRouteGuards(array $modules, array &$issues): array
    {
        $checked = 0;

        foreach ($modules as $module) {
            if (!($module['runtime']['enabled'] ?? $module['runtime_enabled'] ?? true)) {
                continue;
            }

            $ownedRoutes = (array) ($module['routes']['owned'] ?? []);
            $guardRules = (array) ($module['route_guards'] ?? []);

            foreach ($guardRules as $guardRule) {
                $patterns = array_values((array) ($guardRule['patterns'] ?? []));
                $required = array_values((array) ($guardRule['middleware_all'] ?? []));

                foreach ($ownedRoutes as $route) {
                    $pattern = (string) ($route['pattern'] ?? '');
                    if (!$this->matchesGuardPatterns($pattern, $patterns)) {
                        continue;
                    }

                    $checked++;
                    $middlewareClasses = array_values((array) ($route['middleware_classes'] ?? []));

                    foreach ($required as $requiredClass) {
                        if (!in_array($requiredClass, $middlewareClasses, true)) {
                            $issues[] = [
                                'type' => 'missing-required-route-guard',
                                'module' => $module['key'] ?? null,
                                'message' => sprintf(
                                    'Route "%s" in module "%s" is missing required middleware "%s".',
                                    $pattern,
                                    $module['key'] ?? 'unknown',
                                    $requiredClass
                                ),
                            ];
                        }
                    }
                }
            }
        }

        return [
            'ok' => !$this->hasIssuePrefix($issues, 'missing-required-route-guard'),
            'checked' => $checked,
        ];
    }

    /**
     * Checks that permission declarations bridge into registries and navigation.
     *
     * Responsibility: Checks that permission declarations bridge into registries and navigation.
     * @param array<int, array<string, mixed>> $modules
     * @param array<int, array<string, mixed>> $issues
     * @return array<string, int|bool>
     */
    private function lintPermissionBridge(array $modules, array &$issues): array
    {
        $checked = 0;
        $permissionRegistry = PermissionRegistry::getInstance();
        $navigation = NavigationRegistry::getInstance()->allDefinitions();

        foreach ($modules as $module) {
            if (!($module['runtime']['enabled'] ?? $module['runtime_enabled'] ?? true)) {
                continue;
            }

            foreach ((array) ($module['routes']['owned'] ?? []) as $route) {
                foreach ((array) ($route['required_permissions'] ?? []) as $permissionSlug) {
                    $checked++;
                    if ($permissionRegistry->find((string) $permissionSlug) === null) {
                        $issues[] = [
                            'type' => 'route-permission-without-registry-bridge',
                            'module' => $module['key'] ?? null,
                            'message' => sprintf(
                                'Route "%s" in module "%s" requires permission "%s" but PermissionRegistry does not declare it.',
                                $route['pattern'] ?? 'unknown',
                                $module['key'] ?? 'unknown',
                                $permissionSlug
                            ),
                        ];
                    }
                }
            }
        }

        foreach (['admin', 'public'] as $bucket) {
            foreach ((array) ($navigation[$bucket] ?? []) as $item) {
                foreach ((array) ($item['visibility'] ?? []) as $visibilityGroup) {
                    foreach ((array) ($visibilityGroup['permissions_any'] ?? []) as $permissionSlug) {
                        $checked++;
                        if ($permissionRegistry->find((string) $permissionSlug) === null) {
                            $issues[] = [
                                'type' => 'navigation-permission-without-registry-bridge',
                                'module' => $item['module_key'] ?? null,
                                'message' => sprintf(
                                    'Navigation item "%s" references permission "%s" that is not declared in PermissionRegistry.',
                                    $item['label'] ?? 'unknown',
                                    $permissionSlug
                                ),
                            ];
                        }
                    }
                }
            }
        }

        foreach ($modules as $module) {
            if (!($module['runtime']['enabled'] ?? $module['runtime_enabled'] ?? true)) {
                continue;
            }

            $declaredPermissions = array_values(array_filter(array_map(
                static fn (array $permission): string => (string) ($permission['slug'] ?? ''),
                (array) ($module['permissions'] ?? [])
            )));

            if ($declaredPermissions === []) {
                continue;
            }

            $routePermissions = [];
            foreach ((array) ($module['routes']['owned'] ?? []) as $route) {
                $routePermissions = array_merge($routePermissions, (array) ($route['required_permissions'] ?? []));
            }

            $navigationPermissions = [];
            foreach (['admin', 'public'] as $bucket) {
                foreach ((array) (($module['navigation'] ?? [])[$bucket] ?? []) as $item) {
                    foreach ((array) ($item['visibility'] ?? []) as $visibilityGroup) {
                        $navigationPermissions = array_merge(
                            $navigationPermissions,
                            (array) ($visibilityGroup['permissions_any'] ?? [])
                        );
                    }
                }
            }

            $bridgedPermissions = array_values(array_unique(array_merge($routePermissions, $navigationPermissions)));

            foreach ($declaredPermissions as $permissionSlug) {
                $checked++;
                if (!in_array($permissionSlug, $bridgedPermissions, true)) {
                    $issues[] = [
                        'type' => 'declared-permission-without-runtime-bridge',
                        'module' => $module['key'] ?? null,
                        'message' => sprintf(
                            'Module "%s" declares permission "%s" but no route guard or navigation visibility consumes it.',
                            $module['key'] ?? 'unknown',
                            $permissionSlug
                        ),
                    ];
                }
            }
        }

        return [
            'ok' => !$this->hasIssuePrefix($issues, 'route-permission-without-registry-bridge')
                && !$this->hasIssuePrefix($issues, 'navigation-permission-without-registry-bridge')
                && !$this->hasIssuePrefix($issues, 'declared-permission-without-runtime-bridge'),
            'checked' => $checked,
        ];
    }

    /**
     * Checks navigation targets, breadcrumbs, and duplicate links.
     *
     * Responsibility: Checks navigation targets, breadcrumbs, and duplicate links.
     * @param array<int, array<string, mixed>> $modules
     * @param array<int, array<string, mixed>> $issues
     * @return array<string, int|bool>
     */
    private function lintNavigationRegistry(array $modules, array &$issues): array
    {
        $checked = 0;
        $routes = [];
        $seenHrefs = [];

        foreach (Router::getInstance()->getRoutes()->all() as $route) {
            if ($route instanceof Route) {
                $routes[$route->getPattern()] = true;
            }
        }

        $trackNavigationHref = static function (
            string $bucket,
            string $context,
            string $href,
            string $label,
            string $moduleKey,
            array &$seenHrefs,
            array &$issues
        ): void {
            if ($href === '') {
                return;
            }

            $key = $bucket . '|' . $context . '|' . $href;
            if (!isset($seenHrefs[$key])) {
                $seenHrefs[$key] = [
                    'label' => $label,
                    'module' => $moduleKey,
                ];
                return;
            }

            $issues[] = [
                'type' => 'navigation-duplicate-href',
                'module' => $moduleKey,
                'message' => sprintf(
                    'Navigation item "%s" in module "%s" duplicates href "%s" already used by "%s" in module "%s" for %s/%s.',
                    $label,
                    $moduleKey,
                    $href,
                    $seenHrefs[$key]['label'] ?? 'unknown',
                    $seenHrefs[$key]['module'] ?? 'unknown',
                    $bucket,
                    $context !== '' ? $context : 'default'
                ),
            ];
        };

        foreach ($modules as $module) {
            if (!($module['runtime']['enabled'] ?? $module['runtime_enabled'] ?? true)) {
                continue;
            }

            $navigation = (array) ($module['navigation'] ?? []);

            foreach (['admin', 'public'] as $bucket) {
                foreach ((array) ($navigation[$bucket] ?? []) as $item) {
                    $checked++;
                    $href = (string) ($item['href'] ?? '');
                    $context = (string) ($item['context'] ?? '');
                    $label = (string) ($item['label'] ?? 'unknown');
                    $moduleKey = (string) ($module['key'] ?? 'unknown');
                    $trackNavigationHref($bucket, $context, $href, $label, $moduleKey, $seenHrefs, $issues);

                    if ($href === '' || !isset($routes[$href])) {
                        $issues[] = [
                            'type' => 'navigation-route-missing',
                            'module' => $module['key'] ?? null,
                            'message' => sprintf(
                                'Navigation item "%s" in module "%s" points to missing route "%s".',
                                $item['label'] ?? 'unknown',
                                $module['key'] ?? 'unknown',
                                $href !== '' ? $href : '(empty)'
                            ),
                        ];
                    }

                    foreach ((array) ($item['children'] ?? []) as $child) {
                        if (!is_array($child)) {
                            continue;
                        }

                        $checked++;
                        $childHref = (string) ($child['href'] ?? '');
                        $childLabel = (string) ($child['label'] ?? 'unknown');
                        $trackNavigationHref(
                            $bucket,
                            $context,
                            $childHref,
                            $childLabel,
                            $moduleKey,
                            $seenHrefs,
                            $issues
                        );

                        if ($childHref === '' || !isset($routes[$childHref])) {
                            $issues[] = [
                                'type' => 'navigation-route-missing',
                                'module' => $module['key'] ?? null,
                                'message' => sprintf(
                                    'Navigation child "%s" in module "%s" points to missing route "%s".',
                                    $childLabel,
                                    $moduleKey,
                                    $childHref !== '' ? $childHref : '(empty)'
                                ),
                            ];
                        }
                    }
                }
            }

            foreach ((array) ($navigation['breadcrumbs'] ?? []) as $rule) {
                foreach ((array) ($rule['trail'] ?? []) as $segment) {
                    $href = $segment['href'] ?? null;
                    if ($href === null) {
                        continue;
                    }

                    $checked++;
                    if (!isset($routes[$href])) {
                        $issues[] = [
                            'type' => 'breadcrumb-route-missing',
                            'module' => $module['key'] ?? null,
                            'message' => sprintf(
                                'Breadcrumb "%s" in module "%s" points to missing route "%s".',
                                $segment['label'] ?? 'unknown',
                                $module['key'] ?? 'unknown',
                                $href
                            ),
                        ];
                    }
                }
            }
        }

        return [
            'ok' => !$this->hasIssuePrefix($issues, 'navigation-route-missing')
                && !$this->hasIssuePrefix($issues, 'breadcrumb-route-missing')
                && !$this->hasIssuePrefix($issues, 'navigation-duplicate-href'),
            'checked' => $checked,
        ];
    }

    /**
     * Determines whether a route matches any declared guard pattern.
     *
     * Responsibility: Determines whether a route matches any declared guard pattern.
     * @param string[] $patterns
     */
    private function matchesGuardPatterns(string $routePattern, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            if (str_starts_with($pattern, '=')) {
                if ($routePattern === substr($pattern, 1)) {
                    return true;
                }

                continue;
            }

            if (ModuleRegistry::pathMatches($routePattern, $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks plugin manifests and module ownership references.
     *
     * Responsibility: Checks plugin manifests and module ownership references.
     * @param array<int, array<string, mixed>> $modules
     * @param array<int, array<string, mixed>> $issues
     * @return array<string, int|bool>
     */
    private function lintPluginManifests(array $modules, array &$issues): array
    {
        $checked = 0;
        $seenOwners = [];

        foreach (PluginRegistry::getInstance()->all() as $plugin) {
            $checked++;

            if (!($plugin['manifest_valid'] ?? true)) {
                $issues[] = [
                    'type' => 'invalid-plugin-manifest',
                    'module' => null,
                    'message' => sprintf(
                        'Plugin "%s" has an invalid manifest: %s',
                        $plugin['key'] ?? 'unknown',
                        implode('; ', (array) ($plugin['manifest_errors'] ?? []))
                    ),
                ];
            }

            foreach ((array) ($plugin['modules'] ?? []) as $moduleKey) {
                if (!is_string($moduleKey) || trim($moduleKey) === '') {
                    continue;
                }

                if (isset($seenOwners[$moduleKey])) {
                    $issues[] = [
                        'type' => 'duplicate-plugin-module-ownership',
                        'module' => $moduleKey,
                        'message' => sprintf(
                            'Module "%s" is declared by multiple plugins ("%s", "%s").',
                            $moduleKey,
                            $seenOwners[$moduleKey],
                            $plugin['key'] ?? 'unknown'
                        ),
                    ];
                } else {
                    $seenOwners[$moduleKey] = (string) ($plugin['key'] ?? 'unknown');
                }

                $module = $this->findModule($modules, $moduleKey);
                if ($module === null) {
                    $issues[] = [
                        'type' => 'plugin-module-missing',
                        'module' => $moduleKey,
                        'message' => sprintf(
                            'Plugin "%s" references unknown module "%s".',
                            $plugin['key'] ?? 'unknown',
                            $moduleKey
                        ),
                    ];
                }
            }
        }

        return [
            'ok' => !$this->hasIssuePrefix($issues, 'invalid-plugin-manifest')
                && !$this->hasIssuePrefix($issues, 'duplicate-plugin-module-ownership')
                && !$this->hasIssuePrefix($issues, 'plugin-module-missing'),
            'checked' => $checked,
        ];
    }

    /**
     * Finds a module report entry by runtime key.
     *
     * Responsibility: Finds a module report entry by runtime key.
     * @param array<int, array<string, mixed>> $modules
     * @return array<string, mixed>|null
     */
    private function findModule(array $modules, string $moduleKey): ?array
    {
        foreach ($modules as $module) {
            if (($module['key'] ?? '') === $moduleKey) {
                return $module;
            }
        }

        return null;
    }

    /**
     * Determines whether an issue list contains a specific issue type.
     *
     * Responsibility: Determines whether an issue list contains a specific issue type.
     * @param array<int, array<string, mixed>> $issues
     */
    private function hasIssuePrefix(array $issues, string $prefix): bool
    {
        foreach ($issues as $issue) {
            if (($issue['type'] ?? '') === $prefix) {
                return true;
            }
        }

        return false;
    }
}
