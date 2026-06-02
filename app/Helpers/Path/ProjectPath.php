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

namespace Catalyst\Helpers\Path;

/**
 * Defines the Project Path class contract.
 *
 * @package Catalyst\Helpers\Path
 * Responsibility: Coordinates the project path behavior within its module boundary.
 */
final class ProjectPath
{
    /**
     * Handles the boot core workflow.
     */
    public static function bootCore(string ...$segments): string
    {
        return self::join(PD, 'boot-core', ...$segments);
    }

    /**
     * Handles the cache workflow.
     */
    public static function cache(string ...$segments): string
    {
        return self::bootCore('cache', ...$segments);
    }

    /**
     * Handles the route cache file workflow.
     */
    public static function routeCacheFile(): string
    {
        return self::cache('routes.cache.php');
    }

    /**
     * Handles the bin workflow.
     */
    public static function bin(string ...$segments): string
    {
        return self::bootCore('bin', ...$segments);
    }

    /**
     * Handles the database workflow.
     */
    public static function database(string ...$segments): string
    {
        return self::bootCore('database', ...$segments);
    }

    /**
     * Handles the storage workflow.
     */
    public static function storage(string ...$segments): string
    {
        return self::bootCore('storage', ...$segments);
    }

    /**
     * Handles the migrations workflow.
     */
    public static function migrations(string ...$segments): string
    {
        return self::database('migrations', ...$segments);
    }

    /**
     * Handles the join workflow.
     */
    private static function join(string ...$segments): string
    {
        $clean = [];

        foreach ($segments as $index => $segment) {
            $clean[] = $index === 0
                ? rtrim($segment, '\\/')
                : trim($segment, '\\/');
        }

        return implode(DS, array_filter($clean, static fn(string $segment): bool => $segment !== ''));
    }
}
