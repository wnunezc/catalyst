<?php

declare(strict_types=1);

namespace Catalyst\Framework\Cli\Commands;

use Catalyst\Framework\Argument\ArgumentBag;
use Catalyst\Framework\Argument\Option;
use Catalyst\Framework\Cli\AbstractCommand;
use Catalyst\Framework\Module\ModuleHarnessInspector;

final class InspectHarnessCommand extends AbstractCommand
{
    public function getName(): string
    {
        return 'inspect:harness';
    }

    public function getDescription(): string
    {
        return 'Inspect the per-module runtime harness matrix over real routes, assets and guards';
    }

    /** @return Option[] */
    public function getOptions(): array
    {
        return [
            new Option(null, 'json', false, false, 'Render the harness inspection as JSON', false),
            new Option(null, 'module', null, false, 'Filter by module key', true),
            new Option(null, 'surface', null, false, 'Filter by harness surface', true),
        ];
    }

    public function execute(ArgumentBag $args): int
    {
        $report = (new ModuleHarnessInspector())->inspect();
        $moduleFilter = trim((string) ($args->getOptionValue('module') ?? ''));
        $surfaceFilter = trim((string) ($args->getOptionValue('surface') ?? ''));
        $asJson = (bool) ($args->getOptionValue('json') ?? false);

        $modules = array_values(array_filter(
            (array) ($report['modules'] ?? []),
            static function (array $module) use ($moduleFilter, $surfaceFilter): bool {
                if ($moduleFilter !== '' && strcasecmp((string) ($module['key'] ?? ''), $moduleFilter) !== 0) {
                    return false;
                }

                if ($surfaceFilter !== '' && strcasecmp((string) ($module['surface'] ?? ''), $surfaceFilter) !== 0) {
                    return false;
                }

                return true;
            }
        ));

        $report['modules'] = $modules;
        $report['module_count'] = count($modules);

        if ($asJson) {
            $this->line((string) json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            return 0;
        }

        $this->line('');
        $this->info('Module Harness Inspector');
        $this->line(str_repeat('-', 132));
        $this->line(sprintf(
            '  %-22s %-18s %-5s %-5s %-9s %-8s %-22s %s',
            'Key',
            'Surface',
            'HTML',
            'JSON',
            'Mutations',
            'Assets',
            'Representative HTML',
            'Representative JSON'
        ));
        $this->line(str_repeat('-', 132));

        foreach ($modules as $module) {
            $assets = (array) ($module['assets'] ?? []);
            $this->line(sprintf(
                '  %-22s %-18s %-5d %-5d %-9d %-8s %-22s %s',
                (string) ($module['key'] ?? ''),
                (string) ($module['surface'] ?? ''),
                (int) (($module['counts'] ?? [])['html'] ?? 0),
                (int) (($module['counts'] ?? [])['json'] ?? 0),
                (int) (($module['counts'] ?? [])['mutation'] ?? 0),
                !(bool) ($assets['expected'] ?? false) ? 'n/a' : ((bool) ($assets['ok'] ?? false) ? 'ok' : 'issues'),
                (string) (($module['representative'] ?? [])['html'] ?? 'n/a'),
                (string) (($module['representative'] ?? [])['json'] ?? 'n/a')
            ));
        }

        $this->line(str_repeat('-', 132));
        $this->success(sprintf('%d module harness definition(s) inspected.', (int) ($report['module_count'] ?? 0)));
        $this->line('');

        return 0;
    }
}
