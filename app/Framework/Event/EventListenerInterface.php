<?php

declare(strict_types=1);

namespace Catalyst\Framework\Event;

use Catalyst\Entities\EventEnvelope;

interface EventListenerInterface
{
    public function handle(EventEnvelope $event): void;
}
