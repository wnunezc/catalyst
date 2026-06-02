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

    /**
     * Returns the name value.
     */
    public function getName(): string
    {
        return 'version';
    }

    /**
     * Returns the description value.
     */
    public function getDescription(): string
    {
        return 'Display framework and PHP version';
    }

    /**
     * Executes the service workflow.
     */
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
