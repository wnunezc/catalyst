<?php

declare(strict_types=1);

namespace Catalyst\Framework\Cli\Commands;

use Catalyst\Framework\Argument\ArgumentBag;
use Catalyst\Framework\Cli\AbstractCommand;

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

    public function getName(): string
    {
        return 'quality:check';
    }

    public function getDescription(): string
    {
        return 'Run the standard local Composer, routing, structural, security and status checks';
    }

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
