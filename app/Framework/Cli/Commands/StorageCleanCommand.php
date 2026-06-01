<?php

declare(strict_types=1);

namespace Catalyst\Framework\Cli\Commands;

use Catalyst\Framework\Argument\ArgumentBag;
use Catalyst\Framework\Argument\Option;
use Catalyst\Framework\Cli\AbstractCommand;
use Catalyst\Helpers\Path\ProjectPath;

class StorageCleanCommand extends AbstractCommand
{
    public function getName(): string
    {
        return 'storage:clean';
    }

    public function getDescription(): string
    {
        return 'Remove route cache and runtime storage artifacts under boot-core/storage';
    }

    /** @return Option[] */
    public function getOptions(): array
    {
        return [
            new Option(null, 'dry-run', false, false, 'List files that would be removed without deleting them', false),
        ];
    }

    public function execute(ArgumentBag $args): int
    {
        $dryRun  = (bool) ($args->getOptionValue('dry-run') ?? false);
        $targets = $this->collectTargets();

        $this->line('');
        $this->info($dryRun ? 'Storage Clean (dry-run)' : 'Storage Clean');
        $this->line(str_repeat('-', 70));

        if ($targets === []) {
            $this->warn('No runtime artifacts were found.');
            $this->line('');
            return 0;
        }

        foreach ($targets as $target) {
            if ($dryRun) {
                $this->line('  [would remove] ' . $this->relativePath($target));
                continue;
            }

            if (@unlink($target)) {
                $this->line('  [removed] ' . $this->relativePath($target));
                continue;
            }

            $this->error('Failed to remove ' . $this->relativePath($target));
            return 1;
        }

        $this->line(str_repeat('-', 70));
        $this->success(sprintf(
            $dryRun ? '%d artifact(s) queued for cleanup.' : '%d artifact(s) removed.',
            count($targets)
        ));
        $this->line('');

        return 0;
    }

    /**
     * @return string[]
     */
    private function collectTargets(): array
    {
        $targets = [];

        foreach ([
            ProjectPath::routeCacheFile(),
            implode(DS, [PD, 'boot-core', 'storage', 'ws-boot.stamp']),
            implode(DS, [PD, 'boot-core', 'storage', 'ws-server.pid']),
        ] as $file) {
            if (is_file($file)) {
                $targets[] = $file;
            }
        }

        foreach ([
            implode(DS, [PD, 'boot-core', 'storage', 'throttle']),
            implode(DS, [PD, 'boot-core', 'storage', 'logs']),
        ] as $directory) {
            if (!is_dir($directory)) {
                continue;
            }

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                if ($file instanceof \SplFileInfo && $file->isFile()) {
                    $targets[] = $file->getPathname();
                }
            }
        }

        sort($targets);

        return $targets;
    }

    private function relativePath(string $path): string
    {
        $prefix = rtrim(PD, '\\/') . DS;

        return str_starts_with($path, $prefix)
            ? substr($path, strlen($prefix))
            : $path;
    }
}
