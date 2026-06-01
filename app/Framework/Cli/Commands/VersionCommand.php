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

namespace Catalyst\Framework\Cli\Commands;

use Catalyst\Framework\Argument\ArgumentBag;
use Catalyst\Framework\Cli\AbstractCommand;

/**
 * Displays framework and PHP version information
 *
 * @package Catalyst\Framework\Cli\Commands
 */
class VersionCommand extends AbstractCommand
{
    private const FRAMEWORK_VERSION = '1.0.0-dev';

    public function getName(): string
    {
        return 'version';
    }

    public function getDescription(): string
    {
        return 'Display framework and PHP version';
    }

    public function execute(ArgumentBag $args): int
    {
        $this->line('');
        $this->info('Catalyst PHP Framework v' . self::FRAMEWORK_VERSION);
        $this->line('PHP Version : ' . PHP_VERSION);
        $this->line('Platform    : ' . PHP_OS);
        $this->line('SAPI        : ' . PHP_SAPI);
        $this->line('');

        return 0;
    }
}
