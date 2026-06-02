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
use Catalyst\Framework\Deployment\DeploymentRunRepository;

/**
 * Defines the Deploy List Command class contract.
 *
 * @package Catalyst\Framework\Cli\Commands
 * Responsibility: Coordinates the deploy list command behavior within its module boundary.
 */
final class DeployListCommand extends AbstractCommand
{
    /** @return Option[] */
    public function getOptions(): array
    {
        return [
            new Option(null, 'json', false, false, 'Render as JSON', false),
        ];
    }

    /**
     * Returns the name value.
     */
    public function getName(): string
    {
        return 'deploy:list';
    }

    /**
     * Returns the description value.
     */
    public function getDescription(): string
    {
        return 'List deployment profiles and recent runs';
    }

    /**
     * Executes the service workflow.
     */
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
