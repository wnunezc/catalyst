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
 * Defines the Dev Tools Disable Command class contract.
 *
 * @package Catalyst\Framework\Cli\Commands
 * Responsibility: Coordinates the dev tools disable command behavior within its module boundary.
 */
class DevToolsDisableCommand extends AbstractCommand
{
    /**
     * Returns the name value.
     */
    public function getName(): string
    {
        return 'devtools:disable';
    }

    /**
     * Returns the description value.
     */
    public function getDescription(): string
    {
        return 'Disable debug-oriented DevTools runtime flags in app and logging config';
    }

    /** @return Option[] */
    public function getOptions(): array
    {
        return [
            new Option(null, 'dry-run', false, false, 'Preview the resulting flags without writing config files', false),
        ];
    }

    /**
     * Executes the service workflow.
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
