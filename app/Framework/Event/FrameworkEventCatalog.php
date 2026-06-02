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

namespace Catalyst\Framework\Event;

use Catalyst\Framework\Event\Listeners\CaptureAuditEventListener;
use Catalyst\Framework\Event\Listeners\CaptureTimelineMilestoneListener;
use Catalyst\Framework\Event\Listeners\DeliverNotificationListener;
use Catalyst\Framework\Event\Listeners\ProcessAutomationEventListener;

/**
 * Catalog of built-in framework event listener registrations.
 *
 * @package Catalyst\Framework\Event
 * Responsibility: Registers default audit, timeline, notification, and automation event listeners on the event bus.
 */
final class FrameworkEventCatalog
{
    private static bool $registered = false;

    /**
     * Registers the requested definition.
     */
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
