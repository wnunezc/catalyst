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

namespace Catalyst\Framework\Cli;

use Catalyst\Framework\Traits\SingletonTrait;

/**
 * Singleton registry for all registered CLI commands
 *
 * @package Catalyst\Framework\Cli
 */
class CommandRegistry
{
    use SingletonTrait;

    /**
     * Registered commands indexed by name
     *
     * @var array<string, CommandInterface>
     */
    private array $commands = [];

    /**
     * Register a command
     *
     * @param CommandInterface $command
     * @return self
     */
    public function register(CommandInterface $command): self
    {
        $this->commands[$command->getName()] = $command;
        return $this;
    }

    /**
     * Retrieve a command by name
     *
     * @param string $name
     * @return CommandInterface|null
     */
    public function get(string $name): ?CommandInterface
    {
        return $this->commands[$name] ?? null;
    }

    /**
     * Return all registered commands
     *
     * @return array<string, CommandInterface>
     */
    public function all(): array
    {
        return $this->commands;
    }

    /**
     * Check if a command is registered
     *
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->commands[$name]);
    }
}
