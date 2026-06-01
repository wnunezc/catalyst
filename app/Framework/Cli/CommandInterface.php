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
