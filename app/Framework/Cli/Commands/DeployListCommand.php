<?php

declare(strict_types=1);

namespace Catalyst\Framework\Cli\Commands;

use Catalyst\Framework\Argument\ArgumentBag;
use Catalyst\Framework\Argument\Option;
use Catalyst\Framework\Cli\AbstractCommand;
use Catalyst\Framework\Deployment\DeploymentManager;
use Catalyst\Framework\Deployment\DeploymentRunRepository;

final class DeployListCommand extends AbstractCommand
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
        return 'deploy:list';
    }

    public function getDescription(): string
    {
        return 'List deployment profiles and recent runs';
    }

    public function execute(ArgumentBag $args): int
    {
        $payload = [
            'profiles' => DeploymentManager::getInstance()->profiles(),
            'recent_runs' => array_slice(DeploymentRunRepository::getInstance()->all(), 0, 10),
        ];

        if ((bool) ($args->getOptionValue('json') ?? false)) {
            $this->line((string) json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return 0;
        }

        $this->line('');
        $this->info('Deployment Profiles');
        $this->line(str_repeat('-', 90));

        foreach ((array) $payload['profiles'] as $key => $profile) {
            $this->line(sprintf(
                '  %-20s zip=%-3s remote=%-3s %s',
                (string) $key,
                !empty($profile['create_zip']) ? 'yes' : 'no',
                !empty($profile['publish_remote']) ? 'yes' : 'no',
                (string) ($profile['description'] ?? '')
            ));
        }

        $this->line(str_repeat('-', 90));
        $this->info('Recent Runs');
        $this->line(str_repeat('-', 90));

        foreach ((array) $payload['recent_runs'] as $run) {
            $this->line(sprintf(
                '  %-24s %-18s %-12s %s',
                (string) ($run['release_id'] ?? ''),
                (string) ($run['profile_key'] ?? ''),
                (string) ($run['status'] ?? ''),
                (string) ($run['started_at'] ?? '')
            ));
        }

        $this->line('');

        return 0;
    }
}
