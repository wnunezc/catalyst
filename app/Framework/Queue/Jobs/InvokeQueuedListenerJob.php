<?php

declare(strict_types=1);

namespace Catalyst\Framework\Queue\Jobs;

use Catalyst\Entities\EventEnvelope;
use Catalyst\Framework\Event\EventListenerInterface;
use Catalyst\Framework\Queue\QueueableJobInterface;
use RuntimeException;

final class InvokeQueuedListenerJob implements QueueableJobInterface
{
    public function __construct(
        private readonly string $listenerClass,
        private readonly EventEnvelope $event,
        private readonly string $queueNameOverride = 'events'
    ) {
    }

    public function handle(): void
    {
        if (!class_exists($this->listenerClass)) {
            throw new RuntimeException("Queued listener '{$this->listenerClass}' no longer exists.");
        }

        $listener = new $this->listenerClass();

        if ($listener instanceof EventListenerInterface) {
            $listener->handle($this->event);
            return;
        }

        if (is_callable($listener)) {
            $listener($this->event);
            return;
        }

        if (method_exists($listener, 'handle')) {
            $listener->handle($this->event);
            return;
        }

        throw new RuntimeException("Queued listener '{$this->listenerClass}' is not invokable.");
    }

    public function displayName(): string
    {
        return 'listener:' . $this->listenerClass;
    }

    public function queueName(): string
    {
        return $this->queueNameOverride;
    }

    public function maxAttempts(): int
    {
        return 3;
    }

    public function backoffSeconds(): int
    {
        return 15;
    }

    /**
     * @return array<string, mixed>
     */
    public function toPayload(): array
    {
        return [
            'listener_class' => $this->listenerClass,
            'event' => $this->event->toArray(),
            'queue_name' => $this->queueNameOverride,
        ];
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromPayload(array $payload): static
    {
        return new self(
            listenerClass: (string) ($payload['listener_class'] ?? ''),
            event: EventEnvelope::fromArray(isset($payload['event']) && is_array($payload['event']) ? $payload['event'] : []),
            queueNameOverride: trim((string) ($payload['queue_name'] ?? 'events')) ?: 'events'
        );
    }
}
