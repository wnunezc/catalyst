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
 * Defines the Queueable Job Interface interface contract.
 *
 * @package Catalyst\Framework\Queue
 * Responsibility: Coordinates the queueable job interface behavior within its module boundary.
 */
interface QueueableJobInterface
{
    /**
     * Handles the request workflow.
     */
    public function handle(): void;

    /**
     * Handles the display name workflow.
     */
    public function displayName(): string;

    /**
     * Handles the queue name workflow.
     */
    public function queueName(): string;

    /**
     * Handles the max attempts workflow.
     */
    public function maxAttempts(): int;

    /**
     * Handles the backoff seconds workflow.
     */
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
