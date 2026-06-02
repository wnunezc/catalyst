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
use Catalyst\Framework\Health\HealthReportBuilder;

/**
 * Reports framework runtime readiness from the terminal.
 *
 * Responsibility: Builds and renders system health sections for human or JSON CLI status checks.
 *
 * @package Catalyst\Framework\Cli\Commands
 */
class StatusCommand extends AbstractCommand
{
    /**
     * Initializes dependencies required by this CLI component.
     *
     * Responsibility: Initializes dependencies required by this CLI component.
     */
    public function __construct(
        private readonly HealthReportBuilder $reportBuilder = new HealthReportBuilder()
    ) {
    }

    /**
     * Defines the accepted option schema for this command.
     *
     * Responsibility: Defines the accepted option schema for this command.
     * @return Option[]
     */
    public function getOptions(): array
    {
        return [
            new Option(null, 'json', false, false, 'Render status as JSON', false),
        ];
    }

    /**
     * Returns the command name registered in the CLI registry.
     *
     * Responsibility: Returns the command name registered in the CLI registry.
     */
    public function getName(): string
    {
        return 'status';
    }

    /**
     * Returns the short help text shown for this command.
     *
     * Responsibility: Returns the short help text shown for this command.
     */
    public function getDescription(): string
    {
        return 'Show system health checks';
    }

    /**
     * Runs the command workflow using parsed CLI arguments.
     *
     * Responsibility: Runs the command workflow using parsed CLI arguments.
     */
    public function execute(ArgumentBag $args): int
    {
        $asJson = (bool) ($args->getOptionValue('json') ?? false);
        $status = $this->reportBuilder->build();

        if ($asJson) {
            $this->line((string) json_encode($status, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            return $status['ok'] ? 0 : 1;
        }

        $this->line('');
        $this->info('System Status');
        $this->line(str_repeat('-', 50));

        foreach ($status['core'] as $check) {
            $this->printCheck($check['label'], $check['status'] === 'ok');
        }

        $this->line(str_repeat('-', 50));

        foreach (['runtime', 'platform', 'session', 'cache', 'queue', 'scheduler', 'storage', 'secrets', 'throttling'] as $sectionName) {
            $this->renderSection($sectionName, $status[$sectionName]);
        }

        $this->line('');
        $this->info('Route Contract');
        $this->line(str_repeat('-', 50));

        if ($status['route_contract']['ok']) {
            $this->success('Route/slugs/assets contract is coherent.');
        } else {
            $this->error('Route/slugs/assets contract issues: ' . $status['route_contract']['issue_count']);
            foreach ($status['route_contract']['issues'] as $issue) {
                $this->line(sprintf('  [%s] %s', $issue['type'], $issue['message']));
            }
        }

        $this->line(str_repeat('-', 50));

        if ($status['ready']) {
            $this->success('Overall: Ready ✓');
        } elseif ($status['ok']) {
            $this->warn('Overall: Live but not ready');
        } else {
            $this->error('Overall: Issues detected ✗');
        }

        $this->line('');

        return $status['ok'] ? 0 : 1;
    }

    /**
     * Describes the print check helper responsibility inside the CLI component.
     *
     * Responsibility: Supports the print check helper workflow used by this CLI component.
     */
    private function printCheck(string $label, bool $ok): void
    {
        $mark = $ok ? '✓' : '✗';
        if ($ok) {
            $this->line(sprintf('  %-42s %s', $label, $mark));
            return;
        }

        $this->error(sprintf('  %-42s %s', $label, $mark));
    }

    /**
     * Describes the render section helper responsibility inside the CLI component.
     *
     * Responsibility: Supports the render section helper workflow used by this CLI component.
     */
    private function renderSection(string $title, array $checks): void
    {
        $this->line('');
        $this->info(ucfirst($title));
        $this->line(str_repeat('-', 50));

        foreach ($checks as $check) {
            $detail = isset($check['detail']) && $check['detail'] !== ''
                ? ' — ' . $check['detail']
                : '';

            $line = sprintf('  %-26s %s%s', $check['label'], strtoupper($check['status']), $detail);

            if ($check['status'] === 'fail') {
                $this->error($line);
                continue;
            }

            if ($check['status'] === 'warn') {
                $this->warn($line);
                continue;
            }

            $this->line($line);
        }
    }
}
