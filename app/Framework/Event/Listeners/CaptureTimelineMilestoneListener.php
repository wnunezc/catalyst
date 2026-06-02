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
namespace Catalyst\Framework\Event\Listeners;

use Catalyst\Entities\EventEnvelope;
use Catalyst\Framework\Event\EventListenerInterface;
use Catalyst\Framework\Timeline\TimelineManager;
use DateTimeZone;

/**
 * Listener for capturing timeline milestone events.
 *
 * @package Catalyst\Framework\Event\Listeners
 * Responsibility: Records configured timeline events from event envelope payloads.
 */
final class CaptureTimelineMilestoneListener implements EventListenerInterface
{
    /**
     * Handles an event envelope.
     *
     * Responsibility: Handles an event envelope.
     */
    public function handle(EventEnvelope $event): void
    {
        TimelineManager::getInstance()->recordWorkflowTransitionMilestone([
            'resource_key' => $event->payload['resource_key'] ?? '',
            'record_id' => $event->payload['record_id'] ?? 0,
            'workflow_instance_id' => $event->payload['workflow_instance_id'] ?? 0,
            'transition_key' => $event->payload['transition_key'] ?? '',
            'from_state' => $event->payload['from_state'] ?? '',
            'to_state' => $event->payload['to_state'] ?? '',
            'occurred_at' => $event->occurredAt->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s'),
        ]);
    }
}
