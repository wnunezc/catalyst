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

namespace Catalyst\Framework\Database\Concerns;

use Closure;

/**
 * Splits model boot and lifecycle hook registration behavior out of Model.
 *
 * @package Catalyst\Framework\Database\Concerns
 * Responsibility: Boot model traits once per concrete class and dispatch registered ORM lifecycle callbacks.
 */
trait HasModelLifecycleHooks
{
    /**
     * Boots the concrete model class once and invokes discovered trait boot methods.
     */
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

    /**
     * Registers a callback for one ORM lifecycle event on the concrete model class.
     */
    public static function registerHook(string $event, Closure $callback): void
    {
        self::$hooks[static::class][$event][] = $callback;
    }

    /**
     * Dispatches registered callbacks for the given lifecycle event on the current model.
     *
     * Responsibility: Dispatches registered callbacks for the given lifecycle event on the current model.
     */
    protected function fireHook(string $event): void
    {
        foreach (self::$hooks[static::class][$event] ?? [] as $hook) {
            $hook($this);
        }
    }
}
