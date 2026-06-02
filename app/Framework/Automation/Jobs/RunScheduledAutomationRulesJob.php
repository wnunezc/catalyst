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

namespace Catalyst\Framework\Automation\Jobs;

use Catalyst\Framework\Automation\AutomationManager;
use Catalyst\Framework\Queue\QueueableJobInterface;

/**
 * Defines the Run Scheduled Automation Rules Job class contract.
 *
 * @package Catalyst\Framework\Automation\Jobs
 * Responsibility: Coordinates the run scheduled automation rules job behavior within its module boundary.
 */
final class RunScheduledAutomationRulesJob implements QueueableJobInterface
{
    /**
     * Handles the request workflow.
     */
    public function handle(): void
    {
        AutomationManager::getInstance()->runDueSchedules();
    }

    /**
     * Handles the display name workflow.
     */
    public function displayName(): string
    {
        return 'Run scheduled automation rules';
    }

    /**
     * Handles the queue name workflow.
     */
    public function queueName(): string
    {
        return 'automation';
    }

    /**
     * Handles the max attempts workflow.
     */
    public function maxAttempts(): int
    {
        return 3;
    }

    /**
     * Handles the backoff seconds workflow.
     */
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
