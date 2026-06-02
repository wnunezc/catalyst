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
use Catalyst\Framework\Event\EventBus;
use Catalyst\Framework\Timeline\TimelineManager;
use Catalyst\Framework\Tenancy\TenancyManager;
use Throwable;

/**
 * timeline:smoke CLI command.
 *
 * Responsibility: Runs the timeline:smoke command to Exercise canonical PA-09 timeline start/stop/milestone semantics plus workflow-driven milestone capture.
 *
 * @package Catalyst\Framework\Cli\Commands
 */
final class TimelineSmokeCommand extends AbstractCommand
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
        return 'timeline:smoke';
    }

    /**
     * Returns the short help text shown for this command.
     *
     * Responsibility: Returns the short help text shown for this command.
     */
    public function getDescription(): string
    {
        return 'Exercise canonical PA-09 timeline start/stop/milestone semantics plus workflow-driven milestone capture';
    }

    /**
     * Runs the command workflow using parsed CLI arguments.
     *
     * Responsibility: Runs the command workflow using parsed CLI arguments.
     */
    public function execute(ArgumentBag $args): int
    {
        $json = (bool) ($args->getOptionValue('json') ?? false);
        $tenantId = TenancyManager::getInstance()->requireCurrentTenantId();
        $resourceKey = 'framework.timeline.smoke';
        $recordId = random_int(10000, 99999);
        $timeline = TimelineManager::getInstance();
        $result = ['success' => false, 'steps' => []];

        try {
            $timeline->start($resourceKey, $recordId, 'opened', 'Opened', '2026-05-20 10:00:00');
            $timeline->milestone($resourceKey, $recordId, 'reviewed', 'Reviewed', '2026-05-20 11:00:00');
            $timeline->stop($resourceKey, $recordId, 'resolved', 'Resolved', '2026-05-20 12:30:00');

            $summary = $timeline->timelineFor($resourceKey, $recordId);
            $result['steps'][] = [
                'step' => 'start-stop-milestone-semantics',
                'status' => ($summary['started_at'] ?? null) === '2026-05-20 10:00:00'
                    && ($summary['ended_at'] ?? null) === '2026-05-20 12:30:00'
                    && (int) ($summary['milestone_count'] ?? 0) === 1
                    ? 'ok'
                    : 'failed',
            ];
            $result['steps'][] = [
                'step' => 'elapsed-is-consistent',
                'status' => (int) ($summary['elapsed_seconds'] ?? 0) === 9000
                    && (string) ($summary['elapsed_iso8601'] ?? '') === 'PT2H30M0S'
                    ? 'ok'
                    : 'failed',
            ];

            EventBus::getInstance()->dispatch('framework.workflow.transition-completed', [
                'resource_key' => $resourceKey,
                'record_id' => $recordId,
                'workflow_instance_id' => 1,
                'transition_key' => 'approve',
                'from_state' => 'draft',
                'to_state' => 'approved',
            ]);

            $events = DatabaseManager::getInstance()->connection()->select(
                'SELECT event_key
                 FROM timeline_events
                 WHERE tenant_id = ?
                   AND resource_key = ?
                   AND record_id = ?
                 ORDER BY occurred_at ASC, id ASC',
                [$tenantId, $resourceKey, $recordId]
            ) ?: [];

            $eventKeys = array_map(static fn (array $row): string => (string) ($row['event_key'] ?? ''), $events);
            $result['steps'][] = [
                'step' => 'workflow-event-captures-milestone',
                'status' => in_array('workflow.approve', $eventKeys, true) ? 'ok' : 'failed',
            ];

            $result['success'] = !in_array('failed', array_column($result['steps'], 'status'), true);
        } catch (Throwable $e) {
            $result['error'] = $e->getMessage();
        } finally {
            try {
                DatabaseManager::getInstance()->connection()->execute(
                    'DELETE FROM timeline_events
                     WHERE tenant_id = ?
                       AND resource_key = ?
                       AND record_id = ?',
                    [$tenantId, $resourceKey, $recordId]
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
        $this->info('Timeline Smoke');
        $this->line('');

        foreach ((array) ($result['steps'] ?? []) as $step) {
            $this->line(sprintf('  %-32s %-8s', (string) ($step['step'] ?? 'step'), strtoupper((string) ($step['status'] ?? 'unknown'))));
        }

        $this->line('');

        if (!empty($result['success'])) {
            $this->success('Timeline smoke passed.');

            return 0;
        }

        $this->error((string) ($result['error'] ?? 'Timeline smoke failed.'));

        return 1;
    }
}
