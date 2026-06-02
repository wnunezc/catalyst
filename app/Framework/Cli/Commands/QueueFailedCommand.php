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
use Catalyst\Framework\Cli\AbstractCommand;
use Catalyst\Framework\Queue\QueueRepository;

/**
 * queue:failed CLI command.
 *
 * Responsibility: Runs the queue:failed command to List failed jobs persisted by the framework queue.
 *
 * @package Catalyst\Framework\Cli\Commands
 */
final class QueueFailedCommand extends AbstractCommand
{
    /**
     * Defines the accepted option schema for this command.
     *
     * Responsibility: Defines the accepted option schema for this command.
     * @return Option[]
     */
    public function getOptions(): array
    {
        return [
            new Option('l', 'limit', 20, false, 'Maximum failed jobs to show', true),
            new Option('q', 'queue', null, false, 'Filter by queue name', true),
            new Option(null, 'json', false, false, 'Render failed jobs as JSON', false),
        ];
    }

    /**
     * Returns the command name registered in the CLI registry.
     *
     * Responsibility: Returns the command name registered in the CLI registry.
     */
    public function getName(): string
    {
        return 'queue:failed';
    }

    /**
     * Returns the short help text shown for this command.
     *
     * Responsibility: Returns the short help text shown for this command.
     */
    public function getDescription(): string
    {
        return 'List failed jobs persisted by the framework queue';
    }

    /**
     * Runs the command workflow using parsed CLI arguments.
     *
     * Responsibility: Runs the command workflow using parsed CLI arguments.
     */
    public function execute(ArgumentBag $args): int
    {
        $limit = max(1, (int) ($args->getOptionValue('limit') ?? $args->getOptionValue('l') ?? 20));
        $queue = trim((string) ($args->getOptionValue('queue') ?? $args->getOptionValue('q') ?? ''));
        $asJson = (bool) ($args->getOptionValue('json') ?? false);
        $rows = QueueRepository::getInstance()->listFailed($limit, $queue !== '' ? $queue : null);

        if ($asJson) {
            $this->line((string) json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            return 0;
        }

        $this->line('');
        $this->info('Failed Jobs');
        $this->line(str_repeat('-', 120));
        $this->line(sprintf('  %-6s %-18s %-28s %-10s %-20s %s', 'ID', 'Queue', 'Display', 'Attempts', 'Failed At', 'Error'));
        $this->line(str_repeat('-', 120));

        if ($rows === []) {
            $this->warn('No failed jobs found.');
            $this->line('');
            return 0;
        }

        foreach ($rows as $row) {
            $this->line(sprintf(
                '  %-6s %-18s %-28s %-10s %-20s %s',
                (string) ($row['id'] ?? '-'),
                (string) ($row['queue_name'] ?? '-'),
                mb_strimwidth((string) ($row['display_name'] ?? '-'), 0, 28, '...'),
                sprintf('%d/%d', (int) ($row['attempts'] ?? 0), (int) ($row['max_attempts'] ?? 0)),
                (string) ($row['failed_at'] ?? '-'),
                mb_strimwidth((string) ($row['error_message'] ?? ''), 0, 40, '...')
            ));
        }

        $this->line(str_repeat('-', 120));
        $this->success(sprintf('%d failed job(s) listed.', count($rows)));
        $this->line('');

        return 0;
    }
}
