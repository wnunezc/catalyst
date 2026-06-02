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

namespace Catalyst\Framework\Cli;

use Catalyst\Framework\Traits\SingletonTrait;

/**
 * Registry for CLI command instances.
 *
 * Responsibility: Stores command instances by name and exposes lookup operations for the CLI kernel.
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
     * Stores a command instance under its CLI command name.
     *
     * Responsibility: Stores a command instance under its CLI command name.
     */
    public function register(CommandInterface $command): self
    {
        $this->commands[$command->getName()] = $command;
        return $this;
    }

    /**
     * Returns a registered command instance by name.
     *
     * Responsibility: Returns a registered command instance by name.
     */
    public function get(string $name): ?CommandInterface
    {
        return $this->commands[$name] ?? null;
    }

    /**
     * Returns all registered command instances indexed by name.
     *
     * Responsibility: Returns all registered command instances indexed by name.
     * @return array<string, CommandInterface>
     */
    public function all(): array
    {
        return $this->commands;
    }

    /**
     * Reports whether a command name is already registered.
     *
     * Responsibility: Reports whether a command name is already registered.
     */
    public function has(string $name): bool
    {
        return isset($this->commands[$name]);
    }
}
