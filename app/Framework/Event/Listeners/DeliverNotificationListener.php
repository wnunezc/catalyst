<?php

declare(strict_types=1);

namespace Catalyst\Framework\Event\Listeners;

use Catalyst\Entities\EventEnvelope;
use Catalyst\Entities\NotificationDispatch;
use Catalyst\Framework\Event\EventListenerInterface;
use Catalyst\Framework\Notification\NotificationManager;
use RuntimeException;

final class DeliverNotificationListener implements EventListenerInterface
{
    public function handle(EventEnvelope $event): void
    {
        $payload = $event->payload;

        if (isset($payload['notification']) && is_array($payload['notification'])) {
            $payload = $payload['notification'];
        }

        $notification = NotificationDispatch::fromArray($payload);

        if ($notification->userId <= 0 || $notification->title === '') {
            throw new RuntimeException('Notification event payload is incomplete.');
        }

        NotificationManager::getInstance()->send($notification);
    }
}
