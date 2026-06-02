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

/**
 * Defines the Module File Factory class contract.
 *
 * @package Catalyst\Framework\Module
 * Responsibility: Coordinates the module file factory behavior within its module boundary.
 */
final class ModuleFileFactory
{
    /**
     * Initializes the Module File Factory instance.
     */
    public function __construct(
        private readonly ScaffoldManager $manager,
        private readonly ModuleManifestBuilder $manifestBuilder
    ) {
    }

    /**
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
                'contents' => $this->buildControllerContents(
                    $namespaceRoot,
                    $controllerName,
                    $viewNamespace . '.index',
                    is_string($layout) ? $layout : null
                ),
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
                'contents' => $this->buildRouteTemplate(
                    $surface,
                    $controllerName,
                    $routeUri,
                    $space,
                    $module,
                    $viewNamespace,
                    $permissionSlug
                ),
            ],
            [
                'path' => $baseDir . DS . 'module.php',
                'contents' => $this->manifestBuilder->render($manifest),
            ],
        ];
    }

    /**
     * Builds the requested structure.
     */
    private function buildControllerContents(string $namespaceRoot, string $controllerName, string $view, ?string $layout): string
    {
        $viewCall = $layout === null
            ? "return \$this->view('{$view}');"
            : "return \$this->view('{$view}', [], 200, '{$layout}');";

        return <<<PHP
<?php

declare(strict_types=1);

namespace {$namespaceRoot}\Controllers;

use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;

class {$controllerName} extends Controller
{
    public function index(Request \$request): Response
    {
        {$viewCall}
    }
}

PHP;
    }

    /**
     * Builds the requested structure.
     */
    private function buildRouteTemplate(
        string $surface,
        string $controllerName,
        string $routeUri,
        string $space,
        string $module,
        string $viewNamespace,
        string $permissionSlug
    ): string {
        $repositorySegments = $space === 'Framework'
            ? ['Repository', 'Framework', $module]
            : ['Repository', 'App', 'Surface', $module];
        $controllerNamespace = $space === 'Framework'
            ? 'Catalyst\\Repository\\' . $module . '\\Controllers'
            : 'App\\Surface\\' . $module . '\\Controllers';

        $imports = [];
        $middlewareSetup = '';
        $routeTail = ';';

        if (in_array($surface, ['workspace', 'administration'], true)) {
            $imports[] = 'use Catalyst\\Framework\\Middleware\\AuthMiddleware;';
            $imports[] = 'use Catalyst\\Framework\\Middleware\\RoleMiddleware;';
            $roleMiddleware = $permissionSlug !== ''
                ? "new RoleMiddleware(permissions: '{$permissionSlug}')"
                : "new RoleMiddleware(roles: 'admin')";
            $middlewareSetup = '$moduleMiddleware = [AuthMiddleware::class, ' . $roleMiddleware . '];';
            $routeTail = PHP_EOL . '       ->middleware($moduleMiddleware);';
        } elseif ($surface === 'devtools') {
            $imports[] = 'use Catalyst\\Framework\\Middleware\\DevToolsGuardMiddleware;';
            $middlewareSetup = $permissionSlug !== ''
                ? '$moduleMiddleware = new DevToolsGuardMiddleware(permissions: ' . "'{$permissionSlug}'" . ');'
                : '$moduleMiddleware = DevToolsGuardMiddleware::class;';
            $routeTail = PHP_EOL . '       ->middleware($moduleMiddleware);';
        }

        $lines = [
            '<?php',
            '',
            'declare(strict_types=1);',
            '',
            'use Catalyst\Framework\Route\Router;',
            'use Catalyst\Framework\View\View;',
            'use Catalyst\Helpers\I18n\Translator;',
            'use ' . $controllerNamespace . '\\' . $controllerName . ';',
        ];

        foreach ($imports as $import) {
            $lines[] = $import;
        }

        $lines = array_merge($lines, [
            '',
            '$router = Router::getInstance();',
            '',
            'View::getInstance()->addPath(',
            "    '{$viewNamespace}',",
            "    implode(DS, [PD, '" . implode("', '", $repositorySegments) . "', 'Views'])",
            ');',
            '',
            'Translator::getInstance()->addPath(',
            "    implode(DS, [PD, '" . implode("', '", $repositorySegments) . "', 'lang'])",
            ');',
        ]);

        if ($middlewareSetup !== '') {
            $lines[] = '';
            $lines[] = $middlewareSetup;
        }

        $lines[] = '';
        $lines[] = '$router->get(\'/' . $routeUri . '\', [' . $controllerName . '::class, \'index\'])' . $routeTail;
        $lines[] = '';

        return implode(PHP_EOL, $lines);
    }
}
