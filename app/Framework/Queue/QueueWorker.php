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
use Throwable;

/**
 * Defines the Queue Worker class contract.
 *
 * @package Catalyst\Framework\Queue
 * Responsibility: Coordinates the queue worker behavior within its module boundary.
 */
final class QueueWorker
{
    /**
     * @return array{status:string,job_id?:int,display_name?:string,failed_job_id?:int,message?:string}
     */
    public function processNext(?string $queueName = null): array
    {
        $record = QueueRepository::getInstance()->reserveNext($queueName);

        if ($record === null) {
            return [
                'status' => 'idle',
                'message' => 'No queued jobs are ready.',
            ];
        }

        try {
            $job = QueueJobSerializer::decode($record->jobClass, $record->payload);
            $job->handle();

            QueueRepository::getInstance()->complete($record->id);

            EventBus::getInstance()->dispatch('framework.queue.job-processed', [
                'job_id' => $record->id,
                'queue' => $record->queueName,
                'job_class' => $record->jobClass,
                'display_name' => $record->displayName,
                'attempts' => $record->attempts,
            ]);

            return [
                'status' => 'processed',
                'job_id' => $record->id,
                'display_name' => $record->displayName,
            ];
        } catch (Throwable $e) {
            $error = $e->getMessage();

            if ($record->attempts >= $record->maxAttempts) {
                $failedJobId = QueueRepository::getInstance()->moveToFailed($record, $error);

                EventBus::getInstance()->dispatch('framework.queue.job-failed', [
                    'job_id' => $record->id,
                    'failed_job_id' => $failedJobId,
                    'queue' => $record->queueName,
                    'job_class' => $record->jobClass,
                    'display_name' => $record->displayName,
                    'attempts' => $record->attempts,
                    'error' => $error,
                ]);

                return [
                    'status' => 'failed',
                    'job_id' => $record->id,
                    'failed_job_id' => $failedJobId,
                    'display_name' => $record->displayName,
                    'message' => $error,
                ];
            }

            $job = QueueJobSerializer::decode($record->jobClass, $record->payload);
            QueueRepository::getInstance()->releaseForRetry($record, $error, $job->backoffSeconds());

            EventBus::getInstance()->dispatch('framework.queue.job-released', [
                'job_id' => $record->id,
                'queue' => $record->queueName,
                'job_class' => $record->jobClass,
                'display_name' => $record->displayName,
                'attempts' => $record->attempts,
                'error' => $error,
            ]);

            return [
                'status' => 'released',
                'job_id' => $record->id,
                'display_name' => $record->displayName,
                'message' => $error,
            ];
        }
    }
}
