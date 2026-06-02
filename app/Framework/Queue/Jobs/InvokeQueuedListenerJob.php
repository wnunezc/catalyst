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

namespace Catalyst\Framework\Queue\Jobs;

use Catalyst\Entities\EventEnvelope;
use Catalyst\Framework\Event\EventListenerInterface;
use Catalyst\Framework\Queue\QueueableJobInterface;
use RuntimeException;

/**
 * Defines the Invoke Queued Listener Job class contract.
 *
 * @package Catalyst\Framework\Queue\Jobs
 * Responsibility: Coordinates the invoke queued listener job behavior within its module boundary.
 */
final class InvokeQueuedListenerJob implements QueueableJobInterface
{
    /**
     * Initializes the Invoke Queued Listener Job instance.
     */
    public function __construct(
        private readonly string $listenerClass,
        private readonly EventEnvelope $event,
        private readonly string $queueNameOverride = 'events'
    ) {
    }

    /**
     * Handles the request workflow.
     */
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

    /**
     * Handles the display name workflow.
     */
    public function displayName(): string
    {
        return 'listener:' . $this->listenerClass;
    }

    /**
     * Handles the queue name workflow.
     */
    public function queueName(): string
    {
        return $this->queueNameOverride;
    }

    /**
     * Handles the max attempts workflow.
     */
    public function maxAttempts(): int
    {
        return 3;
    }

    /**
     * Handles the backoff seconds workflow.
     */
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
