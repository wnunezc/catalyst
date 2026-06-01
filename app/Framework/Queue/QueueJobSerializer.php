<?php

declare(strict_types=1);

namespace Catalyst\Framework\Queue;

use RuntimeException;

final class QueueJobSerializer
{
    /**
     * @return array{job_class:string,display_name:string,payload:array<string, mixed>,max_attempts:int}
     */
    public static function encode(QueueableJobInterface $job): array
    {
        return [
            'job_class' => $job::class,
            'display_name' => $job->displayName(),
            'payload' => $job->toPayload(),
            'max_attempts' => max(1, $job->maxAttempts()),
        ];
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function decode(string $jobClass, array $payload): QueueableJobInterface
    {
        if (!class_exists($jobClass)) {
            throw new RuntimeException("Queued job class '{$jobClass}' does not exist.");
        }

        if (!is_a($jobClass, QueueableJobInterface::class, true)) {
            throw new RuntimeException("Queued job class '{$jobClass}' must implement " . QueueableJobInterface::class . '.');
        }

        /** @var class-string<QueueableJobInterface> $jobClass */
        return $jobClass::fromPayload($payload);
    }
}
