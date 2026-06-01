<?php

declare(strict_types=1);

namespace Catalyst\Framework\Cli\Commands;

use Catalyst\Framework\Argument\ArgumentBag;
use Catalyst\Framework\Cache\BootstrapCacheManager;
use Catalyst\Framework\Cache\CacheManager;
use Catalyst\Framework\Cli\AbstractCommand;
use Catalyst\Framework\Route\Router;

class CacheClearCommand extends AbstractCommand
{
    public function getName(): string
    {
        return 'cache:clear';
    }

    public function getDescription(): string
    {
        return 'Clear route, bootstrap and application cache artifacts';
    }

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
