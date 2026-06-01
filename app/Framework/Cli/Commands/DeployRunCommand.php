<?php

declare(strict_types=1);

namespace Catalyst\Framework\Cli\Commands;

use Catalyst\Framework\Argument\ArgumentBag;
use Catalyst\Framework\Argument\Option;
use Catalyst\Framework\Cli\AbstractCommand;
use Catalyst\Framework\Deployment\DeploymentManager;
use RuntimeException;

final class DeployRunCommand extends AbstractCommand
{
    /** @return Option[] */
    public function getOptions(): array
    {
        return [
            new Option(null, 'profile', null, true, 'Deployment profile key', true),
            new Option(null, 'dry-run', false, false, 'Run preflight only', false),
        ];
    }

    public function getName(): string
    {
        return 'deploy:run';
    }

    public function getDescription(): string
    {
        return 'Execute the formal deployment pipeline for a configured profile';
    }

    public function execute(ArgumentBag $args): int
    {
        $profile = trim((string) ($args->getOptionValue('profile') ?? ''));
        if ($profile === '') {
            $this->error('Option --profile is required.');

            return 1;
        }

        try {
            $result = DeploymentManager::getInstance()->run(
                $profile,
                (bool) ($args->getOptionValue('dry-run') ?? false)
            );
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return 1;
        }

        $summary = (array) ($result['preflight']['summary'] ?? []);

        $this->line('');
        $this->success('Deployment pipeline completed.');
        $this->line('  Release:   ' . (string) ($result['release_id'] ?? ''));
        $this->line('  Profile:   ' . (string) ($result['profile_key'] ?? ''));
        $this->line('  Dry run:   ' . (!empty($result['dry_run']) ? 'yes' : 'no'));
        $this->line('  Artifact:  ' . (string) ($result['artifact_path'] ?? ''));
        $this->line('  ZIP:       ' . (string) ($result['zip_path'] ?? 'n/a'));
        $this->line('  Remote:    ' . (string) ($result['remote_path'] ?? 'n/a'));
        $this->line('  Preflight: checks=' . (int) ($summary['checks'] ?? 0)
            . ', warnings=' . (int) ($summary['warnings'] ?? 0)
            . ', failures=' . (int) ($summary['failures'] ?? 0)
            . ', route_issues=' . (int) ($summary['route_issues'] ?? 0));
        $this->line('');

        return 0;
    }
}
