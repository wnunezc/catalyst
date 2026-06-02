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

use Catalyst\Entities\NotificationDispatch;
use Catalyst\Framework\Notification\NotificationManager;
use Catalyst\Framework\Queue\QueueableJobInterface;

/**
 * Delivers a notification through the notification manager from the queue.
 *
 * @package Catalyst\Framework\Queue\Jobs
 * Responsibility: Carries notification state across the queue boundary and executes notification delivery.
 */
final class DispatchNotificationJob implements QueueableJobInterface
{
    /**
     * Initializes the Dispatch Notification Job instance.
     *
     * Responsibility: Initializes the Dispatch Notification Job instance.
     */
    public function __construct(
        private readonly NotificationDispatch $notification,
        private readonly string $queueNameOverride = 'notifications'
    ) {
    }

    /**
     * Sends the queued notification.
     *
     * Responsibility: Sends the queued notification.
     */
    public function handle(): void
    {
        NotificationManager::getInstance()->send($this->notification);
    }

    /**
     * Returns a diagnostic label containing the notification type.
     *
     * Responsibility: Returns a diagnostic label containing the notification type.
     */
    public function displayName(): string
    {
        return 'notification:' . $this->notification->type;
    }

    /**
     * Returns the queue selected for notification delivery.
     *
     * Responsibility: Returns the queue selected for notification delivery.
     */
    public function queueName(): string
    {
        return $this->queueNameOverride;
    }

    /**
     * Returns the allowed delivery-attempt count.
     *
     * Responsibility: Returns the allowed delivery-attempt count.
     */
    public function maxAttempts(): int
    {
        return 5;
    }

    /**
     * Returns the retry delay for failed notification delivery.
     *
     * Responsibility: Returns the retry delay for failed notification delivery.
     */
    public function backoffSeconds(): int
    {
        return 30;
    }

    /**
     * Exports notification state for queue persistence.
     *
     * Responsibility: Exports notification state for queue persistence.
     * @return array<string, mixed>
     */
    public function toPayload(): array
    {
        return [
            'notification' => $this->notification->toArray(),
            'queue_name' => $this->queueNameOverride,
        ];
    }

    /**
     * Restores a notification-delivery job from persisted state.
     *
     * @param array<string, mixed> $payload
     */
    public static function fromPayload(array $payload): static
    {
        $notification = NotificationDispatch::fromArray(
            isset($payload['notification']) && is_array($payload['notification']) ? $payload['notification'] : []
        );

        return new self(
            notification: $notification,
            queueNameOverride: trim((string) ($payload['queue_name'] ?? 'notifications')) ?: 'notifications'
        );
    }
}
