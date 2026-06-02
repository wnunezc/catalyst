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
use Catalyst\Framework\Module\ModuleInspector;

/**
 * Defines the Inspect Modules Command class contract.
 *
 * @package Catalyst\Framework\Cli\Commands
 * Responsibility: Coordinates the inspect modules command behavior within its module boundary.
 */
final class InspectModulesCommand extends AbstractCommand
{
    /**
     * Returns the name value.
     */
    public function getName(): string
    {
        return 'inspect:modules';
    }

    /**
     * Returns the description value.
     */
    public function getDescription(): string
    {
        return 'Inspect registered modules, metadata, routes, assets and registry coverage';
    }

    /** @return Option[] */
    public function getOptions(): array
    {
        return [
            new Option('s', 'scope', null, false, 'Filter by scope: App or Framework', true),
            new Option(null, 'json', false, false, 'Render the inspection as JSON', false),
        ];
    }

    /**
     * Executes the service workflow.
     */
    public function execute(ArgumentBag $args): int
    {
        $report = (new ModuleInspector())->inspect();
        $scopeFilter = trim((string) ($args->getOptionValue('scope') ?? $args->getOptionValue('s') ?? ''));
        $asJson = (bool) ($args->getOptionValue('json') ?? false);

        if ($scopeFilter !== '') {
            $report['modules'] = array_values(array_filter(
                (array) ($report['modules'] ?? []),
                static fn (array $module): bool => strcasecmp((string) ($module['scope'] ?? ''), $scopeFilter) === 0
            ));
            $report['module_count'] = count((array) ($report['modules'] ?? []));
        }

        if ($asJson) {
            $this->line((string) json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            return 0;
        }

        $this->line('');
        $this->info('Module Inspector');
        $this->line(str_repeat('-', 150));
        $this->line(sprintf(
            '  %-22s %-10s %-14s %-7s %-8s %-8s %-10s %s',
            'Key',
            'Scope',
            'Slug',
            'Routes',
            'Views',
            'Assets',
            'Manifest',
            'Description'
        ));
        $this->line(str_repeat('-', 150));

        foreach ((array) ($report['modules'] ?? []) as $module) {
            $assets = (array) ($module['assets'] ?? []);
            $published = (array) ($assets['published'] ?? []);
            $assetsOk = ($published['style_exists'] ?? false) && ($published['script_exists'] ?? false);
            $this->line(sprintf(
                '  %-22s %-10s %-14s %-7d %-8s %-8s %-10s %s',
                (string) ($module['key'] ?? '-'),
                (string) ($module['scope'] ?? '-'),
                (string) ($module['slug'] ?? '-'),
                count((array) ($module['routes']['owned'] ?? [])),
                ($module['views']['has_views'] ?? false) ? 'yes' : 'no',
                $assetsOk ? 'ok' : 'issues',
                ($module['manifest_exists'] ?? false)
                    ? (($module['manifest_valid'] ?? true) ? 'valid' : 'invalid')
                    : 'none',
                (string) ($module['description'] ?? '')
            ));
        }

        $this->line(str_repeat('-', 150));
        $this->success(sprintf('%d module(s) inspected.', (int) ($report['module_count'] ?? 0)));
        $this->line('');

        return 0;
    }
}
