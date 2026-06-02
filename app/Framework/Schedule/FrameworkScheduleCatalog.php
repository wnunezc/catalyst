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

namespace Catalyst\Framework\Schedule;

use Catalyst\Entities\ScheduledTask;
use Catalyst\Framework\Automation\Jobs\RunScheduledAutomationRulesJob;
use Catalyst\Framework\Queue\Jobs\PruneQueueHistoryJob;
use Catalyst\Framework\Retention\Jobs\RunRetentionPoliciesJob;

/**
 * Defines the Framework Schedule Catalog class contract.
 *
 * @package Catalyst\Framework\Schedule
 * Responsibility: Coordinates the framework schedule catalog behavior within its module boundary.
 */
final class FrameworkScheduleCatalog
{
    private static bool $registered = false;

    /**
     * Registers the requested definition.
     */
    public static function registerDefaults(ScheduleRegistry $registry): void
    {
        if (self::$registered) {
            return;
        }

        $registry->register(
            ScheduledTask::queuedJob(
                name: 'framework.queue.prune-history',
                expression: '15 3 * * *',
                job: new PruneQueueHistoryJob(),
                description: 'Prune failed queue jobs and old scheduler history.'
            )
        );
        $registry->register(
            ScheduledTask::queuedJob(
                name: 'framework.automation.run-due-rules',
                expression: '* * * * *',
                job: new RunScheduledAutomationRulesJob(),
                description: 'Evaluate due internal automation rules and queue their real actions.'
            )
        );
        $registry->register(
            ScheduledTask::queuedJob(
                name: 'framework.retention.run-policies',
                expression: '30 3 * * *',
                job: new RunRetentionPoliciesJob(),
                description: 'Archive and purge runtime artifacts through the canonical retention policy engine.'
            )
        );

        self::$registered = true;
    }
}
