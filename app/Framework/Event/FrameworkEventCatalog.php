<?php

declare(strict_types=1);

namespace Catalyst\Framework\Event;

use Catalyst\Framework\Event\Listeners\CaptureAuditEventListener;
use Catalyst\Framework\Event\Listeners\CaptureTimelineMilestoneListener;
use Catalyst\Framework\Event\Listeners\DeliverNotificationListener;
use Catalyst\Framework\Event\Listeners\ProcessAutomationEventListener;

final class FrameworkEventCatalog
{
    private static bool $registered = false;

    public static function registerDefaults(EventBus $bus): void
    {
        if (self::$registered) {
            return;
        }

        $bus->listen('framework.notification.dispatch', DeliverNotificationListener::class);
        $bus->listen('framework.notification.dispatch.async', DeliverNotificationListener::class, queued: true, queueName: 'notifications');
        $bus->listen('framework.notification.delivered', CaptureAuditEventListener::class);
        $bus->listen('framework.queue.job-dispatched', CaptureAuditEventListener::class);
        $bus->listen('framework.queue.job-processed', CaptureAuditEventListener::class);
        $bus->listen('framework.queue.job-failed', CaptureAuditEventListener::class);
        $bus->listen('framework.queue.job-released', CaptureAuditEventListener::class);
        $bus->listen('framework.schedule.task-queued', CaptureAuditEventListener::class);
        $bus->listen('framework.workflow.transition-requested', CaptureAuditEventListener::class);
        $bus->listen('framework.workflow.transition-completed', CaptureAuditEventListener::class);
        $bus->listen('framework.workflow.transition-completed', CaptureTimelineMilestoneListener::class);
        $bus->listen('framework.automation.rule-executed', CaptureAuditEventListener::class);
        $bus->listen('framework.automation.rule-failed', CaptureAuditEventListener::class);
        $bus->listen('framework.timeline.started', CaptureAuditEventListener::class);
        $bus->listen('framework.timeline.milestone-recorded', CaptureAuditEventListener::class);
        $bus->listen('framework.timeline.stopped', CaptureAuditEventListener::class);
        $bus->listen('*', ProcessAutomationEventListener::class, queued: true, queueName: 'automation');

        self::$registered = true;
    }
}
