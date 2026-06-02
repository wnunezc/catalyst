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
use Catalyst\Framework\Cli\Support\RouteContractInspector;

/**
 * Defines the Route Lint Command class contract.
 *
 * @package Catalyst\Framework\Cli\Commands
 * Responsibility: Coordinates the route lint command behavior within its module boundary.
 */
class RouteLintCommand extends AbstractCommand
{
    /**
     * Returns the name value.
     */
    public function getName(): string
    {
        return 'route:lint';
    }

    /**
     * Returns the description value.
     */
    public function getDescription(): string
    {
        return 'Validate route casing, approved aliases and work/{slug} asset publication';
    }

    /** @return Option[] */
    public function getOptions(): array
    {
        return [
            new Option(null, 'json', false, false, 'Render lint results as JSON', false),
        ];
    }

    /**
     * Executes the service workflow.
     */
    public function execute(ArgumentBag $args): int
    {
        $report = (new RouteContractInspector())->inspect();
        $asJson = (bool) ($args->getOptionValue('json') ?? false);

        if ($asJson) {
            $this->line((string) json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            return $report['ok'] ? 0 : 1;
        }

        $this->line('');
        $this->info('Route Contract Lint');
        $this->line(str_repeat('-', 70));

        foreach ($report['checks'] as $name => $summary) {
            $label = str_replace('_', ' ', $name);
            $this->line(sprintf(
                '  %-24s %s (%d checked)',
                ucwords($label),
                $summary['ok'] ? 'OK' : 'ISSUES',
                (int) ($summary['checked'] ?? 0)
            ));
        }

        $this->line(str_repeat('-', 70));

        if ($report['ok']) {
            $this->success('Route contract is coherent.');
            $this->line('');
            return 0;
        }

        $this->error('Route contract issues detected: ' . $report['issue_count']);
        foreach ($report['issues'] as $issue) {
            $this->line(sprintf('  [%s] %s', $issue['type'], $issue['message']));
        }

        $this->line('');

        return 1;
    }
}
