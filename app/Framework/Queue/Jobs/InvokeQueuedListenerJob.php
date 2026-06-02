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
 * Invokes an event listener asynchronously through the queue.
 *
 * @package Catalyst\Framework\Queue\Jobs
 * Responsibility: Carries an event and listener identity across the queue boundary and dispatches the restored listener.
 */
final class InvokeQueuedListenerJob implements QueueableJobInterface
{
    /**
     * Initializes the Invoke Queued Listener Job instance.
     *
     * Responsibility: Initializes the Invoke Queued Listener Job instance.
     */
    public function __construct(
        private readonly string $listenerClass,
        private readonly EventEnvelope $event,
        private readonly string $queueNameOverride = 'events'
    ) {
    }

    /**
     * Resolves and invokes the queued listener for the stored event.
     *
     * Responsibility: Resolves and invokes the queued listener for the stored event.
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
     * Returns a diagnostic label containing the listener class.
     *
     * Responsibility: Returns a diagnostic label containing the listener class.
     */
    public function displayName(): string
    {
        return 'listener:' . $this->listenerClass;
    }

    /**
     * Returns the queue selected for asynchronous listeners.
     *
     * Responsibility: Returns the queue selected for asynchronous listeners.
     */
    public function queueName(): string
    {
        return $this->queueNameOverride;
    }

    /**
     * Returns the allowed listener-attempt count.
     *
     * Responsibility: Returns the allowed listener-attempt count.
     */
    public function maxAttempts(): int
    {
        return 3;
    }

    /**
     * Returns the retry delay for a failed listener invocation.
     *
     * Responsibility: Returns the retry delay for a failed listener invocation.
     */
    public function backoffSeconds(): int
    {
        return 15;
    }

    /**
     * Exports listener and event state for queue persistence.
     *
     * Responsibility: Exports listener and event state for queue persistence.
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
     * Restores a listener job from persisted state.
     *
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
