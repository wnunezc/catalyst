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

use Catalyst\Entities\RecordClaim;
use Catalyst\Framework\Argument\ArgumentBag;
use Catalyst\Framework\Argument\Option;
use Catalyst\Framework\Cli\AbstractCommand;
use Catalyst\Framework\Concurrency\RecordClaimManager;
use Catalyst\Framework\Presence\RecordPresenceManager;
use Throwable;

/**
 * presence:smoke CLI command.
 *
 * Responsibility: Runs the presence:smoke command to Exercise canonical PA-08 claim-derived presence, heartbeat and reclaim semantics.
 *
 * @package Catalyst\Framework\Cli\Commands
 */
final class PresenceSmokeCommand extends AbstractCommand
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
            new Option(null, 'resource', 'framework.presence.smoke', false, 'Probe resource key', true),
            new Option(null, 'record-id', null, false, 'Probe record ID (random by default)', true),
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
        return 'presence:smoke';
    }

    /**
     * Returns the short help text shown for this command.
     *
     * Responsibility: Returns the short help text shown for this command.
     */
    public function getDescription(): string
    {
        return 'Exercise canonical PA-08 claim-derived presence, heartbeat and reclaim semantics';
    }

    /**
     * Runs the command workflow using parsed CLI arguments.
     *
     * Responsibility: Runs the command workflow using parsed CLI arguments.
     */
    public function execute(ArgumentBag $args): int
    {
        $resourceKey = trim((string) ($args->getOptionValue('resource') ?? 'framework.presence.smoke'));
        $recordId = (int) ($args->getOptionValue('record-id') ?? 0);
        $recordId = $recordId > 0 ? $recordId : random_int(100000, 999999);
        $json = (bool) ($args->getOptionValue('json') ?? false);

        $claims = RecordClaimManager::getInstance();
        $presence = RecordPresenceManager::getInstance();
        $result = [
            'resource_key' => $resourceKey,
            'record_id' => $recordId,
            'steps' => [],
            'success' => false,
        ];

        try {
            $claimA = $claims->acquire(
                resourceKey: $resourceKey,
                recordId: $recordId,
                actorId: 301,
                actorLabel: 'presence-a',
                ttlSeconds: 120,
                metadata: ['source' => 'presence:smoke', 'phase' => 'acquire-a']
            );
            $presenceA = $presence->presencePayload($claimA);
            $result['steps'][] = [
                'step' => 'presence-owner-visible',
                'status' => (($presenceA['claimed_by'] ?? null) === 301 && ($presenceA['status'] ?? null) === 'active') ? 'ok' : 'failed',
            ];

            try {
                $claims->acquire(
                    resourceKey: $resourceKey,
                    recordId: $recordId,
                    actorId: 302,
                    actorLabel: 'presence-b',
                    ttlSeconds: 120,
                    metadata: ['source' => 'presence:smoke', 'phase' => 'conflict-b']
                );
                $result['steps'][] = ['step' => 'presence-conflict-b', 'status' => 'failed'];
                throw new \RuntimeException('Expected actor B to be blocked by the active claim.');
            } catch (Throwable $e) {
                $result['steps'][] = [
                    'step' => 'presence-conflict-b',
                    'status' => str_contains($e->getMessage(), 'currently claimed') ? 'ok' : 'failed',
                    'message' => $e->getMessage(),
                ];
            }

            $heartbeat = $presence->heartbeat(
                resourceKey: $resourceKey,
                recordId: $recordId,
                actorId: 301,
                actorLabel: 'presence-a',
                ttlSeconds: 45,
                metadata: ['phase' => 'heartbeat-a']
            );
            $result['steps'][] = [
                'step' => 'presence-heartbeat-a',
                'status' => (($heartbeat['claimed_by'] ?? null) === 301 && (int) ($heartbeat['seconds_to_expiry'] ?? 0) <= 45) ? 'ok' : 'failed',
                'seconds_to_expiry' => $heartbeat['seconds_to_expiry'] ?? null,
            ];

            $claimId = (int) ($claimA['id'] ?? 0);
            $expiring = RecordClaim::findOrFail($claimId);
            $expiring->fill(['expires_at' => date('Y-m-d H:i:s', time() - 5)]);
            $expiring->save();
            $result['steps'][] = ['step' => 'presence-expire-a', 'status' => 'ok'];

            $heartbeatB = $presence->heartbeat(
                resourceKey: $resourceKey,
                recordId: $recordId,
                actorId: 302,
                actorLabel: 'presence-b',
                ttlSeconds: 45,
                metadata: ['phase' => 'heartbeat-b']
            );
            $result['steps'][] = [
                'step' => 'presence-reclaim-b',
                'status' => (($heartbeatB['claimed_by'] ?? null) === 302 && ($heartbeatB['status'] ?? null) === 'active') ? 'ok' : 'failed',
            ];

            $released = $claims->release(
                resourceKey: $resourceKey,
                recordId: $recordId,
                actorId: 302,
                reason: 'presence smoke cleanup'
            );
            $result['steps'][] = [
                'step' => 'presence-release-b',
                'status' => $released ? 'ok' : 'failed',
            ];

            foreach ($result['steps'] as $step) {
                if (($step['status'] ?? 'failed') !== 'ok') {
                    throw new \RuntimeException('Presence smoke assertion failed at step: ' . ($step['step'] ?? 'unknown'));
                }
            }

            $result['presence'] = $presence->presencePayload($claims->snapshot($resourceKey, $recordId));
            $result['success'] = true;
        } catch (Throwable $e) {
            $result['error'] = $e->getMessage();
        } finally {
            try {
                $claims->release(
                    resourceKey: $resourceKey,
                    recordId: $recordId,
                    actorId: 302,
                    reason: 'forced presence smoke cleanup',
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
        $this->info('Presence Smoke');
        $this->line('  Resource : ' . $resourceKey);
        $this->line('  Record   : ' . $recordId);
        $this->line('');

        foreach ((array) ($result['steps'] ?? []) as $step) {
            $this->line(sprintf(
                '  %-28s %-8s %s',
                (string) ($step['step'] ?? 'step'),
                strtoupper((string) ($step['status'] ?? 'unknown')),
                (string) ($step['message'] ?? '')
            ));
        }

        $this->line('');

        if (!empty($result['success'])) {
            $this->success('Presence smoke passed.');

            return 0;
        }

        $this->error((string) ($result['error'] ?? 'Presence smoke failed.'));

        return 1;
    }
}
