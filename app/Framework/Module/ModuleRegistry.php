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
use Catalyst\Framework\Traits\SingletonTrait;

/**
 * Registers discovered modules and their runtime metadata.
 *
 * @package Catalyst\Framework\Module
 * Responsibility: Combines discovery, declarations, manifests, localization, runtime state, and route ownership.
 */
final class ModuleRegistry
{
    use SingletonTrait;

    /**
     * @var array<string, array<string, mixed>>
     */
    private const DECLARATIONS = [
        'framework.devtools' => [
            'description' => 'Developer tooling, UML and runtime smoke surfaces.',
            'routes' => [
                'web' => [
                    '/test-layout',
                    '/uml',
                    '/test-features',
                    '/test-features/ui-showcase',
                ],
                'api' => [
                    '/test-features/api/toaster-success',
                    '/test-features/api/toaster-error',
                    '/test-features/api/toaster-warning',
                    '/test-features/api/toaster-info',
                    '/test-features/api/multiple-toasters',
                    '/test-features/api/modal-trigger',
                    '/test-features/api/js-enhancements/partial-refresh',
                    '/test-features/api/validator-test',
                    '/test-features/api/validator-unique',
                ],
                'aliases' => [],
                'prefixes' => [
                    '/test-features',
                    '/uml',
                    '/test-layout',
                ],
            ],
            'route_guards' => [
                [
                    'patterns' => [
                        '/test-features',
                        '/uml',
                        '/test-layout',
                    ],
                    'middleware_all' => [
                        DevToolsGuardMiddleware::class,
                    ],
                ],
            ],
            'permissions' => [
                [
                    'slug' => 'access-devtools',
                    'label' => 'Access DevTools',
                    'description' => 'Access development-only runtime tooling.',
                    'action' => 'access',
                    'resource' => 'devtools',
                    'role_fallback_any' => ['admin'],
                ],
            ],
            'health_checks' => [
                'devtools.runtime.smoke',
            ],
            'feature_flags' => [
                'project_debug',
            ],
            'navigation' => [
                'admin' => [
                    [
                        'context' => 'devtools',
                        'label' => 'Test Features',
                        'href' => '/test-features',
                        'icon' => 'ti ti-test-pipe',
                        'matches' => ['/test-features', '/test-layout'],
                        'group' => 'devtools',
                        'group_label' => 'ui.shell.group_devtools',
                        'group_order' => 10,
                        'hint' => 'Harness principal de runtime',
                        'order' => 10,
                        'visibility' => [
                            ['roles_any' => ['admin'], 'environments' => ['development']],
                            ['permissions_any' => ['access-devtools'], 'environments' => ['development']],
                        ],
                    ],
                    [
                        'context' => 'devtools',
                        'label' => 'UI Showcase',
                        'href' => '/test-features/ui-showcase',
                        'icon' => 'ti ti-components',
                        'matches' => ['/test-features/ui-showcase'],
                        'group' => 'devtools',
                        'group_label' => 'ui.shell.group_devtools',
                        'group_order' => 10,
                        'hint' => 'Catalogo visual y componentes',
                        'order' => 20,
                        'visibility' => [
                            ['roles_any' => ['admin'], 'environments' => ['development']],
                            ['permissions_any' => ['access-devtools'], 'environments' => ['development']],
                        ],
                    ],
                    [
                        'context' => 'devtools',
                        'label' => 'UML / Architecture',
                        'href' => '/uml',
                        'icon' => 'ti ti-schema',
                        'matches' => ['/uml'],
                        'group' => 'devtools',
                        'group_label' => 'ui.shell.group_devtools',
                        'group_order' => 10,
                        'hint' => 'Modelo tecnico y diagramas',
                        'order' => 30,
                        'visibility' => [
                            ['roles_any' => ['admin'], 'environments' => ['development']],
                            ['permissions_any' => ['access-devtools'], 'environments' => ['development']],
                        ],
                    ],
                ],
                'breadcrumbs' => [
                    [
                        'pattern' => '/test-features/ui-showcase',
                        'trail' => [
                            ['label' => 'DevTools', 'href' => '/test-features'],
                            ['label' => 'UI Showcase', 'href' => null],
                        ],
                    ],
                    [
                        'pattern' => '/test-features',
                        'trail' => [
                            ['label' => 'DevTools', 'href' => null],
                        ],
                    ],
                    [
                        'pattern' => '/uml',
                        'trail' => [
                            ['label' => 'DevTools', 'href' => '/test-features'],
                            ['label' => 'Architecture', 'href' => null],
                        ],
                    ],
                    [
                        'pattern' => '/test-layout',
                        'trail' => [
                            ['label' => 'DevTools', 'href' => '/test-features'],
                            ['label' => 'Layout Smoke', 'href' => null],
                        ],
                    ],
                ],
            ],
        ],
    ];

    /**
     * @var array<int, array<string, mixed>>|null
     */
    private ?array $baseModules = null;

    private BuiltInModuleDeclarations $declarations;
    private ModuleDiscovery $discovery;
    private ModuleManifestLoader $manifestLoader;
    private ModuleLocalizationDecorator $localization;
    private ModuleRuntimeStateDecorator $runtimeState;
    private ModuleRouteOwnershipResolver $routeOwnership;

    /**
     * Initializes module metadata collaborators.
     *
     * Responsibility: Initializes module metadata collaborators.
     */
    protected function __construct()
    {
        $this->declarations = new BuiltInModuleDeclarations();
        $this->discovery = new ModuleDiscovery();
        $this->manifestLoader = new ModuleManifestLoader();
        $this->localization = new ModuleLocalizationDecorator();
        $this->runtimeState = new ModuleRuntimeStateDecorator();
        $this->routeOwnership = new ModuleRouteOwnershipResolver();
    }

    /**
     * Returns built-in module declarations.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function builtInDeclarations(): array
    {
        return self::DECLARATIONS;
    }

    /**
     * Returns every discovered module with effective runtime metadata.
     *
     * Responsibility: Returns every discovered module with effective runtime metadata.
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        if ($this->baseModules !== null) {
            return $this->routeOwnership->withOwnedRoutes($this->baseModules);
        }

        $modules = [];

        foreach ($this->discovery->discover() as $module) {
            $declaration = $this->declarations->all()[$module['key']] ?? [];
            [$manifestDeclaration, $manifestErrors] = $this->manifestLoader->loadDeclaration((string) ($module['manifest_file'] ?? ''));
            if ($manifestErrors !== []) {
                $module['manifest_valid'] = false;
                $module['manifest_errors'] = $manifestErrors;
            }

            $module = array_replace_recursive($module, $declaration, $manifestDeclaration);
            $this->manifestLoader->registerLangPath($module);
            $module = $this->localization->localize($module);
            $module = $this->runtimeState->annotate($module);
            $modules[] = $module;
        }

        usort($modules, static function (array $left, array $right): int {
            return [$left['scope'], $left['name']] <=> [$right['scope'], $right['name']];
        });

        $this->baseModules = $modules;

        return $this->routeOwnership->withOwnedRoutes($modules);
    }

    /**
     * Returns modules currently enabled at runtime.
     *
     * Responsibility: Returns modules currently enabled at runtime.
     * @return array<int, array<string, mixed>>
     */
    public function active(): array
    {
        return array_values(array_filter(
            $this->all(),
            static fn (array $module): bool => (bool) ($module['runtime_enabled'] ?? true)
        ));
    }

    /**
     * Clears cached module metadata.
     *
     * Responsibility: Clears cached module metadata.
     */
    public function flushCache(): void
    {
        $this->baseModules = null;
    }

    /**
     * Returns modules belonging to one repository scope.
     *
     * Responsibility: Returns modules belonging to one repository scope.
     * @return array<int, array<string, mixed>>
     */
    public function forScope(string $scope): array
    {
        return array_values(array_filter(
            $this->all(),
            static fn (array $module): bool => ($module['scope'] ?? '') === $scope
        ));
    }

    /**
     * Finds a module by normalized runtime key.
     *
     * Responsibility: Finds a module by normalized runtime key.
     * @return array<string, mixed>|null
     */
    public function findByKey(string $key): ?array
    {
        $normalizedKey = $this->normalizeLookupKey($key);

        foreach ($this->all() as $module) {
            if (($module['key'] ?? '') === $normalizedKey) {
                return $module;
            }
        }

        return null;
    }

    /**
     * Finds the most specific module owning a request path.
     *
     * Responsibility: Finds the most specific module owning a request path.
     * @return array<string, mixed>|null
     */
    public function findByPath(string $path): ?array
    {
        $path = self::normalizePath($path);
        $bestMatch = null;
        $bestLength = -1;

        foreach ($this->all() as $module) {
            foreach ((array)($module['routes']['prefixes'] ?? []) as $pattern) {
                if (!self::pathMatches($path, (string)$pattern)) {
                    continue;
                }

                $length = strlen((string)$pattern);
                if ($length > $bestLength) {
                    $bestMatch = $module;
                    $bestLength = $length;
                }
            }
        }

        return $bestMatch;
    }

    /**
     * Returns permission declarations contributed by all modules.
     *
     * Responsibility: Returns permission declarations contributed by all modules.
     * @return array<int, array<string, mixed>>
     */
    public function permissionDefinitions(): array
    {
        $definitions = [];

        foreach ($this->all() as $module) {
            foreach ((array)($module['permissions'] ?? []) as $definition) {
                if (!is_array($definition) || ($definition['slug'] ?? '') === '') {
                    continue;
                }

                $definition['module_key'] = $module['key'];
                $definition['module_name'] = $module['name'];
                $definition['module_slug'] = $module['slug'];
                $definitions[] = $definition;
            }
        }

        usort($definitions, static function (array $left, array $right): int {
            return [(string)$left['module_key'], (string)$left['slug']]
                <=> [(string)$right['module_key'], (string)$right['slug']];
        });

        return $definitions;
    }

    /**
     * Determines whether a path matches an exact, parameterized, or prefix pattern.
     *
     * @param string $path
     * @param string $pattern
     */
    public static function pathMatches(string $path, string $pattern): bool
    {
        $path = self::normalizePath($path);
        $pattern = self::normalizePath($pattern);

        if ($pattern === $path) {
            return true;
        }

        if (str_contains($pattern, '{')) {
            $segments = array_filter(explode('/', trim($pattern, '/')), static fn (string $segment): bool => $segment !== '');
            $regexSegments = array_map(static function (string $segment): string {
                if (preg_match('/^\{[^}]+\}$/', $segment) === 1) {
                    return '[^/]+';
                }

                return preg_quote($segment, '#');
            }, $segments);
            $regex = '#^/' . implode('/', $regexSegments) . '$#';

            return (bool) preg_match($regex, $path);
        }

        return $pattern !== '/'
            && str_starts_with($path, $pattern . '/');
    }

    /**
     * Normalizes a path for module ownership comparisons.
     */
    private static function normalizePath(string $path): string
    {
        $path = parse_url($path, PHP_URL_PATH) ?: $path;
        $path = trim($path);

        if ($path === '') {
            return '/';
        }

        if ($path[0] !== '/') {
            $path = '/' . $path;
        }

        return $path !== '/' ? rtrim($path, '/') : $path;
    }

    /**
     * Normalizes legacy application lookup keys into surface module keys.
     *
     * Responsibility: Normalizes legacy application lookup keys into surface module keys.
     */
    private function normalizeLookupKey(string $key): string
    {
        $key = trim(strtolower($key));

        if (str_starts_with($key, 'app.') && !str_starts_with($key, 'app.surface.')) {
            return 'app.surface.' . substr($key, strlen('app.'));
        }

        return $key;
    }
}
