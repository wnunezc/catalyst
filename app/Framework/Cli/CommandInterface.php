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

use Catalyst\Framework\Argument\ArgumentBag;
use Catalyst\Framework\Argument\Option;
use Catalyst\Framework\Argument\Parameter;

/**
 * Contract for all CLI commands
 *
 * @package Catalyst\Framework\Cli
 */
interface CommandInterface
{
    /**
     * Command identifier (e.g. "make:controller")
     */
    public function getName(): string;

    /**
     * One-line description shown in help listing
     */
    public function getDescription(): string;

    /**
     * Option DTOs that define the accepted flags for this command
     *
     * @return Option[]
     */
    public function getOptions(): array;

    /**
     * Parameter DTOs that define the accepted positional args
     *
     * @return Parameter[]
     */
    public function getParameters(): array;

    /**
     * Run the command
     *
     * @param ArgumentBag $args Parsed arguments
     * @return int Exit code (0 = success, 1 = error)
     */
    public function execute(ArgumentBag $args): int;
}
