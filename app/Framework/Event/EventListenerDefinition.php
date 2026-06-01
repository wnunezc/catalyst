<?php

declare(strict_types=1);

namespace Catalyst\Framework\Event;

final class EventListenerDefinition
{
    public readonly mixed $listener;

    public function __construct(
        mixed $listener,
        public readonly bool $queued = false,
        public readonly string $queueName = 'default'
    ) {
        $this->listener = $listener;
    }
}
