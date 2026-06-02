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

namespace Catalyst\Framework\Queue;

use Catalyst\Framework\Event\EventBus;
use Catalyst\Framework\Traits\SingletonTrait;
use DateTimeImmutable;
use DateTimeZone;

/**
 * Defines the Queue Manager class contract.
 *
 * @package Catalyst\Framework\Queue
 * Responsibility: Coordinates the queue manager behavior within its module boundary.
 */
final class QueueManager
{
    use SingletonTrait;

    /**
     * Dispatches the requested operation.
     */
    public function dispatch(
        QueueableJobInterface $job,
        ?string $queueName = null,
        int $delaySeconds = 0
    ): int {
        $serialized = QueueJobSerializer::encode($job);
        $queue = $queueName !== null && trim($queueName) !== ''
            ? trim($queueName)
            : $job->queueName();
        $availableAt = new DateTimeImmutable('now', new DateTimeZone('UTC'));

        if ($delaySeconds > 0) {
            $availableAt = $availableAt->modify('+' . $delaySeconds . ' seconds');
        }

        $jobId = QueueRepository::getInstance()->enqueue(
            queueName: $queue,
            jobClass: $serialized['job_class'],
            displayName: $serialized['display_name'],
            payload: $serialized['payload'],
            maxAttempts: (int) $serialized['max_attempts'],
            availableAt: $availableAt
        );

        EventBus::getInstance()->dispatch('framework.queue.job-dispatched', [
            'job_id' => $jobId,
            'queue' => $queue,
            'job_class' => $serialized['job_class'],
            'display_name' => $serialized['display_name'],
            'delay_seconds' => max(0, $delaySeconds),
        ]);

        return $jobId;
    }
}
