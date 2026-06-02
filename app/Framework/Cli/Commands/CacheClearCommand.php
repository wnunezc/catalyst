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
use Catalyst\Framework\Cache\BootstrapCacheManager;
use Catalyst\Framework\Cache\CacheManager;
use Catalyst\Framework\Cli\AbstractCommand;
use Catalyst\Framework\Route\Router;

/**
 * Defines the Cache Clear Command class contract.
 *
 * @package Catalyst\Framework\Cli\Commands
 * Responsibility: Coordinates the cache clear command behavior within its module boundary.
 */
class CacheClearCommand extends AbstractCommand
{
    /**
     * Returns the name value.
     */
    public function getName(): string
    {
        return 'cache:clear';
    }

    /**
     * Returns the description value.
     */
    public function getDescription(): string
    {
        return 'Clear route, bootstrap and application cache artifacts';
    }

    /**
     * Executes the service workflow.
     */
    public function execute(ArgumentBag $args): int
    {
        $ok = true;

        if (Router::getInstance()->clearRouteCache()) {
            $this->success('Route cache cleared.');
        } else {
            $this->error('Failed to clear route cache.');
            $ok = false;
        }

        if (BootstrapCacheManager::clearAll()) {
            $this->success('Bootstrap cache artifacts cleared.');
        } else {
            $this->error('Failed to clear bootstrap cache artifacts.');
            $ok = false;
        }

        if (CacheManager::getInstance()->clear()) {
            $this->success('Application cache store cleared.');
        } else {
            $this->error('Failed to clear application cache store.');
            $ok = false;
        }

        CacheManager::getInstance()->refresh();

        return $ok ? 0 : 1;
    }
}
