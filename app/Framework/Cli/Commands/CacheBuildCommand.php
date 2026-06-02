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
use Catalyst\Framework\Cache\CacheSettings;
use Catalyst\Framework\Cli\AbstractCommand;
use Catalyst\Framework\Cli\CliRouteLoader;
use Catalyst\Framework\Route\Router;
use Catalyst\Helpers\Config\ConfigManager;

/**
 * cache:build CLI command.
 *
 * Responsibility: Runs the cache:build command to Build configured cache artifacts without changing activation flags.
 *
 * @package Catalyst\Framework\Cli\Commands
 */
class CacheBuildCommand extends AbstractCommand
{
    /**
     * Returns the command name registered in the CLI registry.
     *
     * Responsibility: Returns the command name registered in the CLI registry.
     */
    public function getName(): string
    {
        return 'cache:build';
    }

    /**
     * Returns the short help text shown for this command.
     *
     * Responsibility: Returns the short help text shown for this command.
     */
    public function getDescription(): string
    {
        return 'Build configured cache artifacts without changing activation flags';
    }

    /**
     * Runs the command workflow using parsed CLI arguments.
     *
     * Responsibility: Runs the command workflow using parsed CLI arguments.
     */
    public function execute(ArgumentBag $args): int
    {
        $settings = CacheSettings::current();
        $config = ConfigManager::getInstance();
        $ok = true;

        $this->info('Cache build');
        $this->line(sprintf(
            '  env=%s, configured=%s, runtime=%s',
            CacheSettings::environment(),
            $settings['cache_enabled'] ? 'true' : 'false',
            CacheSettings::runtimeEnabled($settings) ? 'true' : 'false'
        ));

        if (!CacheSettings::runtimeEnabled($settings)) {
            $this->warn('Runtime cache consumption is inactive right now. Artifacts can still be built for validation, but only production will consume them.');
        }

        if ($settings['config_cache']) {
            if (BootstrapCacheManager::buildConfigCache($config->all())) {
                $this->success('Config cache built → ' . BootstrapCacheManager::configCacheFile());
            } else {
                $this->error('Failed to build config cache.');
                $ok = false;
            }
        } else {
            $this->warn('Config cache skipped: disabled in /configuration/environment-setup cache settings.');
        }

        if ($settings['discovery_cache']) {
            $files = CliRouteLoader::discoverFreshRouteFiles();
            if (BootstrapCacheManager::buildDiscoveryCache($files)) {
                $this->success('Discovery cache built → ' . BootstrapCacheManager::discoveryCacheFile());
            } else {
                $this->error('Failed to build discovery cache.');
                $ok = false;
            }
        } else {
            $this->warn('Discovery cache skipped: disabled in /configuration/environment-setup cache settings.');
        }

        if ($settings['route_cache']) {
            $this->info('Loading routes…');
            CliRouteLoader::loadAll();

            if (Router::getInstance()->cacheRoutes()) {
                $this->success('Route cache built → ' . Router::getInstance()->getCacheFile());
            } else {
                $this->error('Route cache build failed — routes with Closure handlers cannot be cached.');
                $ok = false;
            }
        } else {
            $this->warn('Route cache skipped: disabled in /configuration/environment-setup cache settings.');
        }

        if ($settings['app_cache']) {
            CacheManager::getInstance()->refresh();
            $summary = CacheManager::getInstance()->summary();
            $this->success('Application cache store ready → driver=' . (string) ($summary['driver'] ?? 'null'));
        } else {
            $this->warn('Application cache skipped: disabled in /configuration/environment-setup cache settings.');
        }

        return $ok ? 0 : 1;
    }
}
