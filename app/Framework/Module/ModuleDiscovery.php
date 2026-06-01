<?php

declare(strict_types=1);

namespace Catalyst\Framework\Module;

use Catalyst\Helpers\Config\AppEntryCatalog;

final class ModuleDiscovery
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function discover(): array
    {
        $modules = [];

        $discoveryRoots = [
            [
                'scope' => 'Framework',
                'pattern' => implode(DS, [PD, 'Repository', 'Framework', '*']),
                'namespace_prefix' => 'Catalyst\\Repository\\',
                'module_key_prefix' => 'framework',
            ],
            [
                'scope' => 'App',
                'pattern' => implode(DS, [PD, 'Repository', 'App', 'Surface', '*']),
                'namespace_prefix' => 'App\\Surface\\',
                'module_key_prefix' => 'app.surface',
            ],
        ];

        foreach ($discoveryRoots as $root) {
            foreach (glob($root['pattern'], GLOB_ONLYDIR) ?: [] as $path) {
                $name = basename($path);
                $slug = strtolower($name);
                $viewPath = $path . DS . 'Views';
                $frontPath = $path . DS . 'front';
                $moduleKey = $root['module_key_prefix'] . '.' . $slug;
                $manifestFile = $path . DS . 'module.php';
                $routeFile = is_file($path . DS . 'routes.php') ? $path . DS . 'routes.php' : null;

                if ($routeFile === null && !is_dir($viewPath) && !is_file($manifestFile)) {
                    continue;
                }

                $routes = [
                    'web' => [],
                    'api' => [],
                    'aliases' => [],
                    'prefixes' => [],
                    'owned' => [],
                ];

                if ($root['scope'] === 'App' && in_array($name, ['Home', 'Landing', 'Dashboard', 'Store'], true)) {
                    $resolvedPath = AppEntryCatalog::resolvePath($name);

                    if (is_string($resolvedPath) && $resolvedPath !== '') {
                        $routes['web'][] = $resolvedPath;
                    }
                }

                $modules[] = [
                    'key' => $moduleKey,
                    'scope' => $root['scope'],
                    'name' => $name,
                    'slug' => $slug,
                    'label' => $name,
                    'namespace' => $root['namespace_prefix'] . $name,
                    'path' => $path,
                    'route_file' => $routeFile,
                    'description' => '',
                    'manifest_file' => $manifestFile,
                    'manifest_exists' => is_file($manifestFile),
                    'manifest_valid' => true,
                    'manifest_errors' => [],
                    'views' => [
                        'namespace' => $slug,
                        'path' => $viewPath,
                        'has_views' => is_dir($viewPath),
                    ],
                    'assets' => [
                        'slug' => $slug,
                        'source' => [
                            'style' => $frontPath . DS . 'style.css',
                            'script' => $frontPath . DS . 'script.js',
                        ],
                        'published' => [
                            'style' => implode(DS, [PD, 'public', 'assets', 'css', 'work', $slug, 'style.css']),
                            'script' => implode(DS, [PD, 'public', 'assets', 'js', 'work', $slug, 'script.js']),
                        ],
                    ],
                    'routes' => $routes,
                    'settings' => [],
                    'permissions' => [],
                    'health_checks' => [],
                    'seeds' => [],
                    'feature_flags' => [],
                    'route_guards' => [],
                    'navigation' => [
                        'admin' => [],
                        'public' => [],
                        'breadcrumbs' => [],
                    ],
                ];
            }
        }

        return $modules;
    }
}
