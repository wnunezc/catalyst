<?php

declare(strict_types=1);

namespace Catalyst\Framework\Event\Listeners;

use Catalyst\Entities\EventEnvelope;
use Catalyst\Framework\Automation\AutomationManager;
use Catalyst\Framework\Event\EventListenerInterface;

final class ProcessAutomationEventListener implements EventListenerInterface
{
    public function handle(EventEnvelope $event): void
    {
        AutomationManager::getInstance()->processEvent($event);
    }
}
