<?php

declare(strict_types=1);

namespace Catalyst\Framework\Cli\Commands;

use Catalyst\Framework\Argument\ArgumentBag;
use Catalyst\Framework\Argument\Option;
use Catalyst\Framework\Cli\AbstractCommand;
use Catalyst\Framework\Plugin\PluginManager;
use RuntimeException;

final class PluginToggleCommand extends AbstractCommand
{
    /** @return Option[] */
    public function getOptions(): array
    {
        return [
            new Option(null, 'plugin', null, true, 'Plugin key to toggle', true),
            new Option(null, 'enabled', null, true, 'Target state: 1/0, true/false, on/off', true),
        ];
    }

    public function getName(): string
    {
        return 'plugin:toggle';
    }

    public function getDescription(): string
    {
        return 'Enable or disable a plugin manifest at runtime';
    }

    public function execute(ArgumentBag $args): int
    {
        $pluginKey = trim((string) ($args->getOptionValue('plugin') ?? ''));
        if ($pluginKey === '') {
            $this->error('Option --plugin is required.');

            return 1;
        }

        try {
            $enabled = $this->normalizeBoolean($args->getOptionValue('enabled'));
            PluginManager::getInstance()->setEnabled($pluginKey, $enabled);
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return 1;
        }

        $this->success(sprintf('Plugin "%s" set to %s.', $pluginKey, $enabled ? 'enabled' : 'disabled'));
        $this->line('');

        return 0;
    }

    private function normalizeBoolean(mixed $value): bool
    {
        $normalized = strtolower(trim((string) $value));

        return match ($normalized) {
            '1', 'true', 'on', 'yes' => true,
            '0', 'false', 'off', 'no' => false,
            default => throw new RuntimeException('Option --enabled must be one of: 1, 0, true, false, on, off.'),
        };
    }
}
