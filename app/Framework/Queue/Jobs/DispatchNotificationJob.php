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
 * Defines the Dispatch Notification Job class contract.
 *
 * @package Catalyst\Framework\Queue\Jobs
 * Responsibility: Coordinates the dispatch notification job behavior within its module boundary.
 */
final class DispatchNotificationJob implements QueueableJobInterface
{
    /**
     * Initializes the Dispatch Notification Job instance.
     */
    public function __construct(
        private readonly NotificationDispatch $notification,
        private readonly string $queueNameOverride = 'notifications'
    ) {
    }

    /**
     * Handles the request workflow.
     */
    public function handle(): void
    {
        NotificationManager::getInstance()->send($this->notification);
    }

    /**
     * Handles the display name workflow.
     */
    public function displayName(): string
    {
        return 'notification:' . $this->notification->type;
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
        return 5;
    }

    /**
     * Handles the backoff seconds workflow.
     */
    public function backoffSeconds(): int
    {
        return 30;
    }

    /**
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
