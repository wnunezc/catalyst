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

namespace Catalyst\Framework\Event;

use Catalyst\Entities\EventEnvelope;
use Catalyst\Framework\Queue\Jobs\InvokeQueuedListenerJob;
use Catalyst\Framework\Queue\QueueManager;
use Catalyst\Framework\Traits\SingletonTrait;
use Catalyst\Helpers\Log\Logger;
use Closure;
use RuntimeException;

/**
 * Defines the Event Bus class contract.
 *
 * @package Catalyst\Framework\Event
 * Responsibility: Coordinates the event bus behavior within its module boundary.
 */
final class EventBus
{
    use SingletonTrait;

    /** @var array<string, array<int, EventListenerDefinition>> */
    private array $listeners = [];

    private Logger $logger;

    /**
     * Initializes the Event Bus instance.
     */
    protected function __construct()
    {
        $this->logger = Logger::getInstance();
        FrameworkEventCatalog::registerDefaults($this);
    }

    /**
     * Handles the listen workflow.
     */
    public function listen(
        string $eventName,
        callable|string $listener,
        bool $queued = false,
        string $queueName = 'default'
    ): self {
        $this->listeners[$eventName] ??= [];
        $this->listeners[$eventName][] = new EventListenerDefinition($listener, $queued, $queueName);

        return $this;
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $meta
     */
    public function dispatch(EventEnvelope|string $event, array $payload = [], array $meta = []): EventEnvelope
    {
        $envelope = $event instanceof EventEnvelope
            ? $event
            : EventEnvelope::named($event, $payload, $meta);

        $listeners = array_merge(
            $this->listeners[$envelope->name] ?? [],
            $this->listeners['*'] ?? []
        );

        $this->logger->debug('Event dispatched', [
            'event' => $envelope->name,
            'event_id' => $envelope->id,
            'listeners' => count($listeners),
        ]);

        foreach ($listeners as $definition) {
            if ($this->shouldSkipQueuedWildcardListener($definition, $envelope->name)) {
                continue;
            }

            if ($definition->queued) {
                if (!is_string($definition->listener)) {
                    throw new RuntimeException('Queued event listeners must be declared as class strings.');
                }

                QueueManager::getInstance()->dispatch(
                    new InvokeQueuedListenerJob($definition->listener, $envelope, $definition->queueName),
                    queueName: $definition->queueName
                );

                continue;
            }

            $this->invoke($definition->listener, $envelope);
        }

        return $envelope;
    }

    /**
     * @return array<string, array<int, EventListenerDefinition>>
     */
    public function listeners(): array
    {
        return $this->listeners;
    }

    /**
     * Determines whether should Skip Queued Wildcard Listener.
     */
    private function shouldSkipQueuedWildcardListener(EventListenerDefinition $definition, string $eventName): bool
    {
        if (!$definition->queued || !is_string($definition->listener)) {
            return false;
        }

        if ($definition->listener !== \Catalyst\Framework\Event\Listeners\ProcessAutomationEventListener::class) {
            return false;
        }

        return str_starts_with($eventName, 'framework.queue.');
    }

    /**
     * Handles the invoke workflow.
     */
    private function invoke(callable|string $listener, EventEnvelope $event): void
    {
        if ($listener instanceof Closure || is_callable($listener)) {
            $listener($event);
            return;
        }

        if (!is_string($listener) || !class_exists($listener)) {
            throw new RuntimeException('Event listener is not resolvable: ' . (is_string($listener) ? $listener : gettype($listener)));
        }

        $instance = new $listener();

        if ($instance instanceof EventListenerInterface) {
            $instance->handle($event);
            return;
        }

        if (is_callable($instance)) {
            $instance($event);
            return;
        }

        if (method_exists($instance, 'handle')) {
            $instance->handle($event);
            return;
        }

        throw new RuntimeException('Event listener must implement EventListenerInterface, be invokable, or expose handle().');
    }
}
