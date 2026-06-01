<?php

declare(strict_types=1);

/**
 *
 * Catalyst PHP Framework
 * PHP Version 8.4 (Required).
 *
 * @package   Catalyst
 *
 * @see       https://catalyst.lh-2.net
 *
 * @author    Walter Nuñez (arcanisgk/original founder) <wnunez@lh-2.net>
 * @copyright 2024 Walter Francisco Nuñez Cruz and Icaros Net
 * @license   Proprietary - https://catalyst.lh-2.net
 *
 * @note      This program is provided "as is" without a warranty of any kind, too express
 *            or implied, including but not limited to the warranties of merchantability,
 *            fitness for a particular purpose, and non-infringement.
 *
 * @category  Framework
 * @filesource
 *
 * @link      https://catalyst.lh-2.net Project homepage
 *
 */

namespace Catalyst\Framework\Cli\Commands;

use Catalyst\Framework\Argument\ArgumentBag;
use Catalyst\Framework\Cache\CacheSettings;
use Catalyst\Framework\Cli\AbstractCommand;
use Catalyst\Framework\Cli\CliRouteLoader;
use Catalyst\Framework\Route\Router;

/**
 * Loads all routes and writes the route cache file
 *
 * Route files are loaded in the same order as Kernel::loadRoutes():
 *   1. boot-core/routes/global-routes.php
 *   2. boot-core/routes/api.php  (if present)
 *   3. Repository/Framework/{Module}/routes.php
 *   4. Repository/App/Surface/{Module}/routes.php
 *
 * Routes with Closure handlers cannot be serialised — the Router will
 * return false in that case and the command reports accordingly.
 *
 * @package Catalyst\Framework\Cli\Commands
 */
class RouteCacheCommand extends AbstractCommand
{
    public function getName(): string
    {
        return 'route:cache';
    }

    public function getDescription(): string
    {
        return 'Cache all registered routes to file';
    }

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
