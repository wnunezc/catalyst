<?php

declare(strict_types=1);

/**
 *
 * Catalyst PHP Framework
 * PHP Version 8.4 (Required).
 *
 * @package   Catalyst
 *
 * @see       https://catalyst.lh-2.net
 *
 * @author    Walter Nuñez (arcanisgk/original founder) <wnunez@lh-2.net>
 * @copyright 2024 Walter Francisco Nuñez Cruz and Icaros Net
 * @license   Proprietary - https://catalyst.lh-2.net
 *
 * @note      This program is provided "as is" without a warranty of any kind, too express
 *            or implied, including but not limited to the warranties of merchantability,
 *            fitness for a particular purpose, and non-infringement.
 *
 * @category  Framework
 * @filesource
 *
 * @link      https://catalyst.lh-2.net Project homepage
 *
 */

namespace Catalyst\Framework\Traits;

use Exception;
use InvalidArgumentException;

/**
 * Trait that handles: Singleton Instance
 *
 * @package Catalyst\Framework\Traits;
 */
trait SingletonTrait
{
    /**
     * @var self|null
     */
    private static self|null $instance = null;

    /**
     * @var array
     */
    private static array $arguments = [];

    /**
     * Protected constructor to prevent direct instantiation
     */
    protected function __construct()
    {
        // This is intentionally empty
        // Child classes can override this to perform initialization
    }

    /**
     * Get the singleton instance of the class
     *
     * @param mixed ...$args
     * @return static The singleton instance
     */
    public static function getInstance(mixed ...$args): static
    {

        if (self::$instance === null) {
            self::$arguments = $args;

            /** @var mixed $args */
            self::$instance = !empty($args) ? new static(...$args) : new static();
        }

        return self::$instance;
    }

    /**
     * Set a specific instance (for mocking/testing only).
     * Not available in production environments.
     *
     * @internal Use only in test or development environments
     * @param object $instance The instance to use (must be an instance of the class using this trait)
     * @return void
     * @throws InvalidArgumentException If the instance is not of the correct type
     * @throws \RuntimeException If called in production
     */
    public static function setInstance(object $instance): void
    {
        if (defined('IS_PRODUCTION') && IS_PRODUCTION) {
            throw new \RuntimeException(
                static::class . '::setInstance() is not available in production.'
            );
        }

        if (!($instance instanceof static)) {
            throw new InvalidArgumentException('Instance must be of type ' . static::class);
        }

        self::$instance = $instance;
    }

    /**
     * Get constructor arguments
     *
     * @return array
     */
    protected static function getArguments(): array
    {
        return self::$arguments;
    }

    /**
     * Reset the singleton instance
     *
     * @return void
     */
    public static function resetInstance(): void
    {
        self::$instance = null;
        self::$arguments = [];
    }

    /**
     * Prevent cloning of the instance
     *
     * @return void
     */
    private function __clone()
    {
        // This is intentionally empty to prevent cloning
    }

    /**
     * Prevent unserialization of the instance
     *
     * @return void
     * @throws Exception If attempted to unserialize a singleton
     */
    public function __wakeup()
    {
        throw new Exception('Cannot unserialize a singleton.');
    }
}