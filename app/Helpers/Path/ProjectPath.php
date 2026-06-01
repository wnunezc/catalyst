<?php

declare(strict_types=1);

namespace Catalyst\Helpers\Path;

final class ProjectPath
{
    public static function bootCore(string ...$segments): string
    {
        return self::join(PD, 'boot-core', ...$segments);
    }

    public static function cache(string ...$segments): string
    {
        return self::bootCore('cache', ...$segments);
    }

    public static function routeCacheFile(): string
    {
        return self::cache('routes.cache.php');
    }

    public static function bin(string ...$segments): string
    {
        return self::bootCore('bin', ...$segments);
    }

    public static function database(string ...$segments): string
    {
        return self::bootCore('database', ...$segments);
    }

    public static function storage(string ...$segments): string
    {
        return self::bootCore('storage', ...$segments);
    }

    public static function migrations(string ...$segments): string
    {
        return self::database('migrations', ...$segments);
    }

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
