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
use Catalyst\Framework\Database\DatabaseManager;
use Catalyst\Framework\Idempotency\IdempotencyConflictException;
use Catalyst\Framework\Idempotency\IdempotencyInProgressException;
use Catalyst\Framework\Idempotency\IdempotencyManager;
use Catalyst\Framework\Idempotency\IdempotencyRepository;
use Catalyst\Framework\Tenancy\TenancyManager;
use Throwable;

/**
 * idempotency:smoke CLI command.
 *
 * Responsibility: Runs the idempotency:smoke command to Exercise canonical PA-12 replay, in-progress and conflict handling over persisted idempotency keys.
 *
 * @package Catalyst\Framework\Cli\Commands
 */
final class IdempotencySmokeCommand extends AbstractCommand
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
        return 'idempotency:smoke';
    }

    /**
     * Returns the short help text shown for this command.
     *
     * Responsibility: Returns the short help text shown for this command.
     */
    public function getDescription(): string
    {
        return 'Exercise canonical PA-12 replay, in-progress and conflict handling over persisted idempotency keys';
    }

    /**
     * Runs the command workflow using parsed CLI arguments.
     *
     * Responsibility: Runs the command workflow using parsed CLI arguments.
     */
    public function execute(ArgumentBag $args): int
    {
        $json = (bool) ($args->getOptionValue('json') ?? false);
        $manager = IdempotencyManager::getInstance();
        $repository = IdempotencyRepository::getInstance();
        $scopePrefix = 'smoke:' . gmdate('YmdHis') . ':' . random_int(1000, 9999);
        $tenantId = TenancyManager::getInstance()->requireCurrentTenantId();
        $result = [
            'success' => false,
            'steps' => [],
        ];

        try {
            $callbackRuns = 0;
            $replayScope = $scopePrefix . ':replay';
            $replayKey = 'idem_replay';

            $first = $manager->execute(
                $replayScope,
                $replayKey,
                ['action' => 'run', 'record_id' => 10],
                function () use (&$callbackRuns): array {
                    $callbackRuns++;

                    return [
                        'ok' => true,
                        'status' => 200,
                        'message' => 'Executed once.',
                        'result' => ['execution' => 1],
                    ];
                }
            );
            $second = $manager->execute(
                $replayScope,
                $replayKey,
                ['action' => 'run', 'record_id' => 10],
                function () use (&$callbackRuns): array {
                    $callbackRuns++;

                    return [
                        'ok' => true,
                        'status' => 200,
                        'message' => 'Should not run twice.',
                        'result' => ['execution' => 2],
                    ];
                }
            );

            $result['steps'][] = [
                'step' => 'replay-stable-result',
                'status' => ($first['replayed'] ?? true) === false
                    && ($second['replayed'] ?? false) === true
                    && $callbackRuns === 1
                    && (($second['outcome']['result']['execution'] ?? null) === 1)
                    ? 'ok'
                    : 'failed',
            ];

            $pendingScope = $scopePrefix . ':pending';
            $pendingKey = 'idem_pending';
            $repository->create([
                'scope_key' => $pendingScope,
                'idempotency_key' => $pendingKey,
                'fingerprint_hash' => hash('sha256', json_encode(['action' => 'run', 'record_id' => 20], JSON_THROW_ON_ERROR)),
                'status' => 'pending',
            ]);

            try {
                $manager->execute(
                    $pendingScope,
                    $pendingKey,
                    ['action' => 'run', 'record_id' => 20],
                    fn (): array => ['ok' => true]
                );
                $result['steps'][] = ['step' => 'pending-detected', 'status' => 'failed'];
            } catch (IdempotencyInProgressException) {
                $result['steps'][] = ['step' => 'pending-detected', 'status' => 'ok'];
            }

            try {
                $manager->execute(
                    $replayScope,
                    $replayKey,
                    ['action' => 'run', 'record_id' => 999],
                    fn (): array => ['ok' => true]
                );
                $result['steps'][] = ['step' => 'conflict-detected', 'status' => 'failed'];
            } catch (IdempotencyConflictException) {
                $result['steps'][] = ['step' => 'conflict-detected', 'status' => 'ok'];
            }

            $result['success'] = !in_array('failed', array_column($result['steps'], 'status'), true);
        } catch (Throwable $e) {
            $result['error'] = $e->getMessage();
        } finally {
            try {
                DatabaseManager::getInstance()->connection()->execute(
                    'DELETE FROM idempotency_keys WHERE tenant_id = ? AND scope_key LIKE ?',
                    [$tenantId, $scopePrefix . '%']
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
        $this->info('Idempotency Smoke');
        $this->line('');

        foreach ((array) ($result['steps'] ?? []) as $step) {
            $this->line(sprintf(
                '  %-24s %-8s',
                (string) ($step['step'] ?? 'step'),
                strtoupper((string) ($step['status'] ?? 'unknown'))
            ));
        }

        $this->line('');

        if (!empty($result['success'])) {
            $this->success('Idempotency smoke passed.');

            return 0;
        }

        $this->error((string) ($result['error'] ?? 'Idempotency smoke failed.'));

        return 1;
    }
}
