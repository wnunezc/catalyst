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
use Catalyst\Framework\Cli\AbstractCommand;

/**
 * quality:check CLI command.
 *
 * Responsibility: Runs the quality:check command to Run the standard local Composer, routing, structural, security and status checks.
 *
 * @package Catalyst\Framework\Cli\Commands
 */
final class QualityCheckCommand extends AbstractCommand
{
    /**
     * @var list<array{label:string,command:string,blocker:bool}>
     */
    private const CHECKS = [
        [
            'label' => 'Composer manifest',
            'command' => 'composer validate --strict',
            'blocker' => true,
        ],
        [
            'label' => 'Composer advisory audit',
            'command' => 'composer audit',
            'blocker' => true,
        ],
        [
            'label' => 'Route contract lint',
            'command' => 'php public/cli.php route:lint',
            'blocker' => true,
        ],
        [
            'label' => 'Framework structural lint',
            'command' => 'php public/cli.php inspect:lint',
            'blocker' => true,
        ],
        [
            'label' => 'Security regression scan',
            'command' => 'php public/cli.php security:check',
            'blocker' => true,
        ],
        [
            'label' => 'Runtime status',
            'command' => 'php public/cli.php status',
            'blocker' => false,
        ],
    ];

    /**
     * Returns the command name registered in the CLI registry.
     *
     * Responsibility: Returns the command name registered in the CLI registry.
     */
    public function getName(): string
    {
        return 'quality:check';
    }

    /**
     * Returns the short help text shown for this command.
     *
     * Responsibility: Returns the short help text shown for this command.
     */
    public function getDescription(): string
    {
        return 'Run the standard local Composer, routing, structural, security and status checks';
    }

    /**
     * Runs the command workflow using parsed CLI arguments.
     *
     * Responsibility: Runs the command workflow using parsed CLI arguments.
     */
    public function execute(ArgumentBag $args): int
    {
        $this->line('');
        $this->info('Catalyst Quality Gate');
        $this->line(str_repeat('-', 72));

        $failedBlockers = 0;
        $failedWarnings = 0;

        foreach (self::CHECKS as $check) {
            $this->line('');
            $this->info(sprintf('[%s] %s', $check['blocker'] ? 'BLOCKER' : 'WARN-ONLY', $check['label']));
            $this->line('> ' . $check['command']);
            $exitCode = $this->runShellCommand($check['command']);

            if ($exitCode === 0) {
                $this->success('Result: PASS');
                continue;
            }

            if ($check['blocker']) {
                $failedBlockers++;
                $this->error('Result: FAIL');
                continue;
            }

            $failedWarnings++;
            $this->warn('Result: WARN');
        }

        $this->line('');
        $this->line(str_repeat('-', 72));

        if ($failedBlockers > 0) {
            $this->error(sprintf('Quality gate failed: %d blocker check(s) failed.', $failedBlockers));
            $this->line('');
            return 1;
        }

        if ($failedWarnings > 0) {
            $this->warn(sprintf('Quality gate passed with %d warning-only check(s).', $failedWarnings));
            $this->line('');
            return 0;
        }

        $this->success('Quality gate passed.');
        $this->line('');

        return 0;
    }

    /**
     * Describes the run shell command helper responsibility inside the CLI component.
     *
     * Responsibility: Supports the run shell command helper workflow used by this CLI component.
     */
    private function runShellCommand(string $command): int
    {
        $previousDirectory = getcwd();

        if ($previousDirectory !== false) {
            chdir(PD);
        }

        passthru($command, $exitCode);

        if ($previousDirectory !== false) {
            chdir($previousDirectory);
        }

        return (int) $exitCode;
    }
}
