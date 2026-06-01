<?php

declare(strict_types=1);

namespace Catalyst\Framework\Authorization;

use Catalyst\Framework\Cache\CacheManager;

final class RbacCacheInvalidator
{
    /**
     * @param array<string, array<int, array<string, mixed>>> $memoryCache
     */
    public function flushAll(array &$memoryCache): void
    {
        $memoryCache = [];
        CacheManager::getInstance()->forever('rbac:version', (string) microtime(true));
    }

    /**
     * @param array<string, array<int, array<string, mixed>>> $memoryCache
     */
    public function flushUser(array &$memoryCache, string $rolesKey, string $permissionsKey, string $persistentRolesKey, string $persistentPermissionsKey): void
    {
        unset($memoryCache[$rolesKey], $memoryCache[$permissionsKey]);
        CacheManager::getInstance()->forget($persistentRolesKey);
        CacheManager::getInstance()->forget($persistentPermissionsKey);
    }
}
