<?php

declare(strict_types=1);

namespace Catalyst\Framework\Event\Listeners;

use Catalyst\Entities\EventEnvelope;
use Catalyst\Framework\Audit\AuditLogManager;
use Catalyst\Framework\Event\EventListenerInterface;

final class CaptureAuditEventListener implements EventListenerInterface
{
    public function handle(EventEnvelope $event): void
    {
        AuditLogManager::getInstance()->recordFrameworkEvent($event);
    }
}
