<?php

declare(strict_types=1);

namespace Catalyst\Framework\Cli\Commands;

use Catalyst\Framework\Argument\ArgumentBag;
use Catalyst\Framework\Argument\Option;
use Catalyst\Framework\Cli\AbstractCommand;
use Catalyst\Helpers\Config\ConfigManager;

class DevToolsDisableCommand extends AbstractCommand
{
    public function getName(): string
    {
        return 'devtools:disable';
    }

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
