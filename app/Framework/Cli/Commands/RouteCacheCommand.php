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
use Catalyst\Framework\Cache\CacheSettings;
use Catalyst\Framework\Cli\AbstractCommand;
use Catalyst\Framework\Cli\CliRouteLoader;
use Catalyst\Framework\Route\Router;

/**
 * route:cache CLI command.
 *
 * Responsibility: Runs the route:cache command to Cache all registered routes to file.
 *
 * @package Catalyst\Framework\Cli\Commands
 */
class RouteCacheCommand extends AbstractCommand
{
    /**
     * Returns the command name registered in the CLI registry.
     *
     * Responsibility: Returns the command name registered in the CLI registry.
     */
    public function getName(): string
    {
        return 'route:cache';
    }

    /**
     * Returns the short help text shown for this command.
     *
     * Responsibility: Returns the short help text shown for this command.
     */
    public function getDescription(): string
    {
        return 'Cache all registered routes to file';
    }

    /**
     * Runs the command workflow using parsed CLI arguments.
     *
     * Responsibility: Runs the command workflow using parsed CLI arguments.
     */
    public function execute(ArgumentBag $args): int
    {
        $cacheConfig = CacheSettings::current();

        if (!(bool) ($cacheConfig['route_cache'] ?? false)) {
            $this->warn('Route cache is currently disabled in /configuration/environment-setup cache settings. This command only builds the artifact; it does not activate runtime usage.');
        }

        if (!CacheSettings::runtimeEnabled($cacheConfig)) {
            $this->warn('Runtime route cache consumption is currently inactive because the environment is not production or the cache master switch is off.');
        }

        $this->info('Loading routes…');
        CliRouteLoader::loadAll();

        $router = Router::getInstance();
        $this->info('Writing route cache…');

        if (!$router->cacheRoutes()) {
            $this->error('Route cache failed — routes with Closure handlers cannot be cached.');
            $this->warn('Replace Closure handlers with Controller@method strings and retry.');
            return 1;
        }

        $this->success('Route cache written → ' . $router->getCacheFile());
        return 0;
    }
}
