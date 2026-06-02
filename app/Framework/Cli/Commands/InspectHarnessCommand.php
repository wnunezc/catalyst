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
use Catalyst\Framework\Module\ModuleHarnessInspector;

/**
 * inspect:harness CLI command.
 *
 * Responsibility: Runs the inspect:harness command to Inspect the per-module runtime harness matrix over real routes, assets and guards.
 *
 * @package Catalyst\Framework\Cli\Commands
 */
final class InspectHarnessCommand extends AbstractCommand
{
    /**
     * Returns the command name registered in the CLI registry.
     *
     * Responsibility: Returns the command name registered in the CLI registry.
     */
    public function getName(): string
    {
        return 'inspect:harness';
    }

    /**
     * Returns the short help text shown for this command.
     *
     * Responsibility: Returns the short help text shown for this command.
     */
    public function getDescription(): string
    {
        return 'Inspect the per-module runtime harness matrix over real routes, assets and guards';
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
            new Option(null, 'json', false, false, 'Render the harness inspection as JSON', false),
            new Option(null, 'module', null, false, 'Filter by module key', true),
            new Option(null, 'surface', null, false, 'Filter by harness surface', true),
        ];
    }

    /**
     * Runs the command workflow using parsed CLI arguments.
     *
     * Responsibility: Runs the command workflow using parsed CLI arguments.
     */
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
