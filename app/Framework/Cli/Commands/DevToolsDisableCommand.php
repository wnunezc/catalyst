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
use Catalyst\Framework\Argument\Option;
use Catalyst\Framework\Cli\AbstractCommand;
use Catalyst\Helpers\Config\ConfigManager;

/**
 * devtools:disable CLI command.
 *
 * Responsibility: Runs the devtools:disable command to Disable debug-oriented DevTools runtime flags in app and logging config.
 *
 * @package Catalyst\Framework\Cli\Commands
 */
class DevToolsDisableCommand extends AbstractCommand
{
    /**
     * Returns the command name registered in the CLI registry.
     *
     * Responsibility: Returns the command name registered in the CLI registry.
     */
    public function getName(): string
    {
        return 'devtools:disable';
    }

    /**
     * Returns the short help text shown for this command.
     *
     * Responsibility: Returns the short help text shown for this command.
     */
    public function getDescription(): string
    {
        return 'Disable debug-oriented DevTools runtime flags in app and logging config';
    }

    /**
     * Defines the accepted option schema for this command.
     *
     * Responsibility: Defines the accepted option schema for this command.
     * @return Option[]
     */
    public function getOptions(): array
    {
        return [
            new Option(null, 'dry-run', false, false, 'Preview the resulting flags without writing config files', false),
        ];
    }

    /**
     * Runs the command workflow using parsed CLI arguments.
     *
     * Responsibility: Runs the command workflow using parsed CLI arguments.
     */
    public function execute(ArgumentBag $args): int
    {
        $config   = ConfigManager::getInstance();
        $app      = $config->entry('app', 'project');
        $logging  = $config->entry('logging', 'logging');
        $dryRun   = (bool) ($args->getOptionValue('dry-run') ?? false);

        if ($dryRun) {
            $this->info('DevTools disable preview');
            $this->line('  app.project_debug    => false');
            $this->line('  logging.display_logs => false');
            $this->line('');

            return 0;
        }

        $config->writeSection('app', [
            'project' => array_replace($app, [
                'project_env'   => $config->getEnvironment(),
                'project_debug' => false,
            ]),
        ]);

        $config->writeSection('logging', [
            'logging' => array_replace($logging, [
                'display_logs' => false,
            ]),
        ]);

        $config->writeSection('devtools', [
            'devtools' => [
                'deprecated'   => true,
                'app_debug'    => false,
                'display_logs' => false,
            ],
        ]);

        $this->success('DevTools runtime flags disabled.');
        $this->line('  app.project_debug    = false');
        $this->line('  logging.display_logs = false');
        $this->line('');

        return 0;
    }
}
