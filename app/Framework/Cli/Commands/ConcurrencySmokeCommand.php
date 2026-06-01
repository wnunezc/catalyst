<?php

declare(strict_types=1);

namespace Catalyst\Framework\Cli\Commands;

use Catalyst\Entities\RecordClaim;
use Catalyst\Framework\Argument\ArgumentBag;
use Catalyst\Framework\Argument\Option;
use Catalyst\Framework\Cli\AbstractCommand;
use Catalyst\Framework\Concurrency\RecordClaimManager;
use Catalyst\Framework\Concurrency\RecordClaimRepository;
use Catalyst\Helpers\Exceptions\OptimisticLockException;
use Throwable;

final class ConcurrencySmokeCommand extends AbstractCommand
{
    /** @return Option[] */
    public function getOptions(): array
    {
        return [
            new Option(null, 'resource', 'framework.concurrency.smoke', false, 'Probe resource key', true),
            new Option(null, 'record-id', null, false, 'Probe record ID (random by default)', true),
            new Option(null, 'json', false, false, 'Render as JSON', false),
        ];
    }

    public function getName(): string
    {
        return 'concurrency:smoke';
    }

    public function getDescription(): string
    {
        return 'Exercise optimistic locking plus claim reclaim on the canonical PA-01 runtime layer';
    }

    public function execute(ArgumentBag $args): int
    {
        $manager = RecordClaimManager::getInstance();
        $repository = RecordClaimRepository::getInstance();
        $resourceKey = trim((string) ($args->getOptionValue('resource') ?? 'framework.concurrency.smoke'));
        $recordId = (int) ($args->getOptionValue('record-id') ?? 0);
        $recordId = $recordId > 0 ? $recordId : random_int(100000, 999999);
        $json = (bool) ($args->getOptionValue('json') ?? false);

        $result = [
            'resource_key' => $resourceKey,
            'record_id' => $recordId,
            'steps' => [],
            'success' => false,
        ];

        try {
            $claimA = $manager->acquire(
                resourceKey: $resourceKey,
                recordId: $recordId,
                actorId: 101,
                actorLabel: 'smoke-a',
                ttlSeconds: 120,
                metadata: ['source' => 'concurrency:smoke', 'phase' => 'acquire-a']
            );
            $result['steps'][] = ['step' => 'claim-acquire-a', 'status' => 'ok', 'claim_id' => $claimA['id'] ?? null];

            $claimId = (int) ($claimA['id'] ?? 0);
            $first = RecordClaim::findOrFail($claimId);
            $second = RecordClaim::findOrFail($claimId);

            $first->fill(['release_reason' => 'smoke-first-update']);
            $first->save();
            $result['steps'][] = ['step' => 'optimistic-save-first', 'status' => 'ok'];

            try {
                $second->fill(['release_reason' => 'smoke-stale-update']);
                $second->save();
                $result['steps'][] = ['step' => 'optimistic-conflict', 'status' => 'failed'];
                throw new \RuntimeException('Expected an optimistic locking conflict, but the stale save succeeded.');
            } catch (OptimisticLockException $e) {
                $result['steps'][] = [
                    'step' => 'optimistic-conflict',
                    'status' => 'ok',
                    'message' => $e->getMessage(),
                ];
            }

            $expiring = RecordClaim::findOrFail($claimId);
            $expiring->fill(['expires_at' => date('Y-m-d H:i:s', time() - 5)]);
            $expiring->save();
            $result['steps'][] = ['step' => 'claim-expire', 'status' => 'ok'];

            $claimB = $manager->acquire(
                resourceKey: $resourceKey,
                recordId: $recordId,
                actorId: 202,
                actorLabel: 'smoke-b',
                ttlSeconds: 120,
                metadata: ['source' => 'concurrency:smoke', 'phase' => 'reclaim-b']
            );
            $result['steps'][] = [
                'step' => 'claim-reclaim-b',
                'status' => ($claimB['claimed_by'] ?? null) === 202 ? 'ok' : 'failed',
                'claimant' => $claimB['claimed_by_label'] ?? null,
            ];

            $released = $manager->release(
                resourceKey: $resourceKey,
                recordId: $recordId,
                actorId: 202,
                reason: 'concurrency smoke cleanup'
            );
            $result['steps'][] = ['step' => 'claim-release', 'status' => $released ? 'ok' : 'failed'];
            $result['claims'] = $repository->search([
                'resource_key' => $resourceKey,
                'record_id' => $recordId,
            ]);
            $result['success'] = true;
        } catch (Throwable $e) {
            $result['error'] = $e->getMessage();
        } finally {
            try {
                $manager->release(
                    resourceKey: $resourceKey,
                    recordId: $recordId,
                    actorId: 202,
                    reason: 'forced concurrency smoke cleanup',
                    force: true
                );
            } catch (Throwable) {
                // Best-effort cleanup only.
            }
        }

        if ($json) {
            $this->line((string) json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return !empty($result['success']) ? 0 : 1;
        }

        $this->line('');
        $this->info('Concurrency Smoke');
        $this->line('  Resource : ' . $resourceKey);
        $this->line('  Record   : ' . $recordId);
        $this->line('');

        foreach ((array) ($result['steps'] ?? []) as $step) {
            $label = (string) ($step['step'] ?? 'step');
            $status = strtoupper((string) ($step['status'] ?? 'unknown'));
            $message = (string) ($step['message'] ?? '');
            $this->line(sprintf('  %-24s %-8s %s', $label, $status, $message));
        }

        if (!empty($result['success'])) {
            $this->line('');
            $this->success('Concurrency smoke passed.');

            return 0;
        }

        $this->line('');
        $this->error((string) ($result['error'] ?? 'Concurrency smoke failed.'));

        return 1;
    }
}
