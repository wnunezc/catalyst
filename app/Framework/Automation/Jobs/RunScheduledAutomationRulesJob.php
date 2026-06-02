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
 * Queue job that executes automation rules due on their schedules.
 *
 * @package Catalyst\Framework\Automation\Jobs
 * Responsibility: Delegates scheduled automation execution to the automation manager.
 */
final class RunScheduledAutomationRulesJob implements QueueableJobInterface
{
    /**
     * Executes the due scheduled automation rules.
     *
     * Responsibility: Executes the due scheduled automation rules.
     */
    public function handle(): void
    {
        AutomationManager::getInstance()->runDueSchedules();
    }

    /**
     * Returns the human-readable queue job name.
     *
     * Responsibility: Returns the human-readable queue job name.
     */
    public function displayName(): string
    {
        return 'Run scheduled automation rules';
    }

    /**
     * Selects the automation queue.
     *
     * Responsibility: Selects the automation queue.
     */
    public function queueName(): string
    {
        return 'automation';
    }

    /**
     * Returns the maximum delivery attempts.
     *
     * Responsibility: Returns the maximum delivery attempts.
     */
    public function maxAttempts(): int
    {
        return 3;
    }

    /**
     * Returns the retry delay in seconds.
     *
     * Responsibility: Returns the retry delay in seconds.
     */
    public function backoffSeconds(): int
    {
        return 60;
    }

    /**
     * Serializes the stateless job payload.
     *
     * Responsibility: Serializes the stateless job payload.
     * @return array<string, mixed>
     */
    public function toPayload(): array
    {
        return [];
    }

    /**
     * Recreates the stateless job from its queue payload.
     *
     * @param array<string, mixed> $payload
     */
    public static function fromPayload(array $payload): static
    {
        return new static();
    }
}
