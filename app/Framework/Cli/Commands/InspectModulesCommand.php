<?php

declare(strict_types=1);

namespace Catalyst\Framework\Cli\Commands;

use Catalyst\Framework\Argument\ArgumentBag;
use Catalyst\Framework\Argument\Option;
use Catalyst\Framework\Cli\AbstractCommand;
use Catalyst\Framework\Module\ModuleInspector;

final class InspectModulesCommand extends AbstractCommand
{
    public function getName(): string
    {
        return 'inspect:modules';
    }

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
