<?php

declare(strict_types=1);

namespace Catalyst\Framework\Queue;

interface QueueableJobInterface
{
    public function handle(): void;

    public function displayName(): string;

    public function queueName(): string;

    public function maxAttempts(): int;

    public function backoffSeconds(): int;

    /**
     * @return array<string, mixed>
     */
    public function toPayload(): array;

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromPayload(array $payload): static;
}
