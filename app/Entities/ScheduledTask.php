<?php

declare(strict_types=1);

namespace Catalyst\Entities;

use Catalyst\Framework\Queue\QueueJobSerializer;
use Catalyst\Framework\Queue\QueueableJobInterface;

final class ScheduledTask
{
    /**
     * @param array<string, mixed> $jobPayload
     */
    public function __construct(
        public readonly string $name,
        public readonly string $expression,
        public readonly string $jobClass,
        public readonly array $jobPayload = [],
        public readonly string $queueName = 'default',
        public readonly string $description = ''
    ) {
    }

    public static function queuedJob(
        string $name,
        string $expression,
        QueueableJobInterface $job,
        string $description = ''
    ): self {
        $serialized = QueueJobSerializer::encode($job);

        return new self(
            name: $name,
            expression: $expression,
            jobClass: $serialized['job_class'],
            jobPayload: $serialized['payload'],
            queueName: $job->queueName(),
            description: $description
        );
    }

    public function makeJob(): QueueableJobInterface
    {
        return QueueJobSerializer::decode($this->jobClass, $this->jobPayload);
    }
}
