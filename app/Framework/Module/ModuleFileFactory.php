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

use Catalyst\Framework\Cli\ScaffoldManager;
use Catalyst\Framework\Cli\Support\PhpValueExporter;

/**
 * Builds files emitted by module scaffolding.
 *
 * @package Catalyst\Framework\Module
 * Responsibility: Renders controller, view, asset, localization, route, and manifest file contents.
 */
final class ModuleFileFactory
{
    private readonly PhpValueExporter $exporter;

    /**
     * Initializes the factory with scaffold and manifest rendering support.
     *
     * Responsibility: Initializes the factory with scaffold and manifest rendering support.
     */
    public function __construct(
        private readonly ScaffoldManager $manager,
        private readonly ModuleManifestBuilder $manifestBuilder,
        ?PhpValueExporter $exporter = null
    ) {
        $this->exporter = $exporter ?? new PhpValueExporter();
    }

    /**
     * Builds every file definition required by a module blueprint.
     *
     * Responsibility: Builds every file definition required by a module blueprint.
     * @param array<string, mixed> $blueprint
     * @return array<int, array<string, string>>
     */
    public function build(array $blueprint): array
    {
        $baseDir = (string) ($blueprint['base_dir'] ?? '');
        $controllerName = (string) ($blueprint['controller_name'] ?? '');
        $viewNamespace = (string) ($blueprint['view_namespace'] ?? '');
        $routeUri = (string) ($blueprint['route_uri'] ?? '');
        $module = (string) ($blueprint['module'] ?? '');
        $space = (string) ($blueprint['space'] ?? 'App');
        $surface = (string) ($blueprint['surface'] ?? 'none');
        $permissionSlug = (string) ($blueprint['permission_slug'] ?? '');
        $namespaceRoot = (string) ($blueprint['namespace_root'] ?? '');
        $layout = $blueprint['layout'] ?? null;
        $manifest = (array) ($blueprint['manifest'] ?? []);

        return [
            [
                'path' => $baseDir . DS . 'Controllers' . DS . $controllerName . '.php',
                'contents' => $this->manager->renderStub('module-controller.php.stub', [
                    'NamespaceRoot' => $namespaceRoot,
                    'ControllerClass' => $controllerName,
                    'ViewCall' => $this->buildControllerViewCall(
                        $viewNamespace . '.index',
                        is_string($layout) ? $layout : null
                    ),
                ]),
            ],
            [
                'path' => $baseDir . DS . 'Views' . DS . 'pages' . DS . 'index.phtml',
                'contents' => $this->manager->renderStub('module-view.php.stub', [
                    'langKey' => $viewNamespace,
                ]),
            ],
            [
                'path' => $baseDir . DS . 'front' . DS . 'script.js',
                'contents' => $this->manager->renderStub('module-script.js.stub', [
                    'moduleSlug' => $viewNamespace,
                ]),
            ],
            [
                'path' => $baseDir . DS . 'front' . DS . 'style.css',
                'contents' => $this->manager->renderStub('module-style.css.stub', [
                    'moduleSlug' => $viewNamespace,
                ]),
            ],
            [
                'path' => $baseDir . DS . 'lang' . DS . 'en' . DS . $viewNamespace . '.json',
                'contents' => $this->manager->renderStub('module-lang-en.json.stub', [
                    'moduleName' => $module,
                ]),
            ],
            [
                'path' => $baseDir . DS . 'lang' . DS . 'es' . DS . $viewNamespace . '.json',
                'contents' => $this->manager->renderStub('module-lang-es.json.stub', [
                    'moduleName' => $module,
                ]),
            ],
            [
                'path' => $baseDir . DS . 'routes.php',
                'contents' => $this->manager->renderStub('module-routes.php.stub', [
                    'ControllerNamespace' => $this->controllerNamespace($space, $module),
                    'ControllerClass' => $controllerName,
                    'MiddlewareImports' => $this->buildMiddlewareImports($surface),
                    'ViewNamespaceLiteral' => $this->exporter->export($viewNamespace),
                    'ViewPathExpression' => $this->buildPathExpression(
                        array_merge($this->repositorySegments($space, $module), ['Views'])
                    ),
                    'LangPathExpression' => $this->buildPathExpression(
                        array_merge($this->repositorySegments($space, $module), ['lang'])
                    ),
                    'MiddlewareSetup' => $this->buildMiddlewareSetup($surface, $permissionSlug),
                    'RoutePathLiteral' => $this->exporter->export('/' . $routeUri),
                    'RouteMiddlewareChain' => $this->buildRouteMiddlewareChain($surface),
                ]),
            ],
            [
                'path' => $baseDir . DS . 'module.php',
                'contents' => $this->manifestBuilder->render($manifest),
            ],
        ];
    }

    /**
     * Builds the controller response statement used by the module controller stub.
     */
    private function buildControllerViewCall(string $view, ?string $layout): string
    {
        if ($layout === null) {
            return 'return $this->view(' . $this->exporter->export($view) . ');';
        }

        return 'return $this->view(' . $this->exporter->export($view) . ', [], 200, '
            . $this->exporter->export($layout) . ');';
    }


    /**
     * Builds a PD-relative path expression for generated route files.
     *
     * @param string[] $segments
     */
    private function buildPathExpression(array $segments): string
    {
        $exportedSegments = array_map(
            fn (string $segment): string => $this->exporter->export($segment),
            $segments
        );

        return 'implode(DS, [PD, ' . implode(', ', $exportedSegments) . '])';
    }

    /**
     * Returns repository path segments below PD for a generated module.
     *
     * @return string[]
     */
    private function repositorySegments(string $space, string $module): array
    {
        if ($space === 'Framework') {
            return ['Repository', 'Framework', $module];
        }

        return ['Repository', 'App', 'Surface', $module];
    }

    /**
     * Returns the controller namespace for the generated route file.
     */
    private function controllerNamespace(string $space, string $module): string
    {
        if ($space === 'Framework') {
            return 'Catalyst\\Repository\\' . $module . '\\Controllers';
        }

        return 'App\\Surface\\' . $module . '\\Controllers';
    }

    /**
     * Builds optional middleware imports for guarded module surfaces.
     */
    private function buildMiddlewareImports(string $surface): string
    {
        if (in_array($surface, ['workspace', 'administration'], true)) {
            return implode(PHP_EOL, [
                'use Catalyst\\Framework\\Middleware\\AuthMiddleware;',
                'use Catalyst\\Framework\\Middleware\\RoleMiddleware;',
            ]);
        }

        if ($surface === 'devtools') {
            return 'use Catalyst\\Framework\\Middleware\\DevToolsGuardMiddleware;';
        }

        return '';
    }

    /**
     * Builds optional middleware setup for guarded module surfaces.
     */
    private function buildMiddlewareSetup(string $surface, string $permissionSlug): string
    {
        if (in_array($surface, ['workspace', 'administration'], true)) {
            $roleMiddleware = $permissionSlug !== ''
                ? 'new RoleMiddleware(permissions: ' . $this->exporter->export($permissionSlug) . ')'
                : 'new RoleMiddleware(roles: ' . $this->exporter->export('admin') . ')';

            return '$moduleMiddleware = [AuthMiddleware::class, ' . $roleMiddleware . '];';
        }

        if ($surface === 'devtools') {
            if ($permissionSlug !== '') {
                return '$moduleMiddleware = new DevToolsGuardMiddleware(permissions: '
                    . $this->exporter->export($permissionSlug) . ');';
            }

            return '$moduleMiddleware = DevToolsGuardMiddleware::class;';
        }

        return '';
    }

    /**
     * Builds the optional middleware chain for the generated route.
     */
    private function buildRouteMiddlewareChain(string $surface): string
    {
        if (in_array($surface, ['workspace', 'administration', 'devtools'], true)) {
            return PHP_EOL . '       ->middleware($moduleMiddleware);';
        }

        return ';';
    }
}
