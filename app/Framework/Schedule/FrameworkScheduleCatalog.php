<?php

declare(strict_types=1);

namespace Catalyst\Framework\Schedule;

use Catalyst\Entities\ScheduledTask;
use Catalyst\Framework\Automation\Jobs\RunScheduledAutomationRulesJob;
use Catalyst\Framework\Queue\Jobs\PruneQueueHistoryJob;
use Catalyst\Framework\Retention\Jobs\RunRetentionPoliciesJob;

final class FrameworkScheduleCatalog
{
    private static bool $registered = false;

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
