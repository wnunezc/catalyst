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
use Catalyst\Framework\Deployment\DeploymentManager;
use RuntimeException;

/**
 * deploy:run CLI command.
 *
 * Responsibility: Runs the deploy:run command to Execute the formal deployment pipeline for a configured profile.
 *
 * @package Catalyst\Framework\Cli\Commands
 */
final class DeployRunCommand extends AbstractCommand
{
    /**
     * Defines the accepted option schema for this command.
     *
     * Responsibility: Defines the accepted option schema for this command.
     * @return Option[]
     */
    public function getOptions(): array
    {
        return [
            new Option(null, 'profile', null, true, 'Deployment profile key', true),
            new Option(null, 'dry-run', false, false, 'Run preflight only', false),
        ];
    }

    /**
     * Returns the command name registered in the CLI registry.
     *
     * Responsibility: Returns the command name registered in the CLI registry.
     */
    public function getName(): string
    {
        return 'deploy:run';
    }

    /**
     * Returns the short help text shown for this command.
     *
     * Responsibility: Returns the short help text shown for this command.
     */
    public function getDescription(): string
    {
        return 'Execute the formal deployment pipeline for a configured profile';
    }

    /**
     * Runs the command workflow using parsed CLI arguments.
     *
     * Responsibility: Runs the command workflow using parsed CLI arguments.
     */
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
