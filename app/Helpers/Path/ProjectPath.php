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
 * Builds canonical filesystem paths inside the Catalyst project.
 *
 * @package Catalyst\Helpers\Path
 * Responsibility: Centralizes paths for boot-core, cache, binaries, storage and migrations.
 */
final class ProjectPath
{
    /**
     * Builds a path below the boot-core directory.
     */
    public static function bootCore(string ...$segments): string
    {
        return self::join(PD, 'boot-core', ...$segments);
    }

    /**
     * Builds a path below the boot-core cache directory.
     */
    public static function cache(string ...$segments): string
    {
        return self::bootCore('cache', ...$segments);
    }

    /**
     * Returns the generated route cache file path.
     */
    public static function routeCacheFile(): string
    {
        return self::cache('routes.cache.php');
    }

    /**
     * Builds a path below the boot-core binary directory.
     */
    public static function bin(string ...$segments): string
    {
        return self::bootCore('bin', ...$segments);
    }

    /**
     * Builds a path below the boot-core database directory.
     */
    public static function database(string ...$segments): string
    {
        return self::bootCore('database', ...$segments);
    }

    /**
     * Builds a path below the boot-core storage directory.
     */
    public static function storage(string ...$segments): string
    {
        return self::bootCore('storage', ...$segments);
    }

    /**
     * Builds a path below the migrations directory.
     */
    public static function migrations(string ...$segments): string
    {
        return self::database('migrations', ...$segments);
    }

    /**
     * Joins filesystem path segments after trimming separators.
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
