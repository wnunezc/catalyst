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

namespace Catalyst\Entities;

use Catalyst\Framework\Queue\QueueJobSerializer;
use Catalyst\Framework\Queue\QueueableJobInterface;

/**
 * Value object for scheduled queue task definitions.
 *
 * @package Catalyst\Entities
 * Responsibility: Carries cron expression, queue target, serialized job payload, and human-readable task metadata.
 */
final class ScheduledTask
{
    /**
     * Initializes an immutable scheduled task definition.
     *
     * Responsibility: Initializes an immutable scheduled task definition.
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

    /**
     * Creates a scheduled task from a queueable job instance.
     */
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

    /**
     * Rehydrates the queueable job represented by this scheduled task.
     *
     * Responsibility: Rehydrates the queueable job represented by this scheduled task.
     */
    public function makeJob(): QueueableJobInterface
    {
        return QueueJobSerializer::decode($this->jobClass, $this->jobPayload);
    }
}
