<?php

declare(strict_types=1);

namespace Catalyst\Framework\Queue;

use Catalyst\Framework\Event\EventBus;
use Throwable;

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
