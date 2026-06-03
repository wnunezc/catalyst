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
use Catalyst\Framework\Release\ReleaseMetadata;

/**
 * version CLI command.
 *
 * Responsibility: Runs the version command to Display framework and PHP version.
 *
 * @package Catalyst\Framework\Cli\Commands
 */
class VersionCommand extends AbstractCommand
{
    /**
     * Returns the command name registered in the CLI registry.
     *
     * Responsibility: Returns the command name registered in the CLI registry.
     */
    public function getName(): string
    {
        return 'version';
    }

    /**
     * Returns the short help text shown for this command.
     *
     * Responsibility: Returns the short help text shown for this command.
     */
    public function getDescription(): string
    {
        return 'Display framework and PHP version';
    }

    /**
     * Runs the command workflow using parsed CLI arguments.
     *
     * Responsibility: Runs the command workflow using parsed CLI arguments.
     */
    public function execute(ArgumentBag $args): int
    {
        try {
            $release = ReleaseMetadata::local();
        } catch (\Throwable $e) {
            $this->error('Unable to read release metadata: ' . $e->getMessage());
            return 1;
        }

        $this->line('');
        $this->info('Catalyst PHP Framework v' . $release['version']);
        $this->line('Channel     : ' . $release['channel']);
        $this->line('Source      : ' . $release['source']);
        $this->line('PHP Version : ' . PHP_VERSION);
        $this->line('Platform    : ' . PHP_OS);
        $this->line('SAPI        : ' . PHP_SAPI);
        $this->line('');

        return 0;
    }
}
