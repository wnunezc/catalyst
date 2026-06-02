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

/**
 * route:bootstrap-regression CLI command.
 *
 * Responsibility: Runs the route:bootstrap-regression command to Verify cache-safe middleware, module view paths and route discovery order.
 *
 * @package Catalyst\Framework\Cli\Commands
 */
final class RouteBootstrapRegressionCommand extends AbstractCommand
{
    /**
     * Defines the accepted option schema for this command.
     *
     * Responsibility: Defines the accepted option schema for this command.
     * @return Option[]
     */
    public function getOptions(): array
    {
        return [
            new Option(null, 'json', false, false, 'Render the result as JSON', false),
        ];
    }

    /**
     * Returns the command name registered in the CLI registry.
     *
     * Responsibility: Returns the command name registered in the CLI registry.
     */
    public function getName(): string
    {
        return 'route:bootstrap-regression';
    }

    /**
     * Returns the short help text shown for this command.
     *
     * Responsibility: Returns the short help text shown for this command.
     */
    public function getDescription(): string
    {
        return 'Verify cache-safe middleware, module view paths and route discovery order';
    }

    /**
     * Runs the command workflow using parsed CLI arguments.
     *
     * Responsibility: Runs the command workflow using parsed CLI arguments.
     */
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
     * Describes the module view paths are registered helper responsibility inside the CLI component.
     *
     * Responsibility: Supports the module view paths are registered helper workflow used by this CLI component.
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
     * Describes the route files are ordered helper responsibility inside the CLI component.
     *
     * Responsibility: Supports the route files are ordered helper workflow used by this CLI component.
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

    /**
     * Describes the optional api route file follows global routes helper responsibility inside the CLI component.
     *
     * Responsibility: Supports the optional api route file follows global routes helper workflow used by this CLI component.
     */
    private function optionalApiRouteFileFollowsGlobalRoutes(): bool
    {
        $globalRoutes = implode(DS, [PD, 'boot-core', 'routes', 'global-routes.php']);
        $optionalApi = implode(DS, [PD, 'boot-core', 'routes', 'api.php']);
        $orderRouteFiles = new \ReflectionMethod(CliRouteLoader::class, 'orderRouteFiles');

        return $orderRouteFiles->invoke(null, [$optionalApi, $globalRoutes]) === [$globalRoutes, $optionalApi];
    }

    /**
     * Describes the login redirect is readable and safe helper responsibility inside the CLI component.
     *
     * Responsibility: Supports the login redirect is readable and safe helper workflow used by this CLI component.
     */
    private function loginRedirectIsReadableAndSafe(): bool
    {
        return RedirectTarget::loginUrl('/configuration/environment-setup')
            === '/login?redirect=/configuration/environment-setup'
            && RedirectTarget::loginUrl('/workspaces/locale-tools?locale=es')
            === '/login?redirect=/workspaces/locale-tools%3Flocale%3Des';
    }
}
