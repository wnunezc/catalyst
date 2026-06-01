<?php

declare(strict_types=1);

namespace Catalyst\Framework\Cli\Commands;

use Catalyst\Framework\Argument\ArgumentBag;
use Catalyst\Framework\Argument\Option;
use Catalyst\Framework\Cli\AbstractCommand;
use Catalyst\Framework\FeatureFlag\FeatureFlagManager;

final class FeatureFlagsListCommand extends AbstractCommand
{
    /** @return Option[] */
    public function getOptions(): array
    {
        return [
            new Option(null, 'actor-user', null, false, 'Evaluate effective state for a user ID', true),
            new Option(null, 'actor-roles', '', false, 'Comma-separated role slugs for evaluation', true),
            new Option(null, 'json', false, false, 'Render as JSON', false),
        ];
    }

    public function getName(): string
    {
        return 'feature-flags:list';
    }

    public function getDescription(): string
    {
        return 'List feature flags with default and effective runtime state';
    }

    public function execute(ArgumentBag $args): int
    {
        $manager = FeatureFlagManager::getInstance();
        $userIdRaw = trim((string) ($args->getOptionValue('actor-user') ?? ''));
        $userId = $userIdRaw !== '' ? (int) $userIdRaw : null;
        $roles = array_values(array_filter(array_map(
            static fn (string $value): string => trim($value),
            explode(',', (string) ($args->getOptionValue('actor-roles') ?? ''))
        ), static fn (string $value): bool => $value !== ''));

        $rows = [];
        foreach ($manager->catalog() as $key => $definition) {
            $rows[] = [
                'key' => $key,
                'scope' => (string) ($definition['scope'] ?? 'runtime'),
                'default_enabled' => !empty($definition['enabled']),
                'effective_enabled' => $manager->isRuntimeEnabled($key, $userId, $roles),
                'read_only' => !empty($definition['read_only']),
                'managed_by' => (string) ($definition['managed_by'] ?? 'features.json'),
            ];
        }

        usort($rows, static fn (array $left, array $right): int => [$left['scope'], $left['key']] <=> [$right['scope'], $right['key']]);

        if ((bool) ($args->getOptionValue('json') ?? false)) {
            $this->line((string) json_encode([
                'actor_user' => $userId,
                'actor_roles' => $roles,
                'rows' => $rows,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return 0;
        }

        $this->line('');
        $this->info('Feature Flags');
        $this->line(str_repeat('-', 100));
        $this->line(sprintf('  %-38s %-14s %-9s %-9s %s', 'Key', 'Scope', 'Default', 'Effective', 'Owner'));
        $this->line(str_repeat('-', 100));

        foreach ($rows as $row) {
            $this->line(sprintf(
                '  %-38s %-14s %-9s %-9s %s',
                $row['key'],
                $row['scope'],
                $row['default_enabled'] ? 'on' : 'off',
                $row['effective_enabled'] ? 'on' : 'off',
                $row['managed_by']
            ));
        }

        $this->line(str_repeat('-', 100));
        $this->success(sprintf('%d flag(s) listed.', count($rows)));
        $this->line('');

        return 0;
    }
}
