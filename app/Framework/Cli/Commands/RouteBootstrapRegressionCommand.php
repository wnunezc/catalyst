<?php

declare(strict_types=1);

namespace Catalyst\Framework\Cli\Commands;

use Catalyst\Framework\Argument\ArgumentBag;
use Catalyst\Framework\Argument\Option;
use Catalyst\Framework\Cli\AbstractCommand;
use Catalyst\Framework\Cli\CliRouteLoader;
use Catalyst\Framework\Http\RedirectTarget;
use Catalyst\Framework\Module\ModuleRegistry;
use Catalyst\Framework\Route\GlobalMiddlewareRegistrar;
use Catalyst\Framework\Route\Router;
use Catalyst\Framework\View\ModuleViewPathRegistrar;
use Catalyst\Framework\View\View;

final class RouteBootstrapRegressionCommand extends AbstractCommand
{
    /** @return Option[] */
    public function getOptions(): array
    {
        return [
            new Option(null, 'json', false, false, 'Render the result as JSON', false),
        ];
    }

    public function getName(): string
    {
        return 'route:bootstrap-regression';
    }

    public function getDescription(): string
    {
        return 'Verify cache-safe middleware, module view paths and route discovery order';
    }

    public function execute(ArgumentBag $args): int
    {
        $middlewareRegistrar = new GlobalMiddlewareRegistrar();
        $router = Router::getInstance();
        $middlewareRegistrar->register($router);

        $modules = ModuleRegistry::getInstance()->active();
        $view = View::getInstance();
        (new ModuleViewPathRegistrar())->register($view, $modules);

        $checks = [
            'global_middleware' => $router->getGlobalMiddleware() === $middlewareRegistrar->middleware(),
            'module_view_paths' => $this->moduleViewPathsAreRegistered($modules, $view->getPaths()),
            'route_file_order' => $this->routeFilesAreOrdered(CliRouteLoader::discoverFreshRouteFiles()),
            'optional_api_order' => $this->optionalApiRouteFileFollowsGlobalRoutes(),
            'readable_login_redirect' => $this->loginRedirectIsReadableAndSafe(),
        ];
        $ok = !in_array(false, $checks, true);

        if ((bool) ($args->getOptionValue('json') ?? false)) {
            $this->line((string) json_encode(['ok' => $ok, 'checks' => $checks], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            return $ok ? 0 : 1;
        }

        $this->line('');
        $this->info('Route Bootstrap Regression');
        $this->line(str_repeat('-', 70));

        foreach ($checks as $name => $passed) {
            $this->line(sprintf('  %-24s %s', ucwords(str_replace('_', ' ', $name)), $passed ? 'OK' : 'ISSUES'));
        }

        $this->line(str_repeat('-', 70));
        $ok ? $this->success('Route bootstrap contract is coherent.') : $this->error('Route bootstrap contract has issues.');
        $this->line('');

        return $ok ? 0 : 1;
    }

    /**
     * @param array<int, array<string, mixed>> $modules
     * @param array<string, string> $paths
     */
    private function moduleViewPathsAreRegistered(array $modules, array $paths): bool
    {
        foreach ($modules as $module) {
            $views = $module['views'] ?? [];
            if (!($views['has_views'] ?? false)) {
                continue;
            }

            $namespace = $views['namespace'] ?? null;
            $path = $views['path'] ?? null;
            if (!is_string($namespace) || !is_string($path) || ($paths[$namespace] ?? null) !== $path) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string[] $files
     */
    private function routeFilesAreOrdered(array $files): bool
    {
        $lastStage = -1;

        foreach ($files as $file) {
            $normalized = str_replace(['/', '\\'], DS, $file);
            $stage = match (true) {
                str_ends_with($normalized, implode(DS, ['boot-core', 'routes', 'global-routes.php'])) => 0,
                str_ends_with($normalized, implode(DS, ['boot-core', 'routes', 'api.php'])) => 1,
                str_contains($normalized, implode(DS, ['boot-core', 'routes'])) => 2,
                str_contains($normalized, implode(DS, ['Repository', 'Framework'])) => 3,
                str_contains($normalized, implode(DS, ['Repository', 'App', 'Surface'])) => 4,
                default => 5,
            };

            if ($stage < $lastStage) {
                return false;
            }

            $lastStage = $stage;
        }

        return true;
    }

    private function optionalApiRouteFileFollowsGlobalRoutes(): bool
    {
        $globalRoutes = implode(DS, [PD, 'boot-core', 'routes', 'global-routes.php']);
        $optionalApi = implode(DS, [PD, 'boot-core', 'routes', 'api.php']);
        $orderRouteFiles = new \ReflectionMethod(CliRouteLoader::class, 'orderRouteFiles');

        return $orderRouteFiles->invoke(null, [$optionalApi, $globalRoutes]) === [$globalRoutes, $optionalApi];
    }

    private function loginRedirectIsReadableAndSafe(): bool
    {
        return RedirectTarget::loginUrl('/configuration/environment-setup')
            === '/login?redirect=/configuration/environment-setup'
            && RedirectTarget::loginUrl('/workspaces/locale-tools?locale=es')
            === '/login?redirect=/workspaces/locale-tools%3Flocale%3Des';
    }
}
