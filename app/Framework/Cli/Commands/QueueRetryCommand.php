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

namespace Catalyst\Framework\Cli\Commands;

use Catalyst\Framework\Argument\ArgumentBag;
use Catalyst\Framework\Argument\Option;
use Catalyst\Framework\Argument\Parameter;
use Catalyst\Framework\Cli\AbstractCommand;
use Catalyst\Framework\Queue\QueueRepository;

/**
 * Defines the Queue Retry Command class contract.
 *
 * @package Catalyst\Framework\Cli\Commands
 * Responsibility: Coordinates the queue retry command behavior within its module boundary.
 */
final class QueueRetryCommand extends AbstractCommand
{
    /** @return Option[] */
    public function getOptions(): array
    {
        return [
            new Option(null, 'all', false, false, 'Retry all failed jobs', false),
        ];
    }

    /** @return Parameter[] */
    public function getParameters(): array
    {
        return [
            new Parameter(0, null, false, null, 'FailedJobId', 'Failed job ID to retry'),
        ];
    }

    /**
     * Returns the name value.
     */
    public function getName(): string
    {
        return 'queue:retry';
    }

    /**
     * Returns the description value.
     */
    public function getDescription(): string
    {
        return 'Retry one failed job or all failed jobs';
    }

    /**
     * Executes the service workflow.
     */
    public function execute(ArgumentBag $args): int
    {
        $retryAll = (bool) ($args->getOptionValue('all') ?? false);
        $failedId = $args->getParameterValue(0);

        if (!$retryAll && ($failedId === null || trim((string) $failedId) === '')) {
            $this->error('Provide a failed job ID or pass --all.');
            return 1;
        }

        $requeued = QueueRepository::getInstance()->retryFailed(
            $retryAll ? null : (int) $failedId
        );

        $this->line('');

        if ($requeued === []) {
            $this->warn('No failed jobs were retried.');
            $this->line('');
            return 0;
        }

        $this->success(sprintf('%d failed job(s) re-queued.', count($requeued)));
        $this->line('New queue job IDs: ' . implode(', ', $requeued));
        $this->line('');

        return 0;
    }
}
