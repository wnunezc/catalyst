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

use RuntimeException;

/**
 * Serializes queue jobs into persistence descriptors and restores them safely.
 *
 * @package Catalyst\Framework\Queue
 * Responsibility: Converts queueable jobs between runtime objects and repository payloads while enforcing their contract.
 */
final class QueueJobSerializer
{
    /**
     * Encodes a queueable job into the fields stored by the queue repository.
     *
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
     * Restores a queueable job after validating its persisted class.
     *
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
