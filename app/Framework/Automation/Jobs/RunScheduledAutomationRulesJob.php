<?php

declare(strict_types=1);

namespace Catalyst\Framework\Automation\Jobs;

use Catalyst\Framework\Automation\AutomationManager;
use Catalyst\Framework\Queue\QueueableJobInterface;

final class RunScheduledAutomationRulesJob implements QueueableJobInterface
{
    public function handle(): void
    {
        AutomationManager::getInstance()->runDueSchedules();
    }

    public function displayName(): string
    {
        return 'Run scheduled automation rules';
    }

    public function queueName(): string
    {
        return 'automation';
    }

    public function maxAttempts(): int
    {
        return 3;
    }

    public function backoffSeconds(): int
    {
        return 60;
    }

    /**
     * @return array<string, mixed>
     */
    public function toPayload(): array
    {
        return [];
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromPayload(array $payload): static
    {
        return new static();
    }
}
