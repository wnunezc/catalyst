<?php

declare(strict_types=1);

namespace Catalyst\Framework\Queue\Jobs;

use Catalyst\Entities\NotificationDispatch;
use Catalyst\Framework\Notification\NotificationManager;
use Catalyst\Framework\Queue\QueueableJobInterface;

final class DispatchNotificationJob implements QueueableJobInterface
{
    public function __construct(
        private readonly NotificationDispatch $notification,
        private readonly string $queueNameOverride = 'notifications'
    ) {
    }

    public function handle(): void
    {
        NotificationManager::getInstance()->send($this->notification);
    }

    public function displayName(): string
    {
        return 'notification:' . $this->notification->type;
    }

    public function queueName(): string
    {
        return $this->queueNameOverride;
    }

    public function maxAttempts(): int
    {
        return 5;
    }

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
