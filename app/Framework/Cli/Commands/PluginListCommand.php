<?php

declare(strict_types=1);

namespace Catalyst\Framework\Cli\Commands;

use Catalyst\Framework\Argument\ArgumentBag;
use Catalyst\Framework\Argument\Option;
use Catalyst\Framework\Cli\AbstractCommand;
use Catalyst\Framework\Plugin\PluginManager;

final class PluginListCommand extends AbstractCommand
{
    /** @return Option[] */
    public function getOptions(): array
    {
        return [
            new Option(null, 'json', false, false, 'Render as JSON', false),
        ];
    }

    public function getName(): string
    {
        return 'plugin:list';
    }

    public function getDescription(): string
    {
        return 'List registered plugin manifests and runtime state';
    }

    public function execute(ArgumentBag $args): int
    {
        $rows = PluginManager::getInstance()->all();

        if ((bool) ($args->getOptionValue('json') ?? false)) {
            $this->line((string) json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return 0;
        }

        $this->line('');
        $this->info('Plugins');
        $this->line(str_repeat('-', 110));
        $this->line(sprintf('  %-24s %-10s %-9s %-9s %s', 'Key', 'Version', 'State', 'Modules', 'Manifest'));
        $this->line(str_repeat('-', 110));

        foreach ($rows as $plugin) {
            $this->line(sprintf(
                '  %-24s %-10s %-9s %-9d %s',
                (string) ($plugin['key'] ?? ''),
                (string) ($plugin['version'] ?? ''),
                !empty($plugin['enabled']) ? 'enabled' : 'disabled',
                count((array) ($plugin['modules'] ?? [])),
                !empty($plugin['manifest_valid']) ? 'valid' : 'invalid'
            ));
        }

        $this->line(str_repeat('-', 110));
        $this->success(sprintf('%d plugin(s) listed.', count($rows)));
        $this->line('');

        return 0;
    }
}
