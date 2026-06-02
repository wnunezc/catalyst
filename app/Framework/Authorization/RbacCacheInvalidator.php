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

namespace Catalyst\Framework\Authorization;

use Catalyst\Framework\Cache\CacheManager;

/**
 * Defines the Rbac Cache Invalidator class contract.
 *
 * @package Catalyst\Framework\Authorization
 * Responsibility: Coordinates the rbac cache invalidator behavior within its module boundary.
 */
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
