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
use Catalyst\Framework\Module\ModuleLinter;

/**
 * inspect:lint CLI command.
 *
 * Responsibility: Runs the inspect:lint command to Run structural framework lint on modules, registries, guards and work assets.
 *
 * @package Catalyst\Framework\Cli\Commands
 */
final class InspectLintCommand extends AbstractCommand
{
    /**
     * Returns the command name registered in the CLI registry.
     *
     * Responsibility: Returns the command name registered in the CLI registry.
     */
    public function getName(): string
    {
        return 'inspect:lint';
    }

    /**
     * Returns the short help text shown for this command.
     *
     * Responsibility: Returns the short help text shown for this command.
     */
    public function getDescription(): string
    {
        return 'Run structural framework lint on modules, registries, guards and work assets';
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
            new Option(null, 'json', false, false, 'Render lint results as JSON', false),
        ];
    }

    /**
     * Runs the command workflow using parsed CLI arguments.
     *
     * Responsibility: Runs the command workflow using parsed CLI arguments.
     */
    public function execute(ArgumentBag $args): int
    {
        $report = (new ModuleLinter())->lint();
        $asJson = (bool) ($args->getOptionValue('json') ?? false);

        if ($asJson) {
            $this->line((string) json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            return $report['ok'] ? 0 : 1;
        }

        $this->line('');
        $this->info('Framework Structural Lint');
        $this->line(str_repeat('-', 90));

        foreach ((array) ($report['checks'] ?? []) as $name => $summary) {
            $this->line(sprintf(
                '  %-28s %s (%d checked)',
                ucwords(str_replace('_', ' ', (string) $name)),
                ($summary['ok'] ?? false) ? 'OK' : 'ISSUES',
                (int) ($summary['checked'] ?? 0)
            ));
        }

        $this->line(str_repeat('-', 90));

        if ($report['ok']) {
            $this->success('Structural lint is coherent.');
            $this->line('');
            return 0;
        }

        $this->error('Structural issues detected: ' . (int) ($report['issue_count'] ?? 0));
        foreach ((array) ($report['issues'] ?? []) as $issue) {
            $module = isset($issue['module']) && $issue['module'] !== null
                ? '[' . $issue['module'] . '] '
                : '';
            $this->line(sprintf(
                '  [%s] %s%s',
                (string) ($issue['type'] ?? 'issue'),
                $module,
                (string) ($issue['message'] ?? '')
            ));
        }

        $this->line('');

        return 1;
    }
}
