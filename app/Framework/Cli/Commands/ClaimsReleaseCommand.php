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
use Catalyst\Framework\Concurrency\RecordClaimManager;
use RuntimeException;

/**
 * claims:release CLI command.
 *
 * Responsibility: Runs the claims:release command to Release one reusable record claim.
 *
 * @package Catalyst\Framework\Cli\Commands
 */
final class ClaimsReleaseCommand extends AbstractCommand
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
            new Option(null, 'resource', '', true, 'Resource key to release', true),
            new Option(null, 'record-id', null, true, 'Record ID to release', true),
            new Option(null, 'actor-id', null, false, 'Actor ID performing the release', true),
            new Option(null, 'reason', 'manual release', false, 'Release reason for audit', true),
            new Option(null, 'force', false, false, 'Force release even if another actor owns the claim', false),
            new Option(null, 'json', false, false, 'Render as JSON', false),
        ];
    }

    /**
     * Returns the command name registered in the CLI registry.
     *
     * Responsibility: Returns the command name registered in the CLI registry.
     */
    public function getName(): string
    {
        return 'claims:release';
    }

    /**
     * Returns the short help text shown for this command.
     *
     * Responsibility: Returns the short help text shown for this command.
     */
    public function getDescription(): string
    {
        return 'Release one reusable record claim';
    }

    /**
     * Runs the command workflow using parsed CLI arguments.
     *
     * Responsibility: Runs the command workflow using parsed CLI arguments.
     */
    public function execute(ArgumentBag $args): int
    {
        $manager = RecordClaimManager::getInstance();
        $resourceKey = trim((string) ($args->getOptionValue('resource') ?? ''));
        $recordId = (int) ($args->getOptionValue('record-id') ?? 0);
        $actorIdRaw = trim((string) ($args->getOptionValue('actor-id') ?? ''));
        $actorId = $actorIdRaw !== '' ? (int) $actorIdRaw : null;
        $force = (bool) ($args->getOptionValue('force') ?? false);
        $json = (bool) ($args->getOptionValue('json') ?? false);

        try {
            $released = $manager->release(
                resourceKey: $resourceKey,
                recordId: $recordId,
                actorId: $actorId,
                reason: (string) ($args->getOptionValue('reason') ?? 'manual release'),
                force: $force
            );
        } catch (RuntimeException $e) {
            if ($json) {
                $this->line((string) json_encode([
                    'released' => false,
                    'error' => $e->getMessage(),
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

                return 1;
            }

            $this->error($e->getMessage());

            return 1;
        }

        if ($json) {
            $this->line((string) json_encode([
                'released' => $released,
                'resource_key' => $resourceKey,
                'record_id' => $recordId,
                'force' => $force,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return $released ? 0 : 1;
        }

        if (!$released) {
            $this->warn(sprintf('No releasable claim was found for %s#%d.', $resourceKey, $recordId));

            return 1;
        }

        $this->success(sprintf('Claim released for %s#%d.', $resourceKey, $recordId));

        return 0;
    }
}
