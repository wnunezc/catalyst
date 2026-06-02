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
use Catalyst\Framework\Route\Route;
use Catalyst\Framework\Route\Router;

/**
 * Defines the Route List Command class contract.
 *
 * @package Catalyst\Framework\Cli\Commands
 * Responsibility: Coordinates the route list command behavior within its module boundary.
 */
class RouteListCommand extends AbstractCommand
{
    /**
     * Returns the name value.
     */
    public function getName(): string
    {
        return 'route:list';
    }

    /**
     * Returns the description value.
     */
    public function getDescription(): string
    {
        return 'List registered routes with method, URI, name, handler and middleware';
    }

    /** @return Option[] */
    public function getOptions(): array
    {
        return [
            new Option('m', 'method', null, false, 'Filter by HTTP method (GET, POST, ...)', true),
            new Option(null, 'json', false, false, 'Render the route list as JSON', false),
        ];
    }

    /**
     * Executes the service workflow.
     */
    public function execute(ArgumentBag $args): int
    {
        CliRouteLoader::loadAll();

        $methodFilter = strtoupper(trim((string) ($args->getOptionValue('method') ?? $args->getOptionValue('m') ?? '')));
        $asJson       = (bool) ($args->getOptionValue('json') ?? false);
        $routes       = array_map(
            fn (Route $route): array => $this->mapRoute($route),
            Router::getInstance()->getRoutes()->all()
        );

        if ($methodFilter !== '') {
            $routes = array_values(array_filter(
                $routes,
                static fn (array $route): bool => in_array($methodFilter, $route['methods'], true)
            ));
        }

        usort(
            $routes,
            static function (array $left, array $right): int {
                return [$left['uri'], implode(',', $left['methods'])]
                    <=> [$right['uri'], implode(',', $right['methods'])];
            }
        );

        if ($asJson) {
            $this->line((string) json_encode($routes, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            return 0;
        }

        $this->line('');
        $this->info('Registered Routes');
        $this->line(str_repeat('-', 160));
        $this->line(sprintf('  %-14s %-36s %-28s %-48s %s', 'Methods', 'URI', 'Name', 'Handler', 'Middleware'));
        $this->line(str_repeat('-', 160));

        if ($routes === []) {
            $this->warn('No routes matched the current filter.');
            $this->line('');
            return 0;
        }

        foreach ($routes as $route) {
            $this->line(sprintf(
                '  %-14s %-36s %-28s %-48s %s',
                implode(',', $route['methods']),
                $route['uri'],
                $route['name'],
                $route['handler'],
                $route['middleware']
            ));
        }

        $this->line(str_repeat('-', 160));
        $this->success(sprintf('%d route(s) listed.', count($routes)));
        $this->line('');

        return 0;
    }

    /**
     * @return array{methods:string[],uri:string,name:string,handler:string,middleware:string}
     */
    private function mapRoute(Route $route): array
    {
        $middleware = array_map(
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

        return [
            'methods'    => $route->getMethods(),
            'uri'        => $route->getPattern(),
            'name'       => $route->getName() ?? '-',
            'handler'    => $this->stringifyHandler($route->getHandler(), $route->getNamespace()),
            'middleware' => $middleware === [] ? '-' : implode(', ', $middleware),
        ];
    }

    /**
     * Handles the stringify handler workflow.
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
}
