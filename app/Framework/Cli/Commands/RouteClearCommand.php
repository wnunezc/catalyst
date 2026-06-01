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
use Catalyst\Framework\Cli\AbstractCommand;
use Catalyst\Framework\Route\Router;

/**
 * Deletes the route cache file
 *
 * @package Catalyst\Framework\Cli\Commands
 */
class RouteClearCommand extends AbstractCommand
{
    public function getName(): string
    {
        return 'route:clear';
    }

    public function getDescription(): string
    {
        return 'Delete the route cache file';
    }

    public function execute(ArgumentBag $args): int
    {
        $router    = Router::getInstance();
        $cacheFile = $router->getCacheFile();

        if (!file_exists($cacheFile)) {
            $this->warn('Route cache does not exist — nothing to clear.');
            return 0;
        }

        if ($router->clearRouteCache()) {
            $this->success('Route cache cleared → ' . $cacheFile);
            return 0;
        }

        $this->error('Failed to delete route cache: ' . $cacheFile);
        return 1;
    }
}
