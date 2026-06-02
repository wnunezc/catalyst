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
use Catalyst\Framework\Database\DatabaseManager;
use Catalyst\Framework\Temporal\EffectiveWindow;
use Throwable;

/**
 * temporal:smoke CLI command.
 *
 * Responsibility: Runs the temporal:smoke command to Exercise canonical PA-04 temporal states and reusable validity SQL filters.
 *
 * @package Catalyst\Framework\Cli\Commands
 */
final class TemporalSmokeCommand extends AbstractCommand
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
            new Option(null, 'json', false, false, 'Render as JSON', false),
        ];
    }

    /**
     * Returns the command name registered in the CLI registry.
     *
     * Responsibility: Returns the command name registered in the CLI registry.
     */
    public function getName(): string
    {
        return 'temporal:smoke';
    }

    /**
     * Returns the short help text shown for this command.
     *
     * Responsibility: Returns the short help text shown for this command.
     */
    public function getDescription(): string
    {
        return 'Exercise canonical PA-04 temporal states and reusable validity SQL filters';
    }

    /**
     * Runs the command workflow using parsed CLI arguments.
     *
     * Responsibility: Runs the command workflow using parsed CLI arguments.
     */
    public function execute(ArgumentBag $args): int
    {
        $json = (bool) ($args->getOptionValue('json') ?? false);
        $window = EffectiveWindow::getInstance();
        $result = [
            'success' => false,
            'steps' => [],
        ];

        try {
            $result['steps'][] = [
                'step' => 'state-active',
                'status' => $window->state(gmdate('Y-m-d H:i:s', time() - 3600), gmdate('Y-m-d H:i:s', time() + 3600)) === EffectiveWindow::STATE_ACTIVE
                    ? 'ok'
                    : 'failed',
            ];
            $result['steps'][] = [
                'step' => 'state-scheduled',
                'status' => $window->state(gmdate('Y-m-d H:i:s', time() + 3600), null) === EffectiveWindow::STATE_SCHEDULED
                    ? 'ok'
                    : 'failed',
            ];
            $result['steps'][] = [
                'step' => 'state-expired',
                'status' => $window->state(null, gmdate('Y-m-d H:i:s', time() - 3600)) === EffectiveWindow::STATE_EXPIRED
                    ? 'ok'
                    : 'failed',
            ];

            $connection = DatabaseManager::getInstance()->connection();
            $result['steps'][] = [
                'step' => 'sql-active-alias',
                'status' => $this->probeSqlState(
                    $connection,
                    $window->sqlForState(EffectiveWindow::STATE_ACTIVE, 'probe.valid_from', 'probe.valid_to'),
                    'DATE_SUB(UTC_TIMESTAMP(), INTERVAL 1 HOUR)',
                    'DATE_ADD(UTC_TIMESTAMP(), INTERVAL 1 HOUR)'
                ) ? 'ok' : 'failed',
            ];
            $result['steps'][] = [
                'step' => 'sql-scheduled-alias',
                'status' => $this->probeSqlState(
                    $connection,
                    $window->sqlForState(EffectiveWindow::STATE_SCHEDULED, 'probe.valid_from', 'probe.valid_to'),
                    'DATE_ADD(UTC_TIMESTAMP(), INTERVAL 1 HOUR)',
                    'NULL'
                ) ? 'ok' : 'failed',
            ];
            $result['steps'][] = [
                'step' => 'sql-expired-alias',
                'status' => $this->probeSqlState(
                    $connection,
                    $window->sqlForState(EffectiveWindow::STATE_EXPIRED, 'probe.valid_from', 'probe.valid_to'),
                    'NULL',
                    'DATE_SUB(UTC_TIMESTAMP(), INTERVAL 1 HOUR)'
                ) ? 'ok' : 'failed',
            ];

            $decorated = $window->decorate([
                'valid_from' => gmdate('Y-m-d H:i:s', time() + 1800),
                'valid_to' => null,
            ]);

            $result['steps'][] = [
                'step' => 'decorate-row',
                'status' => ($decorated['temporal_state'] ?? null) === EffectiveWindow::STATE_SCHEDULED ? 'ok' : 'failed',
            ];

            $result['success'] = !in_array('failed', array_column($result['steps'], 'status'), true);
        } catch (Throwable $e) {
            $result['error'] = $e->getMessage();
        }

        if ($json) {
            $this->line((string) json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return !empty($result['success']) ? 0 : 1;
        }

        $this->line('');
        $this->info('Temporal Smoke');
        $this->line('');

        foreach ((array) ($result['steps'] ?? []) as $step) {
            $this->line(sprintf(
                '  %-24s %-8s',
                (string) ($step['step'] ?? 'step'),
                strtoupper((string) ($step['status'] ?? 'unknown'))
            ));
        }

        $this->line('');

        if (!empty($result['success'])) {
            $this->success('Temporal smoke passed.');

            return 0;
        }

        $this->error((string) ($result['error'] ?? 'Temporal smoke failed.'));

        return 1;
    }

    /**
     * Describes the probe sql state helper responsibility inside the CLI component.
     *
     * Responsibility: Supports the probe sql state helper workflow used by this CLI component.
     */
    private function probeSqlState(
        \Catalyst\Framework\Database\Connection $connection,
        string $whereSql,
        string $validFromExpression,
        string $validToExpression
    ): bool {
        $row = $connection->selectOne(
            'SELECT COUNT(*) AS aggregate
             FROM (
                SELECT ' . $validFromExpression . ' AS valid_from, ' . $validToExpression . ' AS valid_to
             ) probe
             WHERE ' . $whereSql
        );

        return (int) ($row['aggregate'] ?? 0) === 1;
    }
}
