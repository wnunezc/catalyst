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

/**
 * Defines the contract required for jobs executed by the framework queue.
 *
 * @package Catalyst\Framework\Queue
 * Responsibility: Standardizes execution, queue routing, retry policy, and payload serialization for queued work.
 */
interface QueueableJobInterface
{
    /**
     * Executes the queued work.
     *
     * Responsibility: Executes the queued work.
     */
    public function handle(): void;

    /**
     * Returns the diagnostic label shown for the queued job.
     *
     * Responsibility: Returns the diagnostic label shown for the queued job.
     */
    public function displayName(): string;

    /**
     * Returns the queue where the job should be dispatched.
     *
     * Responsibility: Returns the queue where the job should be dispatched.
     */
    public function queueName(): string;

    /**
     * Returns the maximum number of processing attempts.
     *
     * Responsibility: Returns the maximum number of processing attempts.
     */
    public function maxAttempts(): int;

    /**
     * Returns the retry delay in seconds after a failed attempt.
     *
     * Responsibility: Returns the retry delay in seconds after a failed attempt.
     */
    public function backoffSeconds(): int;

    /**
     * Exports the job state required to reconstruct it later.
     *
     * Responsibility: Exports the job state required to reconstruct it later.
     * @return array<string, mixed>
     */
    public function toPayload(): array;

    /**
     * Reconstructs a job from its persisted payload.
     *
     * @param array<string, mixed> $payload
     */
    public static function fromPayload(array $payload): static;
}
