<?php

declare(strict_types=1);

namespace Catalyst\Framework\Cli\Commands;

use Catalyst\Framework\Argument\ArgumentBag;
use Catalyst\Framework\Argument\Option;
use Catalyst\Framework\Cli\AbstractCommand;
use Catalyst\Framework\Concurrency\RecordClaimManager;
use RuntimeException;

final class ClaimsReleaseCommand extends AbstractCommand
{
    /** @return Option[] */
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

    public function getName(): string
    {
        return 'claims:release';
    }

    public function getDescription(): string
    {
        return 'Release one reusable record claim';
    }

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
