<?php

declare(strict_types=1);

namespace Catalyst\Framework\Database\Concerns;

use Closure;

trait HasModelLifecycleHooks
{
    protected static function bootIfNeeded(): void
    {
        $class = static::class;

        if (isset(self::$booted[$class])) {
            return;
        }

        self::$booted[$class] = true;

        $traits = [];
        $target = $class;

        do {
            $traits = array_merge($traits, class_uses($target) ?: []);
        } while ($target = get_parent_class($target));

        foreach (array_unique($traits) as $trait) {
            $method = 'boot' . basename(str_replace('\\', '/', $trait));
            if (method_exists($class, $method)) {
                static::$method();
            }
        }
    }

    public static function registerHook(string $event, Closure $callback): void
    {
        self::$hooks[static::class][$event][] = $callback;
    }

    protected function fireHook(string $event): void
    {
        foreach (self::$hooks[static::class][$event] ?? [] as $hook) {
            $hook($this);
        }
    }
}
