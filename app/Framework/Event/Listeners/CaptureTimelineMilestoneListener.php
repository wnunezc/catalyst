<?php

declare(strict_types=1);

namespace Catalyst\Framework\Event\Listeners;

use Catalyst\Entities\EventEnvelope;
use Catalyst\Framework\Event\EventListenerInterface;
use Catalyst\Framework\Timeline\TimelineManager;
use DateTimeZone;

final class CaptureTimelineMilestoneListener implements EventListenerInterface
{
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
